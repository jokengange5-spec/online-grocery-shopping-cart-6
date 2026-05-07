<?php
ob_start(); 
@include 'config.php';
session_start();

if(isset($_POST['submit'])){

   $message = [];

   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $pass = md5($_POST['pass']); 
   
   $user_type = 'user'; 

   // ERROR HANDLERS
   if(empty($name) || empty($email) || empty($_POST['pass'])){
      $message[] = "Please fill all fields!";
   }

   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $message[] = "Invalid email format!";
   }

   if(strlen($_POST['pass']) < 6){
      $message[] = "Password must be at least 6 characters!";
   }

   $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $check->execute([$email]);

   if($check->rowCount() > 0){
      $message[] = "User already exists!";
   }

   if(empty($message)){
      $insert = $conn->prepare("
          INSERT INTO users(name, email, password, user_type)
          VALUES(?,?,?,?)
      ");

      if($insert->execute([$name, $email, $pass, $user_type])){
         // Kung successful, i-redirect nato sa login.php
         header('location:login.php');
         exit();
      }else{
         $message[] = "Registration failed!";
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Register</title>
   <style>
      body{
         font-family: 'Poppins', sans-serif;
         display: flex;
         justify-content: center;
         align-items: center;
         height: 100vh;
         background: #1f1f1f;
         color: white;
         margin: 0;
      }
      .form-container{
         width: 350px;
         padding: 30px;
         background: #2c2c2c;
         border-radius: 10px;
         box-shadow: 0 4px 15px rgba(0,0,0,0.3);
      }
      h3 {
         text-align: center;
         margin-bottom: 20px;
      }
      input{
         width: 100%;
         padding: 12px;
         margin: 10px 0;
         border: none;
         border-radius: 6px;
         box-sizing: border-box;
      }
      button{
         width: 100%;
         padding: 12px;
         background: #2ecc71;
         border: none;
         color: white;
         font-weight: bold;
         cursor: pointer;
         border-radius: 6px;
         margin-top: 10px;
      }
      button:hover{
         background: #27ae60;
      }
      .message{
         background: rgba(231, 76, 60, 0.2);
         color: #e74c3c;
         padding: 10px;
         border-radius: 5px;
         text-align: center;
         margin-bottom: 15px;
      }
      .success{
         background: rgba(46, 204, 113, 0.2);
         color: #2ecc71;
      }
      p {
         text-align: center;
         font-size: 14px;
      }
      a {
         color: #2ecc71;
         text-decoration: none;
      }
   </style>
</head>
<body>

<div class="form-container">

   <!-- Gidugangan og ID ang form para sa JavaScript -->
   <form method="POST" id="registerForm">
      <h3>Register Now</h3>

      <?php
      if(isset($message)){
         foreach($message as $msg){
            echo '<div class="message">'.$msg.'</div>';
         }
      }
      ?>

      <!-- Gidugangan og ID ang Name input -->
      <input type="text" name="name" id="userName" placeholder="Enter Name" required>
      <input type="email" name="email" placeholder="Enter Email" required>
      <input type="password" name="pass" placeholder="Enter Password" required>

      <button type="submit" name="submit">Register Now</button>
      <p>Already have an account? <a href="login.php">Login here</a></p>
   </form>

</div>

<script>
   // Kini nga script ang mo-handle sa confirmation
   const form = document.getElementById('registerForm');
   const nameInput = document.getElementById('userName');

   form.onsubmit = function() {
      const name = nameInput.value;
      // Mo gawas ang confirmation box
      const confirmAction = confirm("Do you want to register as " + name + "?");
      
      if (confirmAction) {
         // If "Yes/OK", mo padayon ang form submission sa PHP
         return true;
      } else {
         // If "No/Cancel", mo stop ang submission ug magpabilin sa register page
         return false;
      }
   };
</script>

</body>
</html>
