<?php
require_once('db.php');  // Περιλαμβάνουμε το αρχείο db.php για σύνδεση

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Διαβάζουμε τα δεδομένα που ήρθαν από το front-end
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'])) {
        $artworkId = (int) $data['id'];

        // Το SQL query για τη διαγραφή με SQLite
        $sql = "DELETE FROM artworks WHERE id = :id";

        try {
            // Δημιουργούμε το prepared statement για SQLite
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $artworkId, PDO::PARAM_INT);
            $stmt->execute();

            // Επιστροφή επιτυχίας σε μορφή JSON
            echo json_encode(['success' => true, 'message' => 'Το έργο διαγράφηκε επιτυχώς!']);
        } catch (PDOException $e) {
            // Αν υπάρξει σφάλμα, επιστρέφουμε το σφάλμα σε μορφή JSON
            echo json_encode(['success' => false, 'error' => 'Σφάλμα κατά τη διαγραφή του έργου: ' . $e->getMessage()]);
        }
    } else {
        // Αν το ID δεν βρέθηκε, επιστρέφουμε σφάλμα
        echo json_encode(['success' => false, 'error' => 'Απουσιάζει το πεδίο ID.']);
    }
}
?>
