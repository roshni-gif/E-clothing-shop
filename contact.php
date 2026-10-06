<?php

@include 'connect.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$message = [];

function clean_output($value){
   return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function get_count($conn, $table, $user_id){
   if($user_id <= 0) return 0;
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

$user_name = '';
$user_email = '';

if($user_id > 0){
   $select_profile = mysqli_prepare($conn, "SELECT name, email FROM users WHERE id = ? LIMIT 1");
   mysqli_stmt_bind_param($select_profile, "i", $user_id);
   mysqli_stmt_execute($select_profile);
   $profile_result = mysqli_stmt_get_result($select_profile);
   $fetch_profile = mysqli_fetch_assoc($profile_result);
   mysqli_stmt_close($select_profile);

   $user_name = $fetch_profile['name'] ?? '';
   $user_email = $fetch_profile['email'] ?? '';
}

if(isset($_POST['send'])){

   if($user_id <= 0){
      header('location:login.php');
      exit;
   }

   $name = trim($_POST['name'] ?? '');
   $email = trim($_POST['email'] ?? '');
   $number = trim($_POST['number'] ?? '');
   $msg = trim($_POST['msg'] ?? '');

   if($name === '' || $email === '' || $number === '' || $msg === ''){
      $message[] = 'please fill all fields!';
   }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $message[] = 'please enter a valid email address!';
   }else{
      $select_message = mysqli_prepare($conn, "SELECT id FROM `message` WHERE user_id = ? AND name = ? AND email = ? AND number = ? AND message = ? LIMIT 1");
      mysqli_stmt_bind_param($select_message, "issss", $user_id, $name, $email, $number, $msg);
      mysqli_stmt_execute($select_message);
      $message_result = mysqli_stmt_get_result($select_message);

      if(mysqli_num_rows($message_result) > 0){
         $message[] = 'message already sent!';
      }else{
         $insert_message = mysqli_prepare($conn, "INSERT INTO `message`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
         mysqli_stmt_bind_param($insert_message, "issss", $user_id, $name, $email, $number, $msg);
         mysqli_stmt_execute($insert_message);
         mysqli_stmt_close($insert_message);

         $message[] = 'message sent successfully!';
      }

      mysqli_stmt_close($select_message);
   }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact | Cute Fits</title>

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
         <?php if($user_id <= 0){ ?>
            <a href="login.php" class="fas fa-user" title="login"></a>
         <?php }else{ ?>
            <a href="logout.php" class="fas fa-sign-out-alt" title="logout" onclick="return confirm('Logout from your account?');"></a>
         <?php } ?>
      </div>
   </div>
</header>

<section class="welcome shop-welcome contact-welcome">
   <div class="sun"><i class="fas fa-envelope-open-text"></i></div>
   <div class="sub-title">contact us</div>
   <p>Have a question about sizing, orders, delivery, or outfit suggestions? Send us a message and our Cute Fits team will get back to you.</p>
</section>

<section class="contact e-page-section">
   <div class="contact-layout">
      <div class="contact-info-card">
         <div class="sub-title">we are here</div>
         <h1 class="title">Get In Touch</h1>
         <p>Use the form to reach us for order support, product questions, styling help, or general feedback.</p>

         <div class="contact-mini-list">
            <div><i class="fas fa-shirt"></i><span>Clothing and size support</span></div>
            <div><i class="fas fa-truck-fast"></i><span>Order and delivery help</span></div>
            <div><i class="fas fa-heart"></i><span>Wishlist and styling questions</span></div>
         </div>
      </div>

      <form action="" method="POST" class="contact-form">
         <input type="text" name="name" class="box" required placeholder="enter your name" value="<?php echo clean_output($user_name); ?>">
         <input type="email" name="email" class="box" required placeholder="enter your email" value="<?php echo clean_output($user_email); ?>">
         <input type="text" name="number" class="box" required placeholder="enter your number">
         <textarea name="msg" class="box" required placeholder="enter your message" cols="30" rows="10"></textarea>
         <input type="submit" value="send message" class="btn" name="send">
      </form>
   </div>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
