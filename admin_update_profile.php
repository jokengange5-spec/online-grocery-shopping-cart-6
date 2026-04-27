<?php

@include 'config.php';
session_start();

// ✔ CHECK LOGIN FIRST
if(!isset($_SESSION['admin_id'])){
   header('location:login.php');
   exit;
}

$admin_id = $_SESSION['admin_id'];

// ✔ FETCH PROFILE
$select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);


// ✔ UPDATE PROFILE
if(isset($_POST['update_profile'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $admin_id]);

   // IMAGE
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $old_image = $_POST['old_image'];

   if(!empty($image)){
      if($image_size > 2000000){
         $message[] = 'image too large!';
      }else{
         $update_image = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
         $update_image->execute([$image, $admin_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         unlink('uploaded_img/'.$old_image);
         $message[] = 'image updated!';
      }
   }

   // PASSWORD
   if(!empty($_POST['update_pass']) && !empty($_POST['new_pass']) && !empty($_POST['confirm_pass'])){

      $old_pass = md5($_POST['update_pass']);
      $new_pass = md5($_POST['new_pass']);
      $confirm_pass = md5($_POST['confirm_pass']);

      if($old_pass != $fetch_profile['password']){
         $message[] = 'old password not matched!';
      }
      elseif($new_pass != $confirm_pass){
         $message[] = 'confirm password not matched!';
      }
      else{
         $update_pass_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $admin_id]);
         $message[] = 'password updated successfully!';
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
   <title>update admin profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/components.css">

   <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

body{
   font-family: 'Poppins', sans-serif;
   background: linear-gradient(135deg, #1e1e2f, #2c2c54);
   margin:0;
   padding:0;
}

/* TITLE */
.title{
   text-align:center;
   font-size:32px;
   color:#fff;
   margin-top:30px;
   text-transform:uppercase;
   letter-spacing:2px;
}

/* MAIN SECTION */
.update-profile{
   display:flex;
   flex-direction:column;
   align-items:center;
   justify-content:center;
   padding:40px 20px;
}

/* FORM CARD */
.update-profile form{
   width:100%;
   max-width:850px;
   background: rgba(255,255,255,0.08);
   backdrop-filter: blur(12px);
   border-radius:20px;
   padding:30px;
   box-shadow: 0 10px 40px rgba(0,0,0,0.4);
   border:1px solid rgba(255,255,255,0.1);
}

/* PROFILE IMAGE */
.update-profile form img{
   width:120px;
   height:120px;
   border-radius:50%;
   object-fit:cover;
   display:block;
   margin:0 auto 20px auto;
   border:3px solid #fff;
   box-shadow:0 0 20px rgba(255,255,255,0.2);
}

/* FLEX LAYOUT */
.flex{
   display:flex;
   gap:20px;
   flex-wrap:wrap;
}

/* INPUT BOX */
.inputBox{
   flex:1;
   min-width:280px;
}

.inputBox span{
   display:block;
   color:#fff;
   font-size:14px;
   margin:10px 0 5px;
}

/* INPUT FIELD */
.box{
   width:100%;
   padding:12px 15px;
   border-radius:10px;
   border:none;
   outline:none;
   background: rgba(255,255,255,0.15);
   color:#fff;
   font-size:14px;
   transition:0.3s;
}

.box:focus{
   background: rgba(255,255,255,0.25);
   transform: scale(1.02);
}

/* BUTTON AREA */
.flex-btn{
   display:flex;
   justify-content:space-between;
   margin-top:25px;
   flex-wrap:wrap;
   gap:10px;
}

/* BUTTON STYLE */
.btn, .option-btn{
   padding:12px 20px;
   border-radius:10px;
   text-decoration:none;
   text-align:center;
   font-size:14px;
   font-weight:500;
   transition:0.3s;
   border:none;
   cursor:pointer;
}

/* MAIN BUTTON */
.btn{
   background: linear-gradient(45deg, #00c6ff, #0072ff);
   color:#fff;
   box-shadow:0 5px 15px rgba(0,114,255,0.4);
}

.btn:hover{
   transform:translateY(-2px);
}

/* BACK BUTTON */
.option-btn{
   background: rgba(255,255,255,0.15);
   color:#fff;
}

.option-btn:hover{
   background: rgba(255,255,255,0.3);
}

/* MESSAGE */
.message{
   background:#ff4757;
   color:#fff;
   padding:10px 15px;
   margin:10px auto;
   border-radius:8px;
   width:fit-content;
}
</style>

   

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="update-profile">

   <h1 class="title">update profile</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
      <div class="flex">
         <div class="inputBox">
            <span>username :</span>
            <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" placeholder="update username" required class="box">
            <span>email :</span>
            <input type="email" name="email" value="<?= $fetch_profile['email']; ?>" placeholder="update email" required class="box">
            <span>update pic :</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
            <input type="hidden" name="old_image" value="<?= $fetch_profile['image']; ?>">
         </div>
         <div class="inputBox">
            <input type="hidden" name="old_pass" value="<?= $fetch_profile['password']; ?>">
            <span>old password :</span>
            <input type="password" name="update_pass" placeholder="enter previous password" class="box">
            <span>new password :</span>
            <input type="password" name="new_pass" placeholder="enter new password" class="box">
            <span>confirm password :</span>
            <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
         </div>
      </div>
      <div class="flex-btn">
         <input type="submit" class="btn" value="update profile" name="update_profile">
         <a href="admin_page.php" class="option-btn">go back</a>
      </div>
   </form>



</section>













<script src="js/script.js"></script>

</body>
</html>