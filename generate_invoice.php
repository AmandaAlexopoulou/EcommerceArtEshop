<?php
// Include necessary files and classes
require_once 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // === Data sanitization and validation ===
    // Sanitize the customer's name and email
    $name  = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    // Decode the JSON data from 'items' (cart items)
    $items_json = $_POST['items'] ?? '[]';
    $items = json_decode($items_json, true);

    // Check if the required fields are empty, if so, terminate the process
    if (empty($name) || empty($email) || empty($items)) {
        die("❌ Missing customer information or empty cart.");
    }

    // === Check if customer exists, or create a new customer ===
    // Check if the customer already exists by email
    $stmt = $db->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // If customer exists, use their ID; otherwise, create a new customer
    if ($customer) {
        $customer_id = $customer['id'];
    } else {
        // Insert new customer data into the 'customers' table
        $stmt = $db->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        $customer_id = $db->lastInsertId();
    }

    // === Create new order ===
    // Insert a new order with the customer ID and total set to 0 initially
    $stmt = $db->prepare("INSERT INTO orders (customer_id, total) VALUES (?, 0)");
    // SQLite will automatically fill 'created_at' with CURRENT_TIMESTAMP
    $stmt->execute([$customer_id]);
    $order_id = $db->lastInsertId();

    // === Fetch created_at timestamp for confirmation ===
    $stmt = $db->prepare("SELECT created_at FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $created_at = $stmt->fetchColumn();

    // === Insert ordered items into order_items and calculate the total ===
    $total = 0; // Initialize the total amount to 0
    foreach ($items as $item) {
        // Get the artwork ID and quantity from the item
        $artwork_id = intval($item['id'] ?? 0);
        $qty = intval($item['quantity'] ?? 1);

        // Skip the item if the artwork ID is not valid
        if ($artwork_id <= 0) continue;

        // Retrieve the price of the artwork from the 'artworks' table
        $stmt_art = $db->prepare("SELECT price FROM artworks WHERE id = ?");
        $stmt_art->execute([$artwork_id]);
        $art = $stmt_art->fetch(PDO::FETCH_ASSOC);

        // If artwork is found, insert it into the order_items table and update the total
        if ($art) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, artwork_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $artwork_id, $qty, $art['price']]);
            $total += $art['price'] * $qty; // Update the total with the price of the item * quantity
        }
    }

    // === Update the order's total amount ===
    // Update the total value of the order in the 'orders' table
    $stmt = $db->prepare("UPDATE orders SET total = ? WHERE id = ?");
    $stmt->execute([$total, $order_id]);

    // === Create an invoice record ===
    // Generate a unique invoice number (format: INV-YYYY-XXXXXX)
    $invoice_number = sprintf('INV-%s-%06d', date('Y'), $order_id);
    // Insert the new invoice into the 'invoices' table
    $stmt = $db->prepare("INSERT INTO invoices (order_id, invoice_number, total) VALUES (?, ?, ?)");
    $stmt->execute([$order_id, $invoice_number, $total]);
    $invoice_id = $db->lastInsertId();

    // === Send the invoice via email using PHPMailer ===
    $mail = new PHPMailer(true);
    $date = date("d/m/Y H:i"); // Get the current date and time

    try {
        // Configure the email settings
        $mail->isSMTP();
        $mail->Host = '127.0.0.1'; // Use MailHog for local testing
        $mail->Port = 1025;
        $mail->SMTPAuth = false;

        // Set the sender and recipient details
        $mail->setFrom('no-reply@arteshop.local', 'Amanda\'s Art eShop');
        $mail->addAddress($email, $name);
        $mail->Subject = "Your Invoice #$invoice_number";
        $mail->Body = "
Dear $name,

Thank you for your order.
Invoice No: $invoice_number
Total: €$total
Date: $date
        ";

        // Try to send the email
        $mail->send();
        $email_status = 'sent';
        $email_response = 'Email sent successfully';
    } catch (Exception $e) {
        // If sending email fails, capture the error
        $email_status = 'failed';
        $email_response = "Mailer Error: {$mail->ErrorInfo}";
    }

    // === Log the email sending result ===
    $stmt = $db->prepare("INSERT INTO invoice_logs (invoice_id, sent_at, status, response) VALUES (?, ?, ?, ?)");
    $stmt->execute([$invoice_id, date('Y-m-d H:i:s'), $email_status, $email_response]);

    // === Redirect to confirmation page ===
    // Redirect the user to the confirmation page with the invoice number and date
    // header("Location: confirmation.html"); --> Changed to confirmation.php to show the date on the page
    header("Location: confirmation.php?invoice=$invoice_number&date=" . urlencode(date('Y-m-d H:i')));
    exit;

} else {
    // If the request is not POST, terminate the process
    die("Invalid access.");
}
?>

<!-- HTML content for displaying the invoice -->
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= htmlspecialchars($invoice_number) ?></title>
    <link rel="stylesheet" href="css/styles.css?v=1.2">
    <style>
        /* Basic styling for the invoice layout */
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
        <h2>Sales Invoice</h2>
        <p><strong>Invoice No:</strong> <?= htmlspecialchars($invoice_number) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($date) ?></p>
        <p><strong>Customer:</strong> <?= htmlspecialchars($name) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>

        <table>
            <tr>
                <th>Description</th>
                <th>Quantity</th>
                <th>Price (€)</th>
                <th>Total (€)</th>
            </tr>
            <?php
            // Retrieve the ordered items for this invoice
            $items_stmt = $db->prepare("SELECT a.title, oi.quantity, oi.price 
                                        FROM order_items oi
                                        JOIN artworks a ON a.id = oi.artwork_id
                                        WHERE oi.order_id = ?");
            $items_stmt->execute([$order_id]);
            $ordered_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Loop through each item and display it in the table
            foreach ($ordered_items as $it):
                $subtotal = $it['quantity'] * $it['price'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($it['title']) ?></td>
                    <td><?= $it['quantity'] ?></td>
                    <td><?= number_format($it['price'], 2) ?></td>
                    <td><?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach;
