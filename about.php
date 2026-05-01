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
   <title>About Us - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

      :root{
         --green: #27ae60;
         --black: #333;
         --white: #fff;
         --light-color: #666;
         --light-bg: #f6f6f6;
         --border: .1rem solid rgba(0,0,0,.1);
         --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      *{
         margin:0; padding:0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
         text-decoration: none;
      }

      body{
         background-color: var(--light-bg);
      }

      /* ABOUT SECTION */
      .about {
         padding: 5rem 5%;
         max-width: 1200px;
         margin: 0 auto;
         text-align: center;
      }

      .about .title {
         font-size: 3rem;
         color: var(--black);
         margin-bottom: 3rem;
         text-transform: uppercase;
      }

      .about .row {
         display: flex;
         flex-wrap: wrap;
         gap: 2rem;
         justify-content: center;
         align-items: stretch;
      }

      .about .box {
         flex: 1 1 40rem; /* Mo-expand sa laptop, mo-stack sa mobile */
         background-color: var(--white);
         padding: 3rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         border: var(--border);
         transition: .3s ease;
         display: flex;
         flex-direction: column;
         align-items: center;
      }

      .about .box:hover {
         transform: translateY(-10px);
      }

      .about .box img {
         width: 100%;
         max-width: 350px;
         height: 250px;
         object-fit: contain; /* Aron dili ma-stretch ang illustration */
         margin-bottom: 2rem;
      }

      .about .box h3 {
         font-size: 2.2rem;
         color: var(--black);
         margin-bottom: 1.5rem;
         text-transform: capitalize;
      }

      .about .box p {
         font-size: 1.1rem;
         color: var(--light-color);
         line-height: 1.8;
         margin-bottom: 2rem;
      }

      /* BUTTONS */
      .btn {
         display: inline-block;
         background-color: var(--green);
         color: var(--white);
         padding: 1rem 3rem;
         border-radius: .5rem;
         font-size: 1.2rem;
         font-weight: 600;
         transition: .3s linear;
         margin-top: auto; /* I-push ang button sa ubos sa card */
      }

      .btn:hover {
         background-color: var(--black);
         letter-spacing: 1px;
      }

      /* RESPONSIVE */
      @media (max-width: 768px) {
         .about .title {
            font-size: 2.5rem;
         }
         .about .box {
            padding: 2rem;
         }
      }
   </style>
</head>

<body>

<?php include 'header.php'; ?>

<section class="about">

   <h1 class="title">About Our Shop</h1>

   <div class="row">

      <div class="box">
         <img src="image products/picture2.jpg" alt="Why Choose Us">
         <h3>Why Choose Us?</h3>
         <p>Kami sa Joken's Grocery Shop naghatag og de-kalidad nga mga produkto sa barato nga presyo. Sigurado ang katin-aw ug kasaligan sa among serbisyo para sa inyong inadlaw-adlaw nga panginahanglan.</p>
         <a href="contact.php" class="btn">Contact Us</a>
      </div>

      <div class="box">
         <img src="image products/picture3.jpg" alt="What We Provide">
         <h3>What We Provide?</h3>
         <p>Naghatag kami og presko nga mga utan, prutas, ug uban pang grocery items nga gikuha diretso sa mga suppliers aron maseguro nga presko kini pag-abot sa inyong balay.</p>
         <a href="shop.php" class="btn">Our Shop</a>
      </div>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
