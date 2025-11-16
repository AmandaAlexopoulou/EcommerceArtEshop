<?php


ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
header('Content-Type: application/json');

// Έλεγχος μεθόδου
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Μη αποδεκτή μέθοδος.']);
    exit;
}

// Διαβάζουμε JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['name'], $data['email'], $data['items'], $data['total'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ελλιπή ή μη έγκυρα δεδομένα.']);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$items = $data['items'];
$total = (float)$data['total'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Μη έγκυρη διεύθυνση email.']);
    exit;
}

try {
    $db = new PDO('sqlite:api/db/artstore.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->beginTransaction();

    // Εύρεση ή δημιουργία πελάτη
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        $customer_id = $customer['id'];
    } else {
        $stmt = $db->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        $customer_id = $db->lastInsertId();
    }

    // Δημιουργία παραγγελίας
    $stmt = $db->prepare("INSERT INTO orders (customer_id, total, created_at) VALUES (?, ?, datetime('now'))");
    $stmt->execute([$customer_id, $total]);
    $order_id = $db->lastInsertId();

    // Εισαγωγή ειδών παραγγελίας
    $stmt = $db->prepare("INSERT INTO order_items (order_id, artwork_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt->execute([
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // Δημιουργία τιμολογίου στον invoices
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

    $db->commit();

	// ---------------- ΑΠΟΣΤΟΛΗ EMAIL ΜΕ PHPMailer + MailHog ----------------



// Αν χρησιμοποιείς Composer
require __DIR__ . '/vendor/autoload.php';

// Δημιουργία αντικειμένου mailer
$mail = new PHPMailer(true);

try {
    // Ρυθμίσεις SMTP για MailHog
    $mail->isSMTP();
    $mail->Host = 'localhost';
    $mail->Port = 1025;
    $mail->SMTPAuth = false;       // MailHog δεν θέλει login
    $mail->SMTPAutoTLS = false;    // Χωρίς TLS

    // Στοιχεία αποστολέα
    $mail->setFrom('noreply@arteshop.local', 'Amanda\'s Art eShop');

    // Παραλήπτης
    $mail->addAddress($email, $name);

    // Θέμα email
    $mail->Subject = "Το παραστατικό της παραγγελίας σας - {$invoice_number}";

    // HTML email body
    $mail->isHTML(true);

    $body = "
        <h2>Ευχαριστούμε για την αγορά σας!</h2>
        <p>Αγαπητέ/ή <strong>{$name}</strong>,</p>
        <p>Η παραγγελία σας ολοκληρώθηκε με επιτυχία.</p>

        <p><strong>Αριθμός παραστατικού:</strong> {$invoice_number}</p>
        <p><strong>Σύνολο:</strong> €" . number_format($total, 2) . "</p>

        <h3>Προϊόντα:</h3>
        <ul>
    ";

    foreach ($items as $it) {
        $body .= "<li>{$it['title']} – x{$it['quantity']} – €" . number_format($it['price'], 2) . "</li>";
    }

    $body .= "</ul>
        <br><p>Σας ευχαριστούμε που επιλέξατε το Amanda's Art eShop!</p>
    ";

    $mail->Body = $body;

    // Αποστολή email
    $mail->send();

} catch (Exception $e) {
    // Δεν σταματά την παραγγελία — απλά log
    error_log("Mail error: " . $mail->ErrorInfo);
}

    echo json_encode([
        'success' => true,
        'message' => 'Η παραγγελία ολοκληρώθηκε με επιτυχία!',
        'order_id' => $order_id,
        'invoice_number' => $invoice_number
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Σφάλμα: ' . $e->getMessage()]);
}
?>
