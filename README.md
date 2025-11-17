
Art E-Shop is an online store that allows users to browse and purchase artwork. It has a simple, user-friendly interface for customers, as well as an admin panel for managing orders, invoices, and customers. The project is implemented with the following technologies:

Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: SQLite (for local development) via **DBeaver** (for database management)
Server: XAMPP (Apache, MySQL, PHP)

This project aims to demonstrate how an e-commerce store can be created with a focus on selling artworks that can be added and deleted from the database through its user interface.

 Features

User Features:
Browse available artworks (paintings, sculptures, etc.).
Insert new artworks with their name,title,price or delete them (they are also inserted and deleteed in the database)
Add items to the shopping cart.
Checkout process with order confirmation and invoice generation.
Invoice sent to the user after successful purchase.

Admin Features:

Manage invoices, check their status, and update them.
View a summary of all orders and sales.

 Project Setup & Prerequisites

To run this project on your local machine, you'll need the following software:

1. XAMPP - To run Apache and PHP locally.

    Download from: [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)

2. DBeaver- To manage and vizualize the SQLite database.

Download from: [https://dbeaver.io/](https://dbeaver.io/)

3. PHP - Included with XAMPP for backend scripting.

---

 Steps to Run the Project Locally

1. Clone the Repository**:

   ```bash
   git clone https://github.com/your-username/EcommerceArtEshop.git
   ```

2. Set Up XAMPP:

   * Open **XAMPP Control Panel**.
   * Start the **Apache** server (for PHP) and **MySQL** server (optional for MySQL, but SQLite works fine for this project).

3. **Database Setup**:

   * Open **DBeaver** and connect to your SQLite database.
   * Import or create the necessary tables (`orders`, `invoices`, etc.) in the `artstore.sqlite` database. The SQL schema for the database can be found in the project, or you can create it based on the queries in the PHP files.

4. **Upload the Project Files**:

   * Place the project folder inside your **htdocs** folder in XAMPP (usually located in `C:/xampp/htdocs/`).
   * Example folder structure: `C:/xampp/htdocs/ArtEshop`.

5. **Start the Project**:

   * Open your web browser and navigate to `http://localhost/ArtEshop` to access the e-shop.


How to Use

For Customers:

1. Browse the available artworks and add them to your cart.
2. Complete the checkout process by providing your name and email.
3. Once your order is confirmed, an invoice will be generated and sent to your email.

 For Admin:

1. Log in to the admin panel to manage invoices and orders.
2. View the order details, the status of invoices (sent or pending), and take necessary actions.
3. You can upload new artwork images to the e-shop.




## Technologies Used

Frontend:

  * HTML5, CSS3, JavaScript
  * Basic user interface for displaying and purchasing artworks

  Backend:

  * PHP (for handling orders, invoices, and images)
  * SQLite (for storing data about orders, customers, invoices)

  Server & Database:

  * XAMPP (local server environment)
  * DBeaver (for database management)

  External Libraries:

PHPMailer for sending invoice emails
cURL for external API communication (e.g. sending invoices to an external provider)


API Endpoints

`/api/upload_image.php`**: Handles image uploads for new artwork.
`/api/send_invoice.php`**: Sends an invoice to an external provider (integration with an ERP system).
`/send_invoice.php`**: Sends an email with the invoice to the customer.

---

Known Issues

* Currently, the external invoice provider API is simulated (MailHog used for local testing).
* The project is in an early development stage and may require some enhancements in error handling and user interface design.



 Future Enhancements

User Authentication**: Implement login and user registration for customers.
Payment Integration**: Integrate a payment gateway (e.g. Stripe, PayPal).
ERP Integration**: Expand the functionality to connect to a more robust ERP system for invoice management.


License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


