<?php
session_start();
@include 'config.php';
?>

<?php
// ✅ Proper PDO connection test
try {
    $conn->query("SELECT 1");
    $db_status = "DB CONNECTED";
} catch (Exception $e) {
    $db_status = "NO DB CONNECTION";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Joken's Grocery Shop</title>

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
   color:#2c3e50;
}

body::before{
   content:'';
   position:fixed;
   width:100%;
   height:100%;
   background:rgba(0,0,0,0.4);
   z-index:-1;
}

/* HEADER */
.header{
   background:rgba(255,255,255,0.9);
   padding:15px 40px;
   box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.flex{
   display:flex;
   align-items:center;
   justify-content:space-between;
}

/* LOGO */
.logo{
   font-size:22px;
   font-weight:800;
   text-decoration:none;
   color:#2c3e50;
}

.logo span{
   color:#2ecc71;
}

/* NAV */
.navbar a{
   margin:0 15px;
   text-decoration:none;
   color:#333;
   font-weight:600;
   transition:0.3s;
}

.navbar a:hover{
   color:#2ecc71;
}

/* LOGIN BUTTON */
.login-btn{
   background:#2ecc71;
   color:white;
   padding:10px 20px;
   border-radius:10px;
   text-decoration:none;
   font-weight:bold;
   transition:0.3s;
}

.login-btn:hover{
   background:#27ae60;
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

.btn{
   display:inline-block;
   margin-top:15px;
   padding:12px 25px;
   background:#2ecc71;
   color:white;
   border-radius:12px;
   text-decoration:none;
   font-weight:bold;
}

.btn:hover{
   background:#27ae60;
}

}
</style>
</head>

<body>

<!-- HEADER -->
<header class="header">
   <div class="flex">

      <a href="index.php" class="logo">
         🛒 <span>Joken's</span> Grocery Shop
      </a>

      <!-- LOGIN BUTTON -->
      <a href="login.php" class="login-btn">Login</a>

   </div>
</header>

<!-- HERO -->
<div class="home-bg">
   <section class="home">
      <div class="content">
         <span>don't panic, go organize</span>
         <h3>Reach For A Healthier You With Organic Foods</h3>
         <p>Fresh, organic and delivered with care — upgrade your lifestyle today.</p>
         <a href="login.php" class="btn">Get Started</a>
      </div>
   </section>
</div>

</body>
</html>
