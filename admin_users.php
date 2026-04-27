<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_users = $conn->prepare("DELETE FROM users WHERE id = ?");
   $delete_users->execute([$delete_id]);
   header('location:admin_users.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>users</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

body{
   margin:0;
   font-family:'Poppins',sans-serif;
   background: linear-gradient(135deg,#0f172a,#1e293b,#0f172a);
   color:#fff;
}

/* TITLE */
.title{
   text-align:center;
   font-size:2.4rem;
   font-weight:800;
   margin:40px 0 20px;
   background: linear-gradient(90deg,#00f260,#0575e6);
   -webkit-background-clip:text;
   -webkit-text-fill-color:transparent;
}

/* GRID */
.user-accounts .box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));
   gap:25px;
   padding:30px;
}

/* CARD */
.user-accounts .box{
   background: rgba(255,255,255,0.08);
   backdrop-filter: blur(15px);
   border-radius:20px;
   padding:20px;
   text-align:center;
   box-shadow:0 10px 30px rgba(0,0,0,0.4);
   transition:0.3s ease;
   position:relative;
}

.user-accounts .box:hover{
   transform:translateY(-8px);
   box-shadow:0 15px 40px rgba(0,0,0,0.5);
}

/* IMAGE */
.user-accounts .box img{
   width:90px;
   height:90px;
   border-radius:50%;
   object-fit:cover;
   border:3px solid #00f260;
   margin-bottom:10px;
}

/* TEXT */
.user-accounts .box p{
   font-size:14px;
   color:#ddd;
   margin:6px 0;
}

.user-accounts .box span{
   color:#fff;
   font-weight:600;
}

/* USER TYPE */
.user-accounts .box p span[style]{
   color:orange;
   font-weight:700;
}

/* DELETE BUTTON */
.delete-btn{
   display:inline-block;
   margin-top:10px;
   padding:10px 14px;
   border-radius:10px;
   background: linear-gradient(45deg,#e74c3c,#c0392b);
   color:white;
   text-decoration:none;
   font-weight:600;
   transition:0.3s;
}

.delete-btn:hover{
   transform:scale(1.05);
}

/* HIDE ADMIN CARD */
.hidden{
   display:none !important;
}
</style>
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="user-accounts">

   <h1 class="title">User accounts</h1>

   <div class="box-container">

      <?php
         $select_users = $conn->prepare("SELECT * FROM users");
         $select_users->execute();
         while($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)){
      ?>
      <div class="box" style="<?php if($fetch_users['id'] == $admin_id){ echo 'display:none'; }; ?>">
         <img src="uploaded_img/<?= $fetch_users['image']; ?>" alt="">
         <p> user id : <span><?= $fetch_users['id']; ?></span></p>
         <p> username : <span><?= $fetch_users['name']; ?></span></p>
         <p> email : <span><?= $fetch_users['email']; ?></span></p>
         <p> user type : <span style=" color:<?php if($fetch_users['user_type'] == 'admin'){ echo 'orange'; }; ?>"><?= $fetch_users['user_type']; ?></span></p>
         <a href="admin_users.php?delete=<?= $fetch_users['id']; ?>" onclick="return confirm('delete this user?');" class="delete-btn">delete</a>
      </div>
      <?php
      }
      ?>
   </div>

</section>













<script src="js/script.js"></script>
<script>
let userBtn = document.querySelector('#user-btn');
let profile = document.querySelector('.profile');

userBtn.onclick = () => {
   profile.classList.toggle('active');
}
</script>
</body>
</html>