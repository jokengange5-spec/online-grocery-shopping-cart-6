<?php
ob_start(); 
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!$admin_id){
   header('location:login.php');
   exit();
}

$message = [];

if(isset($_POST['add_product'])){
   $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
   $price = htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8');
   $category = htmlspecialchars($_POST['category'] ?? '', ENT_QUOTES, 'UTF-8');
   $details = htmlspecialchars($_POST['details'] ?? '', ENT_QUOTES, 'UTF-8');
   $stock = htmlspecialchars($_POST['stock'] ?? '', ENT_QUOTES, 'UTF-8');
   
   $image_name = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_type = $_FILES['image']['type'];

   $image_base64 = base64_encode(file_get_contents($image_tmp_name));
   $image_data = 'data:' . $image_type . ';base64,' . $image_base64;

   $select_products = $conn->prepare("SELECT * FROM products WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = ['text' => 'Product name already exists!', 'type' => 'error'];
   }elseif($image_size > 2000000){
      $message[] = ['text' => 'Image size is too large!', 'type' => 'error'];
   }else{
      $insert_products = $conn->prepare("INSERT INTO products(name, category, details, price, stock, image) VALUES(?,?,?,?,?,?)");
      $status = $insert_products->execute([$name, $category, $details, $price, $stock, $image_data]);
      
      if($status){
         $message[] = ['text' => 'New product added!', 'type' => 'success'];
      }else{
         $message[] = ['text' => 'Failed to add product!', 'type' => 'error'];
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_products = $conn->prepare("DELETE FROM products WHERE id = ?");
   $delete_products->execute([$delete_id]);
   
   $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);

   $delete_cart = $conn->prepare("DELETE FROM cart WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   
   header('location:admin_products.php');
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Products | Majestic UI</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">

   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

      body {
         background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture5.jpg') no-repeat;
         background-size: cover;
         background-position: center;
         background-attachment: fixed;
         font-family: 'Poppins', sans-serif;
         margin: 0; padding: 0;
      }

      /* FIXED HEADER FIXES */
      header { 
         z-index: 1000 !important; 
         position: sticky !important; 
         top: 0;
      }

      /* MESSAGE STYLES - FIXED CLICK ISSUE */
      .message-container {
         position: fixed;
         top: 2rem; left: 50%;
         transform: translateX(-50%);
         z-index: 10001;
         width: 90%; max-width: 400px;
         pointer-events: none; /* Allows clicks to pass through to elements behind */
      }

      .message {
         background: #fff;
         padding: 1rem 1.5rem;
         border-radius: 10px;
         display: flex;
         justify-content: space-between;
         align-items: center;
         box-shadow: 0 10px 20px rgba(0,0,0,0.3);
         margin-bottom: 10px;
         animation: slideIn 0.3s ease;
         pointer-events: auto; /* Re-enables clicking for the message box itself */
      }
      .message.success { border-left: 5px solid #2ecc71; color: #27ae60; }
      .message.error { border-left: 5px solid #e74c3c; color: #c0392b; }
      .message i { cursor: pointer; color: #333; font-size: 1.2rem; }

      @keyframes slideIn { from{opacity:0; transform:translateY(-20px);} to{opacity:1; transform:translateY(0);} }

      /* UI DESIGN */
      .title{ text-align:center; font-size:2.2rem; margin:30px 0; font-weight:700; background: linear-gradient(90deg,#00f260,#0575e6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
      .add-products form{ width:90%; max-width:900px; margin:0 auto 50px auto; background:rgba(255,255,255,0.08); backdrop-filter: blur(15px); padding:25px; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); }
      .flex{ display:flex; gap:20px; flex-wrap: wrap; }
      .inputBox{ flex:1; min-width: 300px; }
      .box{ width:100%; padding:12px; margin:10px 0; border:none; border-radius:10px; outline:none; background: #fff; }
      .btn{ display:block; width:100%; padding:12px; border:none; border-radius:10px; background:linear-gradient(45deg,#00f260,#0575e6); color:white; font-weight:600; cursor:pointer; transition:0.3s; margin-top: 10px;}
      .btn:hover{ transform:scale(1.02); opacity: 0.9; }

      .show-products .box-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; padding:30px; }
      .show-products .box{ background:rgba(255,255,255,0.08); backdrop-filter: blur(15px); padding:20px; border-radius:20px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.4); position: relative; border: 1px solid rgba(255,255,255,0.1); color: white; }
      .show-products img{ width:100%; height:180px; object-fit:cover; border-radius:15px; margin-bottom:10px; }
      
      .stock-display { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: #fff; padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; }
      .stock-low { color: #ff4d4d; border: 1px solid #ff4d4d; } 

      .price{ font-size:18px; color:#00f260; font-weight:600; margin-bottom: 5px;}
      .name{ font-size:18px; font-weight:600; margin:5px 0; color:#fff; }
      .cat{ font-size:14px; color:#bbb; text-transform: uppercase; letter-spacing: 1px; }
      .details{ font-size:13px; color:#ddd; margin:10px 0; line-height: 1.5; }
      
      .flex-btn { display: flex; justify-content: center; gap: 10px; margin-top: 15px; }
      .option-btn, .delete-btn{ flex: 1; padding:10px; border-radius:10px; text-decoration:none; font-weight:600; font-size: 14px; text-align: center; }
      .option-btn{ background:#3498db; color:white; }
      .delete-btn{ background:#e74c3c; color:white; }
   </style>
</head>
<body>
   
<?php 
if(!empty($message)){
   echo '<div class="message-container">';
   foreach($message as $msg){
      $text = is_array($msg) ? $msg['text'] : $msg;
      $type = is_array($msg) ? $msg['type'] : 'error';
      echo '<div class="message '.$type.'"><span>'.$text.'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
   echo '</div>';
   unset($message);
}

include 'admin_header.php'; 
?>

<section class="add-products">
   <h1 class="title">Add New Product</h1>
   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <input type="text" name="name" class="box" required placeholder="enter product name">
            <select name="category" class="box" required>
               <option value="" selected disabled>select category</option>
               <option value="vegetables">vegetables</option>
               <option value="fruits">fruits</option>
               <option value="meat">meat</option>
               <option value="fish">fish</option>
            </select>
         </div>
         <div class="inputBox">
            <input type="number" min="0" name="price" class="box" required placeholder="enter product price">
            <input type="number" min="0" name="stock" class="box" required placeholder="enter product stock">
            <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="5"></textarea>
      <input type="submit" class="btn" value="add product" name="add_product">
   </form>
</section>

<section class="show-products">
   <h1 class="title">Products Inventory</h1>
   <div class="box-container">
   <?php
      $show_products = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
   <div class="box">
      <div class="stock-display <?= ($fetch_products['stock'] <= 5) ? 'stock-low' : ''; ?>">
         Stock: <?= $fetch_products['stock']; ?>
      </div>
      <img src="<?= $fetch_products['image']; ?>" alt="">
      <div class="price">₱<?= $fetch_products['price']; ?></div>
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="cat"><?= $fetch_products['category']; ?></div>
      <div class="details"><?= $fetch_products['details']; ?></div>
      <div class="flex-btn">
         <a href="admin_update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">Update</a>
         <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p style="color:white; text-align:center; grid-column: 1/-1; font-size: 1.5rem;">No products added yet!</p>';
      }
   ?>
   </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
