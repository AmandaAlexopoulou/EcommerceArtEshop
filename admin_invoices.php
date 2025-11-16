<?php
// admin_invoices.php
$db = new PDO('sqlite:db/artstore.sqlite');//apo lathos htan database.sqlite
$invoices = $db->query("SELECT * FROM invoices ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$stats = $db->query("SELECT * FROM invoices_summary")->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Διαχείριση Παραστατικών</title>
    <link rel="stylesheet" href="css/style.css?v=1.2">
    <style>
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }
        .status-sent { color: green; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align:center;">📄 Παραστατικά Πωλήσεων</h1>
    </header>

    <main>
	<h3 style="text-align:center;">
    Σύνολο: <?= $stats['total_invoices'] ?> παραστατικά,
    <?= $stats['sent_count'] ?> στάλθηκαν,
    <?= $stats['pending_count'] ?> σε εκκρεμότητα.
    <br>Συνολικές πωλήσεις: <?= number_format($stats['total_sales'], 2) ?> €
</h3>

        <table>
            <thead>
                <tr>
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
                <?php foreach ($invoices as $inv): ?>
    <?php
    $items = $db->prepare("SELECT ai.title, oi.quantity, oi.price
                           FROM order_items oi
                           JOIN artworks ai ON ai.id = oi.artwork_id
                           WHERE oi.order_id = ?");
    $items->execute([$inv['order_id']]);
    $items = $items->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <tr>
        <td><?= $inv['id'] ?></td>
        <td><?= htmlspecialchars($inv['customer_name']) ?></td>
        <td><?= htmlspecialchars($inv['customer_email']) ?></td>
        <td>
            <ul>
                <?php foreach ($items as $it): ?>
                    <li><?= htmlspecialchars($it['title']) ?> x <?= $it['quantity'] ?> (<?= number_format($it['price'],2) ?> €)</li>
                <?php endforeach; ?>
            </ul>
        </td>
        <td><?= number_format($inv['total'], 2) ?></td>
        <td class="<?= $inv['status'] === 'sent' ? 'status-sent' : 'status-error' ?>">
            <?= $inv['status'] === 'sent' ? '✔️ Sent' : '❌ Error' ?>
        </td>
        <td><?= $inv['provider_invoice_id'] ?? '-' ?></td>
        <td><?= $inv['timestamp_sent'] ?? '-' ?></td>
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
