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
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   
   <style>
      @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap');

      :root{
          --primary: #27ae60;
          --secondary: #2ecc71;
          --dark: #2c3e50;
          --white: #ffffff;
          --red: #e74c3c;
          --light-gray: #f8f9fa;
          --transition: all 0.3s ease;
      }

      body {
          background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('image products/picture7.jpg') no-repeat;
          background-size: cover;
          background-position: center;
          background-attachment: fixed;
          font-family: 'Outfit', sans-serif;
          margin: 0;
          display: flex;
          flex-direction: column;
          min-height: 100vh;
      }

      .update-profile {
          flex: 1;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 40px 20px;
      }

      .container-card {
          width: 100%;
          max-width: 900px;
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(10px);
          border-radius: 20px;
          overflow: hidden;
          box-shadow: 0 15px 35px rgba(0,0,0,0.2);
      }

      .form-header {
          background: var(--primary);
          color: white;
          padding: 30px;
          text-align: center;
      }

      .form-header h1 {
          margin: 0;
          font-size: 2rem;
          letter-spacing: 1px;
      }

      .form-header p {
          margin-top: 5px;
          opacity: 0.9;
          font-weight: 300;
      }

      form {
          padding: 40px;
      }

      .form-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
          gap: 40px;
      }

      .section-title {
          font-size: 1.2rem;
          color: var(--primary);
          margin-bottom: 20px;
          border-bottom: 2px solid var(--light-gray);
          padding-bottom: 10px;
          display: flex;
          align-items: center;
          gap: 10px;
      }

      .inputBox {
          margin-bottom: 15px;
      }

      .inputBox span {
          display: block;
          font-size: 0.9rem;
          color: #666;
          margin-bottom: 8px;
          font-weight: 600;
      }

      .box {
          width: 100%;
          padding: 12px 15px;
          font-size: 1rem;
          border: 2px solid #eee;
          border-radius: 8px;
          background: var(--light-gray);
          transition: var(--transition);
          box-sizing: border-box;
      }

      .box:focus {
          border-color: var(--primary);
          background: #fff;
          outline: none;
      }

      .btn-container {
          margin-top: 40px;
          display: flex;
          justify-content: center;
          gap: 15px;
          flex-wrap: wrap;
      }

      .action-btn {
          padding: 12px 30px;
          font-size: 1rem;
          border-radius: 50px;
          cursor: pointer;
          border: none;
          text-decoration: none;
          font-weight: 600;
          transition: var(--transition);
          display: inline-flex;
          align-items: center;
          gap: 8px;
      }

      .btn-save { background: var(--primary); color: white; }
      .btn-save:hover { background: var(--secondary); transform: translateY(-2px); }

      .btn-back { background: var(--dark); color: white; }
      .btn-back:hover { opacity: 0.9; transform: translateY(-2px); }

      .btn-logout { background: #fee2e2; color: var(--red); }
      .btn-logout:hover { background: var(--red); color: white; transform: translateY(-2px); }

      @media (max-width: 768px) {
          form { padding: 20px; }
          .form-header h1 { font-size: 1.5rem; }
      }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="update-profile">

   <div class="container-card">
      <div class="form-header">
         <h1><i class="fas fa-user-circle"></i> Profile Settings</h1>
         <p>Manage your account details and security</p>
      </div>

      <form action="" method="POST">
         <div class="form-grid">
            <!-- Account Section -->
            <div class="section">
               <h2 class="section-title"><i class="fas fa-id-card"></i> Personal Info</h2>
               <div class="inputBox">
                  <span>Username</span>
                  <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" class="box" required>
               </div>
               <div class="inputBox">
                  <span>Email Address</span>
                  <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" class="box" required>
               </div>
            </div>

            <!-- Password Section -->
            <div class="section">
               <h2 class="section-title"><i class="fas fa-shield-alt"></i> Security</h2>
               <div class="inputBox">
                  <span>Current Password</span>
                  <input type="password" name="update_pass" placeholder="••••••••" class="box">
               </div>
               <div class="inputBox">
                  <span>New Password</span>
                  <input type="password" name="new_pass" placeholder="••••••••" class="box">
               </div>
               <div class="inputBox">
                  <span>Confirm New Password</span>
                  <input type="password" name="confirm_pass" placeholder="••••••••" class="box">
               </div>
            </div>
         </div>

         <div class="btn-container">
            <button type="submit" name="update_profile" class="action-btn btn-save">
               <i class="fas fa-check"></i> Save Changes
            </button>
            <a href="home.php" class="action-btn btn-back">
               <i class="fas fa-arrow-left"></i> Home
            </a>
            <a href="javascript:void(0);" class="action-btn btn-logout" id="logout-link">
               <i class="fas fa-sign-out-alt"></i> Logout
            </a>
         </div>
      </form>
   </div>

</section>

<?php include 'footer.php'; ?>

<script>
// Logout Confirmation
document.getElementById('logout-link').addEventListener('click', function() {
    Swal.fire({
        title: 'Logout?',
        text: "You will need to login again to access your cart.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#27ae60',
        cancelButtonColor: '#2c3e50',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Stay logged in'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'update_profile.php?logout=1';
        }
    });
});

// Toast Notifications
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
