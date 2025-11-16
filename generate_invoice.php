<?php
require_once 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // === Ανάγνωση και καθαρισμός δεδομένων ===
    $name  = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $items_json = $_POST['items'] ?? '[]';
    $items = json_decode($items_json, true);

    if (empty($name) || empty($email) || empty($items)) {
        die("❌ Ελλιπή στοιχεία πελάτη ή άδειο καλάθι.");
    }

    // === Έλεγχος/Εισαγωγή πελάτη ===
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

    // ===Δημιουργία παραγγελίας ===
   $stmt = $db->prepare("INSERT INTO orders (customer_id, total) VALUES (?, 0)");
   //Η SQLite θα συμπληρώσει το created_at με CURRENT_TIMESTAMP αυτόματα
   $stmt->execute([$customer_id]);
    $order_id = $db->lastInsertId();
	
	
	//===Ανάκτηση για επιβεβαίωση===
	$stmt = $db->prepare("SELECT created_at FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $created_at = $stmt->fetchColumn();


    // ===Εισαγωγή έργων στην order_items και υπολογισμός συνόλου ===
    $total = 0;
    foreach ($items as $item) {
        $artwork_id = intval($item['id'] ?? 0);
        $qty = intval($item['quantity'] ?? 1);

        if ($artwork_id <= 0) continue;

        $stmt_art = $db->prepare("SELECT price FROM artworks WHERE id = ?");
        $stmt_art->execute([$artwork_id]);
        $art = $stmt_art->fetch(PDO::FETCH_ASSOC);

        if ($art) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, artwork_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $artwork_id, $qty, $art['price']]);
            $total += $art['price'] * $qty;
        }
    }

    // ===Ενημέρωση συνολικού ποσού παραγγελίας ===
    $stmt = $db->prepare("UPDATE orders SET total = ? WHERE id = ?");
    $stmt->execute([$total, $order_id]);

    // ===Δημιουργία παραστατικού ===
    $invoice_number = sprintf('INV-%s-%06d', date('Y'), $order_id);
    $stmt = $db->prepare("INSERT INTO invoices (order_id, invoice_number, total) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, $invoice_number, $total]);
    $invoice_id = $db->lastInsertId();

    // ===Αποστολή email μέσω MailHog ===
    $mail = new PHPMailer(true);
    $date = date("d/m/Y H:i");

    try {
        $mail->isSMTP();
        $mail->Host = '127.0.0.1';
        $mail->Port = 1025;
        $mail->SMTPAuth = false;

        $mail->setFrom('no-reply@arteshop.local', 'Amanda\'s Art eShop');
        $mail->addAddress($email, $name);
        $mail->Subject = "Το παραστατικό σας #$invoice_number";
        $mail->Body = "
Αγαπητέ/ή $name,
Ευχαριστούμε για την παραγγελία σας.
Αρ. Παραστατικού: $invoice_number
Σύνολο: €$total
Ημερομηνία: $date
        ";

        $mail->send();
        $email_status = 'sent';
        $email_response = 'Email sent successfully';
    } catch (Exception $e) {
        $email_status = 'failed';
        $email_response = "Mailer Error: {$mail->ErrorInfo}";
    }

    // ===Καταχώριση log για το invoice ===
    $stmt = $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, ?, ?, ?)");
    $stmt->execute([$invoice_id, date('Y-m-d H:i:s'), $email_status, $email_response]);
	
	

    // ===Ανακατεύθυνση ===
    header("Location: confirmation.html");
    exit;

} else {
    die("Μη έγκυρη πρόσβαση.");
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Παραστατικό #<?= htmlspecialchars($invoice_number) ?></title>
    <link rel="stylesheet" href="css/styles.css?v=1.2">
    <style>
        .invoice {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border: 1px solid #ccc;
            font-family: sans-serif;
        }

        .invoice h2 {
            text-align: center;
        }

        .invoice table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .invoice th, .invoice td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }

        .print-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 30px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice">
        <h2>Παραστατικό Πώλησης</h2>
        <p><strong>Αρ. Παραστατικού:</strong> <?= htmlspecialchars($invoice_number) ?></p>
        <p><strong>Ημερομηνία:</strong> <?= htmlspecialchars($date) ?></p>
        <p><strong>Πελάτης:</strong> <?= htmlspecialchars($name) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>

        <table>
            <tr>
                <th>Περιγραφή</th>
                <th>Ποσότητα</th>
                <th>Τιμή (€)</th>
                <th>Σύνολο (€)</th>
            </tr>
            <?php
            $items_stmt = $db->prepare("SELECT a.title, oi.quantity, oi.price 
                                        FROM order_items oi
                                        JOIN artworks a ON a.id = oi.artwork_id
                                        WHERE oi.order_id = ?");
            $items_stmt->execute([$order_id]);
            $ordered_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ordered_items as $it):
                $subtotal = $it['quantity'] * $it['price'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($it['title']) ?></td>
                    <td><?= $it['quantity'] ?></td>
                    <td><?= number_format($it['price'], 2) ?></td>
                    <td><?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="text-align: right;">Σύνολο: <?= number_format($total, 2) ?> €</h3>

        <button class="print-btn" onclick="window.print()">🖨 Εκτύπωση Παραστατικού</button>
    </div>
</body>
</html>
