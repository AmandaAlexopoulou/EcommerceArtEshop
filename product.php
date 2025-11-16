<?php
// Include the database connection
require_once 'db.php';

// Check if 'id' parameter exists and is a number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

// Cast 'id' to integer to prevent injection
$id = (int)$_GET['id'];

// Prepare SQL query to prevent SQL injection
$stmt = $db->prepare("SELECT * FROM artworks WHERE id = ?");
$stmt->execute([$id]);

// Fetch the product as an associative array
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the product exists
if (!$product) {
    die("<p>Product not found.</p>");
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
    <h1>eShop Artworks</h1>
    <nav>
        <a href="index.html">🏠 Home</a>
        <a href="cart.html">🛒 Cart</a>
    </nav>
</header>

<main style="max-width: 800px; margin: 2rem auto; padding: 1rem;">
    <h2><?= htmlspecialchars($product['title']) ?></h2>

    <!-- Product image -->
    <img src="img/artworks/<?= htmlspecialchars($product['image']) ?>" 
         alt="<?= htmlspecialchars($product['title']) ?>" 
         style="max-width: 100%; border-radius: 10px;">

    <!-- Product description -->
    <p style="margin-top: 1rem;"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <!-- Product price -->
    <p><strong>Price:</strong> €<?= number_format($product['price'], 2) ?></p>

    <!-- Add to cart button with data attributes -->
    <button id="add-to-cart" 
            data-id="<?= htmlspecialchars($product['id']) ?>" 
            data-title="<?= htmlspecialchars($product['title']) ?>" 
            data-price="<?= htmlspecialchars($product['price']) ?>">
        ➕ Add to cart
    </button>
</main>

<footer>
    <p>&copy; 2025 Amanda's Art eShop</p>
</footer>

<script>
// Add product to cart using LocalStorage
document.getElementById('add-to-cart').addEventListener('click', function() {
    const id = this.dataset.id;
    const title = this.dataset.title;
    const price = parseFloat(this.dataset.price);

    // Retrieve existing cart or initialize a new array
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Check if product already exists in cart
    const existing = cart.find(item => item.id == id);
    if (existing) {
        // Increase quantity if already exists
        existing.quantity += 1;
    } else {
        // Add new product to cart
        cart.push({ id, title, price, quantity: 1 });
    }

    // Save updated cart to LocalStorage
    localStorage.setItem('cart', JSON.stringify(cart));

    // Notify user
    alert(`Product "${title}" has been added to your cart!`);
});
</script>

</body>
</html>
