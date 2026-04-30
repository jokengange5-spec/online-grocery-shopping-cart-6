<?php
ob_start(); // I-add ni sa pinaka-una gyud
@include 'config.php';
// ... ang uban nimong code
session_start();

if(isset($_POST['submit'])){

   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $pass = md5($_POST['pass']); // keep MD5 for your current DB
   $user_type = $_POST['user_type'];

   $image = $_FILES['image']['name'];
   $tmp = $_FILES['image']['tmp_name'];

   // ✔ FIX: folder path
   $folder = 'uploaded_img/'.$image;

   // ✔ CREATE FOLDER IF NOT EXIST
// ✔ FOLDER PATH
   $target_dir = 'uploaded_img/';
   $folder = $target_dir . $image;

   // ✔ SIGURADUHA NGA NAAY FOLDER UG NAAY PERMISSION
   if(!is_dir($target_dir)){
      mkdir($target_dir, 0777, true);
      chmod($target_dir, 0777); // I-force ang permission sa 777
   }

   // ✔ CHECK USER EXISTS
   $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $check->execute([$email]);

   if($check->rowCount() > 0){
      $message[] = "User already exists!";
   }else{

      // ✔ INSERT USER (NO ID HERE!)
      $insert = $conn->prepare("
         INSERT INTO users(name, email, password, user_type, image)
         VALUES(?,?,?,?,?)
      ");

      if($insert->execute([$name, $email, $pass, $user_type, $image])){

         // ✔ MOVE IMAGE SAFELY
         move_uploaded_file($tmp, $folder);

         header("location:login.php");
         exit;

      }else{
         $message[] = "Registration failed!";
      }

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
   background:#1f1f1f;
   color:white;
}

.form{
   width:350px;
   padding:20px;
   background:#2c2c2c;
   border-radius:10px;
}

input,select{
   width:100%;
   padding:10px;
   margin:8px 0;
   border:none;
   border-radius:6px;
}

button{
   width:100%;
   padding:10px;
   background:#2ecc71;
   border:none;
   color:white;
   cursor:pointer;
}
</style>

</head>
<body>

<div class="form">

<?php
if(isset($message)){
   foreach($message as $msg){
      echo "<p style='color:red;'>$msg</p>";
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
