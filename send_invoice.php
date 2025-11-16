<?php
require_once 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

$stmt = $db->prepare("SELECT invoices.*, customers.name AS customer_name, customers.email AS customer_email
                      FROM invoices
                      JOIN orders ON invoices.order_id = orders.id
                      JOIN customers ON orders.customer_id = customers.id
                      WHERE invoices.id = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    http_response_code(404);
    echo "Δεν βρέθηκε το παραστατικό.";
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'localhost';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;

    $mail->setFrom('no-reply@arteshop.local', 'ArtEshop');
    $mail->addAddress($invoice['customer_email'], $invoice['customer_name']);
    $mail->isHTML(true);
    $mail->Subject = "Παραστατικό #" . $invoice['invoice_number'];
    $mail->Body = "
        <p>Αγαπητέ/ή {$invoice['customer_name']},</p>
        <p>Ευχαριστούμε για την παραγγελία σας.</p>
        <p>Αρ. Παραστατικού: <strong>{$invoice['invoice_number']}</strong></p>
        <p>Σύνολο: €" . number_format($invoice['total'], 2) . "</p>
    ";
    $mail->send();

    // ενημέρωση & log
    $db->prepare("UPDATE invoices SET status='sent', timestamp_sent=datetime('now') WHERE id=?")->execute([$id]);
    $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, datetime('now'), ?, ?)")->execute([$id, 'sent', 'Email sent successfully']);

    echo "✅ Email στάλθηκε επιτυχώς (MailHog).";

} catch (Exception $e) {
    echo "❌ Σφάλμα: {$mail->ErrorInfo}";
}
?>
