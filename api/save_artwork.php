<?php
require_once '../db.php'; // Σύνδεση με τη βάση

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Μόνο POST επιτρέπεται']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['title'], $data['image'], $data['price'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Λείπουν δεδομένα']);
    exit;
}

$title = $data['title'];
$image = $data['image'];
$price = floatval($data['price']);

// Αν υπάρχει ID, κάνουμε update
if (isset($data['id']) && !empty($data['id'])) {
    $stmt = $pdo->prepare("UPDATE artworks SET title = ?, image = ?, price = ? WHERE id = ?");
    $stmt->execute([$title, $image, $price, $data['id']]);
} else {
    // Νέα εγγραφή
    $stmt = $pdo->prepare("INSERT INTO artworks (title, image, price) VALUES (?, ?, ?)");
    $stmt->execute([$title, $image, $price]);
}

echo json_encode(['success' => true, 'message' => 'Το έργο αποθηκεύτηκε επιτυχώς']);
