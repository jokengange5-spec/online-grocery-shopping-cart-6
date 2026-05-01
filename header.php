<?php
$user_id = $_SESSION['user_id'] ?? null;

if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<style>
   /* Root Variables para sa consistency */
   :root {
      --green: #27ae60;
      --black: #333;
      --white: #fff;
      --light-color: #666;
      --border: .1rem solid rgba(0,0,0,.1);
      --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
   }

   /* Header Container */
   .header {
      position: sticky;
      top: 0; left: 0; right: 0;
      z-index: 1000;
      background-color: var(--white);
      box-shadow: var(--shadow);
   }

   .header .flex {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.5rem 5%; /* Spacing para sa Laptop */
      max-width: 1200px;
      margin: 0 auto;
      position: relative;
   }

   /* Logo */
   .header .logo {
      font-size: 2.2rem;
      color: var(--black);
      text-decoration: none;
      font-weight: 800;
   }

   .header .logo span {
      color: var(--green);
   }

   /* Navbar - Makita sa Laptop */
   .header .navbar a {
      margin: 0 1rem;
      font-size: 1.2rem;
      color: var(--light-color);
      text-decoration: none;
      text-transform: capitalize;
   }

   .header .navbar a:hover {
      color: var(--green);
   }

   /* Icons Section */
   .header .icons div,
   .header .icons a {
      font-size: 1.6rem;
      margin-left: 1.2rem;
      color: var(--black);
      cursor: pointer;
      text-decoration: none;
      position: relative;
   }

   .header .icons a span {
      position: absolute;
      top: -10px;
      right: -10px;
      background-color: #e74c3c;
      color: var(--white);
      font-size: 0.9rem;
      padding: 2px 6px;
      border-radius: 50%;
   }

   /* Menu Button - Pakit-on lang sa Cellphone */
   #menu-btn {
      display: none;
   }

   /* RESPONSIVE DESIGN (Para sa Cellphone) */
   @media (max-width: 768px) {
      #menu-btn {
         display: inline-block;
      }

      .header .navbar {
         position: absolute;
         top: 100%; left: 0; right: 0;
         background-color: var(--white);
         border-top: var(--border);
         border-bottom: var(--border);
         display: none; /* Itago ang menu sa sugod */
         flex-direction: column;
         padding: 1rem 0;
      }

      .header .navbar.active {
         display: flex; /* Pakit-on kung gi-click ang menu btn */
      }

      .header .navbar a {
         display: block;
         margin: 1.5rem 2rem;
         font-size: 1.5rem;
      }

      .header .flex {
         padding: 1.5rem 2rem; /* Gamayan ang padding sa mobile */
      }

      .header .logo {
         font-size: 1.8rem;
      }
   }
</style>

<header class="header">
   <div class="flex">
      <a href="home.php" class="logo">🛒 <span>Joken's</span> Grocery</a>

      <nav class="navbar">
         <a href="home.php">home</a>
         <a href="shop.php">shop</a>
         <a href="orders.php">orders</a>
         <a href="about.php">about</a>
         <a href="contact.php">contact</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <a href="search_page.php" class="fas fa-search"></a>
         <a href="update_profile.php" class="fas fa-user"></a>

         <?php
            $count_cart_items_num = 0;
            $count_wishlist_items_num = 0;

            if($user_id){
               $count_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
               $count_cart->execute([$user_id]);
               $count_cart_items_num = $count_cart->rowCount();

               $count_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ?");
               $count_wishlist->execute([$user_id]);
               $count_wishlist_items_num = $count_wishlist->rowCount();
            }
         ?>

         <a href="wishlist.php"><i class="fas fa-heart"></i><span><?= $count_wishlist_items_num; ?></span></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span><?= $count_cart_items_num; ?></span></a>
      </div>
   </div>
</header>

<script>
// JavaScript para sa mobile menu toggle
let navbar = document.querySelector('.header .navbar');
let menuBtn = document.querySelector('#menu-btn');

menuBtn.onclick = () =>{
   navbar.classList.toggle('active');
}

// Itago ang navbar inig scroll
window.onscroll = () =>{
   navbar.classList.remove('active');
}
</script>
