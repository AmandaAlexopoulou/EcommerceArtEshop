<?php
include "db.php";

$q = $_GET['q'] ?? "";

$stmt = $db->prepare("
    SELECT * FROM customers
    WHERE name LIKE ? OR email LIKE ?
    ORDER BY id DESC
");
$stmt->execute(["%$q%", "%$q%"]);

echo "<table>
<tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>";

foreach ($stmt as $c) {
    echo "<tr>
        <td>".htmlspecialchars($c['id'])."</td>
        <td>".htmlspecialchars($c['name'])."</td>
        <td>".htmlspecialchars($c['email'])."</td>
        <td>
            <a href='api/customer_edit.php?id={$c['id']}'>Edit</a> |
            <a href='api/customer_delete.php?id={$c['id']}'>Delete</a>
        </td>
    </tr>";
}

echo "</table>";
