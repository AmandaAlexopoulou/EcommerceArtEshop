<?php
// API endpoint to send invoice to an external provider (potential ERP integration for invoicing)
// Reads incoming JSON data and decodes it into a PHP array
$data = json_decode(file_get_contents("php://input"), true);

// Check if the order_id exists in the incoming data
if (!isset($data['order_id'])) {
    http_response_code(400); // Bad request response code
    echo json_encode(["error" => "Missing order ID"]);
    exit;
}

// Connect to the SQLite database
$db = new PDO('sqlite:../db/database.sqlite');

// Fetch order details from the database using the provided order_id
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$data['order_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// If the order is not found, return an error
if (!$order) {
    error_log("Order not found: ID {$data['order_id']}");
    http_response_code(404); // Not found response code
    echo json_encode(["error" => "Order not found"]);
    exit;
}

// Prepare the invoice data to send to the external provider (JSON format)
$invoice = [
    "customer" => [
        "name" => $order['customer_name'],
        "email" => $order['customer_email']
    ],
    "items" => [[
        "title" => $order['item_title'],
        "price" => floatval($order['item_price']),
    ]],
    "total" => floatval($order['item_price']),
    "date" => $order['created_at']
];

// cURL request to send the invoice to the external provider's API
$ch = curl_init("https://api.provider.gr/invoices");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invoice));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer YOUR_API_TOKEN' // Replace with actual API token
]);

// Retrieve the invoice from our system to check its status
$stmt = $db->prepare("SELECT * FROM invoices WHERE order_id = ?");
$stmt->execute([$data['order_id']]);
$invoice_row = $stmt->fetch(PDO::FETCH_ASSOC);

// If no invoice found or it's invalid, return an error
if (!$invoice_row || !$invoice_row['valid']) {
    http_response_code(404); // Not found response code
    echo json_encode(["error" => "Invoice not found or invalid"]);
    exit;
}

// Execute the cURL request to the external provider
$response = curl_exec($ch);
curl_close($ch);

// Decode the response from the provider API
$response_data = json_decode($response, true);

// Timestamp for logging
$now = date("Y-m-d H:i:s");

// If the provider response indicates an error, update the invoice and log it
if (!$response_data || !isset($response_data['status']) || $response_data['status'] !== 'success') {
    $stmt = $db->prepare("UPDATE invoices SET provider_response = ?, status = 'error', timestamp_sent = ? WHERE order_id = ?");
    $stmt->execute([$response, $now, $data['order_id']]);

    // Log the error response
    $stmt = $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, ?, ?, ?)");
    $stmt->execute([$invoice_row['id'], $now, 'error', $response]);

    http_response_code(502); // Bad gateway response code
    echo json_encode([
        "error" => "API Provider returned an error",
        "provider_raw_response" => $response_data ?? $response
    ]);
    exit;
}

// If successful, retrieve the provider's invoice ID
$provider_id = $response_data['invoice_id'] ?? null;

// Update the invoice status to 'sent' and store the provider's invoice ID
$stmt = $db->prepare("UPDATE invoices SET provider_response = ?, provider_invoice_id = ?, status = 'sent', timestamp_sent = ? WHERE order_id = ?");
$stmt->execute([$response, $provider_id, $now, $data['order_id']]);

// Log the success response
$stmt = $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, ?, ?, ?)");
$stmt->execute([$invoice_row['id'], $now, 'success', $response]);

// Return a success message to the client
echo json_encode([
    "message" => "Invoice sent successfully to provider",
    "order_id" => $data['order_id'],
    "provider_invoice_id" => $provider_id,
    "provider_raw_response" => $response_data
]);
