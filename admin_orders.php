<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

// ✅ HELPER FUNCTION: I-restore ang stock base sa total_products string
function restoreStock($conn, $order_id){
   $get_order = $conn->prepare("SELECT total_products FROM orders WHERE id = ?");
   $get_order->execute([$order_id]);
   $order_data = $get_order->fetch(PDO::FETCH_ASSOC);

   if($order_data){
      $items = explode(', ', $order_data['total_products']);
      foreach($items as $item){
         // Format: "ProductName ( qty )"
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

if(isset($_POST['update_order'])){

   $order_id = $_POST['order_id'];
   $update_payment = $_POST['update_payment'] ?? '';
   $name = isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : '';

   // ✅ KUNG CANCELLED, I-RESTORE ANG STOCK (once lang, dili mag-double)
   if($update_payment === 'Cancelled'){
      $check_status = $conn->prepare("SELECT payment_status FROM orders WHERE id = ?");
      $check_status->execute([$order_id]);
      $current = $check_status->fetch(PDO::FETCH_ASSOC);

      if(strtolower($current['payment_status']) !== 'cancelled'){
         restoreStock($conn, $order_id);
      }
   }

   $update_orders = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
   $update_orders->execute([$update_payment, $order_id]);
   $message[] = 'Payment status has been updated!';
}

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];

   // ✅ RESTORE STOCK BEFORE DELETE (kung dili pa cancelled para dili double)
   $check_status = $conn->prepare("SELECT payment_status FROM orders WHERE id = ?");
   $check_status->execute([$delete_id]);
   $current = $check_status->fetch(PDO::FETCH_ASSOC);

   if($current && strtolower($current['payment_status']) !== 'cancelled'){
      restoreStock($conn, $delete_id);
   }

   $delete_orders = $conn->prepare("DELETE FROM orders WHERE id = ?");
   $delete_orders->execute([$delete_id]);
   header('location:admin_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">

   <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap');

body {
   background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture5.jpg') no-repeat;
   background-size: cover;
   background-position: center;
   background-attachment: fixed;
   font-family: 'Poppins', sans-serif;
   margin: 0;
   padding: 0;
}

.title{
   text-align:center;
   font-size:2.3rem;
   margin:30px 0;
   font-weight:700;
   background: linear-gradient(90deg,#00f260,#0575e6);
   -webkit-background-clip:text;
   -webkit-text-fill-color:transparent;
}

.box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
   gap:25px;
   padding:30px;
}

.box{
   background:rgba(255,255,255,0.08);
   backdrop-filter:blur(15px);
   border-radius:20px;
   padding:20px;
   box-shadow:0 10px 30px rgba(0,0,0,0.4);
   transition:0.3s;
}

.box:hover{
   transform:translateY(-8px);
}

.box p{
   font-size:14px;
   margin:6px 0;
   color:#ddd;
}

.box span{
   color:#fff;
   font-weight:500;
}

.price{
   font-size:20px;
   color:#00f260;
   font-weight:700;
   margin:10px 0;
}

.drop-down{
   width:100%;
   padding:10px;
   border:none;
   border-radius:10px;
   margin:10px 0;
}

.flex-btn{
   display:flex;
   gap:10px;
}

.option-btn, .delete-btn{
   flex:1;
   text-align:center;
   padding:10px;
   border-radius:10px;
   text-decoration:none;
   font-weight:600;
   cursor:pointer;
}

.option-btn{
   background:#3498db;
   color:white;
   border:none;
}

.delete-btn{
   background:#e74c3c;
   color:white;
}

.option-btn:hover, .delete-btn:hover{
   transform:scale(1.05);
}

.empty{
   text-align:center;
   font-size:18px;
   color:#bbb;
}

.message-box {
   background: #2ecc71;
   color: white;
   text-align: center;
   padding: 1rem;
   font-size: 1.5rem;
   margin: 1rem 2rem;
   border-radius: 10px;
}
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

   <h1 class="title">placed orders</h1>

   <div class="box-container">

      <?php
         $select_orders = $conn->prepare("SELECT * FROM orders");
         $select_orders->execute();
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
      ?>
      <div class="box">
         <p> User id : <span><?= $fetch_orders['user_id']; ?></span> </p>
         <p> Placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
         <p> Name : <span><?= $fetch_orders['name']; ?></span> </p>
         <p> Email : <span><?= $fetch_orders['email']; ?></span> </p>
         <p> Number : <span><?= $fetch_orders['number']; ?></span> </p>
         <p> Address : <span><?= $fetch_orders['address']; ?></span> </p>
         <p> Total products : <span><?= $fetch_orders['total_products']; ?></span> </p>
         <p> Total price : <span>₱<?= $fetch_orders['total_price']; ?></span> </p>
         <p> Payment method : <span><?= $fetch_orders['method']; ?></span> </p>
         <form action="" method="POST">
            <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
            <select name="update_payment" class="drop-down">
               <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
               <option value="Pending">Pending</option>
               <option value="Completed">Completed</option>
               <!-- ✅ CANCELLED OPTION para ma-restore ang stock -->
               <option value="Cancelled">Cancelled</option>
            </select>
            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="Update">
               <a href="admin_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Delete this order and restore stock?');">Delete</a>
            </div>
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
      ?>

   </div>

</section>

<script src="js/script.js"></script>
<script>
let userBtn = document.querySelector('#user-btn');
let profile = document.querySelector('.profile');

userBtn.onclick = () => {
   profile.classList.toggle('active');
}
</script>
</body>
</html>
