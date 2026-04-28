<?php

@include 'config.php';
session_start();

if(isset($_POST['submit'])){

   // ✅ FIX: no deprecated filters
   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $pass = md5($_POST['pass']); // keep MD5 for now (your DB already uses it)
   $user_type = $_POST['user_type'];

   // ✅ IMAGE UPLOAD
   $image = $_FILES['image']['name'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   // ❗ check if email exists
   $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $check->execute([$email]);

   if($check->rowCount() > 0){
      $message[] = 'user already exists!';
   }else{

      // ❗ IMPORTANT: DO NOT insert id (PostgreSQL auto-generates it)
      $insert = $conn->prepare("
         INSERT INTO users(name, email, password, user_type, image)
         VALUES(?,?,?,?,?)
      ");

      $insert->execute([$name, $email, $pass, $user_type, $image]);

      // upload image
      move_uploaded_file($image_tmp_name, $image_folder);

      $message[] = 'registered successfully!';
      header('location:login.php');
      exit;
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<style>
body{
   font-family:Poppins,sans-serif;
   display:flex;
   align-items:center;
   justify-content:center;
   height:100vh;
   background:url('image_products/picture7.jpg') no-repeat center center fixed;
   background-size:cover;
}

body::before{
   content:'';
   position:fixed;
   width:100%;
   height:100%;
   background:rgba(0,0,0,0.4);
   z-index:-1;
}

.form-container{
   width:380px;
   padding:30px;
   background:rgba(255,255,255,0.15);
   backdrop-filter:blur(15px);
   border-radius:18px;
   text-align:center;
   color:#fff;
}

.box{
   width:100%;
   padding:10px;
   margin:8px 0;
   border:none;
   border-radius:10px;
}

.btn{
   width:100%;
   padding:10px;
   background:#2ecc71;
   border:none;
   color:white;
   border-radius:10px;
   cursor:pointer;
   font-weight:bold;
}

.btn:hover{
   background:#27ae60;
}

.message{
   background:#e74c3c;
   padding:10px;
   border-radius:10px;
   margin-bottom:10px;
}
</style>

</head>
<body>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo "<div class='message'>$msg</div>";
   }
}
?>

<div class="form-container">

   <form method="POST" enctype="multipart/form-data">

      <h3>Register</h3>

      <input type="text" name="name" class="box" placeholder="Enter name" required>
      <input type="email" name="email" class="box" placeholder="Enter email" required>
      <input type="password" name="pass" class="box" placeholder="Enter password" required>

      <select name="user_type" class="box">
         <option value="user">user</option>
         <option value="admin">admin</option>
      </select>

      <input type="file" name="image" class="box" required>

      <input type="submit" name="submit" value="Register" class="btn">

      <p>Already have account? <a href="login.php">Login</a></p>

   </form>

</div>

</body>
</html>
