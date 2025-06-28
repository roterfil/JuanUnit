Of course. Here is the complete README file in a single block for easy copy-pasting.

Generated markdown
# JuanUnit

A modern, web-based property management system designed for dormitories, apartments, and rental units. JuanUnit provides a centralized platform for administrators to manage tenants, units, and payments, while offering tenants a self-service portal for payments and maintenance requests.

![JuanUnit Dashboard](images/screenshot.png)

---

### Core Features

#### Administrator Panel
*   **Centralized Dashboard:** At-a-glance overview of total tenants, occupied units, pending payments, and open maintenance requests.
*   **Unit Management:** Add, edit, and delete units. Upload unit photos, set rent prices, and manage unit status (Available, Occupied, Under Maintenance).
*   **Tenant Management:** View all registered tenants, assign them to units, and unassign them.
*   **Payment Tracking:** Create payment records for tenants, view payment history, and confirm payments by marking them as 'Paid'.
*   **Maintenance System:** View and manage maintenance requests from tenants, update their status (Pending, In Progress, Completed), and archive completed tasks. Includes a search and filter system.
*   **Announcements:** Post and manage announcements that are visible to all tenants.
*   **Notification System:** Receive automatic notifications for new maintenance requests and tenant-uploaded payment proofs.

#### Tenant Portal
*   **Personal Dashboard:** A personalized overview of the tenant's unit, payment status, and recent announcements.
*   **Online Payments:** View payment history and upload proof of payment (images or PDF) for pending dues.
*   **Maintenance Reporting:** Submit new maintenance requests with detailed descriptions and optional image attachments. Track the status of submitted requests.
*   **Profile Management:** Update personal information and change account passwords securely.
*   **Notifications:** Receive automatic notifications for new announcements, payment confirmations, and maintenance status updates.

---

### Technology Stack

*   **Backend:** PHP
*   **Database:** MySQL
*   **Frontend:** HTML5, CSS3, vanilla JavaScript (for modals, AJAX, and dynamic UI updates)
*   **Server:** Apache (typically via XAMPP, WAMP, or MAMP)
*   **Icons:** Font Awesome

---

### Setup and Installation

Follow these steps to get the project running on your local machine.

#### 1. Prerequisites
Ensure you have a local server environment installed, such as:
*   [XAMPP](https://www.apachefriends.org/index.html) (recommended for Windows/Mac/Linux)
*   WAMP (for Windows)
*   MAMP (for Mac)

#### 2. Clone the Repository
Clone this repository to your local machine inside your server's web directory (e.g., `C:/xampp/htdocs/`).

```bash
git clone https://github.com/your-username/juanunit.git
```

Or simply download the ZIP file and extract it to the `htdocs` folder.

#### 3. Database Setup
1.  Start the Apache and MySQL services from your XAMPP control panel.
2.  Open your web browser and go to `http://localhost/phpmyadmin`.
3.  Create a new database named `juanunit_db`.
4.  Select the `juanunit_db` database, go to the **Import** tab, and choose the `database_schema.sql` file from the project directory.
5.  Click **Go** to import the schema and sample data. This will create all the necessary tables and a default admin account.

#### 4. Configure the Connection
1.  Open the file `includes/db_connect.php` in your code editor.
2.  Update the database credentials to match your local setup. **You will need to add your MySQL password.**

    ```php
    // in includes/db_connect.php

    $host = 'localhost';
    $username = 'root';
    $password = ''; // <-- ADD YOUR MYSQL PASSWORD HERE
    $database = 'juanunit_db';
    ```
    ***Note:*** *This file is configured to exclude the password from version control. Never commit sensitive credentials.*

3.  Make sure the `BASE_URL` constant matches your project's folder name. For example, if your project is in `htdocs/juanunit/`, the URL should be `http://localhost/juanunit/`.

    ```php
    define('BASE_URL', 'http://localhost/juanunit/');
    ```

#### 5. Directory Permissions
Ensure that the web server has permission to write to the `uploads/` directory. This is necessary for tenants and admins to upload images for payment proofs, maintenance requests, and unit photos. For most local setups, these permissions are already set correctly by default.

#### 6. Ready to Launch!
*   **Access the application:** `http://localhost/juanunit/`
*   **Admin Login:**
    *   Username: `admin`
    *   Password: `admin123`
*   **Tenant Login:** You can register a new tenant account from the registration page.