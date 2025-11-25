<?php include "header.php"; ?>
<link rel = "stylesheet" href = "css/style.css">
<a href="dashboard.php" class="btn">← Back to Dashboard</a>
<br><br>

<h2>Customers</h2>

<input type="text" id="search" placeholder="Search name or email">
<div id="results"></div>

<script>
function load(q = "") {
    fetch("api/customers_ajax.php?q=" + encodeURIComponent(q))
        .then(res => res.text())
        .then(html => document.getElementById("results").innerHTML = html);
}

document.getElementById("search").onkeyup = e => load(e.target.value);

load();
</script>

<?php include "footer.php"; ?>
