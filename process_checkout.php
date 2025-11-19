<?php

// -------------------- GENERAL SETUP --------------------
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// -------------------- METHOD CHECK --------------------
// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// -------------------- READ INPUT --------------------
// Read incoming JSON payload
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields: name, email, items, total
if (!$data || !isset($data['name'], $data['email'], $data['items'], $data['total'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Incomplete or invalid data.']);
    exit;
}

// -------------------- SANITIZE INPUT --------------------
$name = trim($data['name']);
$email = trim($data['email']);
$items = $data['items'];
$total = (float)$data['total'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

try {
    // -------------------- DATABASE CONNECTION --------------------
    // Connect to SQLite database
    $db = new PDO('sqlite:api/db/artstore.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Begin transaction (all DB operations succeed or rollback)
    $db->beginTransaction();

    // -------------------- CUSTOMER CHECK / INSERT --------------------
    // Check if customer already exists by email
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        $customer_id = $customer['id'];
    } else {
        // Insert new customer
        $stmt = $db->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        $customer_id = $db->lastInsertId();
    }

    // -------------------- CREATE ORDER --------------------
    $stmt = $db->prepare("INSERT INTO orders (customer_id, total, created_at) VALUES (?, ?, datetime('now'))");
    $stmt->execute([$customer_id, $total]);
    $order_id = $db->lastInsertId();

    // -------------------- INSERT ORDER ITEMS --------------------
    $stmt = $db->prepare("INSERT INTO order_items (order_id, artwork_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt->execute([
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // -------------------- CREATE INVOICE --------------------
    $invoice_number = 'INV-' . strtoupper(uniqid());
    $stmt = $db->prepare("INSERT INTO invoices (
        order_id, invoice_number, issued_at, total, status, valid, provider_response, provider_invoice_id, timestamp_sent
    ) VALUES (?, ?, datetime('now'), ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $order_id,
        $invoice_number,
        $total,
        'pending',   // status
        0,           // valid
        '',          // provider_response
        '',          // provider_invoice_id
        NULL         // timestamp_sent
    ]);

    // Commit transaction
    $db->commit();

    // -------------------- SEND EMAIL VIA PHPMailer --------------------
    // Using Composer autoload
    require __DIR__ . '/vendor/autoload.php';

    $mail = new PHPMailer(true);

    try {
        // SMTP configuration for MailHog (local testing)
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;
        $mail->SMTPAutoTLS = false;

        // Sender info
        $mail->setFrom('noreply@arteshop.local', 'Amanda\'s Art eShop');

        // Recipient
        $mail->addAddress($email, $name);

        // Email subject
        $mail->Subject = "Your order invoice - {$invoice_number}";

        // Enable HTML email
        $mail->isHTML(true);

        // Build email body with order details
        $body = "
            <h2>Thank you for your purchase!</h2>
            <p>Dear <strong>{$name}</strong>,</p>
            <p>Your order has been successfully completed.</p>

            <p><strong>Invoice Number:</strong> {$invoice_number}</p>
            <p><strong>Total:</strong> €" . number_format($total, 2) . "</p>

            <h3>Products:</h3>
            <ul>
        ";

        foreach ($items as $it) {
            $body .= "<li>{$it['title']} – x{$it['quantity']} – €" . number_format($it['price'], 2) . "</li>";
        }

        $body .= "</ul>
            <br><p>Thank you for choosing Amanda's Art eShop!</p>
        ";

        $mail->Body = $body;

        // Send the email
        $mail->send();

    } catch (Exception $e) {
        // Log email errors but don't stop checkout process
        error_log("Mail error: " . $mail->ErrorInfo);
    }

    // -------------------- RESPONSE --------------------
    echo json_encode([
        'success' => true,
        'message' => 'Order completed successfully!',
        'order_id' => $order_id,
        'invoice_number' => $invoice_number
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

/*
SUMMARY OF THIS FILE:

1. Receives order data via POST (JSON: name, email, items, total).
2. Validates method and data.
3. Checks if customer exists, inserts if not.
4. Creates order and inserts items in the database.
5. Creates invoice record in invoices table.
6. Sends confirmation email using PHPMailer + MailHog.
7. Returns JSON response with success/failure, order ID, and invoice number.
*/
?>