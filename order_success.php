<?php
ob_start();
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Order Success</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
      }

      body {
         background: #f5f5f5;
         min-height: 100vh;
         display: flex;
         justify-content: center;
         align-items: center;
      }

      .success-container {
         background: white;
         border-radius: 20px;
         box-shadow: 0 10px 40px rgba(0,0,0,0.1);
         padding: 50px;
         text-align: center;
         max-width: 500px;
         width: 90%;
      }

      .success-icon {
         font-size: 80px;
         color: #27ae60;
         margin-bottom: 20px;
      }

      h1 {
         color: #333;
         margin-bottom: 15px;
      }

      p {
         color: #666;
         margin-bottom: 10px;
         font-size: 16px;
      }

      .btn {
         display: inline-block;
         padding: 15px 40px;
         background: #27ae60;
         color: white;
         text-decoration: none;
         border-radius: 10px;
         margin-top: 20px;
         font-weight: 600;
         transition: all 0.3s ease;
      }

      .btn:hover {
         background: #219a52;
         transform: translateY(-2px);
      }
   </style>
</head>
<body>

<div class="success-container">
   <div class="success-icon">
      <i class="fas fa-check-circle"></i>
   </div>
   <h1>Payment Successful!</h1>
   <p>Your GCash payment has been confirmed.</p>
   <p>Your order is now being processed.</p>
   <p>Thank you for shopping with us!</p>
   <a href="orders.php" class="btn">View My Orders</a>
   <br>
   <a href="shop.php" class="btn" style="background: #007bff; margin-top: 10px;">Continue Shopping</a>
</div>

</body>
</html>
