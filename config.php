<?php

$host = "dpg-xxxxxx-a.oregon-postgres.render.com";
$db_name = "onlineshop_am8u";
$username = "onlineshop_am8u_user";
$password = "OquhdogSYiICZFfiWPg0j0cbvume6VIY";
$port = "5432";

try {

    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db_name",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
