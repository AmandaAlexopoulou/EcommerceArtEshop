<?php
include "db.php";
include "../header.php";
?>

<h2>All Invoice Logs</h2>

<table border="1" cellpadding="6" cellspacing="0">
<tr style="background:#f0f0f0;">
    <th>Log ID</th>
    <th>Invoice ID</th>
    <th>Sent At</th>
    <th>Status</th>
    <th>Response</th>
</tr>

<?php
// Παίρνουμε όλα τα logs χωρίς φίλτρο
$logsStmt = $db->prepare("SELECT * FROM invoice_logs ORDER BY id DESC");
$logsStmt->execute();
$logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$logs) {
    echo "<tr><td colspan='5'><i>No logs found</i></td></tr>";
} else {
    foreach ($logs as $log) {
        echo "<tr>
                <td>{$log['id']}</td>
                <td>{$log['invoice_id']}</td>
                <td>{$log['sent_at']}</td>
                <td>{$log['status']}</td>
                <td><pre>".htmlspecialchars($log['response'])."</pre></td>
              </tr>";
    }
}
?>

</table>

<?php include "../footer.php"; ?>
