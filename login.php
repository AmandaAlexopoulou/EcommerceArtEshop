<?php
session_start();
include 'api/db.php'; // Σύνδεση με τη βάση δεδομένων

// Έλεγχος αν ο χρήστης είναι ήδη συνδεδεμένος
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');  // Αν ο χρήστης είναι ήδη συνδεδεμένος, τον ανακατευθύνουμε στην αρχική σελίδα
    exit();  // Εδώ κλείνει η διαδικασία και αποφεύγουμε να συνεχίσει το script
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Λήψη των στοιχείων από τη φόρμα
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Έλεγχος αν τα πεδία είναι κενά
    if (empty($username) || empty($password)) {
        $error = 'Παρακαλώ συμπληρώστε όλα τα πεδία.';
    } else {
        // Προετοιμασία και εκτέλεση του SQL query για τον χρήστη
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Έλεγχος αν υπάρχει ο χρήστης και αν ο κωδικός είναι σωστός
        if ($user && password_verify($password, $user['password'])) {
            // Αποθήκευση των πληροφοριών στη session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];  // Χρησιμοποιούμε το role_id για περισσότερη ευχέρεια

            // Αν ο χρήστης θέλει να παραμείνει συνδεδεμένος (remember me functionality)
            if (isset($_POST['remember_me'])) {
                setcookie('user_id', $user['id'], time() + (60 * 60 * 24 * 30), '/'); // Το cookie διαρκεί για 30 μέρες
            }

            // Ανακατεύθυνση στην αρχική σελίδα
            header('Location: index.php');
            exit;
        } else {
            $error = 'Λάθος όνομα χρήστη ή κωδικός!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <title>Σύνδεση</title>
</head>
<body>
    <h1>Καλωσήρθατε στο Amanda's Art Eshop!</h1>
    <p>Συνδεθείτε για να κάνετε τις αγορές σας ή να δείτε τα αγαπημένα σας προϊόντα.</p>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <!-- Φόρμα σύνδεσης -->
    <form action="login.php" method="POST">
        <label for="username">Όνομα χρήστη</label>
        <input type="text" name="username" required>
        <br>
        
        <label for="password">Κωδικός</label>
        <input type="password" name="password" required>
        <br>

        <label>
            <input type="checkbox" name="remember_me"> Θυμήσου με
        </label>
        <br>
        
        <button type="submit">Σύνδεση</button>
    </form>
    
    <p>Δεν έχετε λογαριασμό; <a href="register.php">Εγγραφείτε εδώ</a></p>
</body>
</html>
