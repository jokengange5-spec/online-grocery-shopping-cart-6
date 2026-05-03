<?php
// Run this file once to fix permissions, then delete it for security
$dir = __DIR__ . '/uploaded_img';

if (!file_exists($dir)) {
    if (mkdir($dir, 0755, true)) {
        echo "Directory created successfully<br>";
    } else {
        echo "Failed to create directory. Check parent folder permissions.<br>";
    }
}

if (chmod($dir, 0755)) {
    echo "Permissions set to 755 successfully<br>";
} else {
    echo "Failed to set permissions. You need to run this command manually via SSH:<br>";
    echo "<code>sudo chmod 755 " . $dir . "</code><br>";
    echo "Or if that doesn't work:<br>";
    echo "<code>sudo chmod 777 " . $dir . "</code><br>";
}

echo "<br>Current permissions: " . substr(sprintf('%o', fileperms($dir)), -4);
echo "<br>Directory writable: " . (is_writable($dir) ? 'Yes' : 'No');

// Try to create a test file
$test_file = $dir . '/test.txt';
if (@file_put_contents($test_file, 'test')) {
    echo "<br>Test file created successfully";
    @unlink($test_file);
} else {
    echo "<br>Cannot write to directory";
}
?>
