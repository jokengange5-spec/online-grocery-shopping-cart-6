<?php
ob_start(); 
@include 'config.php';
session_start();

$show_success = false; 

if(isset($_POST['submit'])){

   $message = [];

   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $pass = $_POST['pass']; 
   $cpass = $_POST['cpass']; // Added Confirm Password variable
   
   $user_type = 'user'; 

   // ERROR HANDLERS
   if(empty($name) || empty($email) || empty($pass) || empty($cpass)){
      $message[] = "Please fill all fields!";
   }

   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $message[] = "Invalid email format!";
   }

   if(strlen($pass) < 6){
      $message[] = "Password must be at least 6 characters!";
   }

   // NEW: Check if passwords match
   if($pass != $cpass){
      $message[] = "Passwords do not match!";
   }

   $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $check->execute([$email]);

   if($check->rowCount() > 0){
      $message[] = "User already exists!";
   }

   if(empty($message)){
      // Encrypt after all checks are passed
      $secure_pass = md5($pass); 

      $insert = $conn->prepare("
          INSERT INTO users(name, email, password, user_type)
          VALUES(?,?,?,?)
      ");

      if($insert->execute([$name, $email, $secure_pass, $user_type])){
          $show_success = true;
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
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
      body{
         font-family: 'Poppins', sans-serif;
         display: flex;
         justify-content: center;
         align-items: center;
         min-height: 100vh; /* Changed to min-height for better scrolling on small screens */
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
         background: #3d3d3d;
         color: white;
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
         font-size: 14px;
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
      <!-- Added Confirm Password input -->
      <input type="password" name="cpass" placeholder="Confirm Password" required>

      <button type="submit" name="submit">Register Now</button>
      <p>Already have an account? <a href="login.php">Login here</a></p>
   </form>

</div>

<script>
    <?php if($show_success): ?>
        Swal.fire({
            title: 'Success!',
            text: 'Account created successfully for <?php echo $name; ?>',
            icon: 'success',
            background: '#2c2c2c',
            color: '#ffffff',
            confirmButtonColor: '#2ecc71',
            confirmButtonText: 'Login Now'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            }
        });
    <?php endif; ?>
</script>

</body>
</html>
