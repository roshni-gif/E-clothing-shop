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

if(isset($_POST['add_to_wishlist'])){
   if($user_id <= 0){
      header('location:login.php');
      exit;
   }
   $pid = post_int('pid');
   $p_name = post_text('p_name');
   $p_price = post_price('p_price');
   $p_image = post_text('p_image');

   if($pid <= 0 || $p_name === '' || $p_image === ''){
      $message[] = 'invalid product details!';
   }else{
      $check_wishlist = mysqli_prepare($conn, "SELECT id FROM `wishlist` WHERE name = ? AND user_id = ?");
      mysqli_stmt_bind_param($check_wishlist, "si", $p_name, $user_id);
      mysqli_stmt_execute($check_wishlist);
      mysqli_stmt_store_result($check_wishlist);
      $wishlist_exists = mysqli_stmt_num_rows($check_wishlist) > 0;
      mysqli_stmt_close($check_wishlist);

      $check_cart = mysqli_prepare($conn, "SELECT id FROM `cart` WHERE name = ? AND user_id = ?");
      mysqli_stmt_bind_param($check_cart, "si", $p_name, $user_id);
      mysqli_stmt_execute($check_cart);
      mysqli_stmt_store_result($check_cart);
      $cart_exists = mysqli_stmt_num_rows($check_cart) > 0;
      mysqli_stmt_close($check_cart);

      if($wishlist_exists){
         $message[] = 'already added to wishlist!';
      }elseif($cart_exists){
         $message[] = 'already added to cart!';
      }else{
         $insert_wishlist = mysqli_prepare($conn, "INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
         mysqli_stmt_bind_param($insert_wishlist, "iisds", $user_id, $pid, $p_name, $p_price, $p_image);
         mysqli_stmt_execute($insert_wishlist);
         mysqli_stmt_close($insert_wishlist);
         $message[] = 'added to wishlist!';
      }
   }
}

if(isset($_POST['add_to_cart'])){
   if($user_id <= 0){
      header('location:login.php');
      exit;
   }
   $pid = mysqli_real_escape_string($conn, $_POST['pid'] ?? '');
   $p_name = mysqli_real_escape_string($conn, $_POST['p_name'] ?? '');
   $p_price = mysqli_real_escape_string($conn, $_POST['p_price'] ?? '');
   $p_image = mysqli_real_escape_string($conn, $_POST['p_image'] ?? '');
   $p_qty = mysqli_real_escape_string($conn, $_POST['p_qty'] ?? '');
   $p_size = mysqli_real_escape_string($conn, $_POST['p_size'] ?? '');

   if($p_size == ''){
      $message[] = 'please select size!';
   }else{
      $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND pid = '$pid' AND size = '$p_size'") or die('query failed');

      if(mysqli_num_rows($check_cart) > 0){
         $message[] = 'already added to cart!';
      }else{
         $check_wishlist = mysqli_query($conn, "SELECT * FROM wishlist WHERE user_id = '$user_id' AND pid = '$pid'") or die('query failed');
         if(mysqli_num_rows($check_wishlist) > 0){
            mysqli_query($conn, "DELETE FROM wishlist WHERE user_id = '$user_id' AND pid = '$pid'") or die('query failed');
         }

         mysqli_query($conn, "INSERT INTO cart(user_id, pid, name, price, quantity, size, image) 
         VALUES('$user_id', '$pid', '$p_name', '$p_price', '$p_qty', '$p_size', '$p_image')") or die('query failed');
         $message[] = 'added to cart!';
      }
   }
}

$wishlist_count = get_count($conn, 'wishlist', $user_id);
$cart_count = get_count($conn, 'cart', $user_id);
$pid = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT);

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Product View | Cute Fits</title>

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

<section class="welcome shop-welcome">
   <div class="sun"><i class="fas fa-shirt"></i></div>
   <div class="sub-title">product details</div>
   <p>View the outfit details, select quantity, and add the item to your wishlist or shopping bag.</p>
</section>

<section class="quick-view e-page-section">
   <h1 class="title">Quick View</h1>

   <?php
      if($pid){
         $select_products = mysqli_prepare($conn, "SELECT * FROM `products` WHERE id = ? LIMIT 1");
         mysqli_stmt_bind_param($select_products, "i", $pid);
         mysqli_stmt_execute($select_products);
         $product_result = mysqli_stmt_get_result($select_products);

         if(mysqli_num_rows($product_result) > 0){
            while($fetch_products = mysqli_fetch_assoc($product_result)){
   ?>
   <form action="" class="box quick-view-card" method="POST">
      <div class="quick-img">
         <img src="uploaded_img/<?php echo clean_output($fetch_products['image']); ?>" alt="<?php echo clean_output($fetch_products['name']); ?>">
      </div>

      <div class="quick-content">
         <div class="sub-title">Cute Fits pick</div>
         <h2 class="name"><?php echo clean_output($fetch_products['name']); ?></h2>
         <div class="price">Rs. <span><?php echo clean_output($fetch_products['price']); ?></span></div>
         <div class="details"><?php echo clean_output($fetch_products['details']); ?></div>

         <input type="hidden" name="pid" value="<?php echo clean_output($fetch_products['id']); ?>">
         <input type="hidden" name="p_name" value="<?php echo clean_output($fetch_products['name']); ?>">
         <input type="hidden" name="p_price" value="<?php echo clean_output($fetch_products['price']); ?>">
         <input type="hidden" name="p_image" value="<?php echo clean_output($fetch_products['image']); ?>">

         <label class="qty-label">quantity</label>
         <input type="number" min="1" value="1" name="p_qty" class="qty">

         <select name="p_size" class="qty" required>
            <option value="">select size</option>
            <?php
               $size_list = explode(',', $fetch_products['sizes']);
               foreach($size_list as $size){
                  $size = trim($size);
                  if($size != ''){
                     echo '<option value="'.htmlspecialchars($size).'">'.htmlspecialchars($size).'</option>';
                  }
               }
            ?>
         </select>

         <div class="quick-actions">
            <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
            <input type="submit" value="add to cart" class="btn" name="add_to_cart">
         </div>
      </div>
   </form>
   <?php
            }
         }else{
            echo '<p class="empty">product not found!</p>';
         }
         mysqli_stmt_close($select_products);
      }else{
         echo '<p class="empty">invalid product selected!</p>';
      }
   ?>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
