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

if(isset($_POST['update_qty'])){
   $cart_id = post_int('cart_id');
   $p_qty = max(1, post_int('p_qty', 1));

   if($cart_id > 0){
      $update_qty = mysqli_prepare($conn, "UPDATE `cart` SET quantity = ? WHERE id = ? AND user_id = ?");
      mysqli_stmt_bind_param($update_qty, "iii", $p_qty, $cart_id, $user_id);
      mysqli_stmt_execute($update_qty);
      mysqli_stmt_close($update_qty);
      $message[] = 'cart quantity updated!';
   }
}

if(isset($_GET['delete'])){
   $delete_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
   if($delete_id){
      $delete_cart = mysqli_prepare($conn, "DELETE FROM `cart` WHERE id = ? AND user_id = ?");
      mysqli_stmt_bind_param($delete_cart, "ii", $delete_id, $user_id);
      mysqli_stmt_execute($delete_cart);
      mysqli_stmt_close($delete_cart);
   }
   header('location:cart.php');
   exit;
}

if(isset($_GET['delete_all'])){
   $delete_all = mysqli_prepare($conn, "DELETE FROM `cart` WHERE user_id = ?");
   mysqli_stmt_bind_param($delete_all, "i", $user_id);
   mysqli_stmt_execute($delete_all);
   mysqli_stmt_close($delete_all);
   header('location:cart.php');
   exit;
}

$wishlist_count = 0;
$cart_count = 0;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cute Fits Cart</title>

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
         <a href="logout.php" class="fas fa-sign-out-alt" title="logout"></a>
      </div>
   </div>
</header>

<section class="page-hero">
   <div>
      <span>checkout edit</span>
      <h1>Your Shopping Bag</h1>
      <p>Review your selected clothing items before checkout.</p>
   </div>
</section>

<section class="shopping-cart">
   <div class="sub-title">bag details</div>
   <h1 class="title">Products Added</h1>

   <div class="box-container">
      <?php
         $grand_total = 0;
         $select_cart = mysqli_prepare($conn, "SELECT * FROM `cart` WHERE user_id = ? ORDER BY id DESC");
         mysqli_stmt_bind_param($select_cart, "i", $user_id);
         mysqli_stmt_execute($select_cart);
         $cart_result = mysqli_stmt_get_result($select_cart);

         if(mysqli_num_rows($cart_result) > 0){
            while($fetch_cart = mysqli_fetch_assoc($cart_result)){
               $sub_total = (float)$fetch_cart['price'] * (int)$fetch_cart['quantity'];
               $grand_total += $sub_total;
      ?>
      <form action="" method="POST" class="box">
         <a href="cart.php?delete=<?php echo clean_output($fetch_cart['id']); ?>" class="fas fa-times" onclick="return confirm('delete this from cart?');"></a>
         <a href="view_page.php?pid=<?php echo clean_output($fetch_cart['pid']); ?>" class="fas fa-eye"></a>
         <img src="uploaded_img/<?php echo clean_output($fetch_cart['image']); ?>" alt="<?php echo clean_output($fetch_cart['name']); ?>">
         <div class="name"><?php echo clean_output($fetch_cart['name']); ?></div>
         <div class="price">Rs. <span><?php echo clean_output($fetch_cart['price']); ?></span></div>
         
         <div class="size">Size: <?= htmlspecialchars($fetch_cart['size']); ?></div>

         <input type="hidden" name="cart_id" value="<?php echo clean_output($fetch_cart['id']); ?>">
         <div class="flex-btn cart-update-row">
            <input type="number" min="1" value="<?php echo clean_output($fetch_cart['quantity']); ?>" class="qty" name="p_qty">
            <input type="submit" value="update" name="update_qty" class="option-btn">
         </div>

         <div class="sub-total">sub total : <span>Rs. <?php echo clean_output($sub_total); ?></span></div>
      </form>
      <?php
            }
         }else{
            echo '<p class="empty">your cart is empty!</p>';
         }
         mysqli_stmt_close($select_cart);
      ?>
   </div>

   <div class="cart-total">
      <p>grand total : <span>Rs. <?php echo clean_output($grand_total); ?></span></p>
      <a href="shop.php" class="option-btn">continue shopping</a>
      <a href="cart.php?delete_all" class="delete-btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('delete all cart items?');">delete all</a>
      <a href="checkout.php" class="btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>">proceed to checkout</a>
   </div>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
