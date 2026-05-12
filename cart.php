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
      $message[] = ['type' => 'info', 'text' => 'Already added to cart'];
   }else{
      $insert_cart = $conn->prepare("INSERT INTO cart (user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = ['type' => 'success', 'text' => 'Added to cart'];
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_cart_item = $conn->prepare("DELETE FROM cart WHERE id = ?");
   $delete_cart_item->execute([$delete_id]);
   header('location:cart.php');
   exit();
}

if(isset($_GET['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
   exit();
}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $p_qty = (int)$_POST['p_qty']; 
   
   $update_qty = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
   $update_qty->execute([$p_qty, $cart_id]);
   $message[] = ['type' => 'success', 'text' => 'Cart quantity updated'];
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
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
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

      .box-container {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
          gap: 1.5rem;
          align-items: flex-start;
      }

      .box {
          background: var(--white);
          padding: 1.5rem;
          border-radius: 1rem;
          box-shadow: var(--shadow);
          position: relative;
          border: var(--border);
          text-align: center;
      }

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

      /* Custom Recipe Box Style */
      .recipe-box {
         background: var(--white);
         padding: 1.5rem;
         border-radius: 1rem;
         border-left: 5px solid var(--primary);
         box-shadow: var(--shadow);
         text-align: left;
         display: flex;
         align-items: center;
         gap: 1.5rem;
      }

      .recipe-box p {
         font-size: 1.4rem;
         color: var(--black);
         margin: 0;
         line-height: 1.4;
      }

      .swal2-popup {
          border-radius: 15px !important;
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
   <div class="box">
      <a href="javascript:void(0);" class="fas fa-times delete-item" data-id="<?= $fetch_cart['id']; ?>"></a>
      
      <img src="<?= $fetch_cart['image']; ?>" alt="">
      <div class="name"><?= $fetch_cart['name']; ?></div>
      <div class="price">₱<?= number_format($fetch_cart['price'], 2); ?></div>
      
      <form action="" method="POST">
         <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
         <div class="flex-btn">
            <input type="number" min="1" value="<?= $fetch_cart['quantity']; ?>" class="qty" name="p_qty">
            <input type="submit" value="Update" name="update_qty" class="option-btn" style="flex:1;">
         </div>
      </form>
      
      <div class="sub-total"> Sub Total : <span>₱<?= number_format($sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']), 2); ?></span> </div>
   </div>
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
         <a href="javascript:void(0);" class="delete-btn delete-all <?= ($grand_total > 0)?'':'disabled'; ?>">Remove Products</a>
         <a href="checkout.php" class="btn <?= ($grand_total > 0)?'':'disabled'; ?>">Checkout Now</a>
      </div>
   </div>
</section>

<section class="suggestions" style="padding: 0 7% 5rem 7%;">
   <h2 class="rec-title">🍽️ Suggested Recipes</h2>
   <p style="text-align:center; font-size:1.3rem; color:#fff; margin-bottom:2rem; opacity: 0.8;">
      Based on the ingredients in your cart
   </p>

   <div class="box-container">
   <?php
   $suggestions = [];
   if(!empty($all_cart_items)){
      foreach($all_cart_items as $item){
         $name = strtolower($item);

         // Fruits
         if(strpos($name, 'apple') !== false) $suggestions[] = "🍎 Apple → Healthy Fruit Salad or Fresh Apple Juice";
         if(strpos($name, 'avocado') !== false) $suggestions[] = "🥑 Avocado → Creamy Avocado Shake or Guacamole";
         if(strpos($name, 'grapes') !== false) $suggestions[] = "🍇 Grapes → Refreshing Fruit Bowl";
         if(strpos($name, 'watermelon') !== false) $suggestions[] = "🍉 Watermelon → Fresh Watermelon Cooler";
         if(strpos($name, 'banana') !== false) $suggestions[] = "🍌 Banana → Banana Cue or Smoothies";
         
         // Veggies
         if(strpos($name, 'kalabasa') !== false || strpos($name, 'squash') !== false) $suggestions[] = "🎃 Kalabasa → Ginataang Kalabasa at Sitaw";
         if(strpos($name, 'onion') !== false) $suggestions[] = "🧅 Onion → Perfect for Sautéing almost any dish!";
         if(strpos($name, 'talong') !== false || strpos($name, 'eggplant') !== false) $suggestions[] = "🍆 Talong → Tortang Talong or Ensaladang Talong";
         if(strpos($name, 'broccoli') !== false) $suggestions[] = "🥦 Broccoli → Beef with Broccoli or Garlic Stir-fry";
         if(strpos($name, 'cabbage') !== false) $suggestions[] = "🥬 Cabbage → Nilagang Baka or Pancit Guisado";
         if(strpos($name, 'carrot') !== false) $suggestions[] = "🥕 Carrots → Chop Suey or Chicken Curry ingredient";
         if(strpos($name, 'garlic') !== false) $suggestions[] = "🧄 Garlic → Essential for Garlic Fried Rice!";
         
         // Meat & Fish
         if(strpos($name, 'tuna') !== false) $suggestions[] = "🐟 Tuna → Tuna Mayo Sandwich or Sautéed Tuna with Onions";
         if(strpos($name, 'tilapia') !== false) $suggestions[] = "🐟 Tilapia → Crispy Fried Tilapia or Sinigang na Tilapia";
         if(strpos($name, 'beef') !== false) $suggestions[] = "🥩 Beef → Beef Steak (Bistek Tagalog) or Nilagang Baka";
         if(strpos($name, 'pork') !== false) $suggestions[] = "🥩 Pork → Pork Adobo or Sinigang na Baboy";
         if(strpos($name, 'chicken') !== false) $suggestions[] = "🍗 Chicken → Chicken Adobo or Tinolang Manok";
      }
   }

   $unique_suggestions = array_unique($suggestions);

   if(!empty($unique_suggestions)){
      foreach($unique_suggestions as $s){
         echo '
         <div class="recipe-box">
            <p>'.$s.'</p>
         </div>';
      }
   } else if(!empty($all_cart_items)) {
      echo '<p class="empty" style="color:#fff;">No specific recipes found for these items, but they look delicious!</p>';
   }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script>
// 1. Handle Individual Item Delete
document.querySelectorAll('.delete-item').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        
        Swal.fire({
            title: 'Remove Item?',
            text: "Do you want to remove this product from your cart?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#2c3e50',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'cart.php?delete=' + id;
            }
        });
    });
});

// 2. Handle Delete All (Clear Cart)
const deleteAllBtn = document.querySelector('.delete-all');
if(deleteAllBtn) {
    deleteAllBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Clear Entire Cart?',
            text: "This will remove all items you have added!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#2c3e50',
            confirmButtonText: 'Yes, clear all!',
            cancelButtonText: 'No, keep them'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'cart.php?delete_all';
            }
        });
    });
}

// 3. Show Toast Messages
<?php if(isset($message)): ?>
    <?php foreach($message as $msg): ?>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: '<?= $msg['type']; ?>',
            title: '<?= $msg['text']; ?>',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    <?php endforeach; ?>
<?php endif; ?>
</script>

</body>
</html>
