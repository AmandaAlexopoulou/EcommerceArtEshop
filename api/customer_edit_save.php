<?php
include "db.php";

$id    = $_POST['id'] ?? 0;
$name  = $_POST['name'] ?? "";
$email = $_POST['email'] ?? "";

// Update
$stmt = $db->prepare("
    UPDATE customers SET name = ?, email = ? WHERE id = ?
");
$stmt->execute([$name, $email, $id]);

header("Location: ../customers.php?updated=1");
exit;
