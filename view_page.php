<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if(!isset($user_id)){
   header('location:login.php');
   exit();
};

/* LOGIC SA WISHLIST UG CART (Pabilin gihapon) */
if(isset($_POST['add_to_wishlist'])){
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_SPECIAL_CHARS);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_SPECIAL_CHARS);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_SPECIAL_CHARS);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_SPECIAL_CHARS);

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
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_SPECIAL_CHARS);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_SPECIAL_CHARS);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_SPECIAL_CHARS);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_SPECIAL_CHARS);
   $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_SPECIAL_CHARS);

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
         --border: .1rem solid rgba(0,0,0,.1);
         --box-shadow: 0 .5rem 1rem rgba(0,0,0,.05);
      }

      /* GIKUNHORAN NGA CONTAINER SIZE */
      .quick-view .box {
         max-width: 700px; /* Mas compact gikan sa 850px */
         margin: 2rem auto;
         background-color: var(--white);
         border-radius: .8rem;
         box-shadow: var(--box-shadow);
         border: var(--border);
         display: flex;
         flex-wrap: wrap;
         gap: 2rem;
         padding: 1.5rem;
         align-items: center;
      }

      /* GIKUNHORAN NGA IMAGE */
      .quick-view .box img {
         flex: 1 1 25rem; /* Mas gamay nga base width */
         height: 25rem;   /* Mas mubo gikan sa 30rem */
         object-fit: contain;
         border-radius: .5rem;
         background-color: #fcfcfc;
      }

      .quick-view .box .content {
         flex: 1 1 30rem;
      }

      .quick-view .box .content .price {
         font-size: 2rem;
         color: var(--green);
         font-weight: 700;
      }

      .quick-view .box .content .name {
         font-size: 2rem;
         color: var(--black);
         margin: .2rem 0;
         text-transform: capitalize;
      }

      .quick-view .box .content .details {
         font-size: 1.4rem;
         color: var(--light-color);
         line-height: 1.5;
         padding: .8rem 0;
      }

      .quick-view .box .content .qty {
         width: 7rem;
         padding: .6rem;
         border: var(--border);
         border-radius: .4rem;
         font-size: 1.5rem;
         margin-bottom: 1rem;
      }

      /* BUTTONS STYLING */
      .quick-view .box .content .flex-btn {
         display: flex;
         gap: 1rem;
      }

      .quick-view .box .content .flex-btn input {
         padding: 1rem;
         font-size: 1.4rem;
         border-radius: .5rem;
         cursor: pointer;
         transition: .3s;
         border: none;
         font-weight: 600;
      }

      .quick-view .box .content .flex-btn .btn {
         background: var(--green);
         color: var(--white);
         flex: 2;
      }

      .quick-view .box .content .flex-btn .btn:hover {
         background: #219150;
         transform: translateY(-2px);
      }

      .quick-view .box .content .flex-btn .option-btn {
         background: #eee;
         color: var(--black);
         flex: 1;
      }

      .quick-view .box .content .flex-btn .option-btn:hover {
         background: var(--orange);
         color: var(--white);
      }

      @media (max-width: 768px) {
         .quick-view .box {
            flex-direction: column;
            max-width: 95%;
         }
         .quick-view .box img {
            height: 20rem;
         }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="quick-view">
   <h1 class="title">Quick View</h1>

   <?php
      $pid = $_GET['pid'];
      $select_products = $conn->prepare("SELECT * FROM products WHERE id = ?");
      $select_products->execute([$pid]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
            $current_item_name = $fetch_products['name']; 
            $image_src = $fetch_products['image'];
            if (strpos($image_src, 'data:image') === false) {
               $image_src = 'image products/' . $image_src;
            }
   ?>
   <form action="" class="box" method="POST">
      <img src="<?= $image_src; ?>" alt="">
      
      <div class="content">
         <div class="price">₱<?= number_format($fetch_products['price'], 2); ?></div>
         <div class="name"><?= $fetch_products['name']; ?></div>
         <div class="details"><?= $fetch_products['details']; ?></div>
         
         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
         <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
         <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
         <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
         
         <div style="font-size: 1.3rem; color: var(--light-color); margin-bottom: .3rem;">Quantity:</div>
         <input type="number" min="1" value="1" name="p_qty" class="qty">
         
         <div class="flex-btn">
            <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
            <input type="submit" value="Wishlist" class="option-btn" name="add_to_wishlist">
         </div>
      </div>
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">Product not found!</p>';
      }
   ?>
</section>

<!-- RECOMMENDATIONS SECTION (Pabilin gihapon) -->
<section class="products" style="padding-top: 0;">
   <?php
      if(isset($current_item_name)){
         $recommendations = getRecommendations($current_item_name, $conn);
         if(!empty($recommendations)){
            echo '<h1 class="title">You may also like</h1>';
            echo '<div class="box-container">';
            foreach($recommendations as $rec_name){
               $select_rec = $conn->prepare("SELECT * FROM products WHERE name = ?");
               $select_rec->execute([$rec_name]);
               while($fetch_rec = $select_rec->fetch(PDO::FETCH_ASSOC)){
                  $rec_image = $fetch_rec['image'];
                  if (strpos($rec_image, 'data:image') === false) {
                     $rec_image = 'image products/' . $rec_image;
                  }
   ?>
               <form action="" method="POST" class="box">
                  <a href="view_page.php?pid=<?= $fetch_rec['id']; ?>" class="fas fa-eye"></a>
                  <div class="price">₱<?= number_format($fetch_rec['price'], 2); ?></div>
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
