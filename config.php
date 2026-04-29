<?php

$host = "dpg-d7ocn33bc2fs73basvog-a.singapore-postgres.render.com";
$db_name = "online_shop_am8u";
$username = "online_shop_am8u_user";
$password = "OquhdogSYiICZFfiWPg0j0cbvume6VIY";
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
