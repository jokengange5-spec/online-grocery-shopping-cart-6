<?php

@include 'config.php';
session_start();

$show_success = false; // Flag para sa success alert
$redirect_url = '';   // Asa dapit mo redirect
$user_name = '';      // Pangalan sa user

if(isset($_POST['submit'])){

   $email = trim($_POST['email']);
   $pass = $_POST['pass'];

   $hashed_pass = md5($pass);

   $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $hashed_pass]);

   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($row){
      $user_name = $row['name']; // Siguroha nga naay 'name' column sa imong database
      $show_success = true;

      if($row['user_type'] == 'admin'){
         $_SESSION['admin_id'] = $row['id'];
         $redirect_url = 'admin_page.php';
      }else{
         $_SESSION['user_id'] = $row['id'];
         $redirect_url = 'home.php';
      }
   }else{
      $message[] = 'Incorrect email or password!';
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

   <style>
   @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

   *{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }

   body{
      height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background: url('image_products/picture7.jpg') no-repeat center center fixed;
      background-size: cover;
   }

   body::before{
      content:'';
      position:fixed;
      width:100%;
      height:100%;
      background:rgba(0,0,0,0.45);
      z-index:-1;
   }

   .form-container{
      width:370px;
      padding:40px;
      border-radius:20px;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(15px);
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      text-align:center;
      color:white;
   }

   .form-container h3{ font-size:26px; margin-bottom:20px; }

   .box{
      width:100%;
      padding:12px;
      margin:10px 0;
      border:none;
      border-radius:10px;
      outline:none;
      background: rgba(255, 255, 255, 0.9);
   }

   .btn{
      width:100%;
      padding:12px;
      border:none;
      border-radius:10px;
      background:#2ecc71;
      color:white;
      font-weight:bold;
      cursor:pointer;
      margin-top: 10px;
   }

   .btn:hover{ background:#27ae60; }

   .swal2-popup {
      background: #2c2c2c !important;
      color: #fff !important;
      border-radius: 15px !important;
   }
   </style>
</head>
<body>

<section class="form-container">
   <form action="" method="POST">
      <h3><i class="fas fa-user"></i> Login</h3>
      <input type="email" name="email" class="box" placeholder="Enter your email" required>
      <input type="password" name="pass" class="box" placeholder="Enter your password" required>
      <input type="submit" value="Login" class="btn" name="submit">
      <p style="margin-top:15px;">Don't have an account? <a href="register.php" style="color:#2ecc71;">Register</a></p>
   </form>
</section>

<script>
<?php
// Error Message
if(isset($message)){
   foreach($message as $msg){
      echo "
      Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: '$msg',
          confirmButtonColor: '#e74c3c'
      });
      ";
   }
}

// Success Message
if($show_success){
   echo "
   Swal.fire({
       title: 'Success!',
       text: 'Welcome $user_name, you have successfully logged in!',
       icon: 'success',
       confirmButtonColor: '#2ecc71',
       confirmButtonText: 'Proceed'
   }).then((result) => {
       if (result.isConfirmed) {
           window.location.href = '$redirect_url';
       }
   });
   ";
}
?>
</script>

</body>
</html>
