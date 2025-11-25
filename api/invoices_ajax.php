<?php
include "db.php";

//Περιλαμβάνεται το κουμπί View Logs
//κάνει search σε invoice_number& status

//provider_invoice_id


$q = $_GET['q'] ?? "";

// Search με πολλά πεδία
$stmt = $db->prepare("
    SELECT * FROM invoices
    WHERE invoice_number LIKE ?
       OR status LIKE ?
       OR provider_invoice_id LIKE ?
    ORDER BY id DESC
");
$stmt->execute(["%$q%", "%$q%", "%$q%"]);

echo "<table border='1' cellpadding='6' cellspacing='0'>
<tr style='background:#f0f0f0;'>
    <th>ID</th>
    <th>Order ID</th>
    <th>Invoice Number</th>
    <th>Issued</th>
    <th>Total</th>
    <th>Status</th>
    <th>Actions</th>
</tr>";

foreach ($stmt as $inv) {

    $id = htmlspecialchars($inv['id']);

    echo "<tr>
        <td>".htmlspecialchars($inv['id'])."</td>
        <td>".htmlspecialchars($inv['order_id'])."</td>
        <td>".htmlspecialchars($inv['invoice_number'])."</td>
        <td>".htmlspecialchars($inv['issued_at'])."</td>
        <td>".htmlspecialchars($inv['total'])."</td>
        <td>".htmlspecialchars($inv['status'])."</td>
        <td>
            <a href='invoice_logs_view.php?id=$id' class='btn'>View Logs</a>
        </td>
    </tr>";
}

echo "</table>";
