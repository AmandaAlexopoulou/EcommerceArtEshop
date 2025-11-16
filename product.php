<?php
require_once 'db.php';

// Έλεγχος παραμέτρου id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Μη έγκυρο αναγνωριστικό προϊόντος.");
}

$id = (int)$_GET['id'];

// Προετοιμασμένο query για αποφυγή SQL injection
$stmt = $db->prepare("SELECT * FROM artworks WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("<p>Το προϊόν δεν βρέθηκε.</p>");
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> - Amanda's Art eShop</title>
    <link rel="stylesheet" href="css/style.css?v=1.2">
</head>
<body>
<header>
    <h1>eShop Ζωγραφικών Έργων</h1>
    <nav>
        <a href="index.html">🏠 Αρχική</a>
        <a href="cart.html">🛒 Καλάθι</a>
    </nav>
</header>

<main style="max-width: 800px; margin: 2rem auto; padding: 1rem;">
    <h2><?= htmlspecialchars($product['title']) ?></h2>

    <img src="img/artworks/<?= htmlspecialchars($product['image']) ?>" 
         alt="<?= htmlspecialchars($product['title']) ?>" 
         style="max-width: 100%; border-radius: 10px;">

    <p style="margin-top: 1rem;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <p><strong>Τιμή:</strong> €<?= number_format($product['price'], 2) ?></p>

    <button id="add-to-cart" 
            data-id="<?= htmlspecialchars($product['id']) ?>" 
            data-title="<?= htmlspecialchars($product['title']) ?>" 
            data-price="<?= htmlspecialchars($product['price']) ?>">
        ➕ Προσθήκη στο καλάθι
    </button>
</main>

<footer>
    <p>&copy; 2025 Amanda's Art eShop</p>
</footer>

<script>
// Προσθήκη στο καλάθι (LocalStorage)
document.getElementById('add-to-cart').addEventListener('click', function() {
    const id = this.dataset.id;
    const title = this.dataset.title;
    const price = parseFloat(this.dataset.price);

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Αν υπάρχει ήδη, αύξησε την ποσότητα
    const existing = cart.find(item => item.id == id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ id, title, price, quantity: 1 });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    alert(`Το έργο "${title}" προστέθηκε στο καλάθι!`);
});
</script>

</body>
</html>
