<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

// LOGIC PARA SA ADD TO CART GIKAN SA RECOMMENDATIONS
if(isset($_POST['add_to_cart'])){
   $pid = htmlspecialchars($_POST['pid']);
   $p_name = htmlspecialchars($_POST['p_name']);
   $p_price = htmlspecialchars($_POST['p_price']);
   $p_image = htmlspecialchars($_POST['p_image']);
   $p_qty = htmlspecialchars($_POST['p_qty']);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'Already added to cart';
   }else{
      $insert_cart = $conn->prepare("INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Added to cart';
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_cart_item = $conn->prepare("DELETE FROM cart WHERE id = ?");
   $delete_cart_item->execute([$delete_id]);
   header('location:cart.php');
}

if(isset($_GET['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $p_qty = (int)$_POST['p_qty']; 
   
   $update_qty = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
   $update_qty->execute([$p_qty, $cart_id]);
   $message[] = 'Cart quantity updated';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

      :root{
         --primary: #2ecc71;
         --secondary: #27ae60;
         --red: #e74c3c;
         --black: #2c3e50;
         --white: #fff;
         --light-bg: #f8f9fa;
         --shadow: 0 .5rem 1rem rgba(0,0,0,.08);
         --border: .1rem solid rgba(0,0,0,.1);
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

      .shopping-cart { padding: 3rem 7%; }

      .title {
         font-size: 2.5rem;
         text-align: center;
         margin-bottom: .5rem;
         color: var(--white);
         text-transform: uppercase;
      }

      .subtitle {
         text-align: center;
         font-size: 1.4rem;
         color: #ddd;
         margin-bottom: 3rem;
      }

      /* COMPACT GRID SYSTEM */
      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
         gap: 1.5rem;
         align-items: flex-start;
      }

      /* COMPACT PRODUCT BOX */
      .box {
         background: var(--white);
         padding: 1.5rem;
         border-radius: 1rem;
         box-shadow: var(--shadow);
         position: relative;
         border: var(--border);
         text-align: center;
      }

      /* SMALLER IMAGE */
      .box img {
         height: 13rem;
         object-fit: contain;
         margin-bottom: 1rem;
      }

      .fa-times {
         position: absolute;
         top: 1rem;
         right: 1.2rem;
         font-size: 1.8rem;
         color: var(--red);
         cursor: pointer;
         transition: .3s;
      }

      .fa-times:hover { transform: rotate(90deg); }

      .box .name {
         font-size: 1.7rem;
         color: var(--black);
         margin: 0.5rem 0;
         font-weight: 600;
      }

      .box .price {
         font-size: 1.6rem;
         color: var(--secondary);
         margin-bottom: 1rem;
      }

      .flex-btn {
         display: flex;
         gap: 0.8rem;
         margin: 1rem 0;
      }

      .qty {
         width: 7rem;
         padding: 0.8rem;
         border: var(--border);
         border-radius: .5rem;
         font-size: 1.4rem;
      }

      .sub-total {
         margin-top: 1rem;
         padding-top: 1rem;
         border-top: var(--border);
         font-size: 1.5rem;
         color: #666;
      }

      .sub-total span { color: var(--red); font-weight: 600; }

      /* TOTAL SECTION */
      .cart-total {
         margin-top: 2.5rem;
         background: var(--white);
         padding: 2rem;
         border-radius: 1rem;
         text-align: center;
         box-shadow: var(--shadow);
         border: var(--border);
      }

      .grand-total {
         font-size: 2.2rem;
         color: var(--black);
         margin-bottom: 1.5rem;
      }

      .grand-total span { color: var(--red); font-weight: 600; }

      .btn, .option-btn, .delete-btn {
         display: inline-block;
         padding: 1rem 2.5rem;
         border-radius: .5rem;
         font-size: 1.5rem;
         text-decoration: none;
         cursor: pointer;
         transition: .3s;
         border: none;
         margin: .3rem;
         font-weight: 500;
      }

      .btn { background: var(--primary); color: var(--white); }
      .btn:hover { background: var(--secondary); transform: scale(1.02); }

      .option-btn { background: var(--black); color: var(--white); }
      .delete-btn { background: var(--red); color: var(--white); }

      .disabled {
         opacity: .5;
         user-select: none;
         pointer-events: none;
      }

      .empty {
         font-size: 1.8rem;
         color: var(--white);
         text-align: center;
         grid-column: 1 / -1;
         padding: 3rem 0;
      }

      .rec-title {
         margin-top: 4rem;
         font-size: 2.2rem;
         color: var(--white);
         text-align: center;
         display: block;
         padding-bottom: 0.5rem;
         width: 100%;
      }

      @media (max-width: 450px) {
         .shopping-cart { padding: 2rem 2%; }
         .box-container { grid-template-columns: 1fr; }
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="shopping-cart">
   <h1 class="title">🛒 Your Cart</h1>
   <p class="subtitle">Complete your purchase for your groceries</p>

   <div class="box-container">
   <?php
      $grand_total = 0;
      $all_cart_items = []; 
      $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      if($select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){ 
            $all_cart_items[] = $fetch_cart['name'];
   ?>
   <form action="" method="POST" class="box">
      <a href="cart.php?delete=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('Remove this from cart?');"></a>
      <img src="<?= $fetch_cart['image']; ?>" alt="">
      <div class="name"><?= $fetch_cart['name']; ?></div>
      <div class="price">₱<?= number_format($fetch_cart['price'], 2); ?></div>
      <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
      <div class="flex-btn">
         <input type="number" min="1" value="<?= $fetch_cart['quantity']; ?>" class="qty" name="p_qty">
         <input type="submit" value="Update" name="update_qty" class="option-btn" style="flex:1;">
      </div>
      <div class="sub-total"> Sub Total : <span>₱<?= number_format($sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']), 2); ?></span> </div>
   </form>
   <?php
      $grand_total += $sub_total;
      }
   }else{
      echo '<p class="empty">Your cart is feeling light! Add some items.</p>';
   }
   ?>
   </div>

   <div class="cart-total">
      <p class="grand-total">Grand Total : <span>₱<?= number_format($grand_total, 2); ?></span></p>
      <div class="flex-btn" style="justify-content: center; flex-wrap: wrap;">
         <a href="shop.php" class="option-btn">Continue Shopping</a>
         <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>" onclick="return confirm('Clear your entire cart?');">Remove Products</a>
         <a href="checkout.php" class="btn <?= ($grand_total > 0)?'':'disabled'; ?>">Checkout Now</a>
      </div>
   </div>
</section>

<!-- RECIPE RECOMMENDATIONS SECTION -->
<section class="wishlist" style="padding-top: 0; padding-bottom: 5rem;">

   <h2 class="rec-title">🍽️ Suggested Recipes</h2>
   <p style="text-align:center; font-size:1.3rem; color:#fff; margin-bottom:2rem; opacity: 0.8;">
      Personalized suggestions based on your items
   </p>

   <div class="box-container">
   <?php
   $suggestions = [];
   if(!empty($all_cart_items)){
      foreach($all_cart_items as $item){
         $name = strtolower($item);

         // Fruits
         if(strpos($name, 'apple') !== false) $suggestions[] = "🍎 Apple → Salad / Juice";
         if(strpos($name, 'avocado') !== false) $suggestions[] = "🥑 Avocado → Shake / Smoothie";
         if(strpos($name, 'grapes') !== false) $suggestions[] = "🍇 Grapes → Fruit Bowl";
         if(strpos($name, 'watermelon') !== false) $suggestions[] = "🍉 Watermelon → Fresh Juice";

         // Vegetables
         if(strpos($name, 'kalabasa') !== false) $suggestions[] = "🎃 Kalabasa → Ginataan / Ginisa";
         if(strpos($name, 'onion') !== false) $suggestions[] = "🧅 Onion → Sauté / Base Soup";
         if(strpos($name, 'talong') !== false || strpos($name, 'eggplant') !== false) $suggestions[] = "🍆 Talong → Tortang Talong";
         if(strpos($name, 'broccoli') !== false) $suggestions[] = "🥦 Broccoli → Stir Fry / Garlic";
         if(strpos($name, 'cabbage') !== false) $suggestions[] = "🥬 Cabbage → Chop Suey / Pansit";
         if(strpos($name, 'carrot') !== false) $suggestions[] = "🥕 Carrots → Ginisa / Nilaga";

         // Fish & Meat
         if(strpos($name, 'tuna') !== false) $suggestions[] = "🐟 Tuna → Adobo / Grilled";
         if(strpos($name, 'tilapia') !== false) $suggestions[] = "🐟 Tilapia → Fried / Sinigang";
         if(strpos($name, 'beef') !== false) $suggestions[] = "🥩 Beef → Nilaga / Steak";
         if(strpos($name, 'pork') !== false) $suggestions[] = "🥩 Pork → Adobo / Sinigang";
      }
   }

   if(!empty($suggestions)){
      foreach(array_unique($suggestions) as $s){
         echo '<div class="box" style="padding:1rem;"><p style="font-size:1.4rem; color:var(--black);">'.$s.'</p></div>';
      }
   } else if(!empty($all_cart_items)) {
      echo '<p class="empty" style="color:#fff;">No specific recipes for these items yet.</p>';
   }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
