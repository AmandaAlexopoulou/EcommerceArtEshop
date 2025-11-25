<?php
include "../header.php";
//λίστα παραγγελιών
?>

<h2>Orders</h2>

<input type="text" id="search" placeholder="Search by customer or order ID..." style="width:300px;">
<div id="results"></div>

<script>
function load(q = "") {
    fetch("orders_ajax.php?q=" + encodeURIComponent(q))
        .then(res => res.text())
        .then(html => document.getElementById("results").innerHTML = html);
}

document.getElementById("search").onkeyup = e => load(e.target.value);

// load all orders initially
load();
</script>

<?php include "../footer.php"; ?>
