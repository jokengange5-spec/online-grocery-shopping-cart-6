<?php
// Siguraduhon nga dili mag-error kung wala pay session ang user_id
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

<header class="header">

   <div class="flex">

      <!-- LOGO -->
      <a href="home.php" class="logo">
         🛒 <span class="brand">Joken's</span> Grocery <span>Shop</span>
      </a>

      <!-- NAVBAR -->
      <nav class="navbar">
         <a href="home.php">home</a>
         <a href="shop.php">shop</a>
         <a href="orders.php">orders</a>
         <a href="about.php">about</a>
         <a href="contact.php">contact</a>
      </nav>

      <!-- ICONS -->
      <div class="icons">
         
         <a href="search_page.php" class="fas fa-search"></a>
         
         <!-- PROFILE ICON -->
         <a href="update_profile.php" class="fas fa-user"></a>

         <?php
            // Mag-ihap lang kung naay naka-login nga user
            $count_cart_items_num = 0;
            $count_wishlist_items_num = 0;

            if($user_id){
               $count_cart_items = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
               $count_cart_items->execute([$user_id]);
               $count_cart_items_num = $count_cart_items->rowCount();

               $count_wishlist_items = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ?");
               $count_wishlist_items->execute([$user_id]);
               $count_wishlist_items_num = $count_wishlist_items->rowCount();
            }
         ?>

         <!-- WISHLIST -->
         <a href="wishlist.php">
            <i class="fas fa-heart"></i>
            <span>(<?= $count_wishlist_items_num; ?>)</span>
         </a>

         <!-- CART -->
         <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            <span>(<?= $count_cart_items_num; ?>)</span>
         </a>

      </div>

   </div>

</header>
