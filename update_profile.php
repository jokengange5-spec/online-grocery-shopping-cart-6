<?php

@include 'config.php';
session_start();

if(isset($_GET['logout'])){
   session_destroy();
   header('location:index.php');
   exit;
}

if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit;
}

$user_id = $_SESSION['user_id'];

$select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(!$fetch_profile){
   echo "User not found!";
   exit;
}

/* UPDATE PROFILE */
if(isset($_POST['update_profile'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);

   if(!empty($_FILES['image']['name'])){

      $image = $_FILES['image']['name'];
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/'.$image;

      if($image_size > 2000000){
         $message[] = 'Image too large!';
      }else{
         $update_image = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
         $update_image->execute([$image, $user_id]);

         move_uploaded_file($image_tmp_name, $image_folder);

         if(!empty($fetch_profile['image'])){
            unlink('uploaded_img/'.$fetch_profile['image']);
         }

         $message[] = 'Image updated successfully!';
      }
   }

   if(!empty($_POST['update_pass']) && !empty($_POST['new_pass']) && !empty($_POST['confirm_pass'])){

      $old_pass = md5($_POST['update_pass']);
      $new_pass = md5($_POST['new_pass']);
      $confirm_pass = md5($_POST['confirm_pass']);

      if($old_pass != $fetch_profile['password']){
         $message[] = 'Old password is wrong!';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'Password does not match!';
      }else{
         $update_pass_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $user_id]);
         $message[] = 'Password updated successfully!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">

<style>

/* BACKGROUND SAME VIBE AS SHOP */
body{
   background: #0f172a;
   font-family: Arial;
   color: #fff;
}

/* TITLE LIKE SHOP HEADER */
.title{
   text-align:center;
   font-size:28px;
   margin:20px 0;
   color:#00f5a0;
}

/* CONTAINER */
.update-profile{
   padding:20px;
}

/* FORM BOX LIKE PRODUCT CARD */
form{
   max-width:700px;
   margin:0 auto;
   background:#1e293b;
   padding:20px;
   border-radius:10px;
}

/* IMAGE */
form img{
   display:block;
   margin:0 auto 15px;
   width:120px;
   height:120px;
   border-radius:50%;
   border:3px solid #00f5a0;
}

/* INPUT STYLE */
.inputBox{
   margin-bottom:15px;
}

.inputBox span{
   display:block;
   margin:8px 0 5px;
   color:#cbd5e1;
}

.box{
   width:100%;
   padding:10px;
   border-radius:5px;
   border:none;
   outline:none;
   background:#334155;
   color:#fff;
}

/* BUTTON STYLE SAME AS SHOP BTN */
.btn{
   width:100%;
   padding:12px;
   background:#00f5a0;
   border:none;
   color:#000;
   font-weight:bold;
   border-radius:5px;
   cursor:pointer;
   margin-top:10px;
}

.btn:hover{
   background:#00d9f5;
}

/* OPTION BUTTON */
.option-btn{
   display:block;
   text-align:center;
   margin-top:10px;
   padding:10px;
   background:#475569;
   color:#fff;
   text-decoration:none;
   border-radius:5px;
}

/* LOGOUT LIKE SHOP BUTTON */
.logout{
   display:block;
   text-align:center;
   margin:20px auto;
   width:200px;
   padding:12px;
   background:#ff4b2b;
   color:#fff;
   text-decoration:none;
   border-radius:5px;
}

.logout:hover{
   background:#ff416c;
}

</style>

</head>
<body>

<?php include 'header.php'; ?>

<section class="update-profile">

<h1 class="title">⚡ My Profile Dashboard</h1>

<form method="POST" enctype="multipart/form-data">

   <img src="uploaded_img/<?= $fetch_profile['image']; ?>">

   <div class="inputBox">
      <span>Username</span>
      <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" class="box">
   </div>

   <div class="inputBox">
      <span>Email</span>
      <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" class="box">
   </div>

   <div class="inputBox">
      <span>Update Image</span>
      <input type="file" name="image" class="box">
   </div>

   <input type="hidden" name="old_pass" value="<?= $fetch_profile['password']; ?>">

   <div class="inputBox">
      <span>Old Password</span>
      <input type="password" name="update_pass" class="box">
   </div>

   <div class="inputBox">
      <span>New Password</span>
      <input type="password" name="new_pass" class="box">
   </div>

   <div class="inputBox">
      <span>Confirm Password</span>
      <input type="password" name="confirm_pass" class="box">
   </div>

   <input type="submit" name="update_profile" value="Update Profile" class="btn">

   <a href="home.php" class="option-btn">Go Back</a>

</form>

<a href="update_profile.php?logout=1"
   class="logout"
   onclick="return confirm('Logout now?');">
   Logout
</a>

</section>

<?php include 'footer.php'; ?>

</body>
</html>


