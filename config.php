<?php

$host = "localhost";
$db_name = "online_shop";
$username = "postgres"; // Default Postgres username
$password = "qwerty";    // Use the password you set during Postgres installation

try {
    // Change 'mysql' to 'pgsql'
    $conn = new PDO("pgsql:host=$host;dbname=$db_name", $username, $password);
    
    // Set error mode to exception so you can see errors clearly
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>