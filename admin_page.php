<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<style>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

*{
   margin:0;
   padding:0;
   box-sizing:border-box;
   font-family:'Poppins',sans-serif;
}

body{
   background: linear-gradient(135deg,#0f172a,#1e293b,#0f172a);
   color:#fff;
   min-height:100vh;
}

/* TITLE */
.title{
   text-align:center;
   font-size:2.4rem;
   font-weight:800;
   margin:40px 0 20px;
   background: linear-gradient(90deg,#00f260,#0575e6);
   -webkit-background-clip:text;
   -webkit-text-fill-color:transparent;
}

/* GRID */
.dashboard .box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
   gap:25px;
   padding:40px;
}

/* CARD DESIGN */
.box{
   position:relative;
   background:rgba(255,255,255,0.08);
   backdrop-filter: blur(15px);
   border-radius:20px;
   padding:25px;
   text-align:center;
   box-shadow:0 10px 30px rgba(0,0,0,0.4);
   transition:0.3s;
   overflow:hidden;
}

/* HOVER EFFECT */
.box:hover{
   transform:translateY(-10px);
   box-shadow:0 20px 50px rgba(0,0,0,0.6);
}

/* NUMBER */
.box h3{
   font-size:2.3rem;
   margin-bottom:10px;
   color:#00f260;
}

/* TEXT */
.box p{
   font-size:14px;
   color:#cbd5e1;
   margin-bottom:15px;
}

/* BUTTON */
.btn{
   display:inline-block;
   padding:10px 18px;
   border-radius:12px;
   text-decoration:none;
   font-weight:600;
   color:#000;
   background:linear-gradient(45deg,#00f260,#0575e6);
   transition:0.3s;
}

.btn:hover{
   transform:scale(1.05);
}

/* DECORATION GLOW */
.box::before{
   content:'';
   position:absolute;
   top:-40px;
   right:-40px;
   width:120px;
   height:120px;
   background:rgba(0,242,96,0.2);
   border-radius:50%;
}

/* SMALL GLOW LINE */
.box::after{
   content:'';
   position:absolute;
   bottom:0;
   left:0;
   width:100%;
   height:3px;
   background:linear-gradient(90deg,#00f260,#0575e6);
}
</style>
</style>

</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="dashboard">

   <h1 class="title">Admin Dashboard</h1>

   <div class="box-container">

      <!-- PENDING -->
      <div class="box">
      <?php
         $total_pendings = 0;
         $select_pendings = $conn->prepare("SELECT * FROM orders WHERE payment_status = ?");
         $select_pendings->execute(['pending']);
         while($row = $select_pendings->fetch(PDO::FETCH_ASSOC)){
            $total_pendings += $row['total_price'];
         }
      ?>
      <h3>₱<?= $total_pendings; ?></h3>
      <p>Total Pendings</p>
      <a href="admin_orders.php" class="btn">View Orders</a>
      </div>

      <!-- COMPLETED -->
      <div class="box">
      <?php
         $total_completed = 0;
         $select_completed = $conn->prepare("SELECT * FROM orders WHERE payment_status = ?");
         $select_completed->execute(['completed']);
         while($row = $select_completed->fetch(PDO::FETCH_ASSOC)){
            $total_completed += $row['total_price'];
         }
      ?>
      <h3>₱<?= $total_completed; ?></h3>
      <p>Completed Orders</p>
      <a href="admin_orders.php" class="btn">View Orders</a>
      </div>

      <!-- ORDERS -->
      <div class="box">
      <?php
         $select_orders = $conn->prepare("SELECT * FROM orders");
         $select_orders->execute();
      ?>
      <h3><?= $select_orders->rowCount(); ?></h3>
      <p>Orders Placed</p>
      <a href="admin_orders.php" class="btn">View Orders</a>
      </div>

      <!-- PRODUCTS -->
      <div class="box">
      <?php
         $select_products = $conn->prepare("SELECT * FROM products");
         $select_products->execute();
      ?>
      <h3><?= $select_products->rowCount(); ?></h3>
      <p>Products Added</p>
      <a href="admin_products.php" class="btn">View Products</a>
      </div>

      <!-- ML: TOP SELLING PRODUCTS -->
<div class="box">
<?php

// SIMPLE MACHINE LEARNING STYLE: frequency analysis
$select_ml = $conn->prepare("
   SELECT total_products, COUNT(*) as frequency
   FROM orders
   GROUP BY total_products
   ORDER BY frequency DESC
   LIMIT 1
");

$select_ml->execute();
$ml_data = $select_ml->fetch(PDO::FETCH_ASSOC);

$top_product = $ml_data['total_products'] ?? 'No data';

?>
<h3>🔥</h3>
<p>Top Product</p>
<span style="display:block; margin:10px 0; color:#00f260; font-weight:bold;">
   <?= $top_product; ?>
</span>
<a href="admin_orders.php" class="btn">Analyze Orders</a>
</div>
      <!-- USERS -->
      <div class="box">
      <?php
         $select_users = $conn->prepare("SELECT * FROM users WHERE user_type = ?");
         $select_users->execute(['user']);
      ?>
      <h3><?= $select_users->rowCount(); ?></h3>
      <p>Total Users</p>
      <a href="admin_users.php" class="btn">View Accounts</a>
      </div>

      <!-- ADMINS -->
      <div class="box">
      <?php
         $select_admins = $conn->prepare("SELECT * FROM users WHERE user_type = ?");
         $select_admins->execute(['admin']);
      ?>
      <h3><?= $select_admins->rowCount(); ?></h3>
      <p> Admin </p>
      <a href="admin_users.php" class="btn">View Account</a>
      </div>

      <!-- ACCOUNTS -->
      <div class="box">
      <?php
         $select_accounts = $conn->prepare("SELECT * FROM users");
         $select_accounts->execute();
      ?>
      <h3><?= $select_accounts->rowCount(); ?></h3>
      <p>Total Accounts</p>
      <a href="admin_users.php" class="btn">View Accounts</a>
      </div>

      <!-- MESSAGES -->
      <div class="box">
      <?php
         $select_messages = $conn->prepare("SELECT * FROM message");
         $select_messages->execute();
      ?>
      <h3><?= $select_messages->rowCount(); ?></h3>
      <p>Total Messages</p>
      <a href="admin_contacts.php" class="btn">View Messages</a>
      </div>

   </div>

</section>
<script>
let userBtn = document.querySelector('#user-btn');
let profile = document.querySelector('.profile');

userBtn.onclick = () => {
   profile.classList.toggle('active');
}
</script>
</body>
</html>