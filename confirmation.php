<?php
$date = $_GET['date'] ?? date('Y-m-d H:i');
$invoice = $_GET['invoice'] ?? '(άγνωστο)';
?>
<!DOCTYPE html>
<html lang="el">
<head>
  <meta charset="UTF-8">
  <title>Επιβεβαίωση Παραγγελίας</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main style="text-align:center; margin-top:50px;">
    <h1>🎉 Ευχαριστούμε για την παραγγελία σας!</h1>
    <p>Η παραγγελία σας δημιουργήθηκε στις <strong><?= htmlspecialchars($date) ?></strong>.</p>
    <p>Αρ. Παραστατικού: <strong><?= htmlspecialchars($invoice) ?></strong></p>
    <a href="index.html" class="btn">🔙 Επιστροφή στην αρχική</a>
  </main>
</body>
</html>
