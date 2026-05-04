<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

/* ADD TO WISHLIST LOGIC */
if(isset($_POST['add_to_wishlist'])){
   $pid = htmlspecialchars(trim($_POST['pid']));
   // Gi-truncate sa 255 characters para dili mo error sa PostgreSQL
   $p_name = substr(htmlspecialchars(trim($_POST['p_name'])), 0, 255);
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = substr(htmlspecialchars(trim($_POST['p_image'])), 0, 255);

   $check = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check->execute([$p_name, $user_id]);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if($check->rowCount() > 0){
      $message[] = 'Already added to wishlist!';
   }elseif($check_cart->rowCount() > 0){
      $message[] = 'Already added to cart!';
   }else{
      // DEFAULT gigamit para sa compatibility sa PostgreSQL serial IDs
      $insert = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?, ?, ?, ?, ?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Added to wishlist!';
   }
}

/* ADD TO CART LOGIC */
if(isset($_POST['add_to_cart'])){
   $pid = htmlspecialchars(trim($_POST['pid']));
   $p_name = substr(htmlspecialchars(trim($_POST['p_name'])), 0, 255);
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = substr(htmlspecialchars(trim($_POST['p_image'])), 0, 255);
   $p_qty = htmlspecialchars(trim($_POST['p_qty']));

   $check = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check->execute([$p_name, $user_id]);

   if($check->rowCount() > 0){
      $message[] = 'Already added to cart!';
   }else{
      $delete_wish = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
      $delete_wish->execute([$p_name, $user_id]);
      
      $insert = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?, ?, ?, ?, ?, ?)");
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
         background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture7.jpg') no-repeat;
         background-size: cover;
         background-position: center;
         background-attachment: fixed;
         font-family: 'Poppins', sans-serif;
         margin: 0;
         padding: 0;
      }

      .p-category {
         display: flex;
         justify-content: center;
         gap: 1.5rem;
         padding: 2rem;
         flex-wrap: wrap;
         background: var(--white);
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
      }

      .p-category a:hover {
         background: var(--green);
         color: var(--white);
      }

      .products { padding: 2rem 5%; }
      .products .title { text-align: center; margin-bottom: 2rem; font-size: 2.5rem; color: var(--white); text-shadow: 0 2px 5px rgba(0,0,0,0.5); }

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
      }

      .box img {
         height: 13rem;
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

      .box .name { font-size: 1.5rem; color: var(--black); margin: 1rem 0; }
      .box .qty { width: 100%; padding: 1rem; border: var(--border); border-radius: .5rem; margin-bottom: 1rem; }

      .btn, .option-btn {
         width: 100%; display: block; padding: 1rem; border-radius: .5rem;
         cursor: pointer; font-size: 1.1rem; border: none; margin-top: .5rem; transition: .3s;
      }

      .btn { background: var(--green); color: var(--white); }
      .btn:hover { background: var(--black); }
      .option-btn { background: #f39c12; color: var(--white); }
      .option-btn:hover { background: var(--black); }

      @media (max-width: 450px) {
         .box-container { grid-template-columns: 1fr; }
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
      <div class="price">₱<span><?= $fetch_products['price']; ?></span></div>
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      
      <img src="<?= $fetch_products['image']; ?>" alt="">
      
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
         echo '<p class="empty" style="color:white; text-align:center;">No products added yet!</p>';
      }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
