<?php 
// Load the database connection
require_once 'db.php';

// Use PHPMailer namespaces
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Get JSON input from the request body
$data = json_decode(file_get_contents('php://input'), true);

// Retrieve the invoice ID from JSON or default to 0
$id = $data['id'] ?? 0;

// Prepare SQL to fetch the invoice and customer details
$stmt = $db->prepare("
    SELECT invoices.*, customers.name AS customer_name, customers.email AS customer_email
    FROM invoices
    JOIN orders ON invoices.order_id = orders.id
    JOIN customers ON orders.customer_id = customers.id
    WHERE invoices.id = ?
");

// Execute the query with the given ID
$stmt->execute([$id]);

// Fetch the result as an associative array
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the invoice exists
if (!$invoice) {
    http_response_code(404);
    echo "Invoice not found.";
    exit;
}

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host = 'localhost';      // SMTP server (MailHog)
    $mail->Port = 1025;             // MailHog port
    $mail->SMTPAuth = false;        // No authentication for local testing

    // Set sender and recipient
    $mail->setFrom('no-reply@arteshop.local', 'ArtEshop');
    $mail->addAddress($invoice['customer_email'], $invoice['customer_name']);

    // Email content settings
    $mail->isHTML(true);
    $mail->Subject = "Invoice #" . $invoice['invoice_number'];
    $mail->Body = "
        <p>Dear {$invoice['customer_name']},</p>
        <p>Thank you for your order.</p>
        <p>Invoice Number: <strong>{$invoice['invoice_number']}</strong></p>
        <p>Total: €" . number_format($invoice['total'], 2) . "</p>
    ";

    // Send the email
    $mail->send();

    // Update the database that the invoice has been sent
    $db->prepare("UPDATE invoices SET status='sent', timestamp_sent=datetime('now') WHERE id=?")
       ->execute([$id]);

    // Log the email sending
    $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, datetime('now'), ?, ?)")
       ->execute([$id, 'sent', 'Email sent successfully']);

    // Success message
    echo "✅ Email sent successfully (MailHog).";

} catch (Exception $e) {
    // Handle errors and show message
    echo "❌ Error: {$mail->ErrorInfo}";
}
?>
