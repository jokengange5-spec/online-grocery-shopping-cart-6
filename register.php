<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = md5($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   // FIXED: Removed backticks from `users`
   $select = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $select->execute([$email]);

   if($select->rowCount() > 0){
      $message[] = 'user email already exist!';
   }else{
      if($pass != $cpass){
         $message[] = 'confirm password not matched!';
      }else{
         // FIXED: Removed backticks from `users`
         $insert = $conn->prepare("INSERT INTO users(name, email, password, image) VALUES(?,?,?,?)");
         $insert->execute([$name, $email, $pass, $image]);

         if($insert){
            if($image_size > 2000000){
               $message[] = 'image size is too large!';
            }else{
               move_uploaded_file($image_tmp_name, $image_folder);
               $message[] = 'registered successfully!';
               header('location:login.php');
            }
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
   <title>register</title>

   <!-- font awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- your css (still included) -->
   <link rel="stylesheet" href="css/components.css">

   <!-- 💎 MAJESTIC UI DESIGN -->
   <style>
   body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #e8f5e9, #e3f2fd);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
   }

   .form-container {
      width: 100%;
      max-width: 420px;
      padding: 20px;
   }

   form {
      background: rgba(255, 255, 255, 0.9);
      padding: 30px 25px;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
      text-align: center;
      animation: fadeIn 0.6s ease;
   }

   @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
   }

   form h3 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #2c3e50;
      text-transform: uppercase;
      letter-spacing: 1px;
   }

   .box {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border-radius: 12px;
      border: 1px solid #ddd;
      outline: none;
      transition: 0.3s;
      font-size: 14px;
   }

   .box:focus {
      border-color: #2ecc71;
      box-shadow: 0 0 8px rgba(46, 204, 113, 0.3);
   }

   .btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 12px;
      margin-top: 12px;
      font-weight: bold;
      cursor: pointer;
      background: linear-gradient(45deg, #2ecc71, #27ae60);
      color: white;
      transition: 0.3s;
   }

   .btn:hover {
      transform: scale(1.05);
      box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
   }

   form p {
      margin-top: 12px;
      font-size: 14px;
   }

   form p a {
      color: #2ecc71;
      text-decoration: none;
      font-weight: 600;
   }

   form p a:hover {
      text-decoration: underline;
   }

   /* MESSAGE STYLE */
   .message {
      background: #fff;
      padding: 10px;
      margin: 10px auto;
      max-width: 400px;
      border-radius: 10px;
      box-shadow: 0 5px 10px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
   }

   .message i {
      cursor: pointer;
      color: red;
   }
   </style>
</head>
<body>

<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>
   
<section class="form-container">

   <form action="" enctype="multipart/form-data" method="POST">
      <h3>register now</h3>
      <input type="text" name="name" class="box" placeholder="enter your name" required>
      <input type="email" name="email" class="box" placeholder="enter your email" required>
      <input type="password" name="pass" class="box" placeholder="enter your password" required>
      <input type="password" name="cpass" class="box" placeholder="confirm your password" required>
      <input type="file" name="image" class="box" required accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="register now" class="btn" name="submit">
      <p>already have an account? <a href="login.php">login now</a></p>
   </form>

</section>


</body>
</html>