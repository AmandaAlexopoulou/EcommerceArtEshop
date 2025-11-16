<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        
        $uploadDir = __DIR__ . '/../img/artworks/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Μη αποδεκτός τύπος αρχείου']);
            exit;
        }

        // Προαιρετικά: δημιουργία μοναδικού ονόματος για να μην σβήνονται υπάρχοντα
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;

        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            echo json_encode(['success' => true, 'filename' => $fileName]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Αποτυχία ανέβασματος αρχείου']);
        }

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Δεν επιλέχθηκε αρχείο ή υπάρχει σφάλμα']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Μόνο POST επιτρέπεται']);
}
