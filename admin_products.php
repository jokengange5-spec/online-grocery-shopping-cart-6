<?php

ob_start(); 
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
};

$message = []; // Initialize as array to prevent foreach error

if(isset($_POST['add_product'])){

   $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
   $price = htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8');
   $category = htmlspecialchars($_POST['category'] ?? '', ENT_QUOTES, 'UTF-8');
   $details = htmlspecialchars($_POST['details'] ?? '', ENT_QUOTES, 'UTF-8');
   
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_products = $conn->prepare("SELECT * FROM products WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'Product name already exists!';
   }else{
      if($image_size > 2000000){
         $message[] = 'Image size is too large!';
      }else{
         $insert_products = $conn->prepare("INSERT INTO products(name, category, details, price, image) VALUES(?,?,?,?,?)");
         $insert_products->execute([$name, $category, $details, $price, $image]);

         if($insert_products){
            // Added check if folder exists and is writable
            if(!is_dir('uploaded_img')){
               mkdir('uploaded_img', 0777, true);
            }
            
            if(move_uploaded_file($image_tmp_name, $image_folder)){
               $message[] = 'New product added!';
            }else{
               $message[] = 'Upload failed! Check folder permissions.';
            }
         }
      }
   }
};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $select_delete_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
   
   if($fetch_delete_image){
      $image_path = 'uploaded_img/'.$fetch_delete_image['image'];
      if(file_exists($image_path)){
         @unlink($image_path);
      }
   }

   $delete_products = $conn->prepare("DELETE FROM products WHERE id = ?");
   $delete_products->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE pid = ?");
   $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE pid = ?");
$delete_wishlist->execute([$delete_id]);

$delete_cart = $conn->prepare("DELETE FROM cart WHERE pid = ?");
$delete_cart->execute([$delete_id]);
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
   <title>Products</title>

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
         margin: 0;
         padding: 0;
      }

      .title{ text-align:center; font-size:2.2rem; margin:30px 0; font-weight:700; background: linear-gradient(90deg,#00f260,#0575e6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
      .add-products form{ width:90%; max-width:900px; margin:0 auto 50px auto; background:rgba(255,255,255,0.08); backdrop-filter: blur(15px); padding:25px; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.4); }
      .flex{ display:flex; gap:20px; }
      .inputBox{ flex:1; }
      .box{ width:100%; padding:12px; margin:10px 0; border:none; border-radius:10px; outline:none; }
      .btn{ display:block; width:100%; padding:12px; border:none; border-radius:10px; background:linear-gradient(45deg,#00f260,#0575e6); color:white; font-weight:600; cursor:pointer; transition:0.3s; margin-top: 10px;}
      .btn:hover{ transform:scale(1.02); }
      .show-products .box-container{ display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; padding:30px; }
      .show-products .box{ background:rgba(255,255,255,0.08); backdrop-filter: blur(15px); padding:20px; border-radius:20px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.4); transition:0.3s; }
      .show-products img{ width:100%; height:180px; object-fit:cover; border-radius:15px; margin-bottom:10px; }
      .price{ font-size:18px; color:#00f260; font-weight:600; }
      .name{ font-size:18px; font-weight:600; margin:5px 0; color:#fff; }
      .cat{ font-size:14px; color:#bbb; }
      .details{ font-size:13px; color:#ddd; margin:10px 0; }
      .flex-btn { display: flex; justify-content: center; gap: 10px; margin-top: 10px; }
      .option-btn, .delete-btn{ display:inline-block; padding:8px 15px; border-radius:10px; text-decoration:none; font-weight:600; font-size: 14px; }
      .option-btn{ background:#3498db; color:white; }
      .delete-btn{ background:#e74c3c; color:white; }
      .message { background: #fff; padding: 10px; text-align: center; margin: 10px auto; width: 50%; border-radius: 10px; position: relative; z-index: 1000; }
      .message i { cursor: pointer; color: red; margin-left: 10px; }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<?php
// Fix for Line 123: Check if $message is array and not empty
if(isset($message) && is_array($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.$msg.'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="show-products">
   <h1 class="title">Products Added</h1>
   <div class="box-container">
   <?php
      $show_products = $conn->prepare("SELECT * FROM products ");
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
   <div class="box">
      <div class="price">₱<?= $fetch_products['price']; ?></div>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="cat"><?= $fetch_products['category']; ?></div>
      <div class="details"><?= $fetch_products['details']; ?></div>
      <div class="flex-btn">
         <a href="admin_update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p style="color:white; text-align:center; grid-column: 1/-1;">No products added yet!</p>';
      }
   ?>
   </div>
</section>

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
            <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="add product" name="add_product">
   </form>
</section>

<script src="js/script.js"></script>
<script>
let userBtn = document.querySelector('#user-btn');
let profile = document.querySelector('.profile');

if(userBtn){
   userBtn.onclick = () => {
      profile.classList.toggle('active');
   }
}
</script>
</body>
</html>
