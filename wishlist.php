<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit();
};

if(isset($_POST['add_to_cart'])){

   $pid = htmlspecialchars($_POST['pid']);
   $p_name = htmlspecialchars($_POST['p_name']);
   $p_price = htmlspecialchars($_POST['p_price']);
   $p_image = htmlspecialchars($_POST['p_image']);
   $p_qty = htmlspecialchars($_POST['p_qty']);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'Already added to cart!';
   }else{

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Added to cart!';
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM wishlist WHERE id = ?");
   $delete_wishlist_item->execute([$delete_id]);
   header('location:wishlist.php');
}

if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Your Wishlist - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

      :root{
         --primary-color: #2ecc71;
         --red: #e74c3c;
         --black: #2c3e50;
         --white: #fff;
         --light-bg: #f9f9f9;
         --border: .1rem solid rgba(0,0,0,.05);
         --shadow: 0 .5rem 1rem rgba(0,0,0,.05);
      }

      body{
         background-color: var(--light-bg);
         font-family: 'Poppins', sans-serif;
         color: var(--black);
      }

      .title {
         text-align: center;
         margin-bottom: 3rem;
         font-size: 3rem;
         color: var(--black);
         text-transform: capitalize;
         padding-top: 2rem;
      }

      .wishlist {
         padding: 2rem 7%;
      }

      .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr));
         gap: 2rem;
         align-items: flex-start;
      }

      .box {
         background: var(--white);
         padding: 2rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         position: relative;
         text-align: center;
         border: var(--border);
      }

      .box img {
         height: 18rem;
         margin-bottom: 1.5rem;
         object-fit: contain;
      }

      .fa-times, .fa-eye {
         position: absolute;
         top: 1.5rem;
         height: 4.5rem;
         width: 4.5rem;
         line-height: 4.5rem;
         font-size: 2rem;
         background: var(--light-bg);
         color: var(--black);
         border-radius: .5rem;
         transition: .3s;
      }

      .fa-times { left: 1.5rem; color: var(--red); }
      .fa-eye { right: 1.5rem; }

      .fa-times:hover, .fa-eye:hover {
         background: var(--black);
         color: var(--white);
      }

      .box .name {
         font-size: 1.8rem;
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
         padding: 1.2rem;
         border-radius: .5rem;
         border: var(--border);
         margin-bottom: 1rem;
         font-size: 1.6rem;
      }

      .btn {
         display: block;
         width: 100%;
         background: var(--primary-color);
         color: var(--white);
         padding: 1.2rem;
         font-size: 1.6rem;
         border-radius: .5rem;
         cursor: pointer;
         transition: .3s;
         border: none;
      }

      .btn:hover { background: #27ae60; }

      .wishlist-total {
         max-width: 500px;
         margin: 3rem auto;
         background: var(--white);
         padding: 2.5rem;
         text-align: center;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         border: var(--border);
      }

      .wishlist-total p {
         font-size: 2.2rem;
         margin-bottom: 2rem;
      }

      .wishlist-total p span { color: var(--red); font-weight: 600; }

      .option-btn, .delete-btn {
         display: inline-block;
         padding: 1.2rem 3rem;
         font-size: 1.6rem;
         border-radius: .5rem;
         text-decoration: none;
         margin: .5rem;
         transition: .3s;
      }

      .option-btn { background: var(--black); color: var(--white); }
      .delete-btn { background: var(--red); color: var(--white); }

      .delete-btn.disabled {
         opacity: .5;
         user-select: none;
         pointer-events: none;
      }

      .empty {
         text-align: center;
         font-size: 2rem;
         color: var(--red);
         grid-column: 1 / -1;
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="wishlist">

   <h1 class="title">Items You Saved</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ?");
      $select_wishlist->execute([$user_id]);

      if($select_wishlist->rowCount() > 0){
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="POST" class="box">
      <a href="wishlist.php?delete=<?= $fetch_wishlist['id']; ?>" class="fas fa-times" onclick="return confirm('Remove this from wishlist?');"></a>
      <a href="view_page.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
      <img src="image products/<?= $fetch_wishlist['image']; ?>" alt="">
      <div class="name"><?= $fetch_wishlist['name']; ?></div>
      <div class="price">₱<?= $fetch_wishlist['price']; ?></div>
      
      <input type="number" min="1" value="1" class="qty" name="p_qty">
      <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_wishlist['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_wishlist['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_wishlist['image']; ?>">
      
      <input type="submit" value="Add to Cart" name="add_to_cart" class="btn">
   </form>
   <?php
      $grand_total += $fetch_wishlist['price'];
      }
   }else{
      echo '<p class="empty">Your wishlist is currently empty.</p>';
   }
   ?>
   </div>

   <div class="wishlist-total">
      <p>Grand Total : <span>₱<?= $grand_total; ?></span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>" onclick="return confirm('Clear your entire wishlist?');">Remove All</a>
   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
