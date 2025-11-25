<?php
// logout.php
session_start();
session_unset();  // Διαγράφει όλες τις μεταβλητές της συνεδρίας
session_destroy(); // Καταστρέφει τη συνεδρία
setcookie(session_name(), '', time() - 3600, '/'); // Καταστρέφει το cookie συνεδρίας
header('Location: index.php');  // Ανακατεύθυνση στην αρχική σελίδα
exit;
?>
