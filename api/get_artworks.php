<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = new PDO('sqlite:' . __DIR__ . '/db/artstore.sqlite'); // Δημιουργία σύνδεσης με βάση δεδομένων
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ανάκτηση των έργων από τη βάση δεδομένων
    $stmt = $db->query("SELECT * FROM artworks ORDER BY id DESC");
    $artworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Επιστροφή των έργων σε JSON μορφή
    echo json_encode([
        'success' => true,
        'data' => $artworks
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Αν υπάρχει σφάλμα στην επικοινωνία με τη βάση δεδομένων
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
