<?php include "header.php"; include "api/db.php"; ?>

<h1>Dashboard</h1>

<!-- Link προς Customers -->
<a href="customers.php" class="btn">→ Go to Customers</a>
<br><br>

<?php
$customers = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$orders    = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$invoices  = $db->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$logs      = $db->query("SELECT COUNT(*) FROM invoice_logs")->fetchColumn();

// Revenues
$today = $db->query("
    SELECT SUM(total) FROM orders 
    WHERE DATE(created_at) = DATE('now')
")->fetchColumn() ?? 0;

$week = $db->query("
    SELECT SUM(total) FROM orders
    WHERE created_at >= datetime('now','-7 days')
")->fetchColumn() ?? 0;

$avg = $db->query("SELECT AVG(total) FROM orders")->fetchColumn() ?? 0;

$statuses = $db->query("
    SELECT status, COUNT(*) cnt 
    FROM invoices 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$top = $db->query("
    SELECT c.name, SUM(o.total) total 
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    GROUP BY c.id 
    ORDER BY total DESC 
    LIMIT 5
")->fetchAll();
?>

<!-- MAIN STATS -->
<div class="stats-grid">
<link rel = "stylesheet" href = "css/style.css">
    <div class="stat-card">Customers<br><b><?= $customers ?></b></div>
    <div class="stat-card">Orders<br><b><?= $orders ?></b></div>
    <div class="stat-card">Invoices<br><b><?= $invoices ?></b></div>
    <div class="stat-card">Logs<br><b><?= $logs ?></b></div>
</div>

<!-- MONEY -->
<h2>Revenue</h2>
<div class="stats-grid">
    <div class="stat-card">Today<br><b><?= number_format($today,2) ?> €</b></div>
    <div class="stat-card">Last 7 Days<br><b><?= number_format($week,2) ?> €</b></div>
    <div class="stat-card">Average Order<br><b><?= number_format($avg,2) ?> €</b></div>
</div>

<!-- INVOICE STATUS -->
<h2>Invoice Status</h2>
<table>
<tr><th>Status</th><th>Count</th></tr>
<?php foreach ($statuses as $s => $cnt): ?>
<tr>
    <td><?= htmlspecialchars($s) ?></td>
    <td><?= $cnt ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- TOP CUSTOMERS -->
<h2>Top Customers</h2>
<table>
<tr><th>Name</th><th>Total Spent</th></tr>
<?php foreach ($top as $t): ?>
<tr>
    <td><?= htmlspecialchars($t['name']) ?></td>
    <td><?= number_format($t['total'],2) ?> €</td>
</tr>
<?php endforeach; ?>
</table>

<?php include "footer.php"; ?>
