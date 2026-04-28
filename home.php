<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

/* ADD TO WISHLIST */
if(isset($_POST['add_to_wishlist'])){

   // 1. Fixed: Added missing semicolon and replaced deprecated filters
   $pid = htmlspecialchars(trim($_POST['pid'])); 
   $p_name = htmlspecialchars(trim($_POST['p_name']));
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = htmlspecialchars(trim($_POST['p_image']));

   $check = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check->execute([$p_name, $user_id]);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if($check->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   }elseif($check_cart->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      // 2. Fixed: Explicitly naming columns prevents the 'null id' fatal error
      $insert = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'added to wishlist!';
   }
}

/* ADD TO CART */
if(isset($_POST['add_to_cart'])){

   $pid = htmlspecialchars(trim($_POST['pid']));
   $p_name = htmlspecialchars(trim($_POST['p_name']));
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = htmlspecialchars(trim($_POST['p_image']));
   $p_qty = htmlspecialchars(trim($_POST['p_qty']));

   $check = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check->execute([$p_name, $user_id]);

   if($check->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{

      $delete_wish = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
      $delete_wish->execute([$p_name, $user_id]);

      // 3. Fixed: Listing columns specifically so the DB handles the 'id' auto-increment
      $insert = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);

      $message[] = 'added to cart!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

*{
   margin:0;
   padding:0;
   box-sizing:border-box;
}

body{
   font-family:'Poppins',sans-serif;
   background: url('image_products/picture7.jpg') no-repeat center center fixed;
   background-size: cover;
   color:#1f2d3d;
}

/* REMOVE FOG COMPLETELY */
body::before{
   display:none;
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
   padding:45px;
   border-radius:16px;
   background:#ffffff;
   box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

.home h3{
   font-size:2.4rem;
   margin:15px 0;
}

.home p{
   color:#444;
}

/* PRODUCTS */
.products{
   padding:60px 40px;
}

.title{
   text-align:center;
   font-size:2.2rem;
   font-weight:800;
   margin:25px 0;
}

/* GRID */
.box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
   gap:25px;
}

/* CARD */
.box{
   background:#ffffff;
   border-radius:14px;
   padding:15px;
   text-align:center;
   box-shadow:0 8px 20px rgba(0,0,0,0.1);
}

.box:hover{
   transform:translateY(-8px);
}

/* IMAGE */
.box img{
   width:100%;
   height:180px;
   object-fit:cover;
   border-radius:12px;
   margin-top:15px;
}

/* BUTTON */
.btn, .option-btn{
   width:100%;
   padding:10px;
   margin-top:8px;
   border:none;
   border-radius:10px;
   font-weight:bold;
   cursor:pointer;
}

.btn{
   background:#2ecc71;
   color:#fff;
}

.option-btn{
   background:#f1c40f;
   color:#2c3e50;
}

</style>
</head>

<body>

<?php include 'header.php'; ?>

<!-- HERO -->
<div class="home-bg">
   <section class="home">
      <div class="content">
         <h3>Fresh Organic Grocery Shop</h3>
         <p>Affordable and fresh products delivered to you.</p>
      </div>
   </section>
</div>

<!-- PRODUCTS -->
<section class="products">

<h1 class="title">latest products</h1>

<div class="box-container">

<?php
$select_products = $conn->prepare("SELECT * FROM products");
$select_products->execute();

if($select_products->rowCount() > 0){
   while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
?>

<div class="box">

   <p class="price">₱<?= $fetch_products['price']; ?></p>

   <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">

   <div class="name"><?= $fetch_products['name']; ?></div>

   <form method="POST">
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">

      <input type="number" name="p_qty" value="1" min="1">

      <button type="submit" name="add_to_cart" class="btn">add to cart</button>
      <button type="submit" name="add_to_wishlist" class="option-btn">wishlist</button>
   </form>

</div>

<?php
   }
}else{
   echo '<p class="empty">no products found!</p>';
}
?>

</div>
</section>

</body>
</html>
