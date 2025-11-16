<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ορισμός του τύπου περιεχομένου ως JSON
header('Content-Type: application/json');

// Έλεγχος αν το request είναι POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Μέθοδος όχι επιτρεπόμενη
    echo json_encode(['success' => false, 'message' => 'Μόνο POST επιτρέπεται']);
    exit;
}

// Ανάγνωση δεδομένων JSON από το αίτημα
$input = json_decode(file_get_contents('php://input'), true);

// Έλεγχος αν τα απαιτούμενα δεδομένα υπάρχουν και είναι σωστά
$title = trim($input['title'] ?? '');
$price = floatval($input['price'] ?? 0);
// Εισάγουμε μόνο το όνομα της εικόνας π.χ. eikona.jpg στη βάση
$image = trim($input['image'] ?? '');
$description = trim($input['description'] ?? '');

if ($title === '' || $price <= 0 || $image === '' || $description === '') {
    http_response_code(400); // Λείπουν απαραίτητα πεδία
    echo json_encode(['success' => false, 'message' => 'Λείπουν απαραίτητα πεδία']);
    exit;
}

// Σύνδεση στη βάση δεδομένων
require_once __DIR__ . '/db.php';

// Προετοιμασία του SQL query για την εισαγωγή του έργου
try {
    $stmt = $db->prepare("INSERT INTO artworks (title, description, price, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $price, $image]);

    // Επιστροφή μηνύματος επιτυχίας
    echo json_encode(['success' => true, 'message' => 'Το έργο αποθηκεύτηκε επιτυχώς']);
} catch (Exception $e) {
    // Σφάλμα κατά την εισαγωγή στη βάση
    http_response_code(500); // Σφάλμα server
    echo json_encode(['success' => false, 'message' => 'Σφάλμα βάσης: ' . $e->getMessage()]);
}
?>
