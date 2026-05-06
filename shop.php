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
   $p_name = htmlspecialchars(trim($_POST['p_name']));
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = $_POST['p_image']; 

   $check = $conn->prepare("SELECT * FROM wishlist WHERE pid = ? AND user_id = ?");
   $check->execute([$pid, $user_id]);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE pid = ? AND user_id = ?");
   $check_cart->execute([$pid, $user_id]);

   if($check->rowCount() > 0){
      $message[] = ['text' => 'Already added to wishlist!', 'type' => 'error'];
   }elseif($check_cart->rowCount() > 0){
      $message[] = ['text' => 'Already added to cart!', 'type' => 'error'];
   }else{
      $insert = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?, ?, ?, ?, ?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = ['text' => 'Added to wishlist successfully!', 'type' => 'success'];
   }
}

/* ADD TO CART LOGIC */
if(isset($_POST['add_to_cart'])){
   $pid = htmlspecialchars(trim($_POST['pid']));
   $p_name = substr(htmlspecialchars(trim($_POST['p_name'])), 0, 255);
   $p_price = htmlspecialchars(trim($_POST['p_price']));
   $p_image = $_POST['p_image'];
   $p_qty = htmlspecialchars(trim($_POST['p_qty']));

   $check = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check->execute([$p_name, $user_id]);

   if($check->rowCount() > 0){
      $message[] = ['text' => 'Already added to cart!', 'type' => 'error'];
   }else{
      $delete_wish = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
      $delete_wish->execute([$p_name, $user_id]);
      
      $insert = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?, ?, ?, ?, ?, ?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = ['text' => 'Added to cart successfully!', 'type' => 'success'];
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
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

      :root{
          --green: #27ae60;
          --black: #333;
          --white: #fff;
          --light-bg: #f6f6f6;
          --border: 1px solid #ddd;
          --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
          --red: #e74c3c;
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

      /* SUCCESS & ERROR MESSAGES */
      .message-container {
         position: fixed;
         top: 2rem;
         left: 50%;
         transform: translateX(-50%);
         z-index: 10000;
         width: 90%;
         max-width: 450px;
      }

      .message {
         padding: 1.2rem 2rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1.5rem;
         border-radius: .8rem;
         margin-bottom: 1rem;
         box-shadow: 0 10px 25px rgba(0,0,0,0.2);
         animation: slideDown 0.4s ease forwards;
      }

      .message.success { background: #d4edda; color: #155724; border-left: 6px solid #28a745; }
      .message.error { background: #f8d7da; color: #721c24; border-left: 6px solid #dc3545; }
      /* Default if class is empty */
      .message { background: #fff; color: #333; border-left: 6px solid #999; }

      .message span { font-size: 1.5rem; font-weight: 500; display: flex; align-items: center; gap: 10px; }
      .message i.fa-times { font-size: 2rem; cursor: pointer; }

      @keyframes slideDown {
         0% { transform: translateY(-100%); opacity: 0; }
         100% { transform: translateY(0); opacity: 1; }
      }

      /* PRODUCT STYLES */
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

      .p-category a:hover { background: var(--green); color: var(--white); }

      .products { padding: 2rem 5%; }
      .products .title { text-align: center; margin-bottom: 2rem; font-size: 2.5rem; color: var(--white); text-shadow: 0 2px 5px rgba(0,0,0,0.5); }

      .box-container {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 1.5rem;
      }

      .box {
          background: var(--white);
          padding: 1.5rem;
          border-radius: 1rem;
          border: var(--border);
          box-shadow: var(--shadow);
          position: relative;
          text-align: center;
      }

      .box img { height: 13rem; width: 100%; object-fit: contain; margin-bottom: 1rem; }

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
          background: var(--white);
          text-decoration: none;
      }

      .box .name { font-size: 1.5rem; color: var(--black); margin: 1rem 0; }
      .box .qty { width: 100%; padding: 1rem; border: var(--border); border-radius: .5rem; margin-bottom: 1rem; }
      .stock{ margin-top: .5rem; font-size: 1.1rem; color: #333; font-weight: 500; }

      .btn, .option-btn {
          width: 100%; display: block; padding: 1rem; border-radius: .5rem;
          cursor: pointer; font-size: 1.1rem; border: none; margin-top: .5rem; transition: .3s;
      }

      .btn { background: var(--green); color: var(--white); }
      .btn:hover { background: var(--black); }
      .option-btn { background: #f39c12; color: var(--white); }
      .option-btn:hover { background: var(--black); }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<!-- ✅ IMPROVED MESSAGE DISPLAY LOGIC -->
<?php
if(isset($message)){
   echo '<div class="message-container">';
   foreach($message as $msg){
      if(is_array($msg)){
         // Bag-ong format (Array)
         $text = $msg['text'];
         $typeClass = $msg['type']; // 'success' o 'error'
         $icon = ($typeClass == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle';
      } else {
         // Karaan nga format (String)
         $text = $msg;
         $typeClass = ''; 
         $icon = 'fa-info-circle';
      }
      
      echo '
      <div class="message '.$typeClass.'">
         <span><i class="fas '.$icon.'"></i> '.$text.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
   echo '</div>';
}
?>

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
      <div class="stock">Stock: <?= $fetch_products['stock']; ?> pcs</div>
      
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
         echo '<p class="empty" style="color:white; text-align:center; font-size:2rem; width:100%;">No products added yet!</p>';
      }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
