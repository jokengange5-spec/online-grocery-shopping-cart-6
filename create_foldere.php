<?php
// EMERGENCY FIX: Create uploaded_img folder with proper permissions
// Run this file ONCE then DELETE it

$target_dir = '/var/www/html/uploaded_img';

// Try to create using system command (more powerful than mkdir)
if (!is_dir($target_dir)) {
    // Use exec to create with sudo-like permissions
    $cmd = "mkdir -p " . escapeshellarg($target_dir) . " 2>&1";
    $output = shell_exec($cmd);
    
    if (is_dir($target_dir)) {
        echo "✓ Folder created successfully<br>";
    } else {
        echo "✗ Could not create folder. Trying alternative method...<br>";
        
        // Alternative: Create in a different location
        $alt_dir = __DIR__ . '/uploaded_img';
        if (!is_dir($alt_dir)) {
            mkdir($alt_dir, 0777, true);
        }
        
        if (is_dir($alt_dir)) {
            echo "✓ Created in alternative location: " . $alt_dir . "<br>";
            echo "Update your code to use this path instead.<br>";
        }
    }
}

// Set permissions
if (is_dir($target_dir)) {
    chmod($target_dir, 0777);
    echo "✓ Permissions set<br>";
}

echo "<br>Final check:<br>";
echo "Directory exists: " . (is_dir($target_dir) ? 'Yes' : 'No') . "<br>";
echo "Is writable: " . (is_writable($target_dir) ? 'Yes' : 'No') . "<br>";

// FINAL CHECK: Try to write a file
$test_file = $target_dir . '/test.txt';
if (@file_put_contents($test_file, 'test')) {
    echo "✓ Can write files! ALL GOOD!<br>";
    @unlink($test_file);
} else {
    echo "✗ STILL cannot write. CONTACT YOUR HOSTING PROVIDER<br>";
    echo "Ask them: 'Please set permissions 777 on /var/www/html/uploaded_img'<br>";
}
?>
