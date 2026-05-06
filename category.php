<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
};

$category = $_GET['category'] ?? '';

/* ADD TO WISHLIST LOGIC */
if(isset($_POST['add_to_wishlist'])){
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
      // Naggamit og DEFAULT para sa PostgreSQL auto-increment
      $insert_wishlist = $conn->prepare("INSERT INTO wishlist(id, user_id, pid, name, price, image) VALUES(DEFAULT, ?, ?, ?, ?, ?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Added to wishlist!';
   }
}

/* ADD TO CART LOGIC */
if(isset($_POST['add_to_cart'])){
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

      $insert_cart = $conn->prepare("INSERT INTO cart (id, user_id, pid, name, price, quantity, image) VALUES(DEFAULT, ?, ?, ?, ?, ?, ?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Added to cart!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Category - <?= ucfirst($category); ?></title>

   <!-- Google Fonts & Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      :root{
         --green: #27ae60;
         --black: #333;
         --white: #fff;
         --light-bg: #f6f6f6;
         --border: 1px solid #ddd;
         --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      body {
         background-color: var(--light-bg);
         font-family: 'Poppins', sans-serif;
         margin: 0;
         padding: 0;
      }

      /* Category Navigation Section */
      .p-category {
         display: flex;
         justify-content: center;
         gap: 1.5rem;
         padding: 2rem;
         flex-wrap: wrap;
         background: var(--white);
         border-bottom: var(--border);
      }

      .p-category a {
         padding: 1rem 2rem;
         background: var(--white);
         border: var(--border);
         color: var(--black);
         text-decoration: none;
         border-radius: .5rem;
         font-size: 1.1rem;
         transition: .3s;
         display: flex;
         align-items: center;
         gap: .5rem;
      }

      .p-category a:hover {
         background: var(--green);
         color: var(--white);
         transform: translateY(-3px);
      }

      /* Products Section */
      .products {
         padding: 2rem 5%;
      }

      .products .title {
         text-align: center;
         margin-bottom: 2rem;
         font-size: 2.5rem;
         color: var(--black);
         text-transform: capitalize;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
         gap: 1.5rem;
         justify-content: center;
      }

      .box {
         background: var(--white);
         padding: 1.5rem;
         border-radius: 1rem;
         border: var(--border);
         box-shadow: var(--shadow);
         position: relative;
         text-align: center;
         overflow: hidden;
         transition: .3s;
      }

      .box:hover {
         transform: scale(1.02);
      }

      .box img {
         height: 18rem;
         width: 100%;
         object-fit: contain;
         margin-bottom: 1rem;
      }

      .box .price {
         position: absolute;
         top: 1rem; left: 1rem;
         background: var(--green);
         color: var(--white);
         padding: .5rem 1rem;
         border-radius: .5rem;
         font-size: 1.2rem;
      }

      .box .fa-eye {
         position: absolute;
         top: 1rem; right: 1rem;
         height: 3.5rem; width: 3.5rem;
         line-height: 3.5rem;
         border: var(--border);
         border-radius: .5rem;
         color: var(--black);
         text-decoration: none;
         background: var(--white);
      }

      .box .fa-eye:hover {
         background: var(--black);
         color: var(--white);
      }

      .box .name {
         font-size: 1.5rem;
         color: var(--black);
         margin: 1rem 0;
      }

      .box .qty {
         width: 100%;
         padding: 1rem;
         border: var(--border);
         border-radius: .5rem;
         margin-bottom: 1rem;
      }

      /* Buttons */
      .btn, .option-btn {
         width: 100%;
         display: block;
         padding: 1rem;
         border-radius: .5rem;
         cursor: pointer;
         font-size: 1.1rem;
         border: none;
         margin-top: .5rem;
         transition: .3s;
         font-weight: 500;
      }

      .btn { background: var(--green); color: var(--white); }
      .btn:hover { background: var(--black); }

      .option-btn { background: #f39c12; color: var(--white); }
      .option-btn:hover { background: var(--black); }

      .empty {
         text-align: center;
         font-size: 1.5rem;
         color: #666;
         grid-column: 1 / -1;
         padding: 3rem;
      }

      @media (max-width: 450px) {
         .products .title { font-size: 2rem; }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="p-category">
   <a href="category.php?category=fruits">🍎 Fruits</a>
   <a href="category.php?category=vegetables">🥦 Vegetables</a>
   <a href="category.php?category=fish">🐟 Fish</a>
   <a href="category.php?category=meat">🥩 Meat</a>
</section>

<section class="products">

   <h1 class="title">Showing: <?= $category; ?></h1>

   <div class="box-container">

   <?php
      if(!empty($category)){
         // Gigamit nako ang ILIKE para sa case-insensitive search sa PostgreSQL
         $select_products = $conn->prepare("SELECT * FROM products WHERE category ILIKE ?");
         $select_products->execute([$category]);

         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   
   <form action="" method="POST" class="box">
      <div class="price">₱<span><?= $fetch_products['price']; ?></span></div>
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      
      <!-- Path para sa imong mga hulagway -->
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      
      <div class="name"><?= $fetch_products['name']; ?></div>

      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">

      <input type="number" name="p_qty" value="1" min="1" class="qty">
      
      <input type="submit" value="Wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
   </form>

   <?php
            }
         }else{
            echo '<p class="empty">No products found in this category!</p>';
         }
      } else {
         echo '<p class="empty">Please select a category above.</p>';
      }
   ?>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
