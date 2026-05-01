<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

/* ADD TO WISHLIST & CART LOGIC */
if(isset($_POST['add_to_wishlist'])){
   $pid = htmlspecialchars(trim($_POST['pid']));
   $p_name = htmlspecialchars(trim($_POST['p_name']));
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = htmlspecialchars(trim($_POST['p_image']));

   $check = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check->execute([$p_name, $user_id]);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if($check->rowCount() > 0){
      $message[] = 'Already added to wishlist!';
   }elseif($check_cart->rowCount() > 0){
      $message[] = 'Already added to cart!';
   }else{
      $insert = $conn->prepare("INSERT INTO wishlist(id, user_id, pid, name, price, image) VALUES(DEFAULT, ?, ?, ?, ?, ?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Added to wishlist!';
   }
}

if(isset($_POST['add_to_cart'])){
   $pid = htmlspecialchars(trim($_POST['pid']));
   $p_name = htmlspecialchars(trim($_POST['p_name']));
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = htmlspecialchars(trim($_POST['p_image']));
   $p_qty = htmlspecialchars(trim($_POST['p_qty']));

   $check = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check->execute([$p_name, $user_id]);

   if($check->rowCount() > 0){
      $message[] = 'Already added to cart!';
   }else{
      $delete_wish = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
      $delete_wish->execute([$p_name, $user_id]);
      $insert = $conn->prepare("INSERT INTO cart(id, user_id, pid, name, price, quantity, image) VALUES(DEFAULT, ?, ?, ?, ?, ?, ?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Added to cart!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Modern Grocery Shop</title>
   
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

      /* Category Section */
      .p-category {
         display: flex;
         justify-content: center;
         gap: 1.5rem;
         padding: 2.5rem 2rem;
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
         padding: 3rem 2rem;
         max-width: 1200px; /* Nilimitahan ang gilapdon para dili lapad kaayo */
         margin: 0 auto;    /* Gi-center ang tibuok products area */
      }

      .products .title {
         text-align: center;
         margin-bottom: 3rem;
         font-size: 3rem;
         color: var(--black);
         text-transform: uppercase;
         letter-spacing: 1px;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, 28rem); /* Fixed width cards para uniform */
         gap: 2rem;
         justify-content: center; /* I-center ang mga cards sa screen */
      }

      .box {
         background: var(--white);
         padding: 2rem;
         border-radius: 1rem;
         border: var(--border);
         box-shadow: var(--shadow);
         position: relative;
         text-align: center;
         overflow: hidden;
         transition: .3s ease;
      }

      .box:hover {
         transform: translateY(-5px);
         box-shadow: 0 1rem 1.5rem rgba(0,0,0,.15);
      }

      .box img {
         height: 20rem;
         width: 100%;
         object-fit: contain;
         margin-bottom: 1.5rem;
         padding: 1rem;
      }

      .box .price {
         position: absolute;
         top: 1.5rem; left: 1.5rem;
         background: var(--green);
         color: var(--white);
         padding: .6rem 1.2rem;
         border-radius: .5rem;
         font-size: 1.4rem;
         font-weight: 600;
      }

      .box .fa-eye {
         position: absolute;
         top: 1.5rem; right: 1.5rem;
         height: 4rem; width: 4rem;
         line-height: 4rem;
         border: var(--border);
         border-radius: .5rem;
         color: var(--black);
         text-decoration: none;
         background: var(--white);
         font-size: 1.8rem;
         transition: .3s;
      }

      .box .fa-eye:hover {
         background: var(--black);
         color: var(--white);
      }

      .box .name {
         font-size: 1.8rem;
         color: var(--black);
         margin: 1.5rem 0;
         font-weight: 500;
      }

      .box .qty {
         width: 100%;
         padding: 1.2rem;
         border: var(--border);
         border-radius: .5rem;
         margin-bottom: 1rem;
         font-size: 1.6rem;
         background: var(--light-bg);
      }

      /* Buttons */
      .btn, .option-btn {
         width: 100%;
         display: block;
         padding: 1.2rem;
         border-radius: .5rem;
         cursor: pointer;
         font-size: 1.3rem;
         border: none;
         margin-top: .8rem;
         transition: .3s;
         font-weight: 500;
         text-transform: uppercase;
      }

      .btn { background: var(--green); color: var(--white); }
      .btn:hover { background: var(--black); }

      .option-btn { background: #f39c12; color: var(--white); }
      .option-btn:hover { background: var(--black); }

      .empty {
         text-align: center;
         padding: 5rem;
         font-size: 2rem;
         color: #666;
         grid-column: 1 / -1;
      }

      /* Mobile Adjustments */
      @media (max-width: 450px) {
         .box-container {
            grid-template-columns: 1fr; /* Isa ka column ra sa mobile */
         }
         .products .title { font-size: 2.2rem; }
         .box img { height: 18rem; }
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
   <h1 class="title">🛍️ Latest Products</h1>

   <div class="box-container">
   <?php
      $select_products = $conn->prepare("SELECT * FROM products");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" class="box" method="POST">
      <div class="price">₱<span><?= number_format($fetch_products['price'], 2); ?></span></div>
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      
      <img src="image products/<?= $fetch_products['image']; ?>" alt="<?= $fetch_products['name']; ?>">
      
      <div class="name"><?= $fetch_products['name']; ?></div>
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      
      <input type="submit" value="Wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">No products added yet!</p>';
      }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
