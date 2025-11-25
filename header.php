<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../css/style.css">
<title>Artstore Admin</title>
</head>
<body>

<nav>
    <a href="http://localhost/ArtEshop/dashboard.php">Dashboard</a>
    <a href="http://localhost/ArtEshop/customers.php">Customers</a>
    <a href="http://localhost/ArtEshop/api/invoices_view.php">Invoices</a>
    <a href="http://localhost/ArtEshop/api/invoice_logs_view.php">Invoice Logs</a>
	<a href="http://localhost/ArtEshop/api/order_view.php">Orders</a>
	<a href="http://localhost/ArtEshop/login.php">Back To Artworks</a>

</nav>

<div class="container">

<script>
// Load saved theme
if (localStorage.dark === "1") document.body.classList.add("dark");

// Toggle theme
document.getElementById('darkToggle').onclick = function(){
    document.body.classList.toggle("dark");
    localStorage.dark = document.body.classList.contains("dark") ? "1" : "0";
};
</script>

