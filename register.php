<?php
include "config.php";
include "database.php";

// Get input data
$data = json_decode(file_get_contents("php://input"), true) ?? [];

$name     = trim($data['name'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

// Validate input
if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Prepare and execute insert statement
$stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("sss", $name, $email, $password_hash);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Registration successful"]);
} else {
    if (str_contains($stmt->error, "Duplicate entry")) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Email already registered"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Registration failed: " . $stmt->error]);
    }
}

$stmt->close();
?>