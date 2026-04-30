<?php

@include 'config.php';

session_start();

// 1. Siguraduhon nga naay user_id para dili mag-error ang array key
$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit(); // Importante ang exit para dili na mopadayon ang code sa ubos
}

if(isset($_POST['order'])){

   $name = htmlspecialchars(trim($_POST['name']));
   $number = htmlspecialchars(trim($_POST['number']));
   $email = htmlspecialchars(trim($_POST['email']));
   $method = htmlspecialchars(trim($_POST['method']));
   
   // 2. FIX: Ayaw tawga ang $_POST['address'] kay wala na sa imong form
   // Gi-combine na nimo ang address gamit ang flat, street, etc.
   $address = 'flat no. '. $_POST['flat'] .' '. $_POST['street'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'] .' - '. $_POST['pin_code'];
   
   // 3. FIX: Tangtangon ang FILTER_SANITIZE_STRING kay deprecated na
   $address = htmlspecialchars($address); 
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = []; // Gamita ang empty array imbes nga naay empty string

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

   $order_query = $conn->prepare("SELECT * FROM orders WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
   $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

   if($cart_total == 0){
      $message[] = 'your cart is empty';
   }elseif($order_query->rowCount() > 0){
      $message[] = 'order placed already!';
   }else{
      $insert_order = $conn->prepare("INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);

      $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      $message[] = 'order placed successfully!';
   }
}

?>
