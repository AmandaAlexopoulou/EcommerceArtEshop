<?php
session_start();
include 'api/db.php'; // Σύνδεση με τη βάση δεδομένων

// Έλεγχος αν το αίτημα είναι μέθοδος POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Έλεγχος αν υπάρχει ήδη ο χρήστης με το συγκεκριμένο email
    $stmt = $db->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetchColumn();

    if ($existingUser > 0) {
        // Αν υπάρχει ήδη χρήστης με το ίδιο email, παραπέμπουμε τον χρήστη να συνδεθεί
        echo json_encode(['success' => false, 'message' => 'Το email υπάρχει ήδη. Παρακαλώ χρησιμοποιήστε τα στοιχεία του λογαριασμού σας για σύνδεση.']);
        exit;
    }

    // Ορίζουμε το role_id για τον χρήστη
    // Αν ο χρήστης είναι η AmandaAlexopoulou, τότε θα έχει ρόλο admin (role_id = 1)
    $role_id = ($email === 'am.alexopoulou@gmail.com') ? 1 : 2; // Αν είναι η AmandaAlexopoulou, ρόλος 1 (admin), αλλιώς ρόλος 2 (customer)

    // Εισαγωγή του νέου χρήστη στον πίνακα customers
    $stmt = $db->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
    $stmt->execute([$username, $email]);

    // Αποθήκευση του χρήστη με τον κωδικό που έχει κρυπτογραφηθεί στον πίνακα users
    $stmt = $db->prepare("INSERT INTO users (username, email, password, role_id, customer_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $email, $password, $role_id, $db->lastInsertId()]);

    // Επιστροφή επιτυχίας
    echo json_encode(['success' => true, 'message' => 'Η εγγραφή ολοκληρώθηκε επιτυχώς!']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
    <title>Εγγραφή</title>
</head>
<body>
    <h1>Καλωσήρθατε στο Amanda's Art Eshop!</h1>
    <p>Συμπληρώστε τα στοιχεία σας για να εγγραφείτε και να ξεκινήσετε τις αγορές σας.</p>

    <!-- Εδώ υπάρχει μια φόρμα εγγραφής -->
    <form id="register-form" action="register.php" method="POST">
        <input type="text" name="username" placeholder="Όνομα Χρήστη" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Κωδικός" required />
        <button type="submit">Εγγραφή</button>
    </form>

    <p>Έχετε ήδη λογαριασμό; <a href="login.php">Συνδεθείτε εδώ</a></p>

    <script>
        document.getElementById('register-form').addEventListener('submit', function(event) {
            event.preventDefault();

            let formData = new FormData(this);

            // Αποστολή της φόρμας με AJAX
            fetch('register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Αν η εγγραφή είναι επιτυχής, κάνε ανακατεύθυνση ή εμφάνιση μηνύματος
                    alert(data.message);
                    window.location.href = 'login.php'; // Ανακατεύθυνση στη σελίδα σύνδεσης
                } else {
                    // Εμφάνιση του μηνύματος λάθους
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('Σφάλμα κατά την εγγραφή. Παρακαλώ δοκιμάστε ξανά.');
            });
        });
    </script>
</body>
</html>
