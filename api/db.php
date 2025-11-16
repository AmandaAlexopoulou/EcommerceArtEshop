<?php
$dbPath = __DIR__ . '/db/artstore.sqlite';
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Σφάλμα βάσης: ' . $e->getMessage()]));
}
?>
