<?php

@include 'connect.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$message = [];

function clean_output($value){
   return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function post_int($name, $default = 0){
   $value = filter_input(INPUT_POST, $name, FILTER_VALIDATE_INT);
   return $value !== false && $value !== null ? $value : $default;
}

function post_text($name){
   return trim((string)($_POST[$name] ?? ''));
}

function post_price($name){
   $value = filter_input(INPUT_POST, $name, FILTER_VALIDATE_FLOAT);
   return $value !== false && $value !== null ? $value : 0;
}

$wishlist_count = 0;
$cart_count = 0;

if($user_id > 0){
   $count_wishlist = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM `wishlist` WHERE user_id = ?");
   mysqli_stmt_bind_param($count_wishlist, "i", $user_id);
   mysqli_stmt_execute($count_wishlist);
   $wishlist_result = mysqli_stmt_get_result($count_wishlist);
   $wishlist_count = (int)(mysqli_fetch_assoc($wishlist_result)['total'] ?? 0);
   mysqli_stmt_close($count_wishlist);

   $count_cart = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM `cart` WHERE user_id = ?");
   mysqli_stmt_bind_param($count_cart, "i", $user_id);
   mysqli_stmt_execute($count_cart);
   $cart_result = mysqli_stmt_get_result($count_cart);
   $cart_count = (int)(mysqli_fetch_assoc($cart_result)['total'] ?? 0);
   mysqli_stmt_close($count_cart);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About Cute Fits</title>

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
         <a href="wishlist.php" class="fas fa-heart" title="wishlist"><sup><?php echo $wishlist_count; ?></sup></a>
         <a href="cart.php" class="fas fa-shopping-bag" title="cart"><sup><?php echo $cart_count; ?></sup></a>
         <?php if($user_id <= 0){ ?>
            <a href="login.php" class="fas fa-user" title="login"></a>
         <?php }else{ ?>
            <a href="logout.php" class="fas fa-sign-out-alt" title="logout" onclick="return confirm('Logout from your account?');"></a>
         <?php } ?>
      </div>
   </div>
</header>

<section class="page-hero">
   <div>
      <span>about our store</span>
      <h1>Cute Fits Boutique</h1>
      <p>Soft colors, comfortable pieces, and a simple shopping experience made for everyday confidence.</p>
   </div>
</section>

<section class="about-home about-page">
   <div class="about-card">
      <img src="images/about.jpg" alt="about e-clothing">
      <div class="text">
         <div class="sub-title">why choose us</div>
         <h2>Designed for everyday confidence</h2>
         <p>Cute Fits brings together dresses, tops, bottoms, and accessories in a warm pink and brown boutique style. Every page now follows the same visual identity as the home page.</p>
         <div class="about-points">
            <div><i class="fas fa-shirt"></i> New styles</div>
            <div><i class="fas fa-heart"></i> Curated picks</div>
            <div><i class="fas fa-bag-shopping"></i> Easy shopping</div>
         </div>
         <a href="shop.php" class="btn">shop collection</a>
      </div>
   </div>
</section>

<div class="feature-banner">
   <img src="images/why.png" alt="best sellers">
   <div>
      <div class="sub-title">what we provide</div>
      <h2>Clothing for simple modern style</h2>
      <p>Browse coordinated outfits, wardrobe essentials, and accessories selected for clean everyday styling.</p>
      <a href="shop.php" class="btn">our shop</a>
   </div>
</div>

<section class="reviews">
   <div class="sub-title">client love</div>
   <h1 class="title">Client Reviews</h1>

   <div class="box-container">
      <div class="box">
         <img src="images/pic-1.png" alt="client review">
         <p>The store feels soft, clean, and easy to shop. I loved the curated outfit sections.</p>
         <div class="stars">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Rina</h3>
      </div>

      <div class="box">
         <img src="images/pic-2.png" alt="client review">
         <p>The pink and brown design gives the site a boutique feel. The product cards are clear and simple.</p>
         <div class="stars">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
         </div>
         <h3>Sneha</h3>
      </div>

      <div class="box">
         <img src="images/pic-3.png" alt="client review">
         <p>I could quickly find tops, bottoms, dresses, and accessories without confusion.</p>
         <div class="stars">
            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Anisha</h3>
      </div>
   </div>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
