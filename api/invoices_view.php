<?php include "../header.php"; ?>
<!--search + dynamic results + κουμπί view logs-->
<!--η ΚΥΡΙΑ ΣΕΛΙΔΑ που θα βλέπει ο χρήστης εδώ γίνεται ανακατεύθυνση-->

<h2>Invoices</h2>

<input type="text" id="search" placeholder="Search invoice number, status, provider invoice id..." style="width:300px;">
<div id="results"></div>

<script>
function load(q = "") {
    fetch("invoices_ajax.php?q=" + encodeURIComponent(q))
        .then(res => res.text())
        .then(html => document.getElementById("results").innerHTML = html);
}

document.getElementById("search").onkeyup = e => load(e.target.value);

load(); // load all on page open
</script>

<?php include "../footer.php"; ?>
