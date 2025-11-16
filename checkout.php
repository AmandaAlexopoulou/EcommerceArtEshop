<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ολοκλήρωση Παραγγελίας</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <nav>
        <a href="cart.html">🛒 Το καλάθι μου (<span id="cart-count">0</span>)</a>
    </nav>
    <h1>Ολοκλήρωση Παραγγελίας</h1>
</header>

<main>
    <form id="checkout-form">
        <label for="name">Όνομα:</label>
        <input type="text" id="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" required><br><br>

        <h3>Προϊόντα στο καλάθι:</h3>
        <ul id="cart-items"></ul>
        <p>Συνολικό ποσό: €<span id="total">0.00</span></p>

        <button type="submit">Ολοκλήρωση Παραγγελίας</button>
    </form>
</main>

<footer>
    <p>&copy; 2025 eShop Ζωγραφικών Έργων</p>
</footer>

<!-- JS SCRIPTS -->
<script src="js/public.js"></script>
<script src="js/checkout.js"></script>

</body>
</html>
