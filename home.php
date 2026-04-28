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

/*body{
   font-family:'Poppins',sans-serif;
   background: url('image_products/picture7.jpg') no-repeat center center fixed;
   background-size: cover;
   color:#1f2d3d;
}

/* LESS FOGGY OVERLAY */
body::before{
   content:'';
   position:fixed;
   top:0;
   left:0;
   width:100%;
   height:100%;
   background:rgba(0,0,0,0.25);
   z-index:-1;
}

/* HERO CLEAN */
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
   padding:45px;
   border-radius:18px;
   background:rgba(255,255,255,0.88);
   backdrop-filter: blur(6px);
   box-shadow:0 15px 35px rgba(0,0,0,0.2);
}

.home .content span{
   color:#27ae60;
   font-weight:600;
   font-size:14px;
   letter-spacing:1px;
}

.home h3{
   font-size:2.6rem;
   margin:15px 0;
   color:#1f2d3d;
}

.home p{
   color:#444;
}

/* TITLE CLEAN */
.title{
   text-align:center;
   font-size:2.3rem;
   font-weight:800;
   margin:25px 0;
   color:#1f2d3d;
   letter-spacing:1px;
}

/* PRODUCT SECTION */
.products{
   padding:60px 40px;
}

/* GRID */
.box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
   gap:25px;
}

/* PRODUCT CARD CLEAN */
.box{
   background:#ffffff;
   border-radius:16px;
   padding:15px;
   text-align:center;
   transition:0.3s;
   box-shadow:0 10px 20px rgba(0,0,0,0.08);
}

.box:hover{
   transform:translateY(-8px);
   box-shadow:0 18px 35px rgba(0,0,0,0.15);
}

.box img{
   width:100%;
   height:180px;
   object-fit:cover;
   border-radius:12px;
   margin-top:20px;
}

/* PRICE */
.price{
   position:absolute;
   top:12px;
   left:12px;
   background:#2ecc71;
   color:white;
   padding:6px 12px;
   border-radius:20px;
   font-weight:bold;
   font-size:13px;
}

/* BUTTONS CLEAN */
.btn{
   background:#2ecc71;
   color:white;
   width:100%;
   padding:11px;
   margin-top:8px;
   border:none;
   border-radius:12px;
   font-weight:700;
   cursor:pointer;
   transition:0.3s;
}

.btn:hover{
   background:#27ae60;
   transform:scale(1.03);
}

.option-btn{
   background:#f1c40f;
   color:#2c3e50;
   width:100%;
   padding:11px;
   margin-top:8px;
   border:none;
   border-radius:12px;
   font-weight:700;
   cursor:pointer;
   transition:0.3s;
}

.option-btn:hover{
   transform:scale(1.03);
} GLOBAL */

  





  
/* GRID */



/* PRICE */




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
