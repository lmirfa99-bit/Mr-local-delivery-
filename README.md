# Mr Local Delivery Website

This is a complete implementation of a product delivery website with a clean, modern design.

## Features
- **Customer Frontend**:
    - Product Categories (Vegetables, Meat, Grocery, etc.)
    - Cart Management (Add, Remove, Quantity)
    - Checkout (Cash on Delivery only)
    - Order Tracking
- **Admin Panel**:
    - View/Manage Orders
    - Add/Edit Products
- **Tech Stack**: HTML, CSS, JS (Frontend) + PHP, MySQL (Backend)

## Deployment Instructions

### 1. Database Setup
1.  Log in to your hosting **phpMyAdmin**.
2.  Create a new database (e.g., `mrlocal_delivery` or `noon_delivery`).
3.  Import the `schema.sql` file provided in this project.
    - This will create the `products`, `orders`, and `order_items` tables and insert some demo products.

### 2. Backend Configuration
1.  Open `api/db.php`.
2.  Update the database credentials:
    ```php
    $host = 'localhost';
    $db   = 'noon_delivery'; // Your DB Name (or mrlocal_delivery)
    $user = 'root';          // Your DB User
    $pass = '';              // Your DB Password
    ```

### 3. Frontend Configuration
1.  Open `assets/js/script.js`.
2.  Change `MOCK_MODE` to `false`:
    ```javascript
    const MOCK_MODE = false;
    ```
    - **Note**: Keep it `true` if you just want to test the design without a backend.

### 4. Upload Files
1.  Upload all files and folders (`assets`, `api`, `admin`, `*.html`) to your server's public directory (e.g., `public_html`).

### 5. Access
- **Website**: `http://your-domain.com/index.html`
- **Admin Panel**: `http://your-domain.com/admin/index.php`
    - **Password**: `admin123` (Change this in `admin/index.php` line 6)

## Folder Structure
- `assets/`: CSS styles, JS scripts, images.
- `api/`: PHP scripts for fetching data and placing orders.
- `admin/`: PHP files for the admin dashboard.
