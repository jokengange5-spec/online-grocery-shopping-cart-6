<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
};

$success = false;
$error = false;

if(isset($_POST['send'])){

   $name = $_POST['name'];
   $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');

   $email = $_POST['email'];
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Ang EMAIL okay pa, STRING ra ang deprecated

   $number = $_POST['number'];
   $number = htmlspecialchars($_POST['number'], ENT_QUOTES, 'UTF-8');

   $msg = $_POST['msg'];
   $msg = htmlspecialchars($_POST['msg'], ENT_QUOTES, 'UTF-8');

   $select_message = $conn->prepare("SELECT * FROM message WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_message->execute([$name, $email, $number, $msg]);

   if($select_message->rowCount() > 0){
      $error = true;
   }else{
      $insert_message = $conn->prepare("INSERT INTO message(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $msg]);
      $success = true;
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact Us - Joken's Grocery</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

   <style>
      @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

      :root{
         --green: #27ae60;
         --black: #333;
         --white: #fff;
         --light-bg: #f6f6f6;
         --border: .1rem solid rgba(0,0,0,.1);
         --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }

      *{
         margin:0; padding:0;
         box-sizing: border-box;
         font-family: 'Poppins', sans-serif;
         text-decoration: none;
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

      .contact {
         padding: 5rem 5%;
         max-width: 1200px;
         margin: 0 auto;
      }

      .title {
         text-align: center;
         margin-bottom: 3rem;
         font-size: 2.5rem;
         color: var(--black);
         text-transform: uppercase;
      }

      /* Grid Layout para sa Laptop */
      .contact-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(30rem, 1fr));
         gap: 2rem;
         align-items: flex-start;
      }

      /* Form Styling */
      .contact form {
         background-color: var(--white);
         padding: 3rem;
         border-radius: 1.5rem;
         box-shadow: var(--shadow);
         border: var(--border);
         text-align: center;
      }

      .contact form .box {
         width: 100%;
         background-color: var(--light-bg);
         border-radius: .5rem;
         padding: 1.2rem 1.4rem;
         font-size: 1.1rem;
         color: var(--black);
         margin: 1rem 0;
         border: var(--border);
      }

      .contact form textarea {
         height: 15rem;
         resize: none;
      }

      .contact form .btn {
         display: inline-block;
         margin-top: 1rem;
         background-color: var(--green);
         color: var(--white);
         padding: 1rem 3rem;
         border-radius: .5rem;
         font-size: 1.2rem;
         font-weight: 600;
         cursor: pointer;
         width: 100%;
         transition: .3s;
      }

      .contact form .btn:hover {
         background-color: var(--black);
      }

      /* Info Boxes Styling */
      .contact-info {
         display: grid;
         grid-template-columns: 1fr;
         gap: 1.5rem;
      }

      .info-box {
         background-color: var(--white);
         padding: 2rem;
         border-radius: 1rem;
         box-shadow: var(--shadow);
         border: var(--border);
         text-align: center;
         transition: .3s;
      }

      .info-box:hover {
         border-color: var(--green);
      }

      .info-box i {
         height: 5rem;
         width: 5rem;
         line-height: 5rem;
         font-size: 2rem;
         background-color: var(--light-bg);
         color: var(--green);
         border-radius: 50%;
         margin-bottom: 1rem;
      }

      .info-box h3 {
         font-size: 1.5rem;
         color: var(--black);
         margin-bottom: .5rem;
      }

      .info-box p {
         font-size: 1.1rem;
         color: var(--light-color);
      }

      /* Responsive Adjustment */
      @media (max-width: 768px) {
         .contact-container {
            grid-template-columns: 1fr;
         }
      }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="contact">

   <h1 class="title">Get In Touch</h1>

   <div class="contact-container">

      <!-- Contact Info Cards -->
      <div class="contact-info">
         <div class="info-box">
            <i class="fas fa-phone"></i>
            <h3>Our Number</h3>
            <p>+63 912 345 6789</p>
         </div>

         <div class="info-box">
            <i class="fas fa-envelope"></i>
            <h3>Our Email</h3>
            <p>support@jokengrocery.com</p>
         </div>

         <div class="info-box">
            <i class="fas fa-map-marker-alt"></i>
            <h3>Office Address</h3>
            <p>Cagayan de Oro City, Philippines</p>
         </div>
      </div>

      <!-- Message Form -->
      <form action="" method="POST">
         <input type="text" name="name" class="box" required placeholder="Enter your name">
         <input type="email" name="email" class="box" required placeholder="Enter your email">
         <input type="number" name="number" min="0" class="box" required placeholder="Enter your number">
         <textarea name="msg" class="box" required placeholder="Enter your message" cols="30" rows="10"></textarea>
         <input type="submit" value="Send Message" class="btn" name="send">
      </form>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

<!-- SweetAlerts -->
<?php if($success): ?>
<script>
Swal.fire({
   icon: 'success',
   title: 'Salamat!',
   text: 'Imong mensahe malampusong na-ipadala.',
   confirmButtonColor: '#27ae60'
});
</script>
<?php endif; ?>

<?php if($error): ?>
<script>
Swal.fire({
   icon: 'warning',
   title: 'Oops...',
   text: 'Kini nga mensahe na-ipadala na nimo kaniadto.',
   confirmButtonColor: '#d33'
});
</script>
<?php endif; ?>

</body>
</html>
