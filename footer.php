<style>
   .footer {
      background-color: var(--white);
      border-top: var(--border);
      padding: 5rem 5%;
   }

   .footer .box-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(22rem, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
   }

   .footer .box-container .box h3 {
      font-size: 2rem;
      color: var(--black);
      margin-bottom: 2rem;
      text-transform: uppercase;
   }

   .footer .box-container .box a, 
   .footer .box-container .box p {
      display: block;
      font-size: 1.1rem;
      color: var(--light-color);
      padding: 1rem 0;
      line-height: 1.5;
   }

   .footer .box-container .box a i, 
   .footer .box-container .box p i {
      color: var(--green);
      padding-right: 1rem;
      font-size: 1.4rem;
   }

   .footer .box-container .box a:hover {
      color: var(--green);
      text-decoration: underline;
   }

   .footer .credit {
      text-align: center;
      padding: 3rem 2rem;
      margin-top: 3rem;
      font-size: 1.5rem;
      color: var(--black);
      border-top: var(--border);
   }

   .footer .credit span {
      color: var(--green);
   }

   @media (max-width: 450px) {
      .footer .box-container {
         grid-template-columns: 1fr;
      }
   }
</style>

<footer class="footer">

   <section class="box-container">

      <div class="box">
         <h3>Quick Links</h3>
         <a href="home.php"> <i class="fas fa-angle-right"></i> Home</a>
         <a href="shop.php"> <i class="fas fa-angle-right"></i> Shop</a>
         <a href="about.php"> <i class="fas fa-angle-right"></i> About</a>
         <a href="contact.php"> <i class="fas fa-angle-right"></i> Contact</a>
      </div>

      <div class="box">
         <h3>Extra Links</h3>
         <a href="cart.php"> <i class="fas fa-angle-right"></i> Cart</a>
         <a href="wishlist.php"> <i class="fas fa-angle-right"></i> Wishlist</a>
         <a href="login.php"> <i class="fas fa-angle-right"></i> Login</a>
         <a href="register.php"> <i class="fas fa-angle-right"></i> Register</a>
      </div>

      <div class="box">
         <h3>Contact Info</h3>
         <p> <i class="fas fa-phone"></i> +639072024425 </p>
         <p> <i class="fas fa-phone"></i> +639072024425 </p>
         <p> <i class="fas fa-envelope"></i> gangejoken5@gmail.com </p>
         <p> <i class="fas fa-map-marker-alt"></i> Ipil, Philippines - 7040 </p>
      </div>

      <div class="box">
         <h3>Follow Us</h3>
         <a href="https://www.facebook.com/share/1DAXmuCQ5q/?mibextid=wwXIfr"> <i class="fab fa-facebook-f"></i> Facebook </a>
         <a href="#"> <i class="fab fa-twitter"></i> Twitter </a>
         <a href="#"> <i class="fab fa-instagram"></i> Instagram </a>
      </div>

   </section>

   <p class="credit"> &copy; copyright @ <?= date('Y'); ?> by <span>Kazumi / Joken's Grocery</span> | all rights reserved! </p>

</footer>


