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

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Search Page - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

      :root{
         --primary-color: #2ecc71;
         --secondary-color: #27ae60;
         --accent-color: #f39c12;
         --black: #2c3e50;
         --white: #fff;
         --light-bg: #f9f9f9;
         --border: .1rem solid rgba(0,0,0,.05);
         --shadow: 0 .5rem 1rem rgba(0,0,0,.05);
      }

      body {
   background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture7.jpg') no-repeat;
   background-size: cover;
   background-position: center;
   background-attachment: fixed; /* Para dili mo-scroll ang background */
   font-family: 'Poppins', sans-serif;
   margin: 0;
   padding: 0;
}

      /* Simple Search Form */
      .search-form {
         padding: 4rem 2rem 2rem;
         max-width: 700px;
         margin: 0 auto;
      }

      .search-form form {
         display: flex;
         background: var(--white);
         padding: .5rem;
         border-radius: 5rem;
         box-shadow: var(--shadow);
         border: var(--border);
      }

      .search-form form .box {
         flex: 1;
         padding: 1.2rem 2rem;
         font-size: 1.6rem;
         border: none;
         outline: none;
         background: none;
      }

      .search-form form .btn {
         background: var(--primary-color);
         color: var(--white);
         padding: 1.2rem 2.5rem;
         border-radius: 5rem;
         font-size: 1.6rem;
         cursor: pointer;
         transition: .3s linear;
      }

      .search-form form .btn:hover {
         background: var(--secondary-color);
      }

      /* Clean Product Grid */
      .products {
         padding: 2rem 7%;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
         gap: 2rem;
      }

      .box {
         background: var(--white);
         padding: 2rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         position: relative;
         text-align: center;
         transition: .2s;
         border: var(--border);
      }

      .box:hover {
         transform: scale(1.02);
         box-shadow: 0 1rem 2rem rgba(0,0,0,.1);
      }

      .box img {
         height: 18rem;
         margin-bottom: 1.5rem;
         object-fit: contain;
      }

      .box .name {
         font-size: 1.8rem;
         font-weight: 500;
         color: var(--black);
         margin: 1rem 0;
      }

      .box .price {
         font-size: 2rem;
         color: var(--primary-color);
         font-weight: 600;
         margin-bottom: 1.5rem;
      }

      .box .qty {
         width: 100%;
         padding: 1rem;
         border-radius: .5rem;
         background: var(--light-bg);
         margin-bottom: 1rem;
         font-size: 1.5rem;
         border: var(--border);
      }

      .box .flex-btn {
         display: flex;
         gap: 1rem;
         margin-top: 1rem;
      }

      .btn-add, .btn-wish {
         flex: 1;
         padding: 1rem;
         font-size: 1.4rem;
         border-radius: .5rem;
         cursor: pointer;
         border: none;
         transition: .3s;
      }

      .btn-add { background: var(--primary-color); color: var(--white); }
      .btn-add:hover { background: var(--secondary-color); }
      
      .btn-wish { background: #eee; color: var(--black); }
      .btn-wish:hover { background: #ddd; }

      .fa-eye {
         position: absolute;
         top: 1.5rem; right: 1.5rem;
         font-size: 2rem;
         color: #aaa;
         transition: .3s;
      }

      .fa-eye:hover { color: var(--primary-color); }

      .empty {
         text-align: center;
         font-size: 1.8rem;
         color: #888;
         grid-column: 1 / -1;
         padding: 5rem 0;
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="search-form">
   <form action="" method="POST">
      <input type="text" class="box" name="search_box" placeholder="Unsa imong gipangita?" value="<?= isset($_POST['search_box']) ? htmlspecialchars($_POST['search_box']) : ''; ?>" required>
      <button type="submit" name="search_btn" class="btn"><i class="fas fa-search"></i></button>
   </form>
</section>

<section class="products">
   <div class="box-container">
   <?php
      if(isset($_POST['search_btn']) || isset($_POST['search_box'])){
         $search_box = htmlspecialchars($_POST['search_box']);
         
         $select_products = $conn->prepare("
            SELECT * FROM products 
            WHERE name ILIKE ? OR category ILIKE ?
            ORDER BY (CASE WHEN name ILIKE ? THEN 1 ELSE 2 END), name ASC
         ");

         $exact_priority = "{$search_box}%"; 
         $wildcard = "%{$search_box}%";      

         $select_products->execute([$wildcard, $wildcard, $exact_priority]);

         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" class="box" method="POST">
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      <img src="image products/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="price">₱<?= $fetch_products['price']; ?></div>
      
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
      
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <div class="flex-btn">
         <button type="submit" name="add_to_wishlist" class="btn-wish" title="Add to Wishlist"><i class="fas fa-heart"></i></button>
         <button type="submit" name="add_to_cart" class="btn-add">Add to Cart</button>
      </div>
   </form>
   <?php
            }
         }else{
            echo '<p class="empty">Pasayloa, walay nakit-an nga "'.htmlspecialchars($search_box).'".</p>';
         }
      } else {
         echo '<p class="empty">Sulayi pag-search ang ngalan sa produkto o category.</p>';
      }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
