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

if(isset($_POST['add_to_cart'])){

   $wishlist_id = post_int('wishlist_id');
   $pid = post_int('pid');
   $p_name = post_text('p_name');
   $p_price = post_price('p_price');
   $p_image = post_text('p_image');
   $p_qty = max(1, post_int('p_qty', 1));

   if($pid <= 0 || $p_name === '' || $p_image === ''){
      $message[] = 'invalid product details!';
   }else{
      $check_cart = mysqli_prepare($conn, "SELECT id FROM `cart` WHERE name = ? AND user_id = ?");
      mysqli_stmt_bind_param($check_cart, "si", $p_name, $user_id);
      mysqli_stmt_execute($check_cart);
      mysqli_stmt_store_result($check_cart);
      $cart_exists = mysqli_stmt_num_rows($check_cart) > 0;
      mysqli_stmt_close($check_cart);

      if($cart_exists){
         $message[] = 'already added to cart!';
      }else{
         $insert_cart = mysqli_prepare($conn, "INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         mysqli_stmt_bind_param($insert_cart, "iisdis", $user_id, $pid, $p_name, $p_price, $p_qty, $p_image);
         mysqli_stmt_execute($insert_cart);
         mysqli_stmt_close($insert_cart);

         if($wishlist_id > 0){
            $delete_wishlist = mysqli_prepare($conn, "DELETE FROM `wishlist` WHERE id = ? AND user_id = ?");
            mysqli_stmt_bind_param($delete_wishlist, "ii", $wishlist_id, $user_id);
            mysqli_stmt_execute($delete_wishlist);
            mysqli_stmt_close($delete_wishlist);
         }

         $message[] = 'added to cart!';
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
   if($delete_id){
      $delete_wishlist = mysqli_prepare($conn, "DELETE FROM `wishlist` WHERE id = ? AND user_id = ?");
      mysqli_stmt_bind_param($delete_wishlist, "ii", $delete_id, $user_id);
      mysqli_stmt_execute($delete_wishlist);
      mysqli_stmt_close($delete_wishlist);
   }
   header('location:wishlist.php');
   exit;
}

if(isset($_GET['delete_all'])){
   $delete_all = mysqli_prepare($conn, "DELETE FROM `wishlist` WHERE user_id = ?");
   mysqli_stmt_bind_param($delete_all, "i", $user_id);
   mysqli_stmt_execute($delete_all);
   mysqli_stmt_close($delete_all);
   header('location:wishlist.php');
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
   <title>Cute Fits Wishlist</title>

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
      <span>saved styles</span>
      <h1>Your Wishlist</h1>
      <p>Keep your favorite outfits in one place before moving them to your cart.</p>
   </div>
</section>

<section class="wishlist">
   <div class="sub-title">favorite edit</div>
   <h1 class="title">Wishlist Items</h1>

   <div class="box-container">
      <?php
         $grand_total = 0;
         $select_wishlist = mysqli_prepare($conn, "SELECT * FROM `wishlist` WHERE user_id = ? ORDER BY id DESC");
         mysqli_stmt_bind_param($select_wishlist, "i", $user_id);
         mysqli_stmt_execute($select_wishlist);
         $wishlist_result = mysqli_stmt_get_result($select_wishlist);

         if(mysqli_num_rows($wishlist_result) > 0){
            while($fetch_wishlist = mysqli_fetch_assoc($wishlist_result)){
               $grand_total += (float)$fetch_wishlist['price'];
      ?>
      <form action="" method="POST" class="box">
         <a href="wishlist.php?delete=<?php echo clean_output($fetch_wishlist['id']); ?>" class="fas fa-times" onclick="return confirm('remove this from wishlist?');"></a>
         <a href="view_page.php?pid=<?php echo clean_output($fetch_wishlist['pid']); ?>" class="fas fa-eye"></a>
         <img src="uploaded_img/<?php echo clean_output($fetch_wishlist['image']); ?>" alt="<?php echo clean_output($fetch_wishlist['name']); ?>">
         <div class="name"><?php echo clean_output($fetch_wishlist['name']); ?></div>
         <div class="price">Rs. <span><?php echo clean_output($fetch_wishlist['price']); ?></span></div>

         <input type="hidden" name="wishlist_id" value="<?php echo clean_output($fetch_wishlist['id']); ?>">
         <input type="hidden" name="pid" value="<?php echo clean_output($fetch_wishlist['pid']); ?>">
         <input type="hidden" name="p_name" value="<?php echo clean_output($fetch_wishlist['name']); ?>">
         <input type="hidden" name="p_price" value="<?php echo clean_output($fetch_wishlist['price']); ?>">
         <input type="hidden" name="p_image" value="<?php echo clean_output($fetch_wishlist['image']); ?>">
         <input type="number" min="1" value="1" name="p_qty" class="qty">

         <input type="submit" value="add to cart" class="btn" name="add_to_cart">
      </form>
      <?php
            }
         }else{
            echo '<p class="empty">your wishlist is empty!</p>';
         }
         mysqli_stmt_close($select_wishlist);
      ?>
   </div>

   <div class="wishlist-total">
      <p>wishlist total : <span>Rs. <?php echo clean_output($grand_total); ?></span></p>
      <a href="shop.php" class="option-btn">continue shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('delete all wishlist items?');">delete all</a>
   </div>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
