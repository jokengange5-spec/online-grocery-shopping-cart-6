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

// ... (imong delete ug update logic nagpabilin nga pareho) ...

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <!-- ... (imong styles ug head) ... -->
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="shopping-cart">
   <h1 class="title">🛒 Your Cart</h1>
   <div class="box-container">
   <?php
      $grand_total = 0;
      $all_cart_items = []; 
      $select_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      if($select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){ 
            $all_cart_items[] = $fetch_cart['name'];
            // (display cart box here...)
            $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $sub_total;
   ?>
   <form action="" method="POST" class="box">
      <!-- ... (imong cart item display) ... -->
      <div class="name"><?= $fetch_cart['name']; ?></div>
      <div class="sub-total"> Sub Total : <span>₱<?= $sub_total; ?></span> </div>
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">Your cart is empty!</p>';
      }
   ?>
   </div>
   <!-- ... (grand total section) ... -->
</section>

<!-- RECOMMENDATIONS SECTION -->
<section class="wishlist" style="padding-top: 0;">
   <h2 class="rec-title">Best Pairings for Your Meal</h2>
   <p style="text-align:center; font-size:1.4rem; color:var(--secondary); margin-bottom:2rem;">Suggested vegetables & healthy desserts</p>
   
   <div class="box-container">
   <?php
      $is_protein_present = false;
      
      // 1. Check for meat or fish keywords
      if(!empty($all_cart_items)){
         foreach($all_cart_items as $item_name){
            $name_lower = strtolower($item_name);
            // Gi-expand nako ang keywords para mas daghan madakpan
            if(preg_match('/(meat|pork|beef|chicken|manok|baboy|baka|fish|isda|tilapia|bangus|salmon|shrimp|hipon|tuna|crab)/', $name_lower)){
               $is_protein_present = true;
               break; 
            }
         }
      }

      // 2. Build the query parameters to exclude items already in cart
      $params = [];
      $not_in_sql = "";
      if(!empty($all_cart_items)){
         $placeholders = str_repeat('?,', count($all_cart_items) - 1) . '?';
         $not_in_sql = " AND name NOT IN ($placeholders)";
         $params = $all_cart_items;
      }

      // 3. Select products based on the user's cart content
      if($is_protein_present){
         // Kung naay karne/isda, i-recommend ang lamas, utanon, ug prutas
         $query = "SELECT * FROM products WHERE 
                  (name LIKE '%onion%' OR name LIKE '%garlic%' OR name LIKE '%veg%' OR 
                   name LIKE '%cabbage%' OR name LIKE '%tomato%' OR name LIKE '%apple%' OR 
                   name LIKE '%banana%' OR name LIKE '%orange%' OR name LIKE '%mango%' OR 
                   name LIKE '%fruit%' OR name LIKE '%ginger%' OR name LIKE '%pepper%') 
                   $not_in_sql 
                   ORDER BY RAND() LIMIT 6"; // Gigamit nako ang RAND() para sa MySQL
      } else {
         // Default if no protein: suggest random items excluding what's in cart
         $query = "SELECT * FROM products WHERE 1=1 $not_in_sql ORDER BY RAND() LIMIT 6";
      }

      $select_rec = $conn->prepare($query);
      $select_rec->execute($params);

      if($select_rec->rowCount() > 0){
         while($fetch_rec = $select_rec->fetch(PDO::FETCH_ASSOC)){
            $rec_name = strtolower($fetch_rec['name']);
            
            // 4. Dynamic Labels para mas nindot tan-awon
            $label = "Healthy Choice";
            if(preg_match('/(apple|banana|orange|mango|fruit|pineapple|grapes|watermelon)/', $rec_name)){
               $label = "Perfect Dessert (Panghimagas)";
            } else if(preg_match('/(onion|garlic|veg|cabbage|tomato|ginger|pepper|carrot|potato)/', $rec_name)){
               $label = "Best with your Meat/Fish";
            }
   ?>
            <form action="" method="POST" class="box">
               <img src="image products/<?= $fetch_rec['image']; ?>" alt="">
               <div class="name"><?= $fetch_rec['name']; ?></div>
               <small style="color:var(--secondary); font-weight:600; display:block; margin-bottom:1rem;"><?= $label; ?></small>
               <div class="price">₱<?= $fetch_rec['price']; ?></div>
               
               <input type="hidden" name="pid" value="<?= $fetch_rec['id']; ?>">
               <input type="hidden" name="p_name" value="<?= $fetch_rec['name']; ?>">
               <input type="hidden" name="p_price" value="<?= $fetch_rec['price']; ?>">
               <input type="hidden" name="p_image" value="<?= $fetch_rec['image']; ?>">
               
               <input type="number" min="1" value="1" name="p_qty" class="qty" style="width:100%; margin-bottom:1rem;">
               <input type="submit" value="Add to Cart" class="btn" name="add_to_cart" style="width:100%;">
            </form>
   <?php
         } 
      } else {
         echo '<p class="empty" style="grid-column: 1/-1;">Check back later for more suggestions!</p>';
      }
   ?>
   </div>
</section>

<!-- ... (footer ug scripts) ... -->
</body>
</html>
