<?php
include 'config.php';

// Add column if not exists
try {
    $conn->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS image_data TEXT");
    echo "Database updated! You can delete this file now.";
} catch(PDOException $e) {
    echo "Already updated or error: " . $e->getMessage();
}
?>
