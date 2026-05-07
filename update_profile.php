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

   // Update basic info
   $update_profile = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);
   $message[] = ['text' => 'Profile information updated!', 'type' => 'success'];

   // Password Update Logic
   if(!empty($_POST['update_pass']) || !empty($_POST['new_pass'])){
      $old_pass = md5($_POST['update_pass']);
      $new_pass = md5($_POST['new_pass']);
      $confirm_pass = md5($_POST['confirm_pass']);

      if($old_pass != $fetch_profile['password']){
         $message[] = ['text' => 'Old password does not match!', 'type' => 'error'];
      }elseif($new_pass != $confirm_pass){
         $message[] = ['text' => 'Confirm password does not match!', 'type' => 'error'];
      }else{
         $update_pass_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $user_id]);
         $message[] = ['text' => 'Password updated successfully!', 'type' => 'success'];
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
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
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
         background-attachment: fixed;
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
          font-weight: 500;
          display: inline-block;
      }

      .btn { background: var(--primary-color); }
      .btn:hover { background: var(--secondary-color); }
      .option-btn { background: var(--black); }
      .logout-btn { background: var(--red); }

      .swal2-popup { font-family: 'Poppins', sans-serif !important; border-radius: 15px !important; }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="update-profile">

   <form action="" method="POST">
      
      <h1 class="title">Update My Profile</h1>

      <div class="flex">
         <div class="inputBox">
            <span>Username :</span>
            <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" class="box" required>
            <span>Email :</span>
            <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" class="box" required>
         </div>

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
         <a href="javascript:void(0);" class="logout-btn" id="logout-link">Logout</a>
      </div>

   </form>

</section>

<?php include 'footer.php'; ?>

<script>
// 1. Logout Confirmation logic
document.getElementById('logout-link').addEventListener('click', function() {
    Swal.fire({
        title: 'Logout?',
        text: "Are you sure you want to logout?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#2c3e50',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'update_profile.php?logout=1';
        }
    });
});

// 2. Success and Error Notifications (Toasts)
<?php if(isset($message)): ?>
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });

    <?php foreach($message as $msg): ?>
        Toast.fire({
          icon: '<?= $msg['type']; ?>',
          title: '<?= $msg['text']; ?>'
        });
    <?php endforeach; ?>
<?php endif; ?>
</script>

</body>
</html>
