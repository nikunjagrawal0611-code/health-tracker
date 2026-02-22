<?php
session_start();
include "config.php";
include "database.php";

// Get input data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit;
}

// Check if database connection exists
if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection error"]);
    exit;
}

// Prepare and execute query
$stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Query execution failed: " . $stmt->error]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();

if (!$result) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not retrieve results: " . $stmt->error]);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check credentials
if ($user && password_verify($password, $user['password_hash'])) {
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $email;
    
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Login successful"]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
}
?>