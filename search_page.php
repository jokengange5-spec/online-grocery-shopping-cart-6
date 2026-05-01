<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit();
};

// --- LOGIC PARA SA WISHLIST ---
if(isset($_POST['add_to_wishlist'])){
   // GI-FIX: Gitan-tang ang FILTER_SANITIZE_STRING ug gi-puli ang htmlspecialchars o direct assignment
   $pid = htmlspecialchars($_POST['pid']);
   $p_name = htmlspecialchars($_POST['p_name']);
   $p_price = htmlspecialchars($_POST['p_price']);
   $p_image = htmlspecialchars($_POST['p_image']);

   $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check_wishlist->execute([$p_name, $user_id]);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if($check_wishlist->rowCount() > 0){
      $message[] = 'Already added to wishlist!';
   }elseif($check_cart->rowCount() > 0){
      $message[] = 'Already added to cart!';
   }else{
      $insert_wishlist = $conn->prepare("INSERT INTO wishlist (user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Added to wishlist!';
   }
}

// --- LOGIC PARA SA CART ---
if(isset($_POST['add_to_cart'])){
   // GI-FIX: Gitan-tang ang FILTER_SANITIZE_STRING
   $pid = htmlspecialchars($_POST['pid']);
   $p_name = htmlspecialchars($_POST['p_name']);
   $p_price = htmlspecialchars($_POST['p_price']);
   $p_image = htmlspecialchars($_POST['p_image']);
   $p_qty = htmlspecialchars($_POST['p_qty']);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if($check_cart->rowCount() > 0){
      $message[] = 'Already added to cart!';
   }else{
      $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist->execute([$p_name, $user_id]);

      if($check_wishlist->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Added to cart!';
   }
}

?>

<!-- ... (imong HTML ug CSS pabilin gihapon) ... -->

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Page - Joken's Grocery</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <style>
      /* ... (imong style pabilin gihapon) ... */
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="search-form">
   <form action="" method="POST">
      <input type="text" class="box" name="search_box" placeholder="Search products..." value="<?= isset($_POST['search_box']) ? htmlspecialchars($_POST['search_box']) : ''; ?>" required>
      <input type="submit" name="search_btn" value="Search" class="btn">
   </form>
</section>

<section class="products" style="padding-top: 0; min-height:100vh;">

   <div class="box-container">

   <?php
      if(isset($_POST['search_btn']) || isset($_POST['search_box'])){
         // GI-FIX: Gitan-tang ang FILTER_SANITIZE_STRING dinhi sa Line 279
         $search_box = htmlspecialchars($_POST['search_box']);
         
         $select_products = $conn->prepare("
            SELECT * FROM products 
            WHERE name LIKE ? OR category LIKE ?
            ORDER BY (CASE WHEN name LIKE ? THEN 1 ELSE 2 END), name ASC
         ");

         $exact_priority = "{$search_box}%"; 
         $wildcard = "%{$search_box}%";      

         $select_products->execute([$wildcard, $wildcard, $exact_priority]);

         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" class="box" method="POST">
      <div class="price">₱<span><?= $fetch_products['price']; ?></span>/-</div>
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      <img src="image products/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
      
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="Add to Wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
   </form>
   <?php
            }
         }else{
            echo '<p class="empty">No result found for "'.htmlspecialchars($search_box).'"!</p>';
         }
      }else{
         echo '<p class="empty" style="color:var(--light-color);">Please enter a keyword to search.</p>';
      }
   ?>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
