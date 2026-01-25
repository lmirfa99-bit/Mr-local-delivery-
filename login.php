<?php
include 'db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['phone']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Phone and password are required']);
    exit;
}

$phone = preg_replace('/[^0-9]/', '', $data['phone']);
$password = $data['password'];

// Validate phone number
if (strlen($phone) < 8) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
    exit;
}

// Use prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        echo json_encode(['success' => true, 'user' => ['name' => $user['full_name'], 'phone' => $user['phone']]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
