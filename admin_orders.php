<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
};

$message = []; 

// ✅ HELPER FUNCTION: I-restore ang stock (Dugangan ang stock kung i-cancel)
function restoreStock($conn, $order_id){
   $get_order = $conn->prepare("SELECT total_products FROM orders WHERE id = ?");
   $get_order->execute([$order_id]);
   $order_data = $get_order->fetch(PDO::FETCH_ASSOC);

   if($order_data){
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
   }
}

// ✅ HELPER FUNCTION: I-reduce ang stock (Mao kini ang mokuha sa stock kung "Completed")
function reduceStock($conn, $order_id){
   $get_order = $conn->prepare("SELECT total_products FROM orders WHERE id = ?");
   $get_order->execute([$order_id]);
   $order_data = $get_order->fetch(PDO::FETCH_ASSOC);

   if($order_data){
      $items = explode(', ', $order_data['total_products']);
      foreach($items as $item){
         // I-extract ang product name ug quantity gikan sa string (e.g. "Apple (2)")
         preg_match('/^(.+)\s*\(\s*(\d+)\s*\)$/', trim($item), $matches);
         if(count($matches) === 3){
            $product_name = trim($matches[1]);
            $qty = (int)$matches[2];
            // Minus (-) ang stock sa products table
            $reduce = $conn->prepare("UPDATE products SET stock = stock - ? WHERE name = ? AND stock >= ?");
            $reduce->execute([$qty, $product_name, $qty]);
         }
      }
   }
}

if(isset($_POST['update_order'])){

   $order_id = $_POST['order_id'];
   $update_payment = $_POST['update_payment'] ?? '';

   // Susiha ang status karon sa database
   $check_status = $conn->prepare("SELECT payment_status FROM orders WHERE id = ?");
   $check_status->execute([$order_id]);
   $current = $check_status->fetch(PDO::FETCH_ASSOC);
   
   if($current){
      $old_status = strtolower($current['payment_status']);
      $new_status = strtolower($update_payment);

      // ✅ LOGIC: Kung i-set nimo sa Completed, kuhaan ang stock
      if($new_status === 'completed' && $old_status !== 'completed'){
         reduceStock($conn, $order_id);
         $message[] = 'Order marked as Completed. Stock has been deducted!';
      }
      
      // ✅ LOGIC: Kung i-set nimo sa Cancelled, i-uli ang stock
      elseif($new_status === 'cancelled' && $old_status !== 'cancelled'){
         restoreStock($conn, $order_id);
         $message[] = 'Order Cancelled. Stock has been restored!';
      }

      // I-update na ang payment status sa orders table
      $update_orders = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
      $update_orders->execute([$update_payment, $order_id]);
      
      if(empty($message)){
         $message[] = 'Payment status has been updated!';
      }
   }
}

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];

   $check_status = $conn->prepare("SELECT payment_status FROM orders WHERE id = ?");
   $check_status->execute([$delete_id]);
   $current = $check_status->fetch(PDO::FETCH_ASSOC);

   // I-restore ang stock kung i-delete ang order samtang Pending pa kini
   if($current && strtolower($current['payment_status']) === 'pending'){
      restoreStock($conn, $delete_id);
   }

   $delete_orders = $conn->prepare("DELETE FROM orders WHERE id = ?");
   $delete_orders->execute([$delete_id]);
   header('location:admin_orders.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap');
      body { background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture5.jpg') no-repeat; background-size: cover; background-position: center; background-attachment: fixed; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
      .title{ text-align:center; font-size:2.3rem; margin:30px 0; font-weight:700; background: linear-gradient(90deg,#00f260,#0575e6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
      .box-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:25px; padding:30px; }
      .box{ background:rgba(255,255,255,0.08); backdrop-filter:blur(15px); border-radius:20px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,0.4); transition:0.3s; border: 1px solid rgba(255,255,255,0.1); }
      .box p{ font-size:14px; margin:6px 0; color:#ddd; }
      .box span{ color:#fff; font-weight:500; }
      .drop-down{ width:100%; padding:10px; border:none; border-radius:10px; margin:10px 0; background: #fff; }
      .flex-btn{ display:flex; gap:10px; }
      .option-btn, .delete-btn{ flex:1; text-align:center; padding:10px; border-radius:10px; text-decoration:none; font-weight:600; cursor:pointer; border:none; }
      .option-btn{ background:#3498db; color:white; }
      .delete-btn{ background:#e74c3c; color:white; }
      .message-box { background: #2ecc71; color: white; text-align: center; padding: 1rem; font-size: 1.5rem; margin: 1rem 2rem; border-radius: 10px; }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo '<div class="message-box">'.$msg.'</div>';
   }
}
?>

<section class="Placed-orders">
   <h1 class="title">Placed Orders</h1>
   <div class="box-container">
      <?php
         $select_orders = $conn->prepare("SELECT * FROM orders");
         $select_orders->execute();
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
      ?>
      <div class="box">
         <p> User ID : <span><?= $fetch_orders['user_id']; ?></span> </p>
         <p> Placed On : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> Name : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> Total Products : <span style="color: #00f260;"><?= $fetch_orders['total_products']; ?></span> </p>
         <p> Total Price : <span>₱<?= number_format($fetch_orders['total_price'], 2); ?></span> </p>
         <p> Payment Method : <span><?= $fetch_orders['method']; ?></span> </p>
         <form action="" method="POST">
            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
            <select name="update_payment" class="drop-down">
               <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
               <option value="Pending">Pending</option>
               <option value="Completed">Completed</option>
               <option value="Cancelled">Cancelled</option>
            </select>
            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="Update Status">
               <a href="admin_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order?');">Delete</a>
            </div>
         </form>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty">No orders placed yet!</p>';
         }
      ?>
   </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
