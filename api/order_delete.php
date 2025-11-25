<?php
include "db.php";
//διαγραφή παραγγελίας + items
//διαγράφει πρώτα τα items και μετά την παραγγελία για να μην υπάρχουν orphan records
$id = (int)($_GET['id'] ?? 0);

// Διαγραφή πρώτα των items
$stmt = $db->prepare("DELETE FROM order_items WHERE order_id = ?");
$stmt->execute([$id]);

// Διαγραφή της παραγγελίας
$stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
$stmt->execute([$id]);

header("Location: order_view.php");
exit;
