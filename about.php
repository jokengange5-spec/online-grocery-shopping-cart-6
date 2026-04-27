<?php
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shop</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>

<body>

<?php include 'header.php'; ?>

<!-- PRODUCTS -->
<section class="products">

   <div class="product-container">


   </div>
</section>

<!-- ABOUT -->
<section class="about">
   <div class="row">

      <div class="box">
         <img src="image products/shopping1.png" style="width:300px; height:250px; object-fit:cover;" alt="">
         <h3>why choose us?</h3>
         <p>Because we offer quality products, affordable prices, and reliable service you can trust every time.</p>
         <a href="contact.php" class="btn">contact us</a>
      </div>

      <div class="box">
         <img src="image products/shopping2.png" style="width:300px; height:250px; object-fit:cover;"alt=""> 
         <h3>what we provide?</h3>
         <p>We provide fresh, high-quality products delivered with care, ensuring satisfaction in every order.</p>
         <a href="shop.php" class="btn">our shop</a>
      </div>

   </div>
</section>


<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>