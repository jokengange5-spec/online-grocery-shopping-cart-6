<?php
ob_start(); 
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit(); 
}

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

   $cart_query = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   
   if($cart_query->rowCount() > 0){
      while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
         $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ', $cart_products);

   if($cart_total == 0){
      $message[] = 'Your cart is empty';
   }else{
      $insert_order = $conn->prepare("INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);

      $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      // KINI ANG IMPORTANTE: I-save nato ang method sa variable para sa JavaScript
      $payment_method = $method;
      $order_success = true;
   }
}
?>

<!-- ... (imong CSS styles magpabilin) ... -->

<!-- ... (imong HTML structure magpabilin hangtod sa JavaScript sa ubos) ... -->

<?php if(isset($order_success)): ?>
<script>
   // 1. I-show ang Success Message una
   document.getElementById('successPopup').style.display = 'block';
   document.getElementById('overlay').style.display = 'block';

   // 2. Paghulat og 2-3 seconds para mabasa sa user ang success message
   setTimeout(function(){
      <?php if($payment_method == 'gcash'): ?>
         // I-REDIRECT SA GCASH PAYMENT (Pili lang sa duha sa ubos):
         
         // OPTION A: I-redirect sa imong Personal GCash QR Link (kung naa kay Me-QR o link)
         // window.location.href = 'https://m.gcash.com/app/pay?number=09123456789'; 
         
         // OPTION B: I-redirect sa usa ka page nga naay imong QR Code Image (Recommended)
         window.location.href = 'gcash_payment.php?amount=<?= $cart_total; ?>';
         
      <?php else: ?>
         // Kung COD o lain, balik sa orders page
         window.location.href = 'orders.php';
      <?php endif; ?>
   }, 3000);
</script>
<?php endif; ?>
