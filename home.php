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
   <title>Home - Joken's Grocery</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

      :root{
         --green: #27ae60;
         --black: #333;
         --white: #fff;
         --light-color: #666;
         --border: .1rem solid rgba(0,0,0,.1);
         --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      *{
         margin:0; padding:0;
         box-sizing:border-box;
         font-family: 'Poppins', sans-serif;
         outline: none; border: none;
         text-decoration: none;
      }

      body{
         background-color: #f7f7f7;
         color: var(--black);
      }

      /* HERO SECTION */
      .home-bg{
         min-height: 80vh;
         background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture7.jpg') no-repeat;
         background-size: cover;
         background-position: center;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 2rem;
      }

      .home .content{
         text-align: center;
         background: rgba(255, 255, 255, 0.95);
         padding: 3rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         max-width: 700px;
         width: 100%;
      }

      .home .content span{
         color: var(--green);
         font-size: 1.2rem;
         font-weight: 600;
         text-transform: uppercase;
         display: block;
         margin-bottom: 1rem;
      }

      .home .content h3{
         font-size: 3.5rem;
         color: var(--black);
         line-height: 1.2;
         margin-bottom: 1rem;
      }

      .home .content p{
         font-size: 1.1rem;
         color: var(--light-color);
         margin-bottom: 2rem;
         line-height: 1.6;
      }

      .home .content .btn{
         display: inline-block;
         background: var(--green);
         color: var(--white);
         padding: 1rem 3rem;
         border-radius: .5rem;
         font-size: 1.2rem;
         font-weight: 600;
         transition: .3s ease;
      }

      .home .content .btn:hover{
         background: var(--black);
         transform: scale(1.05);
      }

      /* FEATURED CATEGORIES */
      .home-category{
         padding: 5rem 5%;
         text-align: center;
      }

      .home-category .title{
         font-size: 2.5rem;
         margin-bottom: 3rem;
         color: var(--black);
      }

      .home-category .box-container{
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
         gap: 2rem;
      }

      .home-category .box-container .box{
         background: var(--white);
         padding: 2.5rem;
         border-radius: 1rem;
         box-shadow: var(--shadow);
         border: var(--border);
         transition: .3s;
      }

      .home-category .box-container .box:hover{
         background: var(--green);
      }

      .home-category .box-container .box img{
         height: 8rem;
         margin-bottom: 1.5rem;
      }

      .home-category .box-container .box h3{
         font-size: 1.8rem;
         color: var(--black);
      }

      .home-category .box-container .box:hover h3{
         color: var(--white);
      }

      /* RESPONSIVE */
      @media (max-width: 768px){
         .home .content h3{
            font-size: 2.5rem;
         }
         .home .content{
            padding: 2rem;
         }
      }

      @media (max-width: 450px){
         .home .content h3{
            font-size: 2rem;
         }
         .home .content p{
            font-size: 1rem;
         }
      }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- HERO SECTION -->
<div class="home-bg">
   <section class="home">
      <div class="content">
         <span>fresh from our farm</span>
         <h3>Fresh Organic Grocery Shop</h3>
         <p>Your favorite fresh vegetables, fruits, fish, and meat delivered straight to your doorstep</p>
         <a href="shop.php" class="btn">Shop Now</a>
      </div>
   </section>
</div>

<!-- QUICK CATEGORIES -->
<section class="home-category">
   <h1 class="title">Explore Categories</h1>

   <div class="box-container">
      <div class="box">
         <img src="image products/apple.jpg" style="object-fit: contain;" alt="">
         <h3>Fruits</h3>
         <a href="category.php?category=fruits" class="btn" style="margin-top:1rem; font-size:1rem; padding: 0.5rem 1rem;">View All</a>
      </div>

      <div class="box">
         <img src="image products/carrot.jpg" style="object-fit: contain;" alt="">
         <h3>Vegetables</h3>
         <a href="category.php?category=vegetables" class="btn" style="margin-top:1rem; font-size:1rem; padding: 0.5rem 1rem;">View All</a>
      </div>

      <div class="box">
         <img src="image products/tuna.jpg" style="object-fit: contain;" alt="">
         <h3>Fish</h3>
         <a href="category.php?category=fish" class="btn" style="margin-top:1rem; font-size:1rem; padding: 0.5rem 1rem;">View All</a>
      </div>

      <div class="box">
         <img src="image products/pork.jpg" style="object-fit: contain;" alt="">
         <h3>Meat</h3>
         <a href="category.php?category=meat" class="btn" style="margin-top:1rem; font-size:1rem; padding: 0.5rem 1rem;">View All</a>
      </div>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
