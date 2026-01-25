# How to Run the Mr Local Delivery Web App

## Quick Start Guide

### Option 1: Local Development (XAMPP/WAMP/MAMP)

1. **Install a local server:**
   - Download [XAMPP](https://www.apachefriends.org/) (Windows/Mac/Linux)
   - Or [WAMP](https://www.wampserver.com/) (Windows)
   - Or [MAMP](https://www.mamp.info/) (Mac)

2. **Start the server:**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

3. **Setup Database:**
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
   - Create a new database named `mrlocal_delivery` (or `noon_delivery`)
   - Click "Import" tab
   - Choose `schema.sql` file from this project
   - Click "Go" to import

4. **Configure Database Connection:**
   - Open `api/db.php`
   - Update credentials if needed (default works for XAMPP):
     ```php
     $host = 'localhost';
     $db   = 'mrlocal_delivery';  // or 'noon_delivery'
     $user = 'root';
     $pass = '';  // Empty for XAMPP default
     ```

5. **Copy Project Files:**
   - Copy entire project folder to:
     - XAMPP: `C:\xampp\htdocs\lmi\` (or your htdocs folder)
     - WAMP: `C:\wamp64\www\lmi\`
     - MAMP: `/Applications/MAMP/htdocs/lmi/`

6. **Access the Website:**
   - Customer site: `http://localhost/lmi/index.html`
   - Admin panel: `http://localhost/lmi/admin/index.php`
     - Username: `admin`
     - Password: `admin123`

---

### Option 2: Test Mode (No Server Needed)

1. **Open directly in browser:**
   - Simply double-click `index.html`
   - Works for frontend testing only (no backend features)

2. **Enable Mock Mode:**
   - Open `assets/js/script.js`
   - Make sure `MOCK_MODE = true` (line 3)
   - This allows testing without PHP/MySQL

---

### Option 3: Deploy to Web Hosting

1. **Upload files via FTP:**
   - Upload all files to `public_html` folder
   - Keep folder structure intact

2. **Create database on hosting:**
   - Use cPanel or hosting control panel
   - Create database `noon_delivery`
   - Create database user with password
   - Import `schema.sql`

3. **Update database config:**
   - Edit `api/db.php` with hosting credentials:
     ```php
     $host = 'localhost';  // Usually localhost
     $db   = 'your_hosting_db_name';
     $user = 'your_db_username';
     $pass = 'your_db_password';
     ```

4. **Set permissions:**
   - Make sure `assets/images/uploads/` folder is writable (chmod 755)

5. **Disable Mock Mode:**
   - Edit `assets/js/script.js`
   - Set `MOCK_MODE = false`

6. **Access your site:**
   - Visit `https://yourdomain.com/index.html`
   - Admin: `https://yourdomain.com/admin/index.php`

---

## Testing the App

### Customer Features:
1. Browse products on homepage
2. Search for products
3. Filter by category
4. Add items to cart
5. Go to cart, fill delivery address
6. Place order via WhatsApp

### Admin Features:
1. Login at `admin/index.php`
2. View orders (filter by status)
3. Update order status
4. Add/edit/delete products
5. Upload product images

---

## Troubleshooting

### Database Connection Error:
- Check MySQL is running
- Verify database name matches
- Check username/password in `api/db.php`

### Images Not Uploading:
- Create folder: `assets/images/uploads/`
- Set folder permissions to 755 (writable)

### Products Not Loading:
- Check `MOCK_MODE` setting
- Verify `api/get_products.php` is accessible
- Check browser console for errors

### Admin Login Not Working:
- Default credentials: `admin` / `admin123`
- Check PHP sessions are enabled

---

## File Structure

```
lmi/
├── index.html          # Homepage
├── cart.html           # Shopping cart
├── login.html          # Login page
├── signup.html         # Signup page
├── tracking.html       # Order tracking
├── schema.sql          # Database structure
├── api/
│   ├── db.php          # Database connection
│   ├── get_products.php
│   ├── place_order.php
│   ├── get_order.php
│   ├── login.php
│   ├── signup.php
│   └── upload_image.php
├── admin/
│   └── index.php       # Admin panel
└── assets/
    ├── css/
    │   └── style.css
    ├── js/
    │   └── script.js
    └── images/
        └── uploads/    # Auto-created
```

---

## Next Steps

- Change admin password in `admin/index.php` (line 9)
- Update WhatsApp number in `assets/js/script.js` (line 349)
- Customize colors/branding in `assets/css/style.css`
- Add more products via admin panel

