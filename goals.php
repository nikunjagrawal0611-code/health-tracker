<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

include "config.php";
include "database.php";

// Check if user is logged in via session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// POST → save/update goal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];

    $daily_calories = intval($data['daily_calories'] ?? 0);
    $protein_target = intval($data['protein_target'] ?? 0);
    $carbs_target = intval($data['carbs_target'] ?? 0);
    $fat_target = intval($data['fat_target'] ?? 0);
    $weight_goal = floatval($data['weight_goal'] ?? 0);

    $stmt = $conn->prepare("
        INSERT INTO goals (user_id, daily_calories, protein_target, carbs_target, fat_target, weight_goal, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            daily_calories = VALUES(daily_calories),
            protein_target = VALUES(protein_target),
            carbs_target = VALUES(carbs_target),
            fat_target = VALUES(fat_target),
            weight_goal = VALUES(weight_goal)
    ");
    $stmt->bind_param("iiiiid", $user_id, $daily_calories, $protein_target, $carbs_target, $fat_target, $weight_goal);

    if ($stmt->execute()) {
        // Return the most recent goal row after save
        $sel = $conn->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $sel->bind_param("i", $user_id);
        $sel->execute();
        $res = $sel->get_result();
        $saved = $res->fetch_assoc();

        echo json_encode(["success" => true, "goal" => $saved]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    exit;
}

// GET → return current user's goal
$stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$goal = $result->fetch_assoc();

echo json_encode($goal ?? ["error" => "No goal set"]);