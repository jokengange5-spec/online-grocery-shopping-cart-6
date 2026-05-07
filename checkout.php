<?php
ob_start(); 
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit(); 
}

$message = [];

if(isset($_POST['order'])){
   $name = htmlspecialchars(trim($_POST['name']));
   $number = htmlspecialchars(trim($_POST['number']));
   $email = htmlspecialchars(trim($_POST['email']));
   $method = htmlspecialchars(trim($_POST['method']));
   
   $address = 'flat no. '. $_POST['flat'] .' '. $_POST['street'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = htmlspecialchars($address); 
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = []; 
   $cart_items_detail = [];
   $out_of_stock = false; 
   $items_out_of_stock = []; // Sudlanan para sa ngalan sa mga items nga kulang og stock

   $cart_query = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   
   if($cart_query->rowCount() > 0){
      while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
         $p_id = $cart_item['product_id'] ?? $cart_item['pid'] ?? null;
         
         $check_stock = $conn->prepare("SELECT name, stock FROM products WHERE id = ?");
         $check_stock->execute([$p_id]);
         $product_info = $check_stock->fetch(PDO::FETCH_ASSOC);

         if($cart_item['quantity'] > $product_info['stock']){
            $out_of_stock = true;
            $items_out_of_stock[] = '"' . $product_info['name'] . '"';
         }

         $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $cart_items_detail[] = [
            'pid' => $p_id,
            'name' => $cart_item['name'],
            'quantity' => $cart_item['quantity'],
            'price' => $cart_item['price']
         ];
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ', $cart_products);

   if($cart_total == 0){
      $message[] = 'Your cart is empty';
   } elseif($out_of_stock) {
      $product_names_str = implode(', ', array_unique($items_out_of_stock));
      $message[] = 'The quantity of ' . $product_names_str . ' that you entered exceeds the available stocks.';
   } else {
      if($method == 'gcash'){
          $_SESSION['pending_order'] = [
             'user_id' => $user_id,
             'name' => $name,
             'number' => $number,
             'email' => $email,
             'method' => $method,
             'address' => $address,
             'total_products' => $total_products,
             'total_price' => $cart_total,
             'placed_on' => $placed_on,
             'cart_items' => $cart_items_detail
          ];
          header('location:gcash_payment.php');
          exit();
      } else {
          $insert_order = $conn->prepare("INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?)");
          $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on, 'pending']);

          foreach($cart_items_detail as $item){
             if($item['pid']){
                $deduct_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $deduct_stock->execute([$item['quantity'], $item['pid']]);
             }
          }

          $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
          $delete_cart->execute([$user_id]);

          $order_success = true;
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout | Modern Grocery</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   
   <style>
      :root{
          --green: #27ae60;
          --black: #333;
          --white: #fff;
          --light-bg: #f6f6f6;
          --border: 1px solid #ddd;
          --shadow: 0 .5rem 1rem rgba(0,0,0,.1);
          --gcash-blue: #007bff;
      }

      body {
          background-color: var(--light-bg);
          font-family: 'Poppins', sans-serif;
          margin: 0; padding: 0;
      }

      .checkout-container {
          max-width: 1200px;
          margin: 2rem auto;
          padding: 0 2rem;
          display: flex;
          flex-wrap: wrap;
          gap: 2rem;
          align-items: flex-start;
      }

      .display-orders {
          flex: 1 1 40rem;
          background: var(--white);
          padding: 2rem;
          border-radius: 1rem;
          box-shadow: var(--shadow);
          border: var(--border);
      }

      .display-orders h3 {
          font-size: 2rem;
          color: var(--black);
          margin-bottom: 1.5rem;
          border-bottom: var(--border);
          padding-bottom: 1rem;
      }

      .display-orders p {
          display: flex;
          justify-content: space-between;
          font-size: 1.6rem;
          color: #666;
          margin: 1rem 0;
          padding: 0.5rem 0;
      }

      .display-orders p span {
          color: var(--green);
          font-weight: bold;
      }

      .grand-total {
          margin-top: 1.5rem;
          padding-top: 1.5rem;
          border-top: 2px solid var(--light-bg);
          font-size: 2.2rem;
          color: var(--black);
          display: flex;
          justify-content: space-between;
      }

      .grand-total span {
          color: var(--green);
          font-weight: 800;
      }

      .checkout-orders {
          flex: 1 1 60rem;
          background: var(--white);
          padding: 2rem;
          border-radius: 1rem;
          box-shadow: var(--shadow);
          border: var(--border);
      }

      .checkout-orders h3 {
          font-size: 2.2rem;
          color: var(--black);
          margin-bottom: 2rem;
          text-transform: capitalize;
      }

      .checkout-orders .flex {
          display: flex;
          flex-wrap: wrap;
          gap: 1.5rem;
      }

      .checkout-orders .inputBox {
          flex: 1 1 28rem;
      }

      .checkout-orders .inputBox span {
          display: block;
          font-size: 1.4rem;
          color: #666;
          margin-bottom: 0.8rem;
      }

      .checkout-orders .inputBox .box {
          width: 100%;
          background: var(--light-bg);
          padding: 1.2rem 1.4rem;
          font-size: 1.6rem;
          color: var(--black);
          border: var(--border);
          border-radius: .5rem;
      }

      .checkout-orders .inputBox .box:focus {
          border-color: var(--green);
          background: var(--white);
      }

      .btn {
          width: 100%;
          margin-top: 2rem;
          padding: 1.5rem;
          font-size: 1.8rem;
          background: var(--green);
          color: var(--white);
          border: none;
          border-radius: .5rem;
          cursor: pointer;
          transition: all .3s ease;
          font-weight: 600;
          text-transform: uppercase;
          letter-spacing: 1px;
      }

      .btn:hover {
          background: var(--black);
          transform: translateY(-3px);
          box-shadow: 0 1rem 2rem rgba(0,0,0,0.2);
      }

      .btn.disabled {
          background: #ccc;
          cursor: not-allowed;
          opacity: 0.7;
      }

      .payment-info {
          background: #e8f5e9;
          padding: 1rem 1.5rem;
          border-radius: 0.5rem;
          margin: 1rem 0;
          border-left: 4px solid var(--green);
          display: none;
      }

      .payment-info.gcash {
          background: #e3f2fd;
          border-left-color: var(--gcash-blue);
      }

      .payment-info.show {
          display: block;
      }

      .payment-info i {
          margin-right: 0.5rem;
      }

      .payment-info.gcash i {
          color: var(--gcash-blue);
      }

      .message {
          background: #fff;
          padding: 1rem 2rem;
          margin: 1rem auto;
          width: 80%;
          border-radius: 0.5rem;
          box-shadow: var(--shadow);
          text-align: center;
          font-size: 1.6rem;
          color: #e74c3c;
      }

      @media (max-width: 768px) {
          .checkout-container { flex-direction: column; }
          .display-orders, .checkout-orders { flex: 1 1 100%; }
      }

      .popup-message {
          position: fixed;
          top: 50%; left: 50%;
          transform: translate(-50%, -50%);
          background: var(--white);
          padding: 2rem 4rem;
          border-radius: 1rem;
          box-shadow: 0 1rem 3rem rgba(0,0,0,0.3);
          z-index: 10000;
          text-align: center;
          border-top: .5rem solid var(--green);
          display: none;
      }

      .popup-message i {
          font-size: 5rem;
          color: var(--green);
          margin-bottom: 1rem;
      }

      .popup-message p {
          font-size: 2rem;
          color: var(--black);
          font-weight: 600;
      }

      .popup-overlay {
          position: fixed;
          top: 0; left: 0;
          width: 100%; height: 100%;
          background: rgba(0,0,0,0.5);
          z-index: 9999;
          display: none;
      }
   </style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="checkout-container">

   <section class="display-orders">
      <h3>🛒 Order Summary</h3>
      <?php
         $cart_grand_total = 0;
         $select_cart_items = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
         $select_cart_items->execute([$user_id]);
         if($select_cart_items->rowCount() > 0){
            while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
               $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
               $cart_grand_total += $cart_total_price;
      ?>
      <p> 
         <?= $fetch_cart_items['name']; ?> 
         <span>₱<?= number_format($fetch_cart_items['price'], 2); ?> x <?= $fetch_cart_items['quantity']; ?></span> 
      </p>
      <?php
         }
      }else{
         echo '<p class="empty">Your cart is empty!</p>';
      }
      ?>
      <div class="grand-total">Total Amount: <span>₱<?= number_format($cart_grand_total, 2); ?></span></div>
   </section>

   <section class="checkout-orders">
      <form action="" method="POST" id="checkoutForm">
         <h3>🏠 Shipping Details</h3>
         <div class="flex">
            <div class="inputBox">
               <span>Full Name :</span>
               <input type="text" name="name" placeholder="Enter your name" class="box" required>
            </div>
            <div class="inputBox">
               <span>Phone Number :</span>
               <input type="number" name="number" placeholder="09XX XXX XXXX" class="box" required>
            </div>
            <div class="inputBox">
               <span>Email Address :</span>
               <input type="email" name="email" placeholder="example@email.com" class="box" required>
            </div>
            <div class="inputBox">
               <span>Payment Method :</span>
               <select name="method" class="box" id="paymentMethod" required onchange="showPaymentInfo()">
                  <option value="" selected disabled>Select Payment Method</option>
                  <option value="cash on delivery">Cash on Delivery</option>
                  <option value="credit card">Credit Card</option>
                  <option value="gcash">GCash</option>
                  <option value="paypal">PayPal</option>
               </select>
            </div>
            <div class="inputBox">
               <span>Flat/House No. :</span>
               <input type="text" name="flat" placeholder="e.g. Blk 1 Lot 2" class="box" required>
            </div>
            <div class="inputBox">
               <span>Street Name :</span>
               <input type="text" name="street" placeholder="e.g. Orchid St." class="box" required>
            </div>
            <div class="inputBox">
               <span>City :</span>
               <input type="text" name="city" placeholder="e.g. Manila" class="box" required>
            </div>
            <div class="inputBox">
               <span>Province :</span>
               <input type="text" name="state" placeholder="e.g. Metro Manila" class="box" required>
            </div>
            <div class="inputBox">
               <span>Country :</span>
               <input type="text" name="country" value="Philippines" class="box" readonly>
            </div>
            <div class="inputBox">
               <span>Pin Code :</span>
               <input type="number" min="0" name="pin_code" placeholder="e.g. 1000" class="box" required>
            </div>
         </div>
         
         <div class="payment-info gcash" id="gcashInfo">
            <i class="fas fa-info-circle"></i>
            <strong>GCash Payment:</strong> You will be redirected to GCash to complete your payment after clicking "Place Order".
         </div>
         
         <input type="submit" name="order" class="btn <?= ($cart_grand_total > 0)?'':'disabled'; ?>" value="Place Order">
      </form>
   </section>

</div>

<?php include 'footer.php'; ?>

<div class="popup-overlay" id="overlay"></div>
<div class="popup-message" id="successPopup">
   <i class="fas fa-check-circle"></i>
   <p>Order Placed Successfully!</p>
</div>

<?php if(isset($order_success)): ?>
<script>
   document.getElementById('successPopup').style.display = 'block';
   document.getElementById('overlay').style.display = 'block';
   setTimeout(function(){
      window.location.href = 'orders.php';
   }, 3000);
</script>
<?php endif; ?>

<script>
function showPaymentInfo() {
   var method = document.getElementById('paymentMethod').value;
   var gcashInfo = document.getElementById('gcashInfo');
   if(method == 'gcash') {
      gcashInfo.classList.add('show');
   } else {
      gcashInfo.classList.remove('show');
   }
}
</script>

<script src="js/script.js"></script>

</body>
</html>
