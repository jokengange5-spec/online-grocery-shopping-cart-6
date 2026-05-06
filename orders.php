<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

/* --- CANCEL ORDER LOGIC WITH STOCK RESTORE --- */
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];

   // ✅ KUHAON UNA ANG ORDER DETAILS PARA MA-RESTORE ANG STOCK
   $get_order = $conn->prepare("SELECT total_products, payment_status FROM orders WHERE id = ? AND user_id = ?");
   $get_order->execute([$delete_id, $user_id]);
   $order_data = $get_order->fetch(PDO::FETCH_ASSOC);

   if($order_data && $order_data['payment_status'] == 'pending'){

      // ✅ RESTORE STOCK - parse ang total_products string
      // Format: "Apple ( 2 ), Banana ( 1 )"
      $items = explode(', ', $order_data['total_products']);
      foreach($items as $item){
         preg_match('/^(.+)\s*\(\s*(\d+)\s*\)$/', trim($item), $matches);
         if(count($matches) === 3){
            $product_name = trim($matches[1]);
            $qty = (int)$matches[2];
            $restore = $conn->prepare("UPDATE products SET stock = stock + ? WHERE name = ?");
            $restore->execute([$qty, $product_name]);
         }
      }

      // ✅ DELETE ANG ORDER PAGKAHUMAN MA-RESTORE ANG STOCK
      $delete_order = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
      $delete_order->execute([$delete_id, $user_id]);

      header('location:orders.php');
      exit;

   } else {
      $message[] = 'Dili na ma-cancel kini nga order kay naproseso na.';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

      :root{
         --green: #27ae60;
         --red: #e74c3c;
         --orange: #f39c12;
         --black: #333;
         --white: #fff;
         --light-bg: #f6f6f6;
         --border: .1rem solid rgba(0,0,0,.1);
         --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      *{
         margin:0; padding:0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
         text-decoration: none;
      }

      body {
         background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture7.jpg') no-repeat;
         background-size: cover;
         background-position: center;
         background-attachment: fixed;
         margin: 0;
         padding: 0;
      }

      .placed-orders {
         padding: 5rem 5%;
         max-width: 1200px;
         margin: 0 auto;
      }

      .title {
         text-align: center;
         margin-bottom: 3rem;
         font-size: 2.5rem;
         color: var(--white);
         text-transform: uppercase;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
         gap: 2rem;
         align-items: flex-start;
      }

      .box {
         background-color: var(--white);
         padding: 2rem;
         border-radius: 1rem;
         box-shadow: var(--shadow);
         border: var(--border);
         transition: .3s ease;
      }

      .box p {
         margin-bottom: 1rem;
         font-size: 1.1rem;
         color: var(--light-color);
         line-height: 1.5;
         border-bottom: 1px solid #eee;
         padding-bottom: 0.5rem;
      }

      .box p span {
         color: var(--black);
         font-weight: 600;
      }

      .status-badge {
         display: inline-block;
         padding: 0.3rem 1rem;
         border-radius: 2rem;
         font-size: 1rem;
         font-weight: bold;
         text-transform: capitalize;
      }

      .status-pending { background-color: #ffeaa7; color: var(--orange); }
      .status-completed { background-color: #c2fbd7; color: var(--green); }

      .delete-btn {
         display: block;
         width: 100%;
         text-align: center;
         background-color: var(--red);
         color: var(--white);
         font-size: 1.1rem;
         padding: 1rem;
         border-radius: .5rem;
         margin-top: 1rem;
         font-weight: 600;
         transition: .3s;
      }

      .delete-btn:hover {
         background-color: var(--black);
      }

      .delete-btn.disabled {
         background-color: #ccc;
         pointer-events: none;
         cursor: not-allowed;
      }

      .message {
         background: #e74c3c;
         color: white;
         text-align: center;
         padding: 1rem;
         font-size: 1.4rem;
         margin: 1rem auto;
         width: 80%;
         border-radius: 10px;
      }

      .empty {
         color: white;
         text-align: center;
         font-size: 1.6rem;
      }

      @media (max-width: 450px) {
         .box-container { grid-template-columns: 1fr; }
         .title { font-size: 2rem; }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo '<div class="message">'.$msg.'</div>';
   }
}
?>

<section class="placed-orders">

   <h1 class="title">My Placed Orders</h1>

   <div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
      $select_orders->execute([$user_id]);

      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <div class="box">
      <p> <i class="fas fa-calendar"></i> Placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
      <p> <i class="fas fa-user"></i> Name : <span><?= $fetch_orders['name']; ?></span> </p>
      <p> <i class="fas fa-phone"></i> Number : <span><?= $fetch_orders['number']; ?></span> </p>
      <p> <i class="fas fa-envelope"></i> Email : <span><?= $fetch_orders['email']; ?></span> </p>
      <p> <i class="fas fa-map-marker-alt"></i> Address : <span><?= $fetch_orders['address']; ?></span> </p>
      <p> <i class="fas fa-credit-card"></i> Payment Method : <span><?= $fetch_orders['method']; ?></span> </p>
      <p> <i class="fas fa-shopping-basket"></i> Your Orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
      <p> <i class="fas fa-money-bill-wave"></i> Total Price : <span>₱<?= number_format($fetch_orders['total_price'], 2); ?></span> </p>
      <p> Status : 
         <span class="status-badge <?= ($fetch_orders['payment_status'] == 'pending') ? 'status-pending' : 'status-completed'; ?>">
            <?= $fetch_orders['payment_status']; ?>
         </span>
      </p>

      <?php if($fetch_orders['payment_status'] == 'pending'){ ?>
         <a href="orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to cancel this order? Stock will be restored.');">Cancel Order</a>
      <?php } else { ?>
         <a href="#" class="delete-btn disabled">Cannot Cancel</a>
      <?php } ?>

   </div>
   <?php
      }
   }else{
      echo '<p class="empty">You have no existing orders!</p>';
   }
   ?>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
