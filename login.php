<?php

@include 'config.php';
session_start();

if(isset($_POST['submit'])){

   $email = trim($_POST['email']);
   $pass = $_POST['pass'];

   $hashed_pass = md5($pass);

   $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $hashed_pass]);

   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($row){
      if($row['user_type'] == 'admin'){
         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');
         exit;
      }

      if($row['user_type'] == 'user'){
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');
         exit;
      }
   }else{
      $message[] = 'incorrect email or password!';
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

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

*{
   margin:0;
   padding:0;
   box-sizing:border-box;
   font-family:'Poppins',sans-serif;
}

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

.form-container h3{
   font-size:26px;
   margin-bottom:20px;
}

.box{
   width:100%;
   padding:12px;
   margin:10px 0;
   border:none;
   border-radius:10px;
   outline:none;
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
}

.btn:hover{
   background:#27ae60;
}

.message{
   position:fixed; /* Changed to fixed for better visibility */
   top:20px;
   background:#e74c3c;
   color:#fff;
   padding:10px 15px;
   border-radius:10px;
   display:flex;
   gap:10px;
   z-index: 1000;
}

.message i{
   cursor:pointer;
}
</style>

</head>
<body>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container">

   <!-- ✅ ADDED: onsubmit attribute -->
   <form action="" method="POST" onsubmit="return confirmLogin()">
      <h3><i class="fas fa-user"></i> Login</h3>

      <input type="email" name="email" class="box" placeholder="Enter your email" required>
      <input type="password" name="pass" class="box" placeholder="Enter your password" required>

      <input type="submit" value="Login" class="btn" name="submit">

      <p style="margin-top:15px;">Don't have an account? <a href="register.php" style="color:#2ecc71;">Register</a></p>
   </form>

</section>

<!-- ✅ JAVASCRIPT LOGIC -->
<script>
function confirmLogin() {
   // Kini nga popup mohatag og OK (Yes) ug Cancel (No) nga options
   var response = confirm("Proceed to log in?");
   
   if (response) {
      return true; // Mo-proceed sa PHP processing
   } else {
      return false; // Dili mo-submit, pabilin sa login page
   }
}
</script>

</body>
</html>
