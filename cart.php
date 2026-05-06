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
   background-attachment: fixed; /* Para dili mo-scroll ang background */
   font-family: 'Poppins', sans-serif;
   margin: 0;
   padding: 0;
}

      .shopping-cart { padding: 3rem 7%; }

      .title {
         font-size: 3rem;
         text-align: center;
         margin-bottom: 1rem;
         color: var(--black);
         text-transform: uppercase;
      }

      .subtitle {
         text-align: center;
         font-size: 1.6rem;
         color: #666;
         margin-bottom: 3rem;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
         gap: 2rem;
         align-items: flex-start;
      }

      .box {
         background: var(--white);
         padding: 2rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         position: relative;
         border: var(--border);
         text-align: center;
      }

      .box img {
         height: 18rem;
         object-fit: contain;
         margin-bottom: 1.5rem;
      }

      .fa-times {
         position: absolute;
         top: 1.5rem;
         right: 1.5rem;
         font-size: 2rem;
         color: var(--red);
         cursor: pointer;
         transition: .3s;
      }

      .fa-times:hover { transform: rotate(90deg); }

      .box .name {
         font-size: 2rem;
         color: var(--black);
         margin: 1rem 0;
         font-weight: 500;
      }

      .box .price {
         font-size: 1.8rem;
         color: var(--secondary);
         margin-bottom: 1.5rem;
      }

      .flex-btn {
         display: flex;
         gap: 1rem;
         margin: 1.5rem 0;
      }

      .qty {
         width: 8rem;
         padding: 1.2rem;
         border: var(--border);
         border-radius: .5rem;
         font-size: 1.6rem;
      }

      .sub-total {
         margin-top: 1.5rem;
         padding-top: 1.5rem;
         border-top: var(--border);
         font-size: 1.8rem;
         color: #666;
      }

      .sub-total span { color: var(--red); font-weight: 600; }

      .cart-total {
         margin-top: 3rem;
         background: var(--white);
         padding: 3rem;
         border-radius: 1.5rem;
         text-align: center;
         box-shadow: var(--shadow);
         border: var(--border);
      }

      .grand-total {
         font-size: 2.5rem;
         color: var(--black);
         margin-bottom: 2rem;
      }

      .grand-total span { color: var(--red); font-weight: 600; }

      .btn, .option-btn, .delete-btn {
         display: inline-block;
         padding: 1.2rem 3rem;
         border-radius: .5rem;
         font-size: 1.7rem;
         text-decoration: none;
         cursor: pointer;
         transition: .3s;
         border: none;
         margin: .5rem;
      }

      .btn { background: var(--primary); color: var(--white); }
      .btn:hover { background: var(--secondary); }

      .option-btn { background: var(--black); color: var(--white); }
      .delete-btn { background: var(--red); color: var(--white); }

      .disabled {
         opacity: .5;
         user-select: none;
         pointer-events: none;
      }

      .empty {
         font-size: 2rem;
         color: var(--red);
         text-align: center;
         grid-column: 1 / -1;
         padding: 5rem 0;
      }

      .rec-title {
         margin-top: 5rem;
         font-size: 2.5rem;
         color: var(--black);
         text-align: center;
         border-bottom: .2rem solid var(--primary);
         display: inline-block;
         padding-bottom: 1rem;
         width: 100%;
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
      <div class="price">₱<?= $fetch_cart['price']; ?></div>
      <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
      <div class="flex-btn">
         <input type="number" min="1" value="<?= $fetch_cart['quantity']; ?>" class="qty" name="p_qty">
         <input type="submit" value="Update" name="update_qty" class="option-btn" style="flex:1;">
      </div>
      <div class="sub-total"> Sub Total : <span>₱<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?></span> </div>
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
      <p class="grand-total">Grand Total : <span>₱<?= $grand_total; ?></span></p>
      <div class="flex-btn" style="justify-content: center;">
         <a href="shop.php" class="option-btn">Continue Shopping</a>
         <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>" onclick="return confirm('Clear your entire cart?');">Remove Products</a>
         <a href="checkout.php" class="btn <?= ($grand_total > 0)?'':'disabled'; ?>">Checkout Now</a>
      </div>
   </div>
</section>

<!-- RECIPE RECOMMENDATIONS SECTION -->
<section class="wishlist" style="padding-top: 0;">

   <h2 class="rec-title">🍽️ Suggested Recipes for Your Items</h2>
   <p style="text-align:center; font-size:1.4rem; color:var(--secondary); margin-bottom:2rem;">
      Personalized recipe per product in your cart
   </p>

   <?php
   $suggestions = [];

   if(!empty($all_cart_items)){
      foreach($all_cart_items as $item){
         $name = strtolower($item);

         // 🥭 FRUITS
         if(strpos($name, 'apple') !== false){
            $suggestions[] = "🍎 Apple → Fruit Salad / Juice / Dessert Mix";
         }

         if(strpos($name, 'avocado') !== false){
            $suggestions[] = "🥑 Avocado → Milkshake / Dessert / Smoothie";
         }

         if(strpos($name, 'grapes') !== false){
            $suggestions[] = "🍇 Grapes → Fruit Salad / Juice";
         }

         if(strpos($name, 'watermelon') !== false || strpos($name, 'water melon') !== false){
            $suggestions[] = "🍉 Watermelon → Fresh Juice / Fruit Bowl";
         }

         // 🥬 VEGETABLES
         if(strpos($name, 'kalabasa') !== false){
            $suggestions[] = "🎃 Kalabasa → Tinola / Ginisa / Ginataang Kalabasa";
         }

         if(strpos($name, 'onion') !== false){
            $suggestions[] = "🧅 Onion → Base sa Ginisa / Adobo / Soup";
         }

         if(strpos($name, 'talong') !== false || strpos($name, 'eggplant') !== false){
            $suggestions[] = "🍆 Talong → Tortang Talong / Ginisa";
         }

         if(strpos($name, 'broccoli') !== false){
            $suggestions[] = "🥦 Broccoli → Stir Fry / Ginisa with Garlic";
         }

         if(strpos($name, 'cabbage') !== false){
            $suggestions[] = "🥬 Cabbage → Chop Suey / Ginisa";
         }

         if(strpos($name, 'cucumber') !== false){
            $suggestions[] = "🥒 Cucumber → Salad / Refreshing side dish";
         }

         if(strpos($name, 'mushroom') !== false){
            $suggestions[] = "🍄 Mushrooms → Stir Fry / Soup";
         }

         if(strpos($name, 'carrot') !== false){
            $suggestions[] = "🥕 Carrots → Ginisa / Soup / Chop Suey";
         }

         if(strpos($name, 'petchay') !== false || strpos($name, 'pechay') !== false){
            $suggestions[] = "🥬 Petchay → Tinola / Nilaga";
         }

         // 🐟 FISH
         if(strpos($name, 'tuna') !== false){
            $suggestions[] = "🐟 Tuna → Adobo / Grill / Prito";
         }

         if(strpos($name, 'tilapia') !== false){
            $suggestions[] = "🐟 Tilapia → Fried Tilapia / Adobo / Sinigang";
         }

         if(strpos($name, 'katambak') !== false){
            $suggestions[] = "🐟 Katambak → Prito / Paksiw style";
         }

         if(strpos($name, 'kitong') !== false){
            $suggestions[] = "🐟 Kitong → Grilled / Fried Fish";
         }

         if(strpos($name, 'kabalyas') !== false){
            $suggestions[] = "🐟 Kabalyas → Fried / Adobo style";
         }

         // 🍖 MEAT
         if(strpos($name, 'beef') !== false){
            $suggestions[] = "🥩 Beef → Nilaga / Adobo / Steak";
         }

         if(strpos($name, 'pork') !== false){
            $suggestions[] = "🥩 Pork → Adobo / Sinigang / Fried Pork";
         }
      }
   }
   ?>

   <div class="box-container">

      <?php if(!empty($suggestions)){ ?>
         <?php foreach(array_unique($suggestions) as $s){ ?>
            <div class="box">
               <p style="font-size:1.6rem; color:var(--black);">
                  <?= $s; ?>
               </p>
            </div>
         <?php } ?>
      <?php } else { ?>
         <p class="empty">No recipe suggestion yet.</p>
      <?php } ?>

   </div>

</section>

   
<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
