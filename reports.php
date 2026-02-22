<?php
session_start();
include "config.php";
include "database.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Optional: date filters from query params
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

$query = "SELECT entry_date, SUM(calories) AS total_calories
          FROM food_entries
          WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($start && $end) {
    $query .= " AND entry_date BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
    $types .= "ss";
}

$query .= " GROUP BY entry_date ORDER BY entry_date ASC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit;
}

// Dynamic bind_param
$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["error" => "Query failed: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

$stmt->close();

echo json_encode($data);
?>