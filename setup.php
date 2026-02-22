<?php
/**
 * Database Setup Script
 * Run this file in your browser once: http://localhost/health_tracker/api/setup.php
 * It will create all necessary tables
 */

session_start();

header("Content-Type: text/html; charset=utf-8");

// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$db = "health_tracker";

// Create connection WITHOUT selecting database first
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("<h2 style='color: red;'>❌ Connection Error:</h2>
        <p>{$conn->connect_error}</p>
        <p>Make sure MySQL/MariaDB is running and credentials are correct:</p>
        <ul>
            <li>Host: $host</li>
            <li>User: $user</li>
            <li>Password: " . ($pass ? "*****" : "(empty)") . "</li>
        </ul>");
}

echo "<h2>🔧 Health Tracker Database Setup</h2>";

// Create database if it doesn't exist
$createDbQuery = "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($createDbQuery)) {
    echo "<p>✅ Database '$db' ready</p>";
} else {
    echo "<p style='color: red;'>❌ Error creating database: " . $conn->error . "</p>";
}

// Select the database
$conn->select_db($db);

// SQL to create tables
$sqlQueries = [
    "users" => "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    "food_entries" => "
        CREATE TABLE IF NOT EXISTS food_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            meal_type VARCHAR(50),
            food_name VARCHAR(150) NOT NULL,
            quantity VARCHAR(50),
            calories INT DEFAULT 0,
            protein FLOAT DEFAULT 0,
            carbs FLOAT DEFAULT 0,
            fat FLOAT DEFAULT 0,
            entry_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_date (user_id, entry_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    "goals" => "
        CREATE TABLE IF NOT EXISTS goals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNIQUE NOT NULL,
            daily_calories INT DEFAULT 2000,
            protein_target INT DEFAULT 150,
            carbs_target INT DEFAULT 250,
            fat_target INT DEFAULT 65,
            weight_goal FLOAT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

$errors = false;

foreach ($sqlQueries as $tableName => $sql) {
    if ($conn->query($sql)) {
        echo "<p>✅ Table <strong>'$tableName'</strong> ready</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating table '$tableName': " . $conn->error . "</p>";
        $errors = true;
    }
}

// Test connection with the health_tracker database
echo "<h3>🔌 Connection Test</h3>";
$testConn = new mysqli($host, $user, $pass, $db);
if ($testConn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection test failed: " . $testConn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>✅ Successfully connected to database '$db'</p>";
    $testConn->close();
}

$conn->close();

if (!$errors) {
    echo "<h3 style='color: green;'>✅ Setup Complete!</h3>";
    echo "<p>Your database is ready. Your API should now connect successfully.</p>";
    echo "<p>You can delete this file (<code>setup.php</code>) after setup is complete.</p>";
} else {
    echo "<h3 style='color: orange;'>⚠️ Setup completed with warnings</h3>";
    echo "<p>Please check the errors above.</p>";
}
?>