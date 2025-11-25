<?php
include "db.php";
//AJAX search & table
$q = $_GET['q'] ?? "";

// Join με τον πίνακα customers για να εμφανίσουμε το όνομα
$stmt = $db->prepare("
    SELECT o.*, c.name AS customer_name
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    WHERE o.id LIKE ? OR c.name LIKE ?
    ORDER BY o.id DESC
");
$stmt->execute(["%$q%", "%$q%"]);

echo "<table border='1' cellpadding='6' cellspacing='0'>
<tr style='background:#f0f0f0;'>
    <th>ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Created At</th>
    <th>Actions</th>
</tr>";

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    echo "<tr><td colspan='5'><i>No orders found</i></td></tr>";
} else {
    foreach ($orders as $o) {
        $id = $o['id'];
        echo "<tr>
            <td>{$id}</td>
            <td>".htmlspecialchars($o['customer_name'])."</td>
            <td>{$o['total']}</td>
            <td>{$o['created_at']}</td>
            <td>
                <a href='order_view.php?id={$id}'>View</a> |
                <a href='order_edit.php?id={$id}'>Edit</a> |
                <a href='order_delete.php?id={$id}' onclick='return confirm(\"Are you sure?\")'>Delete</a>
            </td>
        </tr>";
    }
}

echo "</table>";
