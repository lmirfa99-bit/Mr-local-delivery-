<?php
// admin/index.php
// NOTE: This will not work without a running PHP server and Database.

session_start();

// Hardcoded Admin Credentials
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'admin123';

if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === $ADMIN_USER && $_POST['password'] === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Invalid Credentials";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head><title>Admin Login</title><link rel="stylesheet" href="../assets/css/style.css"></head>
    <body style="display:flex; justify-content:center; align-items:center; height:100vh; background: #f4f5f9;">
        <form method="POST" style="background:white; padding:2.5rem; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); width: 300px;">
            <h2 style="text-align: center; margin-bottom: 1.5rem;">Admin Login</h2>
            <?php if(isset($error)): ?><p style="color:red; text-align:center;"><?= $error ?></p><?php endif; ?>
            
            <div style="margin-bottom: 1rem;">
                <label style="display:block; margin-bottom:0.5rem; font-weight:bold;">Username</label>
                <input type="text" name="username" placeholder="admin" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display:block; margin-bottom:0.5rem; font-weight:bold;">Password</label>
                <input type="password" name="password" placeholder="admin123" required style="width:100%; padding:0.8rem; border:1px solid #ddd; border-radius: 4px;">
            </div>
            
            <button type="submit" class="add-btn" style="width: 100%; padding: 0.8rem;">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

include '../api/db.php';

// Handle Product Update
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    $image = '';
    
    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            // Validate file size (max 5MB)
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] <= $maxSize) {
                // Create uploads directory if it doesn't exist
                $uploadDir = '../assets/images/uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $image = 'assets/images/uploads/' . $filename;
                }
            }
        }
    }
    
    // Use URL if no file was uploaded or upload failed
    if (empty($image) && !empty($_POST['image'])) {
        $image = $conn->real_escape_string($_POST['image']);
    }
    
    // Only update image if a new one was provided
    if (!empty($image)) {
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=?, image_url=? WHERE id=?");
        $stmt->bind_param("sdssi", $name, $price, $category, $image, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=? WHERE id=?");
        $stmt->bind_param("sdsi", $name, $price, $category, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle Actions (Add Product)
$message = '';
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $conn->real_escape_string($_POST['category']);
    $image = '';
    
    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image_file'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            // Validate file size (max 5MB)
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] <= $maxSize) {
                // Create uploads directory if it doesn't exist
                $uploadDir = '../assets/images/uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $image = 'assets/images/uploads/' . $filename;
                } else {
                    $message = "Error: Failed to save uploaded file.";
                }
            } else {
                $message = "Error: File size exceeds 5MB limit.";
            }
        } else {
            $message = "Error: Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.";
        }
    } else {
        // Use URL if provided
        $image = !empty($_POST['image']) ? $conn->real_escape_string($_POST['image']) : '';
    }
    
    if (empty($message) && !empty($image)) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, category, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $name, $price, $category, $image);
        if ($stmt->execute()) {
            $message = "Product Added!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (empty($image)) {
        $message = "Error: Please provide an image file or URL.";
    }
}

// Handle Product Delete
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $message = "Product Deleted!";
}

// Handle Order Status Update
if (isset($_POST['update_order'])) {
    $status = $_POST['status'];
    // Validate status
    $allowed_statuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        $status = 'pending';
    }
    $order_id = intval($_POST['order_id']);
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
}

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$allowed_statuses = ['all', 'pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
if (!in_array($status_filter, $allowed_statuses)) {
    $status_filter = 'all';
}

// Fetch Data
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");

// Fetch orders with status filter
if ($status_filter === 'all') {
    $orders = $conn->query("SELECT * FROM orders ORDER BY id DESC");
} else {
    $orders = $conn->query("SELECT * FROM orders WHERE status='$status_filter' ORDER BY id DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Mr Local</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #383838; color: white; padding: 2rem 1rem; }
        .sidebar a { display: block; color: #ccc; text-decoration: none; padding: 1rem 0; font-size: 1.1rem; }
        .sidebar a:hover, .sidebar a.active { color: #FEE600; }
        .main-content { flex: 1; padding: 2rem; background: #f4f5f9; }
        .card { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        .status-select { padding: 5px; border-radius: 4px; border: 1px solid #ddd; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 10% auto; padding: 2rem; border-radius: 8px; width: 50%; max-width: 500px; position: relative; }
        .close { position: absolute; right: 1rem; top: 1rem; font-size: 1.5rem; cursor: pointer; }
    </style>
</head>
<body>

<div class="admin-layout">
    <div class="sidebar">
        <h2 style="color: #FEE600; margin-bottom: 2rem;">Mr Local Admin</h2>
        <a href="#orders" class="active">Orders</a>
        <a href="#products">Products</a>
        <a href="?logout=true">Logout</a>
    </div>

    <div class="main-content">
        <h1>Dashboard</h1>
        <?php if($message): ?><div style="background:#d4edda; color:#155724; padding:1rem; margin-bottom:1rem;"><?= $message ?></div><?php endif; ?>

        <!-- Orders Section -->
        <div class="card" id="orders">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2>Recent Orders</h2>
                <form method="GET" style="display: inline-block;">
                    <select name="status" onchange="this.form.submit()" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ddd;">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status_filter == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="shipping" <?= $status_filter == 'shipping' ? 'selected' : '' ?>>Shipping</option>
                        <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    </select>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): while($row = $orders->fetch_assoc()): 
                        // Get order items
                        $order_id = $row['id'];
                        $items_query = $conn->query("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
                        $order_items = [];
                        while($item = $items_query->fetch_assoc()) {
                            $order_items[] = $item;
                        }
                    ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['customer_name']) ?></strong><br>
                            <small style="color: #666;"><?= htmlspecialchars($row['phone']) ?></small>
                        </td>
                        <td style="max-width: 250px;">
                            <div style="font-size: 0.9rem;"><?= htmlspecialchars($row['address']) ?></div>
                            <?php if($row['latitude'] && $row['longitude']): ?>
                                <a href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" target="_blank" style="color:#37B34A; font-size:0.85rem; text-decoration: none;">📍 View Map</a>
                            <?php endif; ?>
                        </td>
                        <td><strong>AED <?= number_format($row['total_amount'], 2) ?></strong></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="pending" <?= $row['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $row['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="shipping" <?= $row['status'] == 'shipping' ? 'selected' : '' ?>>Shipping</option>
                                    <option value="delivered" <?= $row['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                </select>
                                <input type="hidden" name="update_order" value="1">
                            </form>
                        </td>
                        <td>
                            <button onclick='showOrderDetails(<?= json_encode($row) ?>, <?= json_encode($order_items) ?>)' style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">View Items</button>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">No orders found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Products Section -->
        <div class="card" id="products">
            <h2>Manage Products</h2>
            
            <form method="POST" enctype="multipart/form-data" style="margin-bottom: 2rem; background: #f9f9f9; padding: 1rem; border-radius: 4px;">
                <h3>Add New Product</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <input type="text" name="name" placeholder="Product Name" required style="padding:0.5rem;">
                    <input type="number" step="0.01" name="price" placeholder="Price (AED)" required style="padding:0.5rem;">
                    <select name="category" required style="padding:0.5rem;">
                        <option value="vegetables">Vegetables</option>
                        <option value="grocery">Grocery</option>
                        <option value="meat">Meat</option>
                        <option value="fish">Fish</option>
                        <option value="hypermarket">Hypermarket</option>
                    </select>
                    <div style="grid-column: span 2;">
                        <label style="display:block; margin-bottom:0.5rem; font-size: 0.9rem; font-weight: 600;">Product Image (Upload File OR Enter URL)</label>
                        <input type="file" name="image_file" accept="image/*" style="padding:0.5rem; width: 100%; margin-bottom: 0.5rem;">
                        <input type="text" name="image" id="add_image_url" placeholder="Or Image URL" style="padding:0.5rem; width: 100%;">
                        <small style="color: #666; font-size: 0.85rem;">Upload an image file (max 5MB) or provide an image URL</small>
                    </div>
                </div>
                <button type="submit" name="add_product" class="add-btn" style="width: auto; margin-top: 1rem;">Add Product</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): while($p = $products->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?= $p['image_url'] ?>" width="40" height="40" style="object-fit:cover;"></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td>AED <?= $p['price'] ?></td>
                        <td><?= $p['category'] ?></td>
                        <td>
                            <button onclick='openEditModal(<?= json_encode($p) ?>)' style="padding: 5px 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px;">Edit</button>
                            <a href="?delete_product=<?= $p['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')" style="padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="edit_result_id">
            
            <label style="display:block; margin-top:1rem;">Name</label>
            <input type="text" name="name" id="edit_name" required style="width:100%; padding:0.5rem; margin-top:0.5rem;">
            
            <label style="display:block; margin-top:1rem;">Price (AED)</label>
            <input type="number" step="0.01" name="price" id="edit_price" required style="width:100%; padding:0.5rem; margin-top:0.5rem;">
            
            <label style="display:block; margin-top:1rem;">Category</label>
            <select name="category" id="edit_category" required style="width:100%; padding:0.5rem; margin-top:0.5rem;">
                <option value="vegetables">Vegetables</option>
                <option value="grocery">Grocery</option>
                <option value="meat">Meat</option>
                <option value="fish">Fish</option>
                <option value="hypermarket">Hypermarket</option>
            </select>
            
            <label style="display:block; margin-top:1rem;">Product Image</label>
            <div style="background: #f0f0f0; padding: 10px; border-radius: 4px; text-align: center; margin-bottom: 10px;">
                <img id="edit_image_preview" src="" style="max-height: 100px; max-width: 100%; display: none; border-radius: 4px;">
            </div>
            <input type="file" name="image_file" id="edit_image_file" accept="image/*" style="width:100%; padding:0.5rem; margin-bottom:0.5rem;">
            <input type="text" name="image" id="edit_image" placeholder="Or Image URL" style="width:100%; padding:0.5rem;">
            <small style="color: #666; font-size: 0.85rem;">Upload a new image file (max 5MB) or provide an image URL</small>
            
            <button type="submit" name="edit_product" class="add-btn" style="width:100%; margin-top:1.5rem;">Update Product</button>
        </form>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="close" onclick="closeOrderDetailsModal()">&times;</span>
        <h2>Order Details</h2>
        <div id="order-details-content">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
function openEditModal(product) {
    document.getElementById('edit_result_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_category').value = product.category;
    
    const currentImg = product.image_url;
    document.getElementById('edit_image').value = currentImg;
    
    // Show image preview
    const preview = document.getElementById('edit_image_preview');
    if (currentImg) {
        preview.src = currentImg;
        preview.style.display = 'inline-block';
    } else {
        preview.style.display = 'none';
    }
    
    // Clear file input
    document.getElementById('edit_image_file').value = '';
    
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Preview uploaded file before submit
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('edit_image_file');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('edit_image_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Order Details Modal
function showOrderDetails(order, items) {
    const content = document.getElementById('order-details-content');
    
    let itemsHtml = '<h3 style="margin-top: 1.5rem; margin-bottom: 1rem;">Order Items</h3>';
    if (items && items.length > 0) {
        itemsHtml += '<table style="width: 100%; border-collapse: collapse;">';
        itemsHtml += '<thead><tr><th style="text-align: left; padding: 0.5rem; border-bottom: 1px solid #eee;">Item</th><th style="text-align: right; padding: 0.5rem; border-bottom: 1px solid #eee;">Qty</th><th style="text-align: right; padding: 0.5rem; border-bottom: 1px solid #eee;">Price</th></tr></thead>';
        itemsHtml += '<tbody>';
        items.forEach(item => {
            itemsHtml += `<tr>
                <td style="padding: 0.5rem; border-bottom: 1px solid #eee;">${item.name}</td>
                <td style="text-align: right; padding: 0.5rem; border-bottom: 1px solid #eee;">${item.quantity}</td>
                <td style="text-align: right; padding: 0.5rem; border-bottom: 1px solid #eee;">AED ${parseFloat(item.price).toFixed(2)}</td>
            </tr>`;
        });
        itemsHtml += '</tbody></table>';
    } else {
        itemsHtml += '<p style="color: #666;">No items found.</p>';
    }
    
    content.innerHTML = `
        <div style="background: #f9f9f9; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <p><strong>Order ID:</strong> #${order.id}</p>
            <p><strong>Customer:</strong> ${order.customer_name}</p>
            <p><strong>Phone:</strong> ${order.phone}</p>
            <p><strong>Address:</strong> ${order.address}</p>
            <p><strong>Total:</strong> AED ${parseFloat(order.total_amount).toFixed(2)}</p>
            <p><strong>Status:</strong> <span style="text-transform: capitalize; font-weight: bold;">${order.status}</span></p>
            <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
            ${order.latitude && order.longitude ? `<p><a href="https://www.google.com/maps?q=${order.latitude},${order.longitude}" target="_blank" style="color: #37B34A;">📍 View on Map</a></p>` : ''}
        </div>
        ${itemsHtml}
    `;
    
    document.getElementById('orderDetailsModal').style.display = 'block';
}

function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

// Close modal if clicked outside
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) {
        closeEditModal();
    }
    if (event.target == document.getElementById('orderDetailsModal')) {
        closeOrderDetailsModal();
    }
}
</script>

</body>
</html>
