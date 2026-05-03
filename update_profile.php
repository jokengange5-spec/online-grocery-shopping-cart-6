<?php

@include 'config.php';
session_start();

// Logout logic
if(isset($_GET['logout'])){
   session_destroy();
   header('location:login.php');
   exit;
}

if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit;
}

$user_id = $_SESSION['user_id'];

$select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(!$fetch_profile){
   echo "User not found!";
   exit;
}

/* UPDATE PROFILE LOGIC */
if(isset($_POST['update_profile'])){

   $name = htmlspecialchars($_POST['name']);
   $email = htmlspecialchars($_POST['email']);

   $update_profile = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);

   // Image Update
   if(!empty($_FILES['image']['name'])){
      $image = $_FILES['image']['name'];
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/'.$image;

      if($image_size > 2000000){
         $message[] = 'Image is too large (max 2MB)!';
      }else{
         $update_image = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
         $update_image->execute([$image, $user_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         $message[] = 'Profile picture updated!';
      }
   }

   // Password Update
   if(!empty($_POST['update_pass']) || !empty($_POST['new_pass'])){
      $old_pass = md5($_POST['update_pass']);
      $new_pass = md5($_POST['new_pass']);
      $confirm_pass = md5($_POST['confirm_pass']);

      if($old_pass != $fetch_profile['password']){
         $message[] = 'Old password does not match!';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'Confirm password does not match!';
      }else{
         $update_pass_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $user_id]);
         $message[] = 'Password updated successfully!';
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
   <title>Update Profile - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

      :root{
         --primary-color: #2ecc71;
         --secondary-color: #27ae60;
         --red: #e74c3c;
         --black: #2c3e50;
         --white: #fff;
         --light-bg: #f0f2f5;
         --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
         --border: .1rem solid rgba(0,0,0,.1);
      }

     body {
   background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture7.jpg') no-repeat;
   background-size: cover;
   background-position: center;
   background-attachment: fixed; /* Para dili mo-scroll ang background */
   font-family: 'Poppins', sans-serif;
   margin: 0;
   padding: 0;
}

      .update-profile {
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
         padding: 2rem;
      }

      .update-profile form {
         width: 100%;
         max-width: 800px;
         background: var(--white);
         padding: 3rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         text-align: center;
      }

      .update-profile form .title {
         font-size: 2.5rem;
         color: var(--black);
         margin-bottom: 2rem;
         text-transform: uppercase;
         font-weight: 600;
      }

      .update-profile form img {
         height: 150px;
         width: 150px;
         border-radius: 50%;
         object-fit: cover;
         margin-bottom: 1rem;
         border: 5px solid var(--primary-color);
         padding: 5px;
      }

      /* Flexbox for Input Columns */
      .flex {
         display: flex;
         flex-wrap: wrap;
         gap: 2rem;
         text-align: left;
      }

      .flex .inputBox {
         flex: 1 1 35rem;
      }

      .flex .inputBox span {
         display: block;
         font-size: 1.5rem;
         color: #666;
         margin: 1rem 0;
      }

      .flex .inputBox .box {
         width: 100%;
         padding: 1.2rem 1.4rem;
         font-size: 1.6rem;
         border-radius: .5rem;
         background: var(--light-bg);
         border: var(--border);
         color: var(--black);
      }

      .btn-container {
         margin-top: 2rem;
         display: flex;
         gap: 1rem;
         justify-content: center;
         flex-wrap: wrap;
      }

      .btn, .option-btn, .logout-btn {
         padding: 1.2rem 3rem;
         font-size: 1.6rem;
         border-radius: .5rem;
         cursor: pointer;
         border: none;
         text-decoration: none;
         color: var(--white);
         transition: .3s;
      }

      .btn { background: var(--primary-color); }
      .btn:hover { background: var(--secondary-color); }

      .option-btn { background: var(--black); }
      .option-btn:hover { background: #1a252f; }

      .logout-btn { background: var(--red); }
      .logout-btn:hover { background: #c0392b; }

      .message {
         background: #d4edda;
         color: #155724;
         padding: 1rem;
         margin-bottom: 2rem;
         border-radius: .5rem;
         font-size: 1.6rem;
      }

      @media (max-width: 768px) {
         .update-profile form { padding: 2rem; }
      }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="update-profile">

   <form action="" method="POST" enctype="multipart/form-data">
      
      <h1 class="title">Update My Profile</h1>

      <!-- Profile Image Display -->
      <?php if(!empty($fetch_profile['image'])): ?>
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="Profile Picture">
      <?php else: ?>
         <img src="images/default-avatar.png" alt="Default Avatar">
      <?php endif; ?>

      <div class="flex">
         <!-- Personal Info Column -->
         <div class="inputBox">
            <span>Username :</span>
            <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" class="box" required>
            <span>Email :</span>
            <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" class="box" required>
            <span>Update Photo :</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
         </div>

         <!-- Password Update Column -->
         <div class="inputBox">
            <span>Old Password :</span>
            <input type="password" name="update_pass" placeholder="Enter old password" class="box">
            <span>New Password :</span>
            <input type="password" name="new_pass" placeholder="Enter new password" class="box">
            <span>Confirm Password :</span>
            <input type="password" name="confirm_pass" placeholder="Confirm new password" class="box">
         </div>
      </div>

      <div class="btn-container">
         <input type="submit" name="update_profile" value="Save Changes" class="btn">
         <a href="home.php" class="option-btn">Back to Home</a>
         <a href="update_profile.php?logout=1" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
      </div>

   </form>

</section>

<?php include 'footer.php'; ?>

</body>
</html>
