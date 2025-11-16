/*======================================================
  Public + Admin Artworks & Cart Module
  ------------------------------------------------------
  Λειτουργικότητες:
  1. Load artworks from API
  2. Render gallery (public)
  3. Cart management (localStorage)
  4. Checkout & send invoice
  5. Admin operations: save, delete, upload
======================================================*/

// -------------------- 1. Global cart --------------------
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// -------------------- 2. Fetch artworks --------------------
async function fetchArtworks() {
    try {
        const res = await fetch('api/get_artworks.php');
        const text = await res.text();

        let data;
        try {
            data = JSON.parse(text);
        } catch (parseErr) {
            // Διορθώνει escaped quotes
            data = JSON.parse(text.replace(/\\"/g, '"'));
        }

        if (data.success && Array.isArray(data.data)) {
            const cleanedData = data.data.map(item => {
                let imageName = item.image ? item.image.replace(/["\\]/g, '').trim() : '';
                if (!imageName) imageName = 'placeholder.jpg';
                return {
                    ...item,
                    image: imageName,
                    price: parseFloat(item.price) || 0  // ✅ Εξασφαλίζει αριθμητική τιμή
                };
            });
            renderArtworks(cleanedData);
        } else {
            console.error('❌ API returned failure or empty data:', data);
        }
    } catch (err) {
        console.error('Σφάλμα κατά την ανάκτηση των έργων:', err);
    }
}

// -------------------- 3. Render artworks --------------------
function renderArtworks(artworks) {
    const container = document.getElementById('artworks');
    if (!container) return;
    container.innerHTML = '';

    artworks.forEach(art => {
        const div = document.createElement('div');
        div.classList.add('artwork');

        div.innerHTML = `
            <img src="img/artworks/${art.image}" alt="${escapeQuotes(art.title)}">
            <h3>${escapeQuotes(art.title)}</h3>
            <p>${art.description || ''}</p>
            <p>Τιμή: €${art.price.toFixed(2)}</p>
            <button onclick="addToCart(${art.id}, '${escapeQuotes(art.title)}', ${art.price})">
                Προσθήκη στο καλάθι
            </button>
        `;
        container.appendChild(div);
    });
}

// -------------------- 4. Escape single quotes --------------------
function escapeQuotes(str) {
    if (!str) return '';
    return str.replace(/'/g, "\\'");
}

// -------------------- 5. Add to cart --------------------
function addToCart(id, title, price) {
    // Μετατρέπει σε αριθμό για σωστούς υπολογισμούς
    const numericPrice = parseFloat(price) || 0;
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ id, title, price: numericPrice, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

// -------------------- 6. Update cart counter --------------------
function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const counterEl = document.getElementById('cart-count');
    if (counterEl) counterEl.textContent = count;
}

// -------------------- 7. Checkout --------------------
async function checkout(customerName, customerEmail) {
    if (cart.length === 0) {
        alert('Το καλάθι είναι άδειο.');
        return;
    }

    try {
        const res = await fetch('./api/process_checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart, customer_name: customerName, customer_email: customerEmail })
        });

        const result = await res.json();
        if (result.success) {
            alert('Η παραγγελία ολοκληρώθηκε επιτυχώς!');
            const orderId = result.order_id;
            cart = [];
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            await sendInvoice(orderId);
        } else {
            console.error(result);
            alert('Σφάλμα κατά την ολοκλήρωση της παραγγελίας.');
        }
    } catch (err) {
        console.error(err);
        alert('Σφάλμα σύνδεσης με τον server.');
    }
}

// -------------------- 8. Send invoice --------------------
async function sendInvoice(orderId) {
    try {
        const res = await fetch('./api/send_invoice.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        });

        const result = await res.json();
        if (result.message) {
            console.log('Invoice sent:', result);
        } else {
            console.error('Invoice error:', result);
        }
    } catch (err) {
        console.error('Σφάλμα σύνδεσης:', err);
    }
}

// -------------------- 9. Admin: Save artwork --------------------
async function saveArtwork({ id = null, title, image, price }) {
    try {
        const res = await fetch('./api/save_artwork.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, title, image, price: parseFloat(price) || 0 })
        });

        const result = await res.json();
        if (result.success) {
            alert(result.message);
            fetchArtworks();
        } else {
            console.error(result);
            alert('Σφάλμα αποθήκευσης έργου.');
        }
    } catch (err) {
        console.error(err);
        alert('Σφάλμα σύνδεσης με τον server.');
    }
}

// -------------------- 10. Admin: Delete artwork --------------------
async function deleteArtwork(id) {
    if (!confirm('Είσαι σίγουρος ότι θέλεις να διαγράψεις το έργο;')) return;

    try {
        const res = await fetch('./api/delete_artworks.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const result = await res.json();
        if (result.success) {
            alert(result.message);
            fetchArtworks();
        } else {
            console.error(result);
            alert('Σφάλμα κατά τη διαγραφή έργου.');
        }
    } catch (err) {
        console.error(err);
        alert('Σφάλμα σύνδεσης με τον server.');
    }
}

// -------------------- 11. Admin: Upload image --------------------
async function uploadImage(fileInput) {
    const file = fileInput.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    try {
        const res = await fetch('./api/upload_image.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();
        if (result.success) {
            alert('Η εικόνα ανέβηκε: ' + result.filename);
            return result.filename;
        } else {
            alert('Σφάλμα: ' + result.error);
        }
    } catch (err) {
        console.error(err);
        alert('Σφάλμα κατά το ανέβασμα της εικόνας.');
    }
}

// -------------------- 12. Initialization --------------------
window.addEventListener('DOMContentLoaded', () => {
    fetchArtworks();
    updateCartCount();
});
