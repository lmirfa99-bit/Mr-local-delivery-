<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Validate required fields
if (empty($data['name']) || empty($data['phone']) || empty($data['address']) || empty($data['items']) || !isset($data['total'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate phone number
$phone = preg_replace('/[^0-9]/', '', $data['phone']);
if (strlen($phone) < 8) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
    exit;
}

// Validate total amount
$total = floatval($data['total']);
if ($total <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid total amount']);
    exit;
}

$name = trim($data['name']);
$address = trim($data['address']);
$lat = !empty($data['latitude']) ? floatval($data['latitude']) : null;
$lng = !empty($data['longitude']) ? floatval($data['longitude']) : null;

// Create Order using prepared statement
$stmt = $conn->prepare("INSERT INTO orders (customer_name, phone, address, total_amount, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssdd", $name, $phone, $address, $total, $lat, $lng);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Insert Items using prepared statement
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    
    foreach ($data['items'] as $item) {
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        
        // Validate item data
        if ($product_id <= 0 || $quantity <= 0 || $price <= 0) {
            continue; // Skip invalid items
        }
        
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $item_stmt->execute();
    }
    
    $item_stmt->close();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
    $stmt->close();
}

$conn->close();
?>
