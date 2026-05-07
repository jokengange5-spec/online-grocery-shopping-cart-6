<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
};

$message = []; 

// ✅ HELPER FUNCTION: Restore stock
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

      if($new_status === 'cancelled' && $old_status !== 'cancelled'){
          restoreStock($conn, $order_id);
          $message[] = ['text' => 'Order Cancelled. Stock restored!', 'type' => 'info'];
      }

      $update_orders = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
      $update_orders->execute([$update_payment, $order_id]);
      
      if(empty($message)){
          $message[] = ['text' => 'Order status has been updated!', 'type' => 'success'];
      }
   }
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
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
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
      .option-btn{ flex:1; text-align:center; padding:12px; border-radius:10px; text-decoration:none; font-weight:600; cursor:pointer; border:none; font-size: 1.4rem; background: linear-gradient(90deg, #00f260, #0575e6); color:white; }
   </style>
</head>
<body>

<?php 
/* 
   IMPORTANT: Only include admin_header.php if THIS file is NOT admin_header.php. 
   If this file IS the header, remove the include line below.
*/
// include 'components/admin_header.php'; 
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
               <option value="Preparing">Preparing</option>
               <option value="Out for Delivery">Out for Delivery</option>
               <option value="Delivered">Delivered</option>
               <option value="Cancelled">Cancelled</option>
            </select>
            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="Update Status">
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

<script>
<?php if(!empty($message)): ?>
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });

    <?php foreach($message as $msg): ?>
        Toast.fire({
          icon: '<?php echo $msg['type']; ?>',
          title: '<?php echo $msg['text']; ?>'
        });
    <?php endforeach; ?>
<?php endif; ?>
</script>

</body>
</html>
