<?php
/**
 * Database Connection Tester
 * Visit this file to check your database connection
 */

header("Content-Type: application/json");

$response = [
    "status" => "checking",
    "checks" => []
];

// Test 1: mysqli extension
$response["checks"]["mysqli_extension"] = extension_loaded('mysqli') ? "✅ Pass" : "❌ Fail";

// Test 2: Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "health_tracker";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    $response["checks"]["mysql_connection"] = "❌ Failed: " . $conn->connect_error;
    $response["status"] = "failed";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

$response["checks"]["mysql_connection"] = "✅ Connected to MySQL";

// Test 3: Database selection
$canSelectDb = $conn->select_db($db);
if ($canSelectDb) {
    $response["checks"]["database_selection"] = "✅ Database '$db' exists";
} else {
    $response["checks"]["database_selection"] = "❌ Database '$db' not found: Try running /api/setup.php";
    $response["status"] = "needs_setup";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $conn->close();
    exit;
}

// Test 4: Tables
$tables = ["users", "food_entries", "goals"];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        $response["checks"]["table_$table"] = "✅ Table exists";
    } else {
        $response["checks"]["table_$table"] = "❌ Table missing. Run /api/setup.php";
        $response["status"] = "needs_setup";
    }
}

// Test 5: Charset
$charsetResult = $conn->query("SELECT @@character_set_client, @@character_set_connection");
if ($charsetResult) {
    $response["checks"]["charset"] = "✅ Charset OK";
}

$conn->close();

if ($response["status"] === "checking") {
    $response["status"] = "success";
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>