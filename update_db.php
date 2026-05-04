<?php
include 'config.php';

try {
    // Change image column from VARCHAR to TEXT to hold base64 data
    $conn->exec("ALTER TABLE products ALTER COLUMN image TYPE TEXT");
    echo "✓ image column changed to TEXT type successfully!<br>";
    echo "You can now upload images. <a href='admin_products.php'>Go to Admin Products</a>";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
