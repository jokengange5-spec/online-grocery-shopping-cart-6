<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

/* ADD TO WISHLIST */
if(isset($_POST['add_to_wishlist'])){
   // Gi-replace ang FILTER_SANITIZE_STRING og htmlspecialchars
   $pid = htmlspecialchars($_POST['pid'], ENT_QUOTES, 'UTF-8');
   $p_name = htmlspecialchars($_POST['p_name'], ENT_QUOTES, 'UTF-8');
   $p_price = htmlspecialchars($_POST['p_price'], ENT_QUOTES, 'UTF-8');
   $p_image = htmlspecialchars($_POST['p_image'], ENT_QUOTES, 'UTF-8');

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   }elseif($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   }else{
      // Siguroha nga ang database columns match ani (user_id, pid, name, price, image)
      $insert_wishlist = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'added to wishlist!';
   }
}

/* UPDATE SA ADD TO CART (Line 53) */
if(isset($_POST['add_to_cart'])){

   $pid = htmlspecialchars($_POST['pid'], ENT_QUOTES, 'UTF-8');
   $p_name = htmlspecialchars($_POST['p_name'], ENT_QUOTES, 'UTF-8');
   $p_price = htmlspecialchars($_POST['p_price'], ENT_QUOTES, 'UTF-8');
   $p_image = htmlspecialchars($_POST['p_image'], ENT_QUOTES, 'UTF-8');
   $p_qty = htmlspecialchars($_POST['p_qty'], ENT_QUOTES, 'UTF-8');

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

/* ... code sa taas ... */

   if($check_cart->rowCount() > 0){
      $message[] = 'Already added to cart!';
   } else {
      // Dinhi dapit ang Line 57-59
      $insert_cart = $conn->prepare("INSERT INTO cart(id, user_id, pid, name, price, quantity, image) VALUES(DEFAULT, ?, ?, ?, ?, ?, ?)");
      
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);

      $message[] = 'Added to cart!';
   } // Kani nga bracket ang nagsira sa 'else'
} // Kani nga bracket ang nagsira sa 'if(isset($_POST["add_to_cart"]))'
?>
?>
   
<?php include 'header.php'; ?>
<!-- CATEGORY SECTION (TOP) -->
<section class="p-category">
  

   <a href="category.php?category=fruits">Fruits</a>
   <a href="category.php?category=vegetables">Vegetables</a>
   <a href="category.php?category=fish">Fish</a>
   <a href="category.php?category=meat">Meat</a>

</section>

<!-- PRODUCTS SECTION (BELOW) -->
<section class="products">
   <h1 class="title">🛍️ Latest Products</h1>

   <div class="product-container">

   <?php
      // SAMPLE PRODUCTS (you can replace later with database)
      
?>




      
   </div>

</section>


<section class="products">

   

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM products");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" class="box" method="POST">
      <div class="price">₱<span><?= $fetch_products['price']; ?></span>/-</div>
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
     
   }
   ?>

   </div>

</section>








<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
