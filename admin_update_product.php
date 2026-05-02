<?php

@include 'config.php';

session_start();

// FIX: Gamit og isset para dili mo-error kung wala pay login
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

if(!$admin_id){
   header('location:login.php');
   exit(); // Importante: pirme butangi og exit() human sa header redirect
}

// ... ang uban nimong code ...;

if(isset($_POST['update_product'])){

   $pid = $_POST['pid'];
   $name = $_POST['name'];
   // Line 17 pataas, pulihi ang imong filter_var og ingon ani:
$name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
$price = htmlspecialchars($_POST['price'], ENT_QUOTES, 'UTF-8');
$category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
$details = htmlspecialchars($_POST['details'], ENT_QUOTES, 'UTF-8');

// Para sa image name (Line 26):
$image = htmlspecialchars($_FILES['image']['name'], ENT_QUOTES, 'UTF-8');
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $old_image = $_POST['old_image'];

   $update_product = $conn->prepare("UPDATE products SET name = ?, category = ?, details = ?, price = ? WHERE id = ?");
   $update_product->execute([$name, $category, $details, $price, $pid]);

   $message[] = 'product updated successfully!';

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'image size is too large!';
      }else{

         $update_image = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
         $update_image->execute([$image, $pid]);

         if($update_image){
            move_uploaded_file($image_tmp_name, $image_folder);
            unlink('uploaded_img/'.$old_image);
            $message[] = 'image updated successfully!';
         }
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

   <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

body{
   font-family: 'Poppins', sans-serif;
   background: linear-gradient(135deg, #141e30, #243b55);
   margin:0;
   padding:0;
}

/* TITLE */
.title{
   text-align:center;
   font-size:32px;
   color:#fff;
   margin:30px 0;
   letter-spacing:2px;
   text-transform:uppercase;
}

/* SECTION */
.update-product{
   display:flex;
   justify-content:center;
   align-items:center;
   flex-direction:column;
   padding:30px;
}

/* FORM CARD */
.majestic-form{
   width:100%;
   max-width:900px;
   background: rgba(255,255,255,0.08);
   backdrop-filter: blur(15px);
   border-radius:20px;
   padding:30px;
   box-shadow: 0 15px 40px rgba(0,0,0,0.5);
   border:1px solid rgba(255,255,255,0.1);
}

/* IMAGE */
.image-preview{
   text-align:center;
   margin-bottom:20px;
}

.image-preview img{
   width:180px;
   height:180px;
   object-fit:cover;
   border-radius:15px;
   border:3px solid #fff;
   box-shadow:0 0 20px rgba(255,255,255,0.2);
}

/* INPUT GROUP */
.input-group{
   margin-bottom:15px;
}

.input-group label{
   display:block;
   color:#fff;
   margin-bottom:5px;
   font-size:14px;
}

/* INPUT STYLE */
.input-group input,
.input-group select,
.input-group textarea{
   width:100%;
   padding:12px;
   border-radius:10px;
   border:none;
   outline:none;
   background: rgba(255,255,255,0.15);
   color:#fff;
   font-size:14px;
   transition:0.3s;
}

.input-group textarea{
   resize:none;
   height:100px;
}

.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus{
   background: rgba(255,255,255,0.25);
   transform: scale(1.02);
}

/* BUTTON AREA */
.flex-btn{
   display:flex;
   justify-content:space-between;
   gap:10px;
   margin-top:20px;
   flex-wrap:wrap;
}

/* BUTTONS */
.btn, .option-btn{
   padding:12px 20px;
   border-radius:10px;
   text-decoration:none;
   border:none;
   cursor:pointer;
   font-size:14px;
   font-weight:500;
   transition:0.3s;
}

/* UPDATE BUTTON */
.btn{
   background: linear-gradient(45deg, #00c6ff, #0072ff);
   color:#fff;
   box-shadow:0 5px 15px rgba(0,114,255,0.4);
}

.btn:hover{
   transform:translateY(-2px);
}

/* BACK BUTTON */
.option-btn{
   background: rgba(255,255,255,0.15);
   color:#fff;
}

.option-btn:hover{
   background: rgba(255,255,255,0.3);
}

/* EMPTY TEXT */
.empty{
   color:#fff;
   text-align:center;
   margin-top:20px;
}
</style>

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="update-product">

   <h1 class="title">✨ Update Product</h1>   

   <?php
      $update_id = $_GET['update'];
      $select_products = $conn->prepare("SELECT * FROM products WHERE id = ?");
      $select_products->execute([$update_id]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>

   <form action="" method="post" enctype="multipart/form-data" class="majestic-form">

      <input type="hidden" name="old_image" value="<?= $fetch_products['image']; ?>">
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">

      <div class="image-preview">
         <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      </div>

      <div class="input-group">
         <label>Product Name</label>
         <input type="text" name="name" required value="<?= $fetch_products['name']; ?>">
      </div>

      <div class="input-group">
         <label>Price</label>
         <input type="number" name="price" min="0" required value="<?= $fetch_products['price']; ?>">
      </div>

      <div class="input-group">
         <label>Category</label>
         <select name="category" required>
            <option selected><?= $fetch_products['category']; ?></option>
            <option value="vegetables">Vegetables</option>
            <option value="fruits">Fruits</option>
            <option value="meat">Meat</option>
            <option value="fish">Fish</option>
         </select>
      </div>

      <div class="input-group">
         <label>Details</label>
         <textarea name="details" required><?= $fetch_products['details']; ?></textarea>
      </div>

      <div class="input-group">
         <label>Update Image</label>
         <input type="file" name="image" accept="image/jpg, image/jpeg, image/png">
      </div>

      <div class="flex-btn">
         <input type="submit" value="Update Product" name="update_product" class="btn">
         <a href="admin_products.php" class="option-btn">← Go Back</a>
      </div>

   </form>

   <?php
         }
      }else{
         echo '<p class="empty">No products found!</p>';
      }
   ?>

</section>












<script src="js/script.js"></script>

</body>
</html>
