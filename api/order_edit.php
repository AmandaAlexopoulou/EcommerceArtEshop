<?php
include "db.php";
include "../header.php";
//επεξεργασία παραγγελίας
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<p>Order not found.</p>";
    include "../footer.php";
    exit;
}

// Επεξεργασία μόνο του total (μπορείς να προσθέσεις και άλλα πεδία)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = $_POST['total'];
    $stmt = $db->prepare("UPDATE orders SET total = ? WHERE id = ?");
    $stmt->execute([$total, $id]);
    header("Location: order_view.php");
    exit;
}
?>

<h2>Edit Order #<?= $order['id'] ?></h2>
<form method="post">
    <label>Total</label><br>
    <input type="number" step="0.01" name="total" value="<?= $order['total'] ?>" required><br><br>
    <button type="submit">Save</button>
    <a href="orders_view.php">Cancel</a>
</form>

<?php include "../footer.php"; ?>
