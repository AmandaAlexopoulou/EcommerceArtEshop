// checkout.js
// Φόρτωση της φόρμας ολοκλήρωσης παραγγελίας

document.addEventListener("DOMContentLoaded", function () {
    const cartItems = JSON.parse(localStorage.getItem("cart")) || [];
    const cartItemsList = document.getElementById("cart-items");
    const totalElement = document.getElementById("total");
    const form = document.getElementById("checkout-form");
    const nameInput = document.getElementById("name");
    const emailInput = document.getElementById("email");

    // Αν το καλάθι είναι άδειο
    if (cartItems.length === 0) {
        cartItemsList.innerHTML = "<li>Το καλάθι είναι άδειο.</li>";
        form.querySelector(".submit-btn").disabled = true;
        return;
    }

    // Εμφάνιση προϊόντων και υπολογισμός συνολικού ποσού
    let total = 0;
    cartItems.forEach(item => {
        const li = document.createElement("li");
        li.textContent = `${item.title} (x${item.quantity}) - €${(item.price * item.quantity).toFixed(2)}`;
        cartItemsList.appendChild(li);
        total += item.price * item.quantity;
    });
    totalElement.textContent = total.toFixed(2);

    // 🔹 Υποβολή παραγγελίας
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const orderData = {
            name: nameInput.value.trim(),
            email: emailInput.value.trim(),
            items: cartItems,
            total: total
        };

        console.log("Sending orderData:", orderData);

        try {
            // Αν το checkout.html και το process_checkout.php είναι στον ΙΔΙΟ φάκελο:
            const response = await fetch("process_checkout.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(orderData)
            });

            // Διαβάζουμε πρώτα ως text για να δούμε τι πραγματικά επιστρέφει ο server
            const text = await response.text();
            console.log("Raw response text:", text);

            // Μετατροπή σε JSON
            let result;
            try {
                result = JSON.parse(text);
            } catch (err) {
                console.error("JSON parse error:", err);
                alert("❌ Ο server δεν επέστρεψε έγκυρο JSON. Δες console.");
                return;
            }

            // Έλεγχος απάντησης
            if (result.success) {
                alert(`✅ ${result.message}\nΑρ. παραστατικού: ${result.invoice_number}`);
                localStorage.removeItem("cart");
                window.location.href = "confirmation.html";
            } else {
                alert(`⚠️ Σφάλμα: ${result.message}`);
            }

        } catch (err) {
            console.error("Fetch error:", err);
            alert("❌ Πρόβλημα σύνδεσης με τον διακομιστή. Δες console.");
        }
    });
});
