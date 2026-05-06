<?php
ob_start(); 
@include 'config.php';
session_start();

if(isset($_POST['submit'])){

   // ERROR HANDLER ARRAY
   $message = [];

   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $pass = md5($_POST['pass']); 
   
   // FIX: Gi-hardcode nato nga 'user' permi ang i-save
   $user_type = 'user'; 

   // =========================
   // ERROR HANDLERS
   // =========================

   // EMPTY FIELDS
   if(empty($name) || empty($email) || empty($_POST['pass'])){
      $message[] = "Please fill all fields!";
   }

   // VALID EMAIL
   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $message[] = "Invalid email format!";
   }

   // PASSWORD LENGTH
   if(strlen($_POST['pass']) < 6){
      $message[] = "Password must be at least 6 characters!";
   }

   // CHECK KUNG EXISTING NA ANG EMAIL
   $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $check->execute([$email]);

   if($check->rowCount() > 0){
      $message[] = "User already exists!";
   }

   // ONLY INSERT IF NO ERRORS
   if(empty($message)){

      // INSERT AS USER
      $insert = $conn->prepare("
         INSERT INTO users(name, email, password, user_type)
         VALUES(?,?,?,?)
      ");

     if($insert->execute([$name, $email, $pass, $user_type])){

   $message[] = "Successfully registered!";

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

   <form method="POST">
      <h3>Register Now</h3>

      <?php
      if(isset($message)){
         foreach($message as $msg){
            echo '<div class="message">'.$msg.'</div>';
         }
      }
      ?>

      <input type="text" name="name" placeholder="Enter Name" required>
      <input type="email" name="email" placeholder="Enter Email" required>
      <input type="password" name="pass" placeholder="Enter Password" required>

      <button type="submit" name="submit">Register Now</button>
      <p>Already have an account? <a href="login.php">Login here</a></p>
   </form>

</div>

</body>
</html>
