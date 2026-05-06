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
         background-size: cover; background-position: center; background-attachment: fixed;
         font-family: 'Poppins', sans-serif; margin: 0; padding: 0;
      }

      /* --- CRITICAL FIX PARA SA PROFILE CLICK --- */
      header {
         position: sticky !important;
         top: 0;
         z-index: 2000 !important; /* Mas taas pa sa tanan elements */
         background: rgba(255,255,255,0.1) !important;
         backdrop-filter: blur(10px);
      }

      /* Siguroha nga ang profile dropdown mogawas sa ibabaw */
      .header .flex .profile {
         z-index: 3000 !important;
      }

      .message-container {
         position: fixed;
         top: 5rem; left: 50%;
         transform: translateX(-50%);
         z-index: 4000; /* Pinakataas para sa alert */
         width: 90%; max-width: 400px;
         pointer-events: none; /* Dili mo-block sa clicks sa luyo */
      }

      .message {
         pointer-events: auto; /* Ma-click gihapon ang X button */
         background: #fff; padding: 1rem; border-radius: 10px;
         display: flex; justify-content: space-between; margin-bottom: 10px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.3);
      }
      /* --- END OF FIX --- */

      .title{ text-align:center; font-size:2.2rem; margin:30px 0; font-weight:700; background: linear-gradient(90deg,#00f260,#0575e6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
      .add-products form{ width:90%; max-width:900px; margin:0 auto 50px auto; background:rgba(255,255,255,0.08); backdrop-filter: blur(15px); padding:25px; border-radius:20px; border: 1px solid rgba(255,255,255,0.1); }
      .flex{ display:flex; gap:20px; flex-wrap: wrap; }
      .inputBox{ flex:1; min-width: 300px; }
      .box{ width:100%; padding:12px; margin:10px 0; border-radius:10px; border:none; outline:none; }
      .btn{ display:block; width:100%; padding:12px; border-radius:10px; border:none; background:linear-gradient(45deg,#00f260,#0575e6); color:white; font-weight:600; cursor:pointer; }
      
      .show-products .box-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; padding:30px; position: relative; z-index: 1; }
      .show-products .box{ background:rgba(255,255,255,0.08); backdrop-filter: blur(15px); padding:20px; border-radius:20px; text-align:center; color: white; border: 1px solid rgba(255,255,255,0.1); }
      .show-products img{ width:100%; height:180px; object-fit:cover; border-radius:15px; }
      
      .stock-display { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: #fff; padding: 5px 10px; border-radius: 8px; font-size: 12px; }
      .price{ color:#00f260; font-weight:600; font-size: 18px; }
      .flex-btn { display: flex; gap: 10px; margin-top: 15px; }
      .option-btn, .delete-btn{ flex: 1; padding:10px; border-radius:10px; text-decoration:none; color:white; font-size: 14px; }
      .option-btn{ background:#3498db; }
      .delete-btn{ background:#e74c3c; }
   </style>
</head>
<body>

<?php 
if(!empty($message)){
   echo '<div class="message-container">';
   foreach($message as $msg){
      $type = $msg['type'] ?? 'error';
      echo '<div class="message"><span>'.$msg['text'].'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
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
      <div class="stock-display">Stock: <?= $fetch_products['stock']; ?></div>
      <img src="<?= $fetch_products['image']; ?>" alt="">
      <div class="price">₱<?= $fetch_products['price']; ?></div>
      <div class="name" style="color:white;"><?= $fetch_products['name']; ?></div>
      <div class="flex-btn">
         <a href="admin_update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">Update</a>
         <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Delete?');">Delete</a>
      </div>
   </div>
   <?php
         }
      }
   ?>
   </div>
</section>

<script src="js/script.js"></script>
</body>
</html>
