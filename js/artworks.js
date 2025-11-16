/*
Αρχιτεκτονικός ρόλος:
Συνδέει το backend API (μέσω PHP) με το user interface, παρέχοντας
δυναμική φόρτωση, ανέβασμα και αποθήκευση έργων.*/

/* 
  Φόρτωση όλων των έργων από το backend*/
async function fetchArtworks() {
    try {
        const response = await fetch('api/get_artworks.php');
        //const artworks = await response.json();
        const data = await response.json();
        if (data.success) {
            renderArtworks(data.data); // γιατί τα έργα βρίσκονται στο data.data
        }
       
    } catch (error) {
        console.error("Σφάλμα κατά την ανάκτηση των έργων:", error);
    }
}

/* 
  Απόδοση έργων στη σελίδα
 */
function renderArtworks(artworks) {
    const artworksContainer = document.getElementById('artworks');
    artworksContainer.innerHTML = '';

    artworks.forEach(artwork => {
        const artworkDiv = document.createElement('div');
        artworkDiv.classList.add('artwork');
        artworkDiv.innerHTML = `
      <img src="img/artworks/${artwork.image}" alt="${artwork.title}" />
      <h3>${artwork.title}</h3>
      <p>${artwork.description || ''}</p>
      <p>Τιμή: €${parseFloat(artwork.price).toFixed(2)}</p>
      <button onclick="addToCart(${artwork.id}, '${escapeQuotes(artwork.title)}', ${artwork.price})">
        Προσθήκη στο καλάθι
      </button>
    `;
        artworksContainer.appendChild(artworkDiv);
    });
}

/*
  Προστασία από quotes στους τίτλους
*/
function escapeQuotes(str) {
    return str.replace(/'/g, "\\'");
}

/* 
  Καλάθι (localStorage)
*/
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function addToCart(id, title, price) {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ id, title, price, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const el = document.getElementById('cart-count');
    if (el) el.textContent = count;
}

/* ---------------------------
  Αρχικοποίηση στη φόρτωση σελίδας
------------------------------ */
window.addEventListener('DOMContentLoaded', () => {
    fetchArtworks();
    updateCartCount();
    setupUploadAndForm();
});

/* 
  Upload εικόνας + αποθήκευση νέου έργου
 */
function setupUploadAndForm() {
    const uploadForm = document.getElementById("uploadForm");
    const artworkForm = document.getElementById("artworkForm");
    const uploadStatus = document.getElementById("uploadStatus");
    const imageField = document.getElementById("artworkImageField");

    // ----- Ανέβασμα εικόνας -----
    if (uploadForm) {
        uploadForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(uploadForm);
            uploadStatus.textContent = "Ανέβασμα...";
            try {
                const res = await fetch("api/upload_image.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    uploadStatus.textContent = "Η εικόνα ανέβηκε επιτυχώς!";
                    imageField.value = data.filename;
                } else {
                    uploadStatus.textContent = "Σφάλμα: " + data.error;
                }
            } catch (err) {
                console.error(err);
                uploadStatus.textContent = "Σφάλμα κατά το ανέβασμα.";
            }
        });
    }

    // ----- Αποθήκευση νέου έργου -----
    if (artworkForm) {
        artworkForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const title = document.getElementById("artworkTitle").value.trim();
            const price = document.getElementById("artworkPrice").value.trim();
            const image = imageField.value;

            if (!image) {
                alert("Πρέπει πρώτα να ανεβάσεις εικόνα!");
                return;
            }

            try {
                const res = await fetch("api/add_artwork.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ title, price, image })
                });
                const data = await res.json();

                if (data.success) {
                    alert("Το έργο αποθηκεύτηκε επιτυχώς!");
                    fetchArtworks(); // Επαναφόρτωση της λίστας χωρίς reload
                    artworkForm.reset();
                    uploadForm.reset();
                    uploadStatus.textContent = "";
                    imageField.value = "";
                } else {
                    alert("Σφάλμα: " + data.message);
                }
            } catch (err) {
                console.error(err);
                alert("Σφάλμα κατά την αποθήκευση του έργου.");
            }
        });
    }
}
