<?php
session_start();
include 'api/db.php'; // Σύνδεση με τη βάση δεδομένων

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
$isLoggedIn = isset($_SESSION['user_id']);

// Ελέγχουμε αν ο χρήστης έχει το σωστό ρόλο
$isAdmin = false;
if ($isLoggedIn) {
    // Λήψη του role_id του χρήστη
    $userId = $_SESSION['user_id'];
    $stmt = $db->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Αν υπάρχει το user και το role_id του είναι 1 (admin)
    if ($user && $user['role_id'] == 1) {
        $isAdmin = true;
    }
}

// Φόρτωμα έργων από τη βάση
$stmt = $db->prepare("SELECT * FROM artworks");
$stmt->execute();
$artworks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Amanda's Art eShop</title>
    <link rel="stylesheet" href="css/style.css?v=1.1" />
    <style>
        /* Στυλ για καλύτερη εμφάνιση */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #fafafa;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2a2a72;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-weight: 600;
        }

        nav a {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem;
            background-color: #ffde59;
            color: #2a2a72;
            font-weight: 700;
            border-radius: 20px;
            text-decoration: none;
            font-size: 1.1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        }

        nav a:hover {
            background-color: #f0c419;
            color: #1a1a4d;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
        }

        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .artwork-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .artwork-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        footer {
            background: #2a2a72;
            color: #fff;
            text-align: center;
            padding: 1rem 0;
            margin-top: 3rem;
        }

        .form-container {
            margin-top: 2rem;
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container input, .form-container textarea, .form-container button {
            width: 100%;
            padding: 0.8rem;
            margin: 0.5rem 0;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>eShop Ζωγραφικών Έργων</h1>
        <nav>
            <a href="cart.html">🛒 Το καλάθι μου (<span id="cart-count">0</span>)</a>
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" id="logout-btn">Αποσύνδεση</a>
            <?php else: ?>
                <a href="login.php">Σύνδεση</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <section id="artworks" class="artwork-grid" aria-label="Λίστα έργων">
            <?php foreach ($artworks as $artwork): ?>
            <div class="artwork-item" data-id="<?= $artwork['id'] ?>">
                <img src="img/artworks/<?= $artwork['image'] ?>" alt="<?= $artwork['title'] ?>">
                <h3><?= $artwork['title'] ?></h3>
                <p><?= $artwork['description'] ?></p>
                <p>Τιμή: <?= $artwork['price'] ?> €</p>

                <!-- Κουμπί προσθήκης στο καλάθι για όλους τους χρήστες -->
                <?php if (!$isAdmin): ?>
                    <button class="add-to-cart-btn" data-id="<?= $artwork['id'] ?>">Προσθήκη στο Καλάθι</button>
                <?php endif; ?>

                <!-- Διαγραφή έργου μόνο για admin -->
                <?php if ($isAdmin): ?>
                    <button class="delete-artwork-btn" data-id="<?= $artwork['id'] ?>">Διαγραφή</button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </section>

         <!-- Κουμπί για το Dashboard μόνο για admin -->
    <section class="form-container">
        <h2>Admin Dashboard</h2>
        <a href="dashboard.php" class="btn">Go to Dashboard</a>
    </section>
		
		<?php if ($isAdmin): ?>
		 <!-- Φόρμα για προσθήκη νέου έργου μόνο για admin -->
            <section class="form-container">
                <h2>Προσθήκη Νέου Έργου</h2>
                <form id="add-artwork-form">
                    <input type="text" id="title" placeholder="Τίτλος" required />
                    <textarea id="description" placeholder="Περιγραφή" required></textarea>
                    <input type="number" id="price" placeholder="Τιμή" required />
                    <input type="text" id="image" placeholder="URL Εικόνας" required />
                    <button type="submit">Προσθήκη Έργου</button>
                </form>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Amanda's Art eShop</p>
    </footer>

    <script>

document.addEventListener('DOMContentLoaded', () => {
    // Συνάρτηση για την προσθήκη έργου στο καλάθι
    function addToCart(artworkId) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const artworkElement = document.querySelector(`.artwork-item[data-id='${artworkId}']`);
        const title = artworkElement.querySelector('h3').textContent;
        const priceText = artworkElement.querySelector('p:nth-of-type(2)').textContent;
        const price = parseFloat(priceText.replace('Τιμή: ', '').replace(' €', ''));

        // Έλεγχος αν το έργο υπάρχει ήδη στο καλάθι
        const existingItemIndex = cart.findIndex(item => item.id === artworkId);

        if (existingItemIndex !== -1) {
            // Αυξήστε την ποσότητα αν το έργο υπάρχει ήδη στο καλάθι
            cart[existingItemIndex].quantity += 1;
        } else {
            // Προσθέτουμε το νέο έργο στο καλάθι
            cart.push({ id: artworkId, title, price, quantity: 1 });
        }

        // Αποθήκευση του καλαθιού στο localStorage
        localStorage.setItem('cart', JSON.stringify(cart));

        // Ενημέρωση του αριθμού των προϊόντων στο καλάθι
        updateCartCount();
    }

    // Συνάρτηση για ενημέρωση του αριθμού των προϊόντων στο καλάθι
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
        document.getElementById('cart-count').textContent = cartCount;
    }

    // Ενημέρωση του αριθμού των προϊόντων όταν φορτώνει η σελίδα
    updateCartCount();

    // Προσθήκη event listener για τα κουμπιά προσθήκης στο καλάθι
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const artworkId = e.target.getAttribute('data-id');
            addToCart(artworkId);
        });
    });

    // Διαχείριση φόρμας προσθήκης έργου (μόνο για admin)
    const form = document.getElementById('add-artwork-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;
            const price = parseFloat(document.getElementById('price').value);
            const image = document.getElementById('image').value;

            const newArtwork = { title, description, price, image };

            fetch('api/add_artwork.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(newArtwork)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const artworkElement = document.createElement('div');
                    artworkElement.classList.add('artwork-item');
                    artworkElement.setAttribute('data-id', data.id);
                    artworkElement.innerHTML = `
                        <img src="img/artworks/${image}" alt="${title}">
                        <h3>${title}</h3>
                        <p>${description}</p>
                        <p>Τιμή: ${price} €</p>
                        <button class="delete-artwork-btn">Διαγραφή</button>
                    `;
                    document.getElementById('artworks').appendChild(artworkElement);
                } else {
                    alert('Σφάλμα κατά την προσθήκη του έργου');
                }
            })
            .catch(error => console.error('Σφάλμα κατά την προσθήκη έργου:', error));
        });
    }

    // Διαχείριση διαγραφής έργου (μόνο για admin)
    const deleteButtons = document.querySelectorAll('.delete-artwork-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const artworkId = e.target.getAttribute('data-id');
            deleteArtwork(artworkId, e.target.closest('.artwork-item'));
        });
    });

    // Διαγραφή έργου από τη βάση δεδομένων και τη σελίδα
    function deleteArtwork(id, artworkElement) {
        fetch('api/delete_artworks.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                artworkElement.remove();
            } else {
                alert('Σφάλμα κατά τη διαγραφή του έργου');
            }
        })
        .catch(error => console.error('Σφάλμα κατά τη διαγραφή:', error));
    }
});
    </script>
</body>
</html>
