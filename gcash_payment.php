<?php
include 'config.php';
session_start();
$amount = $_GET['amount'] ?? '0';
?>
<!DOCTYPE html>
<html>
<head>
    <title>GCash Payment</title>
    <style>
        body{ font-family: sans-serif; text-align: center; padding: 50px; background: #f1f1f1; }
        .payment-box{ background: white; padding: 30px; border-radius: 15px; display: inline-block; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        img{ width: 250px; margin: 20px 0; }
        .btn{ background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="payment-box">
        <h2>GCash Payment</h2>
        <p>Please scan the QR code below to pay:</p>
        <h1 style="color: #0575e6;">₱<?php echo number_format($amount, 2); ?></h1>
        
        <!-- Ipakita imong QR Code dri -->
        <img src="images/my_gcash_qr.jpg" alt="GCash QR Code">
        
        <p>After paying, please take a screenshot for proof.</p>
        <br><br>
        <a href="orders.php" class="btn">I have already paid</a>
    </div>
</body>
</html>
