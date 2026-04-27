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
<style>@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

/* GLOBAL HEADER */
.header{
   position:sticky;
   top:0;
   left:0;
   width:100%;
   z-index:1000;
   background:rgba(255,255,255,0.75);
   backdrop-filter: blur(15px);
   box-shadow:0 8px 25px rgba(0,0,0,0.08);
   border-bottom:1px solid rgba(0,0,0,0.05);
}

/* FLEX CONTAINER */
.header .flex{
   display:flex;
   align-items:center;
   justify-content:space-between;
   padding:15px 30px;
}

/* LOGO */
.header .logo{
   font-size:22px;
   font-weight:800;
   text-decoration:none;
   background:linear-gradient(90deg,#2ecc71,#3498db);
   -webkit-background-clip:text;
   -webkit-text-fill-color:transparent;
}

.header .logo span{
   color:#2c3e50;
}

/* NAVBAR */
.header .navbar a{
   margin:0 12px;
   text-decoration:none;
   font-weight:600;
   color:#2c3e50;
   padding:8px 12px;
   border-radius:10px;
   transition:0.3s;
}

.header .navbar a:hover{
   background:linear-gradient(45deg,#2ecc71,#27ae60);
   color:white;
   transform:scale(1.05);
}

/* ICONS */
.header .icons div{
   font-size:18px;
   margin-left:15px;
   cursor:pointer;
   transition:0.3s;
   color:#2c3e50;
}

.header .icons div:hover{
   color:#2ecc71;
   transform:scale(1.2);
}

/* PROFILE BOX */
.profile{
   position:absolute;
   top:80px;
   right:20px;
   width:260px;
   background:white;
   border-radius:20px;
   box-shadow:0 10px 30px rgba(0,0,0,0.15);
   padding:20px;
   text-align:center;
   display:none;
}

.profile.active{
   display:block;
}

.profile img{
   width:80px;
   height:80px;
   border-radius:50%;
   object-fit:cover;
   margin-bottom:10px;
   border:3px solid #2ecc71;
}

.profile p{
   font-weight:700;
   margin-bottom:10px;
}

/* BUTTONS */
.btn,.delete-btn{
   display:block;
   margin:8px 0;
   padding:10px;
   border-radius:12px;
   text-decoration:none;
   font-weight:600;
   transition:0.3s;
}

.btn{
   background:linear-gradient(45deg,#2ecc71,#27ae60);
   color:white;
}

.delete-btn{
   background:linear-gradient(45deg,#e74c3c,#c0392b);
   color:white;
}

.btn:hover,.delete-btn:hover{
   transform:scale(1.05);
}

/* MESSAGE */
.message{
   background:#2ecc71;
   color:white;
   padding:12px 20px;
   margin:10px;
   border-radius:12px;
   display:flex;
   justify-content:space-between;
   align-items:center;
   animation:fadeIn 0.4s ease;
}

.message i{
   cursor:pointer;
}

@keyframes fadeIn{
   from{opacity:0; transform:translateY(-10px);}
   to{opacity:1; transform:translateY(0);}
}

/* MOBILE */
@media(max-width:768px){

   .header .navbar{
      position:absolute;
      top:70px;
      left:0;
      right:0;
      background:white;
      flex-direction:column;
      display:none;
      text-align:center;
      padding:20px;
   }

   .header .navbar.active{
      display:flex;
   }
}s
</style>
<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_page.php">Home</a>
         <a href="admin_products.php">Products</a>
         <a href="admin_orders.php">Orders</a>
         <a href="admin_users.php">Users</a>
         <a href="admin_contacts.php">Messages</a>
      </nav>

      <div class="icons">
         <div </div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <p><?= $fetch_profile['name']; ?></p>
         <a href="admin_update_profile.php" class="btn">update profile</a>
         <a href="logout.php" class="delete-btn">logout</a>
        

         
      </div>

   </div>

</header>



<script>
let menuBtn = document.querySelector('#menu-btn');
let navbar = document.querySelector('.navbar');

menuBtn.onclick = () => {
   navbar.classList.toggle('active');
};
</script>