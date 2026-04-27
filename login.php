<?php

@include 'config.php';
session_start();

if(isset($_POST['submit'])){

   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $pass = md5($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $rowCount = $stmt->rowCount();  

   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($rowCount > 0){

      if($row['user_type'] == 'admin'){
         $_SESSION['admin_id'] = $row['id'];
         header('location:admin_page.php');

      }elseif($row['user_type'] == 'user'){
         $_SESSION['user_id'] = $row['id'];
         header('location:home.php');

      }else{
         $message[] = 'no user found!';
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

/* SAME BACKGROUND SA INDEX */
body{
   height:100vh;
   display:flex;
   align-items:center;
   justify-content:center;
   background: url('image products/picture7.jpg') no-repeat center center fixed;
   background-size: cover;
}

body::before{
   content:'';
   position:fixed;
   width:100%;
   height:100%;
   background:rgba(0,0,0,0.5);
   z-index:-1;
}

/* LOGIN CARD */
.form-container{
   width:370px;
   padding:40px;
   border-radius:25px;
   background: rgba(255,255,255,0.15);
   backdrop-filter: blur(18px);
   box-shadow: 0 10px 40px rgba(0,0,0,0.2);
   text-align:center;
   color:white;
   animation:fadeIn 0.6s ease;
}

@keyframes fadeIn{
   from{opacity:0; transform:translateY(20px);}
   to{opacity:1; transform:translateY(0);}
}

.form-container h3{
   font-size:26px;
   margin-bottom:20px;
   font-weight:700;
}

/* INPUT */
.box{
   width:100%;
   padding:12px;
   margin:10px 0;
   border:none;
   border-radius:12px;
   outline:none;
   background: rgba(255,255,255,0.25);
   color:white;
}

.box::placeholder{
   color:#eee;
}

/* BUTTON */
.btn{
   width:100%;
   padding:12px;
   border:none;
   border-radius:12px;
   background: linear-gradient(45deg,#2ecc71,#27ae60);
   color:white;
   font-weight:700;
   cursor:pointer;
   transition:0.3s;
}

.btn:hover{
   transform:scale(1.05);
   box-shadow:0 8px 20px rgba(0,0,0,0.3);
}

/* LINK */
p{
   margin-top:15px;
   font-size:14px;
}

p a{
   color:#2ecc71;
   text-decoration:none;
   font-weight:600;
}

p a:hover{
   text-decoration:underline;
}

/* MESSAGE */
.message{
   position:absolute;
   top:20px;
   background:#e74c3c;
   padding:10px 20px;
   border-radius:12px;
   color:#fff;
   display:flex;
   align-items:center;
   gap:10px;
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

   <form action="" method="POST">
      <h3><i class="fas fa-user-circle"></i> Login</h3>

      <input type="email" name="email" class="box" placeholder="Enter your email" required>
      <input type="password" name="pass" class="box" placeholder="Enter your password" required>

      <input type="submit" value="Login Now" class="btn" name="submit">

      <p>Don't have an account? <a href="register.php">Register now</a></p>
   </form>

</section>

</body>
</html>