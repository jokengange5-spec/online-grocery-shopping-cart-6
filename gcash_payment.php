<?php
ob_start();
include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit();
}

// Check if there's a pending order
if(!isset($_SESSION['pending_order'])){
   header('location:checkout.php');
   exit();
}

$pending_order = $_SESSION['pending_order'];

// Handle payment confirmation
if(isset($_POST['confirm_payment'])){
   $insert_order = $conn->prepare("INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?)");
   $insert_order->execute([
      $pending_order['user_id'],
      $pending_order['name'],
      $pending_order['number'],
      $pending_order['email'],
      $pending_order['method'],
      $pending_order['address'],
      $pending_order['total_products'],
      $pending_order['total_price'],
      $pending_order['placed_on'],
      'paid' // GCash payment is instant
   ]);

   // Clear cart
   $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
   $delete_cart->execute([$user_id]);

   // Clear pending order
   unset($_SESSION['pending_order']);

   // Redirect to success page
   header('location:order_success.php');
   exit();
}

// Handle cancellation
if(isset($_POST['cancel_payment'])){
   unset($_SESSION['pending_order']);
   header('location:checkout.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>GCash Payment</title>
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

      .gcash-container {
         background: white;
         border-radius: 20px;
         box-shadow: 0 10px 40px rgba(0,0,0,0.1);
         width: 90%;
         max-width: 480px;
         padding: 30px;
      }

      .gcash-header {
         text-align: center;
         margin-bottom: 30px;
      }

      .gcash-logo {
         background: #007bff;
         color: white;
         width: 80px;
         height: 80px;
         border-radius: 50%;
         display: flex;
         justify-content: center;
         align-items: center;
         font-size: 40px;
         font-weight: bold;
         margin: 0 auto 15px;
      }

      .gcash-header h2 {
         color: #007bff;
         font-size: 24px;
      }

      .order-summary {
         background: #f8f9fa;
         padding: 20px;
         border-radius: 10px;
         margin-bottom: 20px;
      }

      .order-summary h3 {
         color: #333;
         margin-bottom: 15px;
         font-size: 18px;
      }

      .order-summary .item {
         display: flex;
         justify-content: space-between;
         padding: 8px 0;
         color: #666;
         font-size: 14px;
      }

      .order-summary .total {
         display: flex;
         justify-content: space-between;
         padding-top: 15px;
         margin-top: 15px;
         border-top: 2px solid #dee2e6;
         font-weight: bold;
         font-size: 18px;
         color: #333;
      }

      .total-amount {
         color: #007bff;
      }

      .payment-instruction {
         text-align: center;
         margin: 20px 0;
         color: #666;
         font-size: 14px;
         line-height: 1.6;
      }

      .btn {
         width: 100%;
         padding: 15px;
         border: none;
         border-radius: 10px;
         font-size: 16px;
         font-weight: 600;
         cursor: pointer;
         margin-top: 10px;
         transition: all 0.3s ease;
      }

      .btn-confirm {
         background: #007bff;
         color: white;
      }

      .btn-confirm:hover {
         background: #0056b3;
         transform: translateY(-2px);
      }

      .btn-cancel {
         background: #f8f9fa;
         color: #dc3545;
         border: 2px solid #dc3545;
      }

      .btn-cancel:hover {
         background: #dc3545;
         color: white;
      }

      .gcash-details {
         background: #e3f2fd;
         padding: 15px;
         border-radius: 10px;
         margin: 20px 0;
         text-align: center;
      }

      .gcash-details p {
         margin: 5px 0;
         color: #333;
      }

      .gcash-number {
         font-size: 20px;
         font-weight: bold;
         color: #007bff;
         margin: 10px 0;
      }

      .reference-note {
         font-size: 12px;
         color: #666;
         margin-top: 10px;
      }
   </style>
</head>
<body>

<div class="gcash-container">
   <div class="gcash-header">
      <div class="gcash-logo">G</div>
      <h2>GCash Payment</h2>
   </div>

   <div class="order-summary">
      <h3>Order Summary</h3>
      <?php 
      $total = 0;
      foreach($pending_order['cart_items'] as $item): 
         $subtotal = $item['price'] * $item['quantity'];
         $total += $subtotal;
      ?>
      <div class="item">
         <span><?= $item['name']; ?> (x<?= $item['quantity']; ?>)</span>
         <span>₱<?= number_format($subtotal, 2); ?></span>
      </div>
      <?php endforeach; ?>
      <div class="total">
         <span>Total</span>
         <span class="total-amount">₱<?= number_format($pending_order['total_price'], 2); ?></span>
      </div>
   </div>

   <div class="gcash-details">
      <p><strong>Send your payment to:</strong></p>
      <div class="gcash-number">0912-345-6789</div>
      <p>Account Name: <strong>Grocery Store Inc.</strong></p>
      <p class="reference-note">*Use your name as payment reference</p>
   </div>

   <div class="payment-instruction">
      <i class="fas fa-info-circle"></i>
      Click "Confirm Payment" after sending your GCash payment.<br>
      Your order will be processed immediately.
   </div>

   <form method="POST">
      <button type="submit" name="confirm_payment" class="btn btn-confirm">
         <i class="fas fa-check-circle"></i> Confirm Payment
      </button>
      <button type="submit" name="cancel_payment" class="btn btn-cancel">
         <i class="fas fa-times-circle"></i> Cancel & Go Back
      </button>
   </form>
</div>

</body>
</html>
