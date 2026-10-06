<?php

@include 'connect.php';
session_start();

if(!isset($_SESSION['user_id'])){
   header('location:login.php');
   exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = [];

function clean_output($value){
   return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders | Cute Fits</title>

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
   <div class="sun"><i class="fas fa-receipt"></i></div>
   <div class="sub-title">order history</div>
   <p>Track your clothing orders, payment status, delivery details, and selected outfits in one place.</p>
</section>

<section class="placed-orders e-page-section">
   <h1 class="title">My Orders</h1>

   <div class="box-container order-grid">
      <?php
         $select_orders = mysqli_prepare($conn, "SELECT * FROM `orders` WHERE user_id = ? ORDER BY id DESC");
         mysqli_stmt_bind_param($select_orders, "i", $user_id);
         mysqli_stmt_execute($select_orders);
         $orders_result = mysqli_stmt_get_result($select_orders);

         if(mysqli_num_rows($orders_result) > 0){
            while($fetch_orders = mysqli_fetch_assoc($orders_result)){
               $status = !empty($fetch_orders['payment_status']) ? strtolower($fetch_orders['payment_status']) : 'pending';
               if($status === 'completed' || $status === 'paid') {
                  $status_class = 'paid';
               } elseif($status === 'cancelled') {
                  $status_class = 'cancelled';
               } else {
                  $status_class = 'pending';
               }
      ?>
      <div class="box order-card">
         <div class="order-card-head">
            <h3>Order #<?php echo clean_output($fetch_orders['id']); ?></h3>
            <span class="status-badge <?php echo clean_output($status_class); ?>">
               <?php echo ucfirst(clean_output($status)); ?>
            </span>
         </div>

         <p><strong>Placed on</strong> <span><?php echo clean_output($fetch_orders['placed_on']); ?></span></p>
         <p><strong>Name</strong> <span><?php echo clean_output($fetch_orders['name']); ?></span></p>
         <p><strong>Number</strong> <span><?php echo clean_output($fetch_orders['number']); ?></span></p>
         <p><strong>Email</strong> <span><?php echo clean_output($fetch_orders['email']); ?></span></p>
         <p><strong>Address</strong> <span><?php echo clean_output($fetch_orders['address']); ?></span></p>
         <p><strong>Payment</strong> <span><?php echo clean_output($fetch_orders['method']); ?></span></p>
         <p><strong>Items</strong> <span><?php echo clean_output($fetch_orders['total_products']); ?></span></p>
         <p class="order-total"><strong>Total</strong> <span>Rs. <?php echo clean_output($fetch_orders['total_price']); ?></span></p>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty">no orders placed yet!</p>';
         }

         mysqli_stmt_close($select_orders);
      ?>
   </div>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
