<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
};

$message = []; 

// ✅ HELPER FUNCTION: I-restore ang stock (Gamiton kini kung i-Cancel o i-Delete ang order)
function restoreStock($conn, $order_id){
   $get_order = $conn->prepare("SELECT total_products FROM orders WHERE id = ?");
   $get_order->execute([$order_id]);
   $order_data = $get_order->fetch(PDO::FETCH_ASSOC);

   if($order_data){
      // I-parse ang string (pananglitan: "Eggplant ( 2 ), Apple ( 5 )")
      $items = explode(', ', $order_data['total_products']);
      foreach($items as $item){
         preg_match('/^(.+)\s*\(\s*(\d+)\s*\)$/', trim($item), $matches);
         if(count($matches) === 3){
            $product_name = trim($matches[1]);
            $qty = (int)$matches[2];
            // I-add balik ang stock sa products table base sa ngalan
            $restore = $conn->prepare("UPDATE products SET stock = stock + ? WHERE name = ?");
            $restore->execute([$qty, $product_name]);
         }
      }
   }
}

// UPDATE ORDER STATUS
if(isset($_POST['update_order'])){

   $order_id = $_POST['order_id'];
   $update_payment = $_POST['update_payment'] ?? '';

   $check_status = $conn->prepare("SELECT payment_status FROM orders WHERE id = ?");
   $check_status->execute([$order_id]);
   $current = $check_status->fetch(PDO::FETCH_ASSOC);
   
   if($current){
      $old_status = strtolower($current['payment_status']);
      $new_status = strtolower($update_payment);

      // ✅ LOGIC: Kung i-CANCEL ang order, i-uli ang stock.
      // Dili na kita mag-reduce stock diri kay nabuhat na to sa checkout.php
      if($new_status === 'cancelled' && $old_status !== 'cancelled'){
         restoreStock($conn, $order_id);
         $message[] = 'Order Cancelled. Stock has been restored to inventory!';
      }

      $update_orders = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
      $update_orders->execute([$update_payment, $order_id]);
      
      if(empty($message)){
         $message[] = 'Payment status has been updated!';
      }
   }
}

// DELETE ORDER
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];

   $check_status = $conn->prepare("SELECT payment_status FROM orders WHERE id = ?");
   $check_status->execute([$delete_id]);
   $current = $check_status->fetch(PDO::FETCH_ASSOC);

   // ✅ LOGIC: Kung i-delete ang order nga wala pa ma-complete (e.g., Pending gihapon), 
   // i-uli ang stock para dili mausik ang inventory.
   if($current && strtolower($current['payment_status']) !== 'completed' && strtolower($current['payment_status']) !== 'cancelled'){
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
      body { 
         background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('image products/picture5.jpg') no-repeat; 
         background-size: cover; 
         background-position: center; 
         background-attachment: fixed; 
         font-family: 'Poppins', sans-serif; 
         margin: 0; padding: 0; 
      }
      .title{ text-align:center; font-size:2.3rem; margin:30px 0; font-weight:700; color: #fff; text-transform: uppercase; letter-spacing: 2px; }
      .box-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:25px; padding:30px; }
      .box{ background:rgba(255,255,255,0.1); backdrop-filter:blur(15px); border-radius:20px; padding:20px; box-shadow:0 10px 30px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); transition: 0.3s; }
      .box:hover { transform: translateY(-5px); border-color: #00f260; }
      .box p{ font-size:14px; margin:8px 0; color:#eee; line-height: 1.6; }
      .box span{ color:#fff; font-weight:600; }
      .drop-down{ width:100%; padding:12px; border:none; border-radius:10px; margin:15px 0; background: #fff; font-size: 1.4rem; }
      .flex-btn{ display:flex; gap:10px; }
      .option-btn, .delete-btn{ flex:1; text-align:center; padding:12px; border-radius:10px; text-decoration:none; font-weight:600; cursor:pointer; border:none; font-size: 1.4rem; }
      .option-btn{ background: linear-gradient(90deg, #00f260, #0575e6); color:white; }
      .delete-btn{ background:#e74c3c; color:white; }
      .message-box { background: rgba(46, 204, 113, 0.9); color: white; text-align: center; padding: 1.2rem; font-size: 1.6rem; margin: 1rem 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<?php
if(isset($message) && is_array($message)){
   foreach($message as $msg){
      echo '<div class="message-box">'.$msg.'</div>';
   }
}
?>

<section class="Placed-orders">
   <h1 class="title">Manage Orders</h1>
   <div class="box-container">
      <?php
         $select_orders = $conn->prepare("SELECT * FROM orders ORDER BY id DESC");
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
         <p> Payment Method : <span><?= strtoupper($fetch_orders['method']); ?></span> </p>
         
         <form action="" method="POST">
            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
            <select name="update_payment" class="drop-down">
               <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
               <option value="Pending">Pending</option>
               <option value="Completed">Completed</option>
               <option value="Cancelled">Cancelled</option>
            </select>
            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="Update">
               <a href="admin_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order? (Stock will be restored if not completed)');">Delete</a>
            </div>
         </form>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty" style="color:white; text-align:center; font-size:2rem; width:100%;">No orders found.</p>';
         }
      ?>
   </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
