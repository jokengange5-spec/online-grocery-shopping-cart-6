<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit();
};

if(isset($_POST['add_to_wishlist'])){

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_SPECIAL_CHARS);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_SPECIAL_CHARS);
   $p_price = $_POST['p_price']; // GI-FIX: gikan sa pprice ngadto sa p_price
   $p_price = filter_var($p_price, FILTER_SANITIZE_SPECIAL_CHARS);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_SPECIAL_CHARS);

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

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_SPECIAL_CHARS);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_SPECIAL_CHARS);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_SPECIAL_CHARS);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_SPECIAL_CHARS);
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_SPECIAL_CHARS);

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
   <title>Quick View</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
      :root {
         --green: #27ae60;
         --orange: #f39c12;
         --black: #333;
         --white: #fff;
         --light-color: #666;
         --light-bg: #f6f6f6;
         --border: .1rem solid rgba(0,0,0,.1);
         --box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      .quick-view .box {
         display: flex;
         align-items: center;
         gap: 2rem;
         padding: 2rem;
         background-color: var(--white);
         border-radius: .5rem;
         box-shadow: var(--box-shadow);
         border: var(--border);
         flex-wrap: wrap;
         max-width: 500px;
         margin: 0 auto;
      }

      .quick-view .box img {
         flex: 1 1 30rem;
         height: 30rem;
         object-fit: contain;
         border-radius: .5rem;
      }

      .quick-view .box .content {
         flex: 1 1 20rem;
      }

      .quick-view .box .content .price {
         font-size: 2.5rem;
         color: var(--green);
         font-weight: bold;
      }

      .quick-view .box .content .name {
         font-size: 2.5rem;
         color: var(--black);
         padding: 1rem 0;
         text-transform: capitalize;
      }

      .quick-view .box .content .details {
         font-size: 1.6rem;
         color: var(--light-color);
         line-height: 2;
         padding: 1rem 0;
      }

      .quick-view .box .content .qty {
         width: 100%;
         padding: 1.2rem 1.4rem;
         border-radius: .5rem;
         border: var(--border);
         margin: 1rem 0;
         font-size: 1.8rem;
      }

      .quick-view .box .content .flex-btn {
         display: flex;
         gap: 1rem;
      }

      .quick-view .box .content .flex-btn input {
         flex: 1;
      }

      @media (max-width: 768px) {
         .quick-view .box {
            flex-direction: column;
         }
         .quick-view .box img {
            height: 30rem;
            width: 100%;
         }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="quick-view">

   <h1 class="title">Product Details</h1>

   <?php
      $pid = $_GET['pid'];
      $select_products = $conn->prepare("SELECT * FROM products WHERE id = ?");
      $select_products->execute([$pid]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
            $current_item_name = $fetch_products['name']; 
            
            // LOGIC PARA SA PICTURE (FOLDER O BASE64)
            $image_src = $fetch_products['image'];
            if (strpos($image_src, 'data:image') === false) {
               $image_src = 'image products/' . $image_src;
            }
   ?>
   <form action="" class="box" method="POST">
      <img src="<?= $image_src; ?>" alt="">
      
      <div class="content">
         <div class="price">₱<span><?= $fetch_products['price']; ?></span></div>
         <div class="name"><?= $fetch_products['name']; ?></div>
         <div class="details"><?= $fetch_products['details']; ?></div>
         
         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
         <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
         <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
         <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
         
         <input type="number" min="1" value="1" name="p_qty" class="qty">
         
         <div class="flex-btn">
            <input type="submit" value="wishlist" class="option-btn" name="add_to_wishlist">
            <input type="submit" value="add to cart" class="btn" name="add_to_cart">
         </div>
      </div>
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>

</section>

<section class="products" style="padding-top: 0;">

   <?php
      if(isset($current_item_name)){
         $recommendations = getRecommendations($current_item_name, $conn);

         if(!empty($recommendations)){
            echo '<h1 class="title">You may also like:</h1>';
            echo '<div class="box-container">';
            
            foreach($recommendations as $rec_name){
               $select_rec = $conn->prepare("SELECT * FROM products WHERE name = ?");
               $select_rec->execute([$rec_name]);
               
               while($fetch_rec = $select_rec->fetch(PDO::FETCH_ASSOC)){
                  // LOGIC PARA SA PICTURE RECOMMENDATIONS
                  $rec_image = $fetch_rec['image'];
                  if (strpos($rec_image, 'data:image') === false) {
                     $rec_image = 'image products/' . $rec_image;
                  }
   ?>
               <form action="" method="POST" class="box">
                  <a href="view_page.php?pid=<?= $fetch_rec['id']; ?>" class="fas fa-eye"></a>
                  <div class="price">₱<span><?= $fetch_rec['price']; ?></span></div>
                  <img src="<?= $rec_image; ?>" alt="">
                  <div class="name"><?= $fetch_rec['name']; ?></div>
                  <input type="hidden" name="pid" value="<?= $fetch_rec['id']; ?>">
                  <input type="hidden" name="p_name" value="<?= $fetch_rec['name']; ?>">
                  <input type="hidden" name="p_price" value="<?= $fetch_rec['price']; ?>">
                  <input type="hidden" name="p_image" value="<?= $fetch_rec['image']; ?>">
                  <input type="number" min="1" value="1" name="p_qty" class="qty">
                  <input type="submit" value="wishlist" class="option-btn" name="add_to_wishlist">
                  <input type="submit" value="add to cart" class="btn" name="add_to_cart">
               </form>
   <?php
               }
            }
            echo '</div>';
         }
      }
   ?>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
