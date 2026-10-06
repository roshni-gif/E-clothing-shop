<?php

@include 'connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = [];

// Fetch logged-in user's registered details to pre-fill the order form
$user_stmt = mysqli_prepare($conn, "SELECT name, email FROM `users` WHERE id = ?");
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user_data = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($user_stmt);

$prefill_name  = $user_data['name']  ?? '';
$prefill_email = $user_data['email'] ?? '';

function clean_output($value){
   return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function post_text($name){
   return trim((string)($_POST[$name] ?? ''));
}

function get_count($conn, $table, $user_id){
   $count = 0;
   $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM `$table` WHERE user_id = ?");
   mysqli_stmt_bind_param($stmt, "i", $user_id);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   if($row = mysqli_fetch_assoc($result)){
      $count = (int)$row['total'];
   }
   mysqli_stmt_close($stmt);
   return $count;
}

$wishlist_count = get_count($conn, 'wishlist', $user_id);
$cart_count = get_count($conn, 'cart', $user_id);

if(isset($_POST['order'])){

   $name = post_text('name');
   $number = post_text('number');
   $email = post_text('email');
   $method = post_text('method');
   $flat = post_text('flat');
   $street = post_text('street');
   $city = post_text('city');
   $state = post_text('state');
   $country = post_text('country');
   $pin_code = post_text('pin_code');
   $address = 'flat no. '.$flat.', '.$street.', '.$city.', '.$state.', '.$country.' - '.$pin_code;
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];

   $cart_query = mysqli_prepare($conn, "SELECT * FROM `cart` WHERE user_id = ?");
   mysqli_stmt_bind_param($cart_query, "i", $user_id);
   mysqli_stmt_execute($cart_query);
   $cart_result = mysqli_stmt_get_result($cart_query);

   while($cart_item = mysqli_fetch_assoc($cart_result)){
      $qty = max(1, (int)$cart_item['quantity']);
      $price = (float)$cart_item['price'];
      $cart_products[] = $cart_item['name'].' ( '.$qty.' )';
      $cart_total += ($price * $qty);
   }
   mysqli_stmt_close($cart_query);

   $total_products = implode(', ', $cart_products);

   if($cart_total <= 0){
      $message[] = 'your cart is empty';
   }else{
      $order_query = mysqli_prepare($conn, "SELECT id FROM `orders` WHERE user_id = ? AND name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
      mysqli_stmt_bind_param($order_query, "issssssd", $user_id, $name, $number, $email, $method, $address, $total_products, $cart_total);
      mysqli_stmt_execute($order_query);
      mysqli_stmt_store_result($order_query);
      $order_exists = mysqli_stmt_num_rows($order_query) > 0;
      mysqli_stmt_close($order_query);

      if($order_exists){
         $message[] = 'order placed already!';
      }else{
         $insert_order = mysqli_prepare($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES(?,?,?,?,?,?,?,?,?,?)");
         $default_status = 'pending';
         mysqli_stmt_bind_param($insert_order, "issssssdss", $user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on, $default_status);
         mysqli_stmt_execute($insert_order);
         mysqli_stmt_close($insert_order);

         $delete_cart = mysqli_prepare($conn, "DELETE FROM `cart` WHERE user_id = ?");
         mysqli_stmt_bind_param($delete_cart, "i", $user_id);
         mysqli_stmt_execute($delete_cart);
         mysqli_stmt_close($delete_cart);

         $message[] = 'order placed successfully!';
         $cart_count = 0;
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
   <title>Checkout | Cute Fits</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
if(!empty($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.clean_output($msg).'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>';
   }
}
?>

<div class="top-strip">Pink edit is live. Free delivery on selected outfits.</div>

<header class="site-header">
   <div class="flex">
      <a href="home.php" class="logo">
         <span>CUTE FITS</span>
         <small>fashion store</small>
      </a>

      <nav class="navbar">
         <a href="home.php">home</a>
         <a href="shop.php">shop</a>
         <a href="about.php">about</a>
         <a href="home.php#profile">profile</a>
         <a href="orders.php">orders</a>
         <a href="contact.php">contact</a>
      </nav>

      <div class="icons">
         <a href="search_page.php" class="fas fa-search" title="search"></a>
         <a href="wishlist.php" class="fas fa-heart" title="wishlist"><sup><?php echo clean_output($wishlist_count); ?></sup></a>
         <a href="cart.php" class="fas fa-shopping-bag" title="cart"><sup><?php echo clean_output($cart_count); ?></sup></a>
         <a href="logout.php" class="fas fa-sign-out-alt" title="logout"></a>
      </div>
   </div>
</header>

<section class="welcome shop-welcome">
   <div class="sun"><i class="fas fa-credit-card"></i></div>
   <div class="sub-title">secure checkout</div>
   <p>Confirm your selected outfits and enter your delivery details to place your order.</p>
</section>

<section class="display-orders checkout-summary">
   <h1 class="title">Order Summary</h1>

   <div class="summary-list">
      <?php
         $cart_grand_total = 0;
         $select_cart_items = mysqli_prepare($conn, "SELECT * FROM `cart` WHERE user_id = ? ORDER BY id DESC");
         mysqli_stmt_bind_param($select_cart_items, "i", $user_id);
         mysqli_stmt_execute($select_cart_items);
         $cart_items_result = mysqli_stmt_get_result($select_cart_items);

         if(mysqli_num_rows($cart_items_result) > 0){
            while($fetch_cart_items = mysqli_fetch_assoc($cart_items_result)){
               $price = (float)$fetch_cart_items['price'];
               $qty = max(1, (int)$fetch_cart_items['quantity']);
               $cart_total_price = $price * $qty;
               $cart_grand_total += $cart_total_price;
      ?>
      <p><?php echo clean_output($fetch_cart_items['name']); ?> <span>Rs. <?php echo clean_output($price); ?> x <?php echo clean_output($qty); ?></span></p>
      <?php
            }
         }else{
            echo '<p class="empty">your cart is empty!</p>';
         }
         mysqli_stmt_close($select_cart_items);
      ?>
   </div>

   <div class="grand-total">grand total : <span>Rs. <?php echo clean_output($cart_grand_total); ?></span></div>
</section>

<section class="checkout-orders e-page-section">
   <form action="" method="POST" class="checkout-form">
      <h3>Place Your Order</h3>

      <div class="flex">
         <div class="inputBox">
            <span>your name</span>
            <input type="text" name="name" placeholder="enter your name" class="box" value="<?php echo clean_output($prefill_name); ?>" required>
         </div>
         <div class="inputBox">
            <span>your number</span>
            <input type="text" name="number" placeholder="enter your number" class="box" required>
         </div>
         <div class="inputBox">
            <span>your email</span>
            <input type="email" name="email" placeholder="enter your email" class="box" value="<?php echo clean_output($prefill_email); ?>" required>
         </div>
         <div class="inputBox">
            <span>payment method</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">cash on delivery</option>
               <option value="card payment">card payment</option>
            </select>
         </div>
         <div class="inputBox">
            <span>address line 01</span>
            <input type="text" name="flat" placeholder="flat / house number" class="box" required>
         </div>
         <div class="inputBox">
            <span>address line 02</span>
            <input type="text" name="street" placeholder="street / area" class="box" required>
         </div>
         <div class="inputBox">
            <span>city</span>
            <input type="text" name="city" placeholder="e.g. Kathmandu" class="box" required>
         </div>
         <div class="inputBox">
            <span>state / province</span>
            <input type="text" name="state" placeholder="e.g. Bagmati" class="box" required>
         </div>
         <div class="inputBox">
            <span>country</span>
            <input type="text" name="country" placeholder="e.g. Nepal" class="box" required>
         </div>
         <div class="inputBox">
            <span>pin code</span>
            <input type="text" name="pin_code" placeholder="e.g. 44600" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?php echo ($cart_grand_total > 0) ? '' : 'disabled'; ?>" value="place order">
   </form>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
