<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $delete_message = $conn->prepare("DELETE FROM message WHERE id = ?");
   $delete_message->execute([$delete_id]);
   header('location:admin_contacts.php');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>messages</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
   <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap');

body {
   background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('image products/picture5.jpg') no-repeat;
   background-size: cover;
   background-position: center;
   background-attachment: fixed; /* Para dili mo-scroll ang background */
   font-family: 'Poppins', sans-serif;
   margin: 0;
   padding: 0;
}

/* TITLE */
.title{
   text-align:center;
   font-size:2.2rem;
   margin:30px 0;
   font-weight:700;
   background: linear-gradient(90deg,#00f260,#0575e6);
   -webkit-background-clip:text;
   -webkit-text-fill-color:transparent;
}

/* GRID */
.box-container{
   display:grid;
   grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
   gap:25px;
   padding:30px;
}

/* CARD */
.box{
   background:rgba(255,255,255,0.08);
   backdrop-filter:blur(15px);
   padding:20px;
   border-radius:20px;
   box-shadow:0 10px 30px rgba(0,0,0,0.4);
   transition:0.3s;
   position:relative;
}

.box:hover{
   transform:translateY(-8px);
}

/* TEXT */
.box p{
   font-size:14px;
   margin:8px 0;
   color:#ddd;
}

.box span{
   color:#fff;
   font-weight:500;
}

/* MESSAGE CONTENT */
.message-text{
   background:rgba(0,0,0,0.3);
   padding:10px;
   border-radius:10px;
   margin-top:8px;
   font-size:13px;
   line-height:1.4;
}

/* DELETE BUTTON */
.delete-btn{
   display:block;
   text-align:center;
   margin-top:15px;
   padding:10px;
   border-radius:10px;
   text-decoration:none;
   font-weight:600;
   background:#e74c3c;
   color:white;
   transition:0.3s;
}

.delete-btn:hover{
   transform:scale(1.05);
}

/* EMPTY */
.empty{
   text-align:center;
   font-size:18px;
   color:#bbb;
}

/* ICON DECORATION */
.box::before{
   content:'';
   position:absolute;
   top:-30px;
   right:-30px;
   width:100px;
   height:100px;
   background:rgba(0,242,96,0.2);
   border-radius:50%;
}
</style>

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="messages">

   <h1 class="title">messages</h1>

   <div class="box-container">

   <?php
      $select_message = $conn->prepare("SELECT * FROM message");
      $select_message->execute();
      if($select_message->rowCount() > 0){
         while($fetch_message = $select_message->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <p> user id : <span><?= $fetch_message['user_id']; ?></span> </p>
      <p> name : <span><?= $fetch_message['name']; ?></span> </p>
      <p> number : <span><?= $fetch_message['number']; ?></span> </p>
      <p> email : <span><?= $fetch_message['email']; ?></span> </p>
      <p> message : <span><?= $fetch_message['message']; ?></span> </p>
      <a href="admin_contacts.php?delete=<?= $fetch_message['id']; ?>" onclick="return confirm('delete this message?');" class="delete-btn">delete</a>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">you have no messages!</p>';
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
