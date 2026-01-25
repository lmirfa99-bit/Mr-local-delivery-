<?php
include 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$phone = isset($_GET['phone']) ? preg_replace('/[^0-9]/', '', $_GET['phone']) : '';

if ($order_id > 0) {
    // Get order by ID using prepared statement
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} elseif (!empty($phone)) {
    // Get latest order by phone using prepared statement
    $stmt = $conn->prepare("SELECT * FROM orders WHERE phone = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Please provide order ID or phone number']);
    exit;
}

if ($result && $result->num_rows > 0) {
    $order = $result->fetch_assoc();
    
    // Get order items using prepared statement
    $order_id = intval($order['id']);
    $items_stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $items = [];
    if ($items_result && $items_result->num_rows > 0) {
        while($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
    }
    $items_stmt->close();
    
    $order['items'] = $items;
    
    echo json_encode(['success' => true, 'order' => $order]);
} else {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
}

$conn->close();
?>

