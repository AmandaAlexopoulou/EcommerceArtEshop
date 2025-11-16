<?php
// API endpoint to send invoice to external provider
//δοκιμή για μελλοντική διασύνδεση ERP / παρόχου τιμολόγησης
// Διαβάζει το εισερχόμενο JSON και το μετατρέπει σε πίνακα
$data = json_decode(file_get_contents("php://input"), true);

// Έλεγχος αν υπάρχει το order_id
if (!isset($data['order_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing order ID"]);
    exit;
}

// Σύνδεση με τη βάση SQLite
$db = new PDO('sqlite:../db/database.sqlite');

// Ανάκτηση στοιχείων παραγγελίας
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");//to problhma me ta parastatika mporei na einai stous typous twn arxeiwn 
$stmt->execute([$data['order_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    error_log("Order not found: ID {$data['order_id']}");
    http_response_code(404);
    echo json_encode(["error" => "Order not found"]);
    exit;
}

// Δημιουργία JSON payload για τον πάροχο
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

// cURL αποστολή στο API του provider.gr
$ch = curl_init("https://api.provider.gr/invoices");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invoice));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer YOUR_API_TOKEN'
]);

// Ανάκτηση παραστατικού από το δικό μας σύστημα (invoices)
$stmt = $db->prepare("SELECT * FROM invoices WHERE order_id = ?");
$stmt->execute([$data['order_id']]);
$invoice_row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice_row || !$invoice_row['valid']) {
    http_response_code(404);
    echo json_encode(["error" => "Invoice not found or invalid"]);
    exit;
}

// Εκτέλεση του cURL
$response = curl_exec($ch);
curl_close($ch);

// Μετατροπή σε JSON
$response_data = json_decode($response, true);

// Χρονική σήμανση για logging
$now = date("Y-m-d H:i:s");

// Επεξεργασία αποτυχίας
if (!$response_data || !isset($response_data['status']) || $response_data['status'] !== 'success') {
    // Ενημέρωση πίνακα invoices για αποτυχία
    $stmt = $db->prepare("UPDATE invoices SET provider_response = ?, status = 'error', timestamp_sent = ? WHERE order_id = ?");
    $stmt->execute([$response, $now, $data['order_id']]);

    // Καταγραφή στο ιστορικό
    $stmt = $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, ?, ?, ?)");
    $stmt->execute([$invoice_row['id'], $now, 'error', $response]);

    http_response_code(502);
    echo json_encode([
        "error" => "API Provider returned an error",
        "provider_raw_response" => $response_data ?? $response
    ]);
    exit;
}

// Ανάκτηση του ID από τον provider
$provider_id = $response_data['invoice_id'] ?? null;

// Ενημέρωση πίνακα invoices για επιτυχία
$stmt = $db->prepare("UPDATE invoices SET provider_response = ?, provider_invoice_id = ?, status = 'sent', timestamp_sent = ? WHERE order_id = ?");
$stmt->execute([$response, $provider_id, $now, $data['order_id']]);

// Καταγραφή επιτυχίας στο ιστορικό
$stmt = $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, ?, ?, ?)");
$stmt->execute([$invoice_row['id'], $now, 'success', $response]);

// Επιτυχής απάντηση στον client
echo json_encode([
    "message" => "Invoice sent successfully to provider",
    "order_id" => $data['order_id'],
    "provider_invoice_id" => $provider_id,
    "provider_raw_response" => $response_data
]);
