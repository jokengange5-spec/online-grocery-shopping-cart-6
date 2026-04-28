<?php

$host = "dpg-xxxxxx-a.oregon-postgres.render.com";
$db_name = "onlineshop_am8u";
$username = "onlineshop_am8u_user";
$password = "OquhdogSYiICZFfiWPg0j0cbvume6VIY";
$port = "5432";

try {

    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db_name;sslmode=require",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully!";

} catch (PDOException $e) {

    die("Connection failed: " . $e->getMessage());

}

?>
