<?php

ob_start();
include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

$message = [];

// 1. Get product ID from URL
if(isset($_GET['update'])){
   $update_id = $_GET['update'];
   
   $select_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
   $select_product->execute([$update_id]);
   
   if($select_product->rowCount() > 0){
      $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
   }else{
      header('location:admin_products.php');
      exit();
   }
}else{
   header('location:admin_products.php');
   exit();
}

// 2. Handle update process
if(isset($_POST['update_product'])){

   $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
   $price = htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8');
   $category = htmlspecialchars($_POST['category'] ?? '', ENT_QUOTES, 'UTF-8');
   $details = htmlspecialchars($_POST['details'] ?? '', ENT_QUOTES, 'UTF-8');
   $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT); // Siguroha nga integer ang stock

   // Check if new image is uploaded
   if(!empty($_FILES['image']['name'])){
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_type = $_FILES['image']['type'];
      
      if($image_size > 2000000){
         $message[] = 'Image size is too large!';
      }else{
         $image_base64 = base64_encode(file_get_contents($image_tmp_name));
         $image_data = 'data:' . $image_type . ';base64,' . $image_base64;
         
         $update_product = $conn->prepare("UPDATE products SET name=?, category=?, details=?, price=?, image=?, stock=? WHERE id=?");
         $update_product->execute([$name, $category, $details, $price, $image_data, $stock, $update_id]);
         
         // REDIRECT human sa update aron ma-clear ang form data
         header("location:admin_update_product.php?update=" . $update_id . "&msg=updated");
         exit();
      }
   }else{
      $update_product = $conn->prepare("UPDATE products SET name=?, category=?, details=?, price=?, stock=? WHERE id=?");
      $update_product->execute([$name, $category, $details, $price, $stock, $update_id]);
      
      // REDIRECT human sa update aron ma-clear ang form data
      header("location:admin_update_product.php?update=" . $update_id . "&msg=updated");
      exit();
   }
}

// Check kung gikan ba sa redirect para ipakita ang message
if(isset($_GET['msg']) && $_GET['msg'] == 'updated'){
   $message[] = 'Product updated successfully!';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Product</title>

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
      .update-product form {
         width: 90%;
         max-width: 900px;
         margin: 0 auto 50px auto;
         background: rgba(255,255,255,0.08);
         backdrop-filter: blur(15px);
         padding: 25px;
         border-radius: 20px;
         box-shadow: 0 10px 40px rgba(0,0,0,0.4);
      }
      .flex{ display:flex; gap:20px; }
      .inputBox{ flex:1; }
      .box{
         width: 100%;
         padding: 12px;
         margin: 10px 0;
         border: none;
         border-radius: 10px;
         outline: none;
         background: white;
      }
      .btn{
         display: block;
         width: 100%;
         padding: 12px;
         border: none;
         border-radius: 10px;
         background: linear-gradient(45deg,#00f260,#0575e6);
         color: white;
         font-weight: 600;
         cursor: pointer;
         transition: 0.3s;
         margin-top: 10px;
      }
      .btn:hover{ transform:scale(1.02); }
      .title{
         text-align: center;
         font-size: 2.2rem;
         margin: 30px 0;
         font-weight: 700;
         background: linear-gradient(90deg,#00f260,#0575e6);
         -webkit-background-clip: text;
         -webkit-text-fill-color: transparent;
      }
      .current-image {
         text-align: center;
         margin: 20px 0;
         color: white;
      }
      .current-image img {
         max-width: 200px;
         border-radius: 10px;
         margin-top: 10px;
      }
      .message {
         background: #fff;
         padding: 10px;
         text-align: center;
         margin: 10px auto;
         width: 50%;
         border-radius: 10px;
         position: relative;
         z-index: 1000;
      }
      .message i {
         cursor: pointer;
         color: red;
         margin-left: 10px;
      }
      label {
         color: white;
         display: block;
         margin-top: 10px;
      }
   </style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '<div class="message"><span>'.$msg.'</span> <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
   }
}
?>

<section class="update-product">
   <h1 class="title">Update Product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="current-image">
         <p>Current Image:</p>
         <img src="<?= $fetch_product['image']; ?>" alt="">
      </div>
      
      <div class="flex">
         <div class="inputBox">
            <label>Product Name</label>
            <input type="text" name="name" class="box" required value="<?= $fetch_product['name']; ?>">
            
            <label>Category</label>
            <select name="category" class="box" required>
               <option value="vegetables" <?= $fetch_product['category'] == 'vegetables' ? 'selected' : ''; ?>>vegetables</option>
               <option value="fruits" <?= $fetch_product['category'] == 'fruits' ? 'selected' : ''; ?>>fruits</option>
               <option value="meat" <?= $fetch_product['category'] == 'meat' ? 'selected' : ''; ?>>meat</option>
               <option value="fish" <?= $fetch_product['category'] == 'fish' ? 'selected' : ''; ?>>fish</option>
            </select>
         </div>
         <div class="inputBox">
            <label>Price (₱)</label>
            <input type="number" min="0" name="price" class="box" required value="<?= $fetch_product['price']; ?>">
            
            <label>Stock Quantity</label>
            <input type="number" min="0" name="stock" class="box" required value="<?= $fetch_product['stock']; ?>">
            
            <label>New Image (leave empty to keep current)</label>
            <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      
      <label>Product Details</label>
      <textarea name="details" class="box" required cols="30" rows="10"><?= $fetch_product['details']; ?></textarea>
      
      <input type="submit" class="btn" value="update product" name="update_product">
   </form>
</section>

</body>
</html>
