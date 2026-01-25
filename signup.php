<?php
include 'db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$phone = preg_replace('/[^0-9]/', '', $data['phone']);
$password = $data['password'];
$full_name = isset($data['name']) ? trim($data['name']) : '';

// Validate phone number
if (empty($phone) || strlen($phone) < 8) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
    exit;
}

// Validate password
if (empty($password) || strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Check if user already exists using prepared statement
$check_stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
$check_stmt->bind_param("s", $phone);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Phone number already registered']);
    $check_stmt->close();
    exit;
}
$check_stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'customer';

// Insert new user using prepared statement
$stmt = $conn->prepare("INSERT INTO users (phone, password, full_name, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $phone, $hashed_password, $full_name, $role);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Account created successfully',
        'user' => [
            'id' => $stmt->insert_id,
            'phone' => $phone,
            'name' => $full_name
        ]
    ]);
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating account: ' . $stmt->error]);
    $stmt->close();
}

$conn->close();
?>

