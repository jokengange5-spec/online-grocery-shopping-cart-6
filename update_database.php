<?php
include 'config.php';

try {
    // Add image_data column to products table (PostgreSQL syntax)
    $sql = "ALTER TABLE products ADD COLUMN IF NOT EXISTS image_data TEXT";
    $conn->exec($sql);
    echo "✓ image_data column added successfully!<br>";

    // If you're also using Cloudinary, you might want to make the image column larger
    $sql2 = "ALTER TABLE products ALTER COLUMN image TYPE TEXT";
    $conn->exec($sql2);
    echo "✓ image column type updated to TEXT (for long URLs)<br>";

    echo "<br>Database is now ready for file uploads!<br>";
    echo "<a href='admin_products.php'>Go to Admin Products</a>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
