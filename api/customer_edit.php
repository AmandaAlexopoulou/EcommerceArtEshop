<?php
//DB connection & HTML header
include "db.php";
include "../header.php";

//Αν δεν υπάρχει id -> 0 & αποφεύγει warnings
$id = $_GET['id'] ?? 0;

// Get customer-> Φέρνει τον πελάτη από τη βάση
$stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
//Έλεγχος αν υπάρχει
//Αν δεν υπάρχει πελάτης ->μήνυμα -> stop
if (!$c) {
    echo "<p>Customer not found.</p>";
    include "../footer.php";
    exit;
}
?>

<h2>Edit Customer</h2>
<!--Στέλνει το id κρυμμένο στο POST-->  
<form action="customer_edit_save.php" method="post">
    <input type="hidden" name="id" value="<?= $c['id'] ?>">
<!--Προσθήκη htmlspecialchars για αποφυγή XSS->το πεδίο είναι υποχρεωτικό-->
    <label>Name</label><br>
    <input type="text" name="name" value="<?= htmlspecialchars($c['name']) ?>" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($c['email']) ?>" required><br><br>

    <button type="submit">Save</button>
    <a href="customers.php">Cancel</a>
</form>

<?php include "../footer.php"; ?>
