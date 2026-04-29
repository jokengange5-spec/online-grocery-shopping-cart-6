<?php
@include 'config.php';
session_start();

// I-uncomment ni para makita nimo kung unsay sulod sa session
// var_dump($_SESSION); 

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   // Imbes nga i-redirect dayon, i-echo usa ang error para mahibal-an nimo
   // die('Error: Walay nakit-an nga User ID sa session!'); 
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


</body>
</html>
