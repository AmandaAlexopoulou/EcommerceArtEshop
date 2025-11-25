<?php
include "db.php";

$id = $_GET['id'] ?? 0;

if (!isset($_GET['confirm'])) {
    echo "<h3>Delete customer $id?</h3>";
    echo "<a href='customer_delete.php?id=$id&confirm=1'>YES, DELETE</a> | ";
    echo "<a href='../customers.php'>Cancel</a>";
    exit;
}

// Perform delete
$stmt = $db->prepare("DELETE FROM customers WHERE id = ?");
$stmt->execute([$id]);

header("Location: ../customers.php?deleted=1");
exit;
