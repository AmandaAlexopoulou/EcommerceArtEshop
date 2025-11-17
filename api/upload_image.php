<?php
// Set the response content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if a file is uploaded without errors
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        
        // Define the upload directory for images
        $uploadDir = __DIR__ . '/../img/artworks/';
        
        // If the directory doesn't exist, create it
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Get the temporary file name and the original file name
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        
        // Extract the file extension and convert it to lowercase
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check if the file extension is valid (jpg, jpeg, png, gif)
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            http_response_code(400); // Bad request response code
            // Return error if the file type is not supported
            echo json_encode(['error' => 'Μη αποδεκτός τύπος αρχείου']);
            exit;
        }

        // Optionally, create a unique filename by appending the current timestamp
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;

        // Define the target file path
        $targetFile = $uploadDir . $fileName;

        // Try to move the uploaded file to the target directory
        if (move_uploaded_file($fileTmp, $targetFile)) {
            // If successful, return a success response with the filename
            echo json_encode(['success' => true, 'filename' => $fileName]);
        } else {
            http_response_code(500); // Internal server error response code
            // Return error if the file couldn't be moved
            echo json_encode(['error' => 'Αποτυχία ανέβασματος αρχείου']);
        }

    } else {
        http_response_code(400); // Bad request response code
        // Return error if no file is uploaded or if there's an upload error
        echo json_encode(['error' => 'Δεν επιλέχθηκε αρχείο ή υπάρχει σφάλμα']);
    }
} else {
    http_response_code(405); // Method Not Allowed response code
    // Return error if the request method is not POST
    echo json_encode(['error' => 'Μόνο POST επιτρέπεται']);
}
