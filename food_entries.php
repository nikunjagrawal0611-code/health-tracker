<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

include "config.php";
include "database.php";

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// POST → add new food entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    $stmt = $conn->prepare("INSERT INTO food_entries 
        (user_id, meal_type, food_name, quantity, calories, protein, carbs, fat, entry_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "isssiddss",
        $user_id,
        $data['meal_type'],
        $data['food_name'],
        $data['quantity'],
        $data['calories'],
        $data['protein'],
        $data['carbs'],
        $data['fat'],
        $data['entry_date']
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Entry added successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    exit;
}

// GET → return food entries with pagination & filters
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$start = $_GET['start'] ?? null;
$end   = $_GET['end'] ?? null;
$meal  = $_GET['meal'] ?? null;

$query = "SELECT * FROM food_entries WHERE user_id = ?";
$types = "i";
$params = [$user_id];

if ($start && $end) {
    $query .= " AND entry_date BETWEEN ? AND ?";
    $types .= "ss";
    $params[] = $start;
    $params[] = $end;
}

if ($meal) {
    $query .= " AND meal_type = ?";
    $types .= "s";
    $params[] = $meal;
}

// Count total records
$countQuery = str_replace("SELECT *", "SELECT COUNT(*) as total", $query);
$stmtCount = $conn->prepare($countQuery);
$stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalRecords = $stmtCount->get_result()->fetch_assoc()['total'];

// Add pagination
$query .= " ORDER BY entry_date DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "data" => $data,
    "page" => $page,
    "total_pages" => ceil($totalRecords / $limit),
    "total_records" => $totalRecords
]);