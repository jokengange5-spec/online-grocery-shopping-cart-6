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
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

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
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
   $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

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

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Page - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

      :root{
         --green: #27ae60;
         --black: #333;
         --white: #fff;
         --light-bg: #f6f6f6;
         --border: .1rem solid rgba(0,0,0,.1);
         --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      body{
         background-color: var(--light-bg);
         font-family: 'Poppins', sans-serif;
      }

      /* Search Section Styling */
      .search-form {
         padding: 5rem 5% 2rem 5%;
         display: flex;
         justify-content: center;
      }

      .search-form form {
         display: flex;
         gap: 1rem;
         width: 100%;
         max-width: 800px;
         background-color: var(--white);
         padding: 1.5rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         border: var(--border);
      }

      .search-form form .box {
         width: 100%;
         padding: 1.2rem 1.4rem;
         font-size: 1.2rem;
         color: var(--black);
         background-color: var(--light-bg);
         border-radius: .8rem;
         border: var(--border);
      }

      .search-form form .btn {
         background-color: var(--green);
         color: var(--white);
         padding: 1rem 3rem;
         border-radius: .8rem;
         font-size: 1.2rem;
         cursor: pointer;
         transition: .3s;
         border: none;
      }

      .search-form form .btn:hover {
         background-color: var(--black);
      }

      /* Products Grid Styling */
      .products {
         padding: 2rem 5%;
      }

      .products .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(28rem, 1fr));
         gap: 2rem;
         justify-content: center;
         align-items: stretch;
      }

      .products .box {
         background-color: var(--white);
         padding: 2rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         border: var(--border);
         position: relative;
         text-align: center;
         transition: .3s ease;
         display: flex;
         flex-direction: column;
      }

      .products .box:hover {
         transform: translateY(-5px);
         border-color: var(--green);
      }

      .products .box img {
         width: 100%;
         height: 20rem;
         object-fit: contain;
         margin-bottom: 2rem;
      }

      .products .box .price {
         position: absolute;
         top: 1.5rem; left: 1.5rem;
         background-color: var(--green);
         color: var(--white);
         padding: .5rem 1.5rem;
         border-radius: .5rem;
         font-size: 1.3rem;
      }

      .products .box .fa-eye {
         position: absolute;
         top: 1.5rem; right: 1.5rem;
         height: 4rem; width: 4rem;
         line-height: 4rem;
         border: var(--border);
         border-radius: .5rem;
         color: var(--black);
         font-size: 1.5rem;
         background-color: var(--white);
      }

      .products .box .fa-eye:hover {
         background-color: var(--black);
         color: var(--white);
      }

      .products .box .name {
         font-size: 2rem;
         color: var(--black);
         margin: 1rem 0;
      }

      .products .box .qty {
         width: 100%;
         padding: 1.2rem;
         border-radius: .5rem;
         border: var(--border);
         font-size: 1.2rem;
         margin-bottom: 1rem;
         background-color: var(--light-bg);
      }

      .option-btn {
         background-color: #f39c12;
         color: var(--white);
         padding: 1rem;
         border-radius: .5rem;
         width: 100%;
         font-size: 1.1rem;
         margin-bottom: .8rem;
         cursor: pointer;
         border: none;
      }

      .btn {
         background-color: var(--green);
         color: var(--white);
         padding: 1rem;
         border-radius: .5rem;
         width: 100%;
         font-size: 1.1rem;
         cursor: pointer;
         border: none;
      }

      .empty {
         text-align: center;
         font-size: 2rem;
         color: var(--black);
         width: 100%;
         padding: 2rem;
      }

      /* Mobile Adjustment */
      @media (max-width: 768px) {
         .products .box-container {
            grid-template-columns: 1fr;
         }
         .search-form form {
            flex-direction: column;
         }
      }
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
         $search_box = filter_var($_POST['search_box'], FILTER_SANITIZE_STRING);
         
         $select_products = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ?");
         $select_products->execute(["%{$search_box}%", "%{$search_box}%"]);

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
