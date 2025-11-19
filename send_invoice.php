<?php
// ===== DEBUG =====
// Καταγραφή έναρξης εκτέλεσης του script για debugging
// Η πληροφορία γράφεται στο αρχείο debug.txt στο root του project
file_put_contents(__DIR__ . "/debug.txt", "RUNNING SEND_INVOICE\n", FILE_APPEND);

// Φόρτωση σύνδεσης με τη βάση δεδομένων
// Το db.php περιέχει τον PDO connection στον SQLite πίνακα
require_once __DIR__ . '/api/db.php';

// Χρήση των namespaces της PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Καθορισμός των paths των αρχείων της PHPMailer
// Πρέπει να υπάρχουν στον φάκελο phpmailer/src
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

// Ανάγνωση δεδομένων JSON που στέλνονται στο script μέσω POST
$data = json_decode(file_get_contents('php://input'), true);

// Λήψη του invoice ID από τα δεδομένα
// Αν δεν υπάρχει, το id γίνεται 0
$id = $data['id'] ?? 0;

// Καταγραφή του invoice ID στο debug αρχείο
file_put_contents(__DIR__ . "/debug.txt", "INVOICE ID = $id\n", FILE_APPEND);

// Εκτέλεση query για λήψη στοιχείων του παραστατικού και του πελάτη
$stmt = $db->prepare("
    SELECT invoices.*, customers.name AS customer_name, customers.email AS customer_email
    FROM invoices
    JOIN orders ON invoices.order_id = orders.id
    JOIN customers ON orders.customer_id = customers.id
    WHERE invoices.id = ?
");
$stmt->execute([$id]);
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

// Αν το invoice δεν βρεθεί, γράφεται στο debug αρχείο και επιστρέφεται error 404
if (!$invoice) {
    file_put_contents(__DIR__ . "/debug.txt", "INVOICE NOT FOUND\n", FILE_APPEND);
    http_response_code(404);
    echo json_encode(["error" => "Invoice not found"]);
    exit;
}

// Δημιουργία αντικειμένου PHPMailer
$mail = new PHPMailer(true);

// Αποθήκευση της τρέχουσας ημερομηνίας/ώρας για log
$now = date("Y-m-d H:i:s");

try {
    // Ρύθμιση SMTP για αποστολή μέσω localhost (π.χ. MailHog)
    $mail->isSMTP();
    $mail->Host = 'localhost';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;

    // Καθορισμός αποστολέα
    $mail->setFrom('no-reply@arteshop.local', 'ArtEshop');

    // Προσθήκη παραλήπτη από στοιχεία του invoice
    $mail->addAddress($invoice['customer_email'], $invoice['customer_name']);

    // Email σε HTML μορφή
    $mail->isHTML(true);
    $mail->Subject = "Invoice #" . $invoice['invoice_number'];
    $mail->Body = "
        <p>Dear {$invoice['customer_name']},</p>
        <p>Thank you for your order.</p>
        <p>Invoice Number: <strong>{$invoice['invoice_number']}</strong></p>
        <p>Total: €" . number_format($invoice['total'], 2) . "</p>
    ";

    // Αποστολή email
    $mail->send();
    $status = 'sent';
    $response = 'Email sent successfully';

    // Ενημέρωση του invoice στον πίνακα invoices για να σημειωθεί ως "sent"
    $db->prepare("UPDATE invoices SET status='sent', timestamp_sent=? WHERE id=?")
       ->execute([$now, $id]);

} catch (Exception $e) {
    // Σε περίπτωση λάθους καταγράφεται το σφάλμα
    $status = 'error';
    $response = $mail->ErrorInfo;
}

// Καταγραφή της αποστολής ή του σφάλματος στον πίνακα invoice_logs
$stmt = $db->prepare("
    INSERT INTO invoice_logs (invoice_id, sent_at, status, response)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$id, $now, $status, $response]);

// Καταγραφή στο debug.txt για να βλέπουμε ότι η εγγραφή έγινε
file_put_contents(__DIR__ . "/debug.txt", "LOG INSERTED: ID=$id STATUS=$status\n", FILE_APPEND);

// Επιστροφή JSON response με αποτέλεσμα αποστολής
echo json_encode([
    "success" => $status === 'sent',
    "message" => $response,
    "invoice_id" => $id
]);
?>
