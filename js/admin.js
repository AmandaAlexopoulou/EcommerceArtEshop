// ============================
// Admin Panel Script for Artworks
/*Συνδεδεμένα API endpoints:
Endpoint	Περιγραφή
api / get_artworks.php	Φέρνει τη λίστα έργων.
    api / save_artwork.php	Αποθηκεύει(insert / update) έργο.
        api / upload_image.php	Χειρίζεται upload εικόνας.
            api / delete_artwork.php	Διαγράφει έργο από τη βάση.
            
 Αρχιτεκτονικός ρόλος:
Αποτελεί το management module του συστήματος.
Χειρίζεται CRUD λειτουργίες για έργα τέχνης (artworks), με πλήρη επικοινωνία μέσω JSON API.
            
            */
// ============================

// Store all artworks fetched from the server
let artworks = [];

// Get references to HTML elements used in the admin interface
const form = document.getElementById("artwork-form");         // The form for adding/updating artwork
const listContainer = document.getElementById("artwork-list"); // Container where artworks are displayed
const idField = document.getElementById("artwork-id");         // Hidden input field for the artwork ID (used in edit mode)
//Δημιουργεί JSON payload και στέλνει POST στο save_artwork.php.
/
// ===================================
// 1. Handle Artwork Form Submission
// ===================================

//Υποβολή Φόρμας (Προσθήκη/Ενημέρωση)
form.addEventListener("submit", async (e) => {
    e.preventDefault(); // Prevent default form behavior (e.g. page reload)

    // Get ID from hidden field – if present, we're editing an existing artwork
    const id = idField.value;
    

    // Create a new artwork object from form inputs
    const newArt = {
        title: form.title.value,
        image: form.image.value,
        price: parseFloat(form.price.value), // Convert price string to float
    };

    // If ID exists, include it (update mode)
    if (id) {
        newArt.id = id;
    }

    try {
        // Send artwork data to backend via POST
        const res = await fetch('api/save_artwork.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newArt)
        });

        const result = await res.json(); // Parse JSON response

        if (result.success) {
            alert(result.message);     // Show success message
            idField.value = '';        // Clear ID field
            form.reset();              // Reset form
            await loadArtworks();      // Reload artworks list
        } else {
            alert('Σφάλμα: ' + (result.error || 'Άγνωστο')); // Show error message
        }

    } catch (err) {
        alert('Σφάλμα δικτύου'); // Show network error
    }
});


// ===================================
// 2. Handle Image File Upload (AJAX)
// ===================================

/*Αν είναι επιτυχής  η υποβολή της φόρμας → reset φόρμας + επαναφόρτωση λίστας 
Ανέβασμα Εικόνας (AJAX Upload) */

document.getElementById('imageFile').addEventListener('change', async (e) => {
    const file = e.target.files[0]; // Get selected file

    if (!file) return; // Exit if no file selected
    //Αν είναι επιτυχές → reset φόρμας + επαναφόρτωση λίστας τοτε γίνεται
//ανέβασμα εικόνας(AJAX Upload)

    const formData = new FormData(); // Create form data
    formData.append('image', file);  // Append image file
    /**Στέλνει multipart request.
Αν επιτυχές, ενημερώνει το κρυφό form.image.value. */
    try {
        // Send image to backend
        const res = await fetch('api/upload_image.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json(); // Get JSON response

        if (data.success) {
            form.image.value = data.filename; // Set hidden input with image filename
            alert('Η εικόνα ανέβηκε επιτυχώς!');
        } else {
            alert('Σφάλμα: ' + (data.error || 'Άγνωστο'));
        }

    } catch {
        alert('Σφάλμα κατά την αποστολή της εικόνας');
    }
});


// ===================================
// 3. Load All Artworks from Backend
// ===================================


//Φόρτωση Έργων:
async function loadArtworks() {
    const res = await fetch('api/get_artworks.php'); // Get artworks from API
    artworks = await res.json();                     // Store in global variable
    renderArtworks();                                // Render in the admin panel
}


// ===================================
// 4. Render Artworks in the Admin List
// ===================================

function renderArtworks() {
    listContainer.innerHTML = ""; // Clear previous list

    // Loop through each artwork and create a DOM element
    artworks.forEach((art) => {
        const div = document.createElement("div");
        div.className = "artwork";

        // Inner HTML for each artwork entry
        div.innerHTML = `
      <img src="img/artworks/${art.image}" alt="${art.title}">
      <h3>${art.title}</h3>
      <p>${art.price}€</p>
      <button onclick="editArtwork(${art.id})">✏️</button>
      <button onclick="deleteArtwork(${art.id})">🗑️</button>
    `;

        listContainer.appendChild(div); // Add to container
    });
}


// ===================================
// 5. Edit Artwork (Populate Form)
// ===================================

function editArtwork(id) {//προγεμίζει φόρμα για edit.
    const art = artworks.find(a => a.id == id); // Find artwork by ID
    if (!art) return;

    // Populate form fields with artwork data
    idField.value = art.id;
    form.title.value = art.title;
    form.image.value = art.image;
    form.price.value = art.price;
}


// ===================================
// 6. Delete Artwork (Optional)
// ===================================

async function deleteArtwork(id) {//τέλνει DELETE request με επιβεβαίωση.
    // Confirm with user before deleting
    if (!confirm("Είστε σίγουροι ότι θέλετε να διαγράψετε αυτό το έργο;")) return;

    try {
        // Send DELETE request to backend
        const res = await fetch(`api/delete_artwork.php?id=${id}`, {
            method: 'DELETE'
        });

        const result = await res.json(); // Parse response

        if (result.success) {
            alert('Το έργο διαγράφηκε');
            await loadArtworks(); // Refresh list
        } else {
            alert('Σφάλμα: ' + (result.error || 'Άγνωστο'));
        }

    } catch {
        alert('Σφάλμα δικτύου');
    }
}


// ===================================
// 7. Initial Page Load
// ===================================

// Load artworks when the admin panel first loads
//Αρχικοποίηση
loadArtworks();
