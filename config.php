<?php

$host = "dpg-d7p9sfpkh4rs73da47t0-a";
$db_name = "online_shop_m071";
$username = "online_shop";
$password = "4n9SAIPuQoDJDY7IYxGdMUsPLwYUK18g";
$port = "5432";

try {

    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db_name;sslmode=require",
        $username,
        $password
    );

    // Set error mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional test message (remove in production)
    // echo "Connected successfully!";

} catch (PDOException $e) {

    die("Connection failed: " . $e->getMessage());

}

?>
