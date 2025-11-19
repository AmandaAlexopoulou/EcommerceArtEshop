<?php
require_once __DIR__ . '/api/db.php';

// Connect to the SQLite database (fixed incorrect database filename)
$db = new PDO('sqlite:db/artstore.sqlite');

// Retrieve all invoices from the database, ordered by ID in descending order
$invoices = $db->query("SELECT * FROM invoices ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Retrieve invoice summary statistics (total invoices, sent, pending, total sales)
$stats = $db->query("SELECT * FROM invoices_summary")->fetch(PDO::FETCH_ASSOC);
//for debugging 
echo "DB file: " . $db->query("PRAGMA database_list")->fetchColumn(2);

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <!-- Set character encoding to UTF-8 to support Greek characters -->
    <meta charset="UTF-8">
    
    <!-- Title for the page that appears in the browser tab -->
    <title>Διαχείριση Παραστατικών</title>

    <!-- External CSS link to style the page -->
    <link rel="stylesheet" href="css/style.css?v=1.2">

    <style>
        /* Custom styling for the table */
        table {
            width: 90%; /* Make the table take up 90% of the page width */
            margin: 20px auto; /* Center the table horizontally */
            border-collapse: collapse; /* Remove double borders between cells */
        }
        th, td {
            padding: 12px; /* Add padding to table cells */
            border: 1px solid #ccc; /* Light grey border for each table cell */
        }
        /* Styling for status sent (green text) and error (red text) */
        .status-sent { color: green; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
    </style>
</head>
<body>

    <!-- Page header with title "Πραστατικά Πωλήσεων" -->
    <header>
        <h1 style="text-align:center;">📄 Παραστατικά Πωλήσεων</h1>
    </header>

    <!-- Main content of the page -->
    <main>
        <!-- Invoice summary stats displayed in the center of the page -->
        <h3 style="text-align:center;">
            Σύνολο: <?= $stats['total_invoices'] ?> παραστατικά,
            <?= $stats['sent_count'] ?> στάλθηκαν,
            <?= $stats['pending_count'] ?> σε εκκρεμότητα.
            <br>Συνολικές πωλήσεις: <?= number_format($stats['total_sales'], 2) ?> €
        </h3>

        <!-- Table to display all invoices -->
        <table>
            <thead>
                <tr>
                    <!-- Column headers for the table -->
                    <th>ID</th>
                    <th>Πελάτης</th>
                    <th>Email</th>
                    <th>Προϊόν</th>
                    <th>Σύνολο (€)</th>
                    <th>Κατάσταση</th>
                    <th>Provider ID</th>
                    <th>Ημερομηνία Αποστολής</th>
                    <th>Ενέργεια</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop through each invoice and display it in a table row -->
                <?php foreach ($invoices as $inv): ?>
                    <?php
                    // For each invoice, fetch associated items in the order
                    $items = $db->prepare("SELECT ai.title, oi.quantity, oi.price
                                           FROM order_items oi
                                           JOIN artworks ai ON ai.id = oi.artwork_id
                                           WHERE oi.order_id = ?");
                    $items->execute([$inv['order_id']]); // Execute the query with the current order_id
                    $items = $items->fetchAll(PDO::FETCH_ASSOC); // Fetch the order items as an associative array
                    ?>

                    <tr>
                        <!-- Display the invoice information in table cells -->
                        <td><?= $inv['id'] ?></td>
                        <td><?= htmlspecialchars($inv['customer_name']) ?></td>
                        <td><?= htmlspecialchars($inv['customer_email']) ?></td>
                        <td>
                            <!-- Display the list of items in the order -->
                            <ul>
                                <?php foreach ($items as $it): ?>
                                    <li><?= htmlspecialchars($it['title']) ?> x <?= $it['quantity'] ?> (<?= number_format($it['price'], 2) ?> €)</li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td><?= number_format($inv['total'], 2) ?></td>
                        <!-- Display status with custom styling for sent or error -->
                        <td class="<?= $inv['status'] === 'sent' ? 'status-sent' : 'status-error' ?>">
                            <?= $inv['status'] === 'sent' ? '✔️ Sent' : '❌ Error' ?>
                        </td>
                        <!-- Display the provider invoice ID, or a dash if not available -->
                        <td><?= $inv['provider_invoice_id'] ?? '-' ?></td>
                        <!-- Display the timestamp of when the invoice was sent, or a dash if not available -->
                        <td><?= $inv['timestamp_sent'] ?? '-' ?></td>
                        <!-- Button to view provider response (if available) in an alert box -->
                        <td>
                            <button onclick="alert(`<?= htmlspecialchars($inv['provider_response'] ?? 'Χωρίς απόκριση') ?>`)">📄 Προβολή</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
