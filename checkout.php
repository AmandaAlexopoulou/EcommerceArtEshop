<!DOCTYPE html>
<html lang="el">
<head>
    <!-- Set character encoding to UTF-8 to support Greek characters -->
    <meta charset="UTF-8">
    
    <!-- Ensure the page is responsive on mobile devices (viewport configuration) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Title of the page, which appears in the browser tab -->
    <title>Ολοκλήρωση Παραγγελίας</title>
    
    <!-- Link to the external CSS file for styling -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <!-- Navigation bar -->
    <nav>
        <!-- Link to the shopping cart page with an element showing the current cart item count -->
        <a href="cart.html">🛒 Το καλάθι μου (<span id="cart-count">0</span>)</a>
    </nav>
    
    <!-- Main header for the checkout page -->
    <h1>Ολοκλήρωση Παραγγελίας</h1>
</header>

<main>
    <!-- Form for collecting checkout information from the user -->
    <form id="checkout-form">
        
        <!-- Field for entering the customer's name -->
        <label for="name">Όνομα:</label>
        <input type="text" id="name" required><br><br>

        <!-- Field for entering the customer's email address -->
        <label for="email">Email:</label>
        <input type="email" id="email" required><br><br>

        <!-- Section showing the items currently in the shopping cart -->
        <h3>Προϊόντα στο καλάθι:</h3>
        <ul id="cart-items"></ul>
        
        <!-- Display the total amount for the items in the cart -->
        <p>Συνολικό ποσό: €<span id="total">0.00</span></p>

        <!-- Submit button to finalize the order -->
        <button type="submit">Ολοκλήρωση Παραγγελίας</button>
    </form>
</main>

<footer>
    <!-- Footer content with copyright information -->
    <p>&copy; 2025 eShop Ζωγραφικών Έργων</p>
</footer>

<!-- JS SCRIPTS -->
<!-- External JavaScript for public functionalities, possibly for shared components like the cart -->
<script src="js/public.js"></script>
<!-- JavaScript specific to the checkout page -->
<script src="js/checkout.js"></script>

</body>
</html>
