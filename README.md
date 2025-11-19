
Art E-Shop is an online store that allows users to browse and purchase artwork. It has a simple, user-friendly interface for customers, as well as an admin panel for managing orders, invoices, and customers.It also contains login and user registration for customers. The project is implemented with the following technologies:

Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: SQLite (for local development) via DBeaver (for database management)
Server: XAMPP (Apache, MySQL, PHP)
It also uses PHPmailer and Composer
This project aims to demonstrate how an e-commerce store can be created with a focus on selling artworks that can be added and deleted from the database through its user interface.

 Features

User Capabilities:
Browse available artworks (paintings, sculptures, etc.).
Add items to the shopping cart.
Just by artworks
Log in/Log out in case they have an account

Admin Capabilities: 
Login with their unique username, email and password
Insert new artworks with their name,title,price or delete them (they are also inserted and deleteed in the database)
The project also contains: 
Checkout process with order confirmation and invoice generation.
Invoice sent to the user after successful purchase (with PHP mailer).



 Project Setup & Prerequisites

To run this project on your local machine, you'll need the following software:

1. XAMPP - To run Apache and PHP locally.

    Download from: [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)

2. DBeaver- To manage and vizualize the SQLite database.

Download from: [https://dbeaver.io/](https://dbeaver.io/)

3. PHP - Included with XAMPP for backend scripting.

 Steps to Run the Project Locally

1. Clone the Repository**:

   ```bash
   git clone https://github.com/your-username/EcommerceArtEshop.git
   ```

2. Set Up XAMPP:

    Open XAMPP Control Panel.
   Start the Apache server (for PHP) and **MySQL** server (optional for MySQL, but SQLite works fine for this project).

3. Database Setup:

 Open DBeaver  and connect to your SQLite database.
  Import or create the necessary tables (`orders`, `invoices`, etc.) in the `artstore.sqlite` database. The SQL schema for the database can be found in the project, or you can create it based on the queries in the PHP files.

4. Upload the Project Files:

Place the project folder inside your **htdocs** folder in XAMPP (usually located in `C:/xampp/htdocs/`).
 Example folder structure: `C:/xampp/htdocs/ArtEshop`.

5. Start the Project:

 Open your web browser and navigate to `http://localhost/ArtEshop` to access the e-shop.


How to Use

For Customers:

1. Browse the available artworks and add them to your cart.
2. Complete the checkout process by providing your name and email.
3. Once your order is confirmed, an invoice will be generated and sent to your email.

For Users:
1.Log in with your username and password
2.Browse the available artworks and add them to your cart.
3.Complete the checkout process by providing your name and email.
4. Once your order is confirmed, an invoice will be generated and sent to your email.

 
 For Admin:
1. Log in to the admin panel to manage the available artworks.
2. Add or delete the artworks that are available for sale on the eshop.





 Technologies Used

Frontend:

HTML5, CSS3, JavaScript
Basic user interface for displaying and purchasing artworks

  Backend:

PHP (for handling orders, invoices, and images)
 SQLite (for storing data about orders, customers, invoices)

  Server & Database:

   XAMPP (local server environment)
   DBeaver (for database management)

  External Libraries:

PHPMailer for sending invoice emails
cURL for external API communication (e.g. sending invoices to an external provider)


API Endpoints

`/api/upload_image.php`: Handles image uploads for new artwork.
`/api/send_invoice.php`: Sends an invoice to an external provider (integration with an ERP system).
`/send_invoice.php`: Sends an email with the invoice to the customer.

---



 Currently, the external invoice provider API is simulated (MailHog used for local testing).

Brief Database Documentation – ArtEshop

Table Descriptions & Key Relationships:

customers:

Primary Key: id

Relationships:

1:N with orders (customer_id)

1:N with users (customer_id)

roles:

Primary Key: id

Relationships:

1:N with users (role_id)

users:

Primary Key: id

Foreign Keys: customer_id → customers.id, role_id → roles.id

artworks:

Primary Key: id

Relationships:

1:N with order_items (artwork_id)

orders:

Primary Key: id

Foreign Key: customer_id → customers.id

Relationships:

1:N with order_items (order_id)

1:1 or 1:N with invoices (order_id)

order_items:

Primary Key: id

Foreign Keys: order_id → orders.id, artwork_id → artworks.id

invoices:

Primary Key: id

Foreign Key: order_id → orders.id

Relationships:

1:N with invoice_logs (invoice_id)

invoice_logs:

Primary Key: id

Foreign Key: invoice_id → invoices.id

License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.


