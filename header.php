<?php

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
           <a href="orders.php">order</a>
         <a href="about.php">about</a>
         <a href="contact.php">contact</a>
      </nav>

      <!-- ICONS -->
      <div class="icons">

         

         <!-- PROFILE ICON (CLICKABLE) -->
         <a href="update_profile.php" class="fas fa-user"></a>

         <a href="search_page.php" class="fas fa-search"></a>

         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);

            $count_wishlist_items = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ?");
            $count_wishlist_items->execute([$user_id]);
         ?>

         <!-- WISHLIST -->
         <a href="wishlist.php">
            <i class="fas fa-heart"></i>
            <span>(<?= $count_wishlist_items->rowCount(); ?>)</span>
         </a>

         <!-- CART -->
         <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            <span>(<?= $count_cart_items->rowCount(); ?>)</span>
         </a>

      </div>

   </div>

</header>

















