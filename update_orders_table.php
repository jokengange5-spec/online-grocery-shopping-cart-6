<?php
include 'config.php';
try {
    $conn->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) DEFAULT 'pending'");
    echo "Orders table updated! Delete this file now.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
