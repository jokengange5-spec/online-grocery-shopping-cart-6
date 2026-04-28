<?php

@include 'config.php';
session_start();

if(isset($_POST['submit'])){

   // ✅ CLEAN INPUT (NO deprecated filters)
   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $pass = md5($_POST['pass']); // keep MD5 for compatibility
   $user_type = $_POST['user_type'];

   // IMAGE UPLOAD
   $image = $_FILES['image']['name'];
   $tmp = $_FILES['image']['tmp_name'];
   $folder = 'uploaded_img/'.$image;

   // CHECK EXISTING USER
   $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $check->execute([$email]);

   if($check->rowCount() > 0){
      $message[] = "User already exists!";
   }else{

      // ❗ IMPORTANT: DO NOT insert id
      $insert = $conn->prepare("
         INSERT INTO users(name, email, password, user_type, image)
         VALUES(?,?,?,?,?)
      ");

      $insert->execute([$name, $email, $pass, $user_type, $image]);

      move_uploaded_file($tmp, $folder);

      $message[] = "Registered successfully!";
      header("location:login.php");
      exit;
   }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<style>
body{
   font-family:Poppins;
   display:flex;
   justify-content:center;
   align-items:center;
   height:100vh;
   background:#222;
   color:white;
}

.form{
   width:350px;
   padding:20px;
   background:#333;
   border-radius:10px;
}

input,select{
   width:100%;
   padding:10px;
   margin:8px 0;
}

button{
   width:100%;
   padding:10px;
   background:#2ecc71;
   border:none;
   color:white;
}
</style>
</head>
<body>

<div class="form">

<?php
if(isset($message)){
   foreach($message as $msg){
      echo "<p>$msg</p>";
   }
}
?>

<form method="POST" enctype="multipart/form-data">

   <input type="text" name="name" placeholder="Name" required>
   <input type="email" name="email" placeholder="Email" required>
   <input type="password" name="pass" placeholder="Password" required>

   <select name="user_type">
      <option value="user">User</option>
      <option value="admin">Admin</option>
   </select>

   <input type="file" name="image" required>

   <button type="submit" name="submit">Register</button>

</form>

</div>

</body>
</html>
