<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

if(isset($_POST['add_to_wishlist'])){

   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   }elseif($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      $insert_wishlist = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'added to wishlist!';
   }
}

if(isset($_POST['add_to_cart'])){

   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
   $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to cart!';
   }
  
}

?>
<!DOCTYPE html>
<html lang="en">
   <head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">


<html lang="en">
   <head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

*{
   margin:0;
   padding:0;
   box-sizing:border-box;
}

/* GLOBAL */
body{
   font-family:'Poppins',sans-serif;
   background: url('image products/picture7.jpg') no-repeat center center fixed;
   background-size: cover;
   color:#2c3e50;
}
body::before{
   content:'';
   position:fixed;
   top:0;
   left:0;
   width:100%;
   height:100%;
   background:rgba(0,0,0,0.4);
   z-index:-1;
}

/* HERO */
.home-bg{
   min-height:85vh;
   display:flex;
   align-items:center;
   justify-content:center;
   padding:40px;
}

.home .content{
   text-align:center;
   max-width:800px;
   padding:40px;
   border-radius:25px;
   background: rgba(255,255,255,0.6);
   backdrop-filter: blur(15px);
   box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.home .content span{
   color:#2ecc71;
   letter-spacing:2px;
   font-size:14px;
}

.home h3{
   font-size:2.8rem;
   margin:15px 0;
}

.home p{
   color:#555;
}

/* PRODUCTS */
.products{
   padding:60px 40px;
}

/* TITLE */
.title{
   text-align:center;
   font-size:2.4rem;
   font-weight:800;
   margin:25px 0;
   letter-spacing:2px;
   text-transform:uppercase;
   background:linear-gradient(90deg,#2ecc71,#3498db);
   -webkit-background-clip:text;
   -webkit-text-fill-color:transparent;
}

.title::after{
   content:'';
   width:80px;
   height:3px;
   background:#2ecc71;
   display:block;
   margin:10px auto;
   border-radius:5px;
}

/* GRID */
.box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
   gap:25px;
}

/* CARD */
.box{
   position:relative;
   background:rgba(255,255,255,0.95);
   border-radius:22px;
   padding:15px;
   text-align:center;
   transition:0.35s;
   box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.box:hover{
   transform:translateY(-10px) scale(1.02);
   box-shadow:0 20px 40px rgba(0,0,0,0.15);
}

/* IMAGE */
.box img{
   width:100%;
   height:180px;
   object-fit:cover;
   border-radius:16px;
   margin-top:20px;
}

/* PRICE */
.price{
   position:absolute;
   top:12px;
   left:12px;
   background:linear-gradient(45deg,#27ae60,#2ecc71);
   color:white;
   padding:6px 14px;
   border-radius:30px;
   font-weight:bold;
   font-size:13px;
}

/* VIEW ICON */
.box .fa-eye{
   position:absolute;
   top:12px;
   right:12px;
   background:white;
   padding:9px;
   border-radius:50%;
   color:#2c3e50;
   box-shadow:0 6px 14px rgba(0,0,0,0.1);
   transition:0.3s;
}

.box .fa-eye:hover{
   background:#2ecc71;
   color:white;
}

/* NAME */
.name{
   margin:12px 0;
   font-weight:700;
   font-size:17px;
}

/* QTY */
.qty{
   width:70px;
   padding:6px;
   border-radius:10px;
   border:1px solid #ddd;
   text-align:center;
   margin:10px auto;
   display:block;
}

/* BUTTONS */
.btn,
.option-btn{
   width:100%;
   padding:11px;
   margin-top:8px;
   border:none;
   border-radius:14px;
   cursor:pointer;
   font-weight:700;
   transition:0.3s;
}

/* CART BUTTON */
.btn{
   background:linear-gradient(45deg,#2ecc71,#27ae60);
   color:white;
}

.btn:hover{
   transform:scale(1.05);
}

/* WISHLIST BUTTON */
.option-btn{
   background:linear-gradient(45deg,#f1c40f,#f39c12);
   color:#2c3e50;
}

.option-btn:hover{
   transform:scale(1.05);
}

/* EMPTY */
.empty{
   text-align:center;
   font-size:18px;
   color:#888;s
   margin-top:30px;
}

</style>

</head>

<body>

<?php include 'header.php'; ?>


<div class="home-bg">
   <section class="home">
      <div class="content">
         <span>don't panic, go organize</span>
         <h3>Reach For A Healthier You With Organic Foods</h3>
         <p>Fresh, organic and delivered with care — upgrade your lifestyle today.</p>
         <a href="about.php" class="btn">about us</a>
      </div>
   </section>
</div>

   <?php
      $select_products = $conn->prepare("SELECT * FROM products");
      $select_products->execute();

      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>



   <?php
      }
   }else{
   
   }
   ?>

   </div>
</section>










</body>
</html>