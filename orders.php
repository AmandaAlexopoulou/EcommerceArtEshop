<?php
include "header.php";
include "api/db.php";
//LIST + SEARCH
$q = $_GET["q"] ?? "";

$stmt = $db->prepare("
    SELECT o.*, c.name customer_name
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    WHERE c.name LIKE ? OR o.id LIKE ?
    ORDER BY o.id DESC
");
$stmt->execute(["%$q%", "%$q%"]);
?>
<h2>Orders</h2>

<input type="text" id="search" placeholder="Search order ID or customer">
<script>
document.getElementById("search").onkeyup = e => {
    let q = e.target.value;
    location = "orders.php?q=" + q;
}
</script>

<table>
<tr>
    <th>ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Created At</th>
    <th>Actions</th>
</tr>

<?php foreach($stmt as $o): ?>
<tr>
    <td><?= $o['id'] ?></td>
    <td><?= htmlspecialchars($o['customer_name']) ?></td>
    <td><?= $o['total'] ?></td>
    <td><?= $o['created_at'] ?></td>
    <td>
        <a href="order_edit.php?id=<?= $o['id'] ?>">Edit</a> |
        <a href="order_delete.php?id=<?= $o['id'] ?>">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php include "footer.php"; ?>