<?php

@include 'connect.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? mysqli_real_escape_string($conn, $_SESSION['user_id']) : '';
$message = [];

function clean_output($value){
   return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$user_name = 'Cute Fits Member';
$user_email = '';
$user_image = '';
$wishlist_count = 0;
$cart_count = 0;

if(!empty($user_id)){
   $select_profile = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'") or die('query failed');
   $fetch_profile = mysqli_fetch_assoc($select_profile);
   if($fetch_profile){
      $user_name = $fetch_profile['name'] ?? 'Cute Fits Member';
      $user_email = $fetch_profile['email'] ?? '';
      $user_image = $fetch_profile['image'] ?? '';
   }

   $wishlist_count_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM wishlist WHERE user_id = '$user_id'") or die('query failed');
   $wishlist_count = mysqli_fetch_assoc($wishlist_count_query)['total'];

   $cart_count_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM cart WHERE user_id = '$user_id'") or die('query failed');
   $cart_count = mysqli_fetch_assoc($cart_count_query)['total'];
}

if(isset($_POST['add_to_wishlist'])){

   if(empty($user_id)){
      header('location:login.php');
      exit;
   }

   $pid = mysqli_real_escape_string($conn, $_POST['pid']);
   $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
   $p_price = mysqli_real_escape_string($conn, $_POST['p_price']);
   $p_image = mysqli_real_escape_string($conn, $_POST['p_image']);

   $check_wishlist = mysqli_query($conn, "SELECT * FROM wishlist WHERE name = '$p_name' AND user_id = '$user_id'") or die('query failed');
   $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE name = '$p_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_wishlist) > 0){
      $message[] = 'already added to wishlist!';
   }elseif(mysqli_num_rows($check_cart) > 0){
      $message[] = 'already added to cart!';
   }else{
      mysqli_query($conn, "INSERT INTO wishlist(user_id, pid, name, price, image) VALUES('$user_id', '$pid', '$p_name', '$p_price', '$p_image')") or die('query failed');
      $message[] = 'added to wishlist!';
   }
}

if(isset($_POST['add_to_cart'])){

   if(empty($user_id)){
      header('location:login.php');
      exit;
   }

   $pid = mysqli_real_escape_string($conn, $_POST['pid']);
   $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
   $p_price = mysqli_real_escape_string($conn, $_POST['p_price']);
   $p_image = mysqli_real_escape_string($conn, $_POST['p_image']);
   $p_qty = max(1, (int)$_POST['p_qty']);

   $check_cart = mysqli_query($conn, "SELECT * FROM cart WHERE name = '$p_name' AND user_id = '$user_id'") or die('query failed');

   if(mysqli_num_rows($check_cart) > 0){
      $message[] = 'already added to cart!';
   }else{
      $check_wishlist = mysqli_query($conn, "SELECT * FROM wishlist WHERE name = '$p_name' AND user_id = '$user_id'") or die('query failed');

      if(mysqli_num_rows($check_wishlist) > 0){
         mysqli_query($conn, "DELETE FROM wishlist WHERE name = '$p_name' AND user_id = '$user_id'") or die('query failed');
      }

      mysqli_query($conn, "INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES('$user_id', '$pid', '$p_name', '$p_price', '$p_qty', '$p_image')") or die('query failed');
      $message[] = 'added to cart!';
   }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cute Fits Home</title>

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
         <a href="#profile">profile</a>
         <a href="orders.php">orders</a>
         <a href="contact.php">contact</a>
      </nav>

      <div class="icons">
         <a href="search_page.php" class="fas fa-search" title="search"></a>
         <a href="wishlist.php" class="fas fa-heart" title="wishlist"><sup><?php echo $wishlist_count; ?></sup></a>
         <a href="cart.php" class="fas fa-shopping-bag" title="cart"><sup><?php echo $cart_count; ?></sup></a>
         <?php if(empty($user_id)){ ?>
            <a href="login.php" class="fas fa-user" title="login"></a>
         <?php }else{ ?>
            <a href="logout.php" class="fas fa-sign-out-alt" title="logout" onclick="return confirm('Logout from your account?');"></a>
         <?php } ?>
      </div>
   </div>
</header>

<section class="hero">
   <div class="content">
      <span>new season edit</span>
      <h1>Pink Brown Essentials</h1>
      <p>Soft blush tones, warm brown details, and everyday outfits selected for simple modern style.</p>
      <a href="shop.php" class="btn">shop now</a>
   </div>

   <div class="hero-card">
      <img src="images/home.png" alt="fashion model">
   </div>
</section>

<section class="welcome">
   <div class="sun"><i class="fa-regular fa-sun"></i></div>
   <div class="sub-title">Welcome to Cute Fits</div>
   <p>Discover curated clothing for everyday wear, special moments, and seasonal styling. Shop new arrivals, best sellers, and wardrobe essentials in one simple place.</p>
</section>

<section class="about-home" id="about">
   <div class="about-card">
      <img src="images/hero-side.jpg" alt="about cute fits">
      <div class="text">
         <div class="sub-title">about our store</div>
         <h2>Designed for everyday confidence</h2>
         <p>Cute Fits brings together soft colors, clean silhouettes, and comfortable pieces that can move from casual days to special plans. Our home page uses a warm pink and brown palette to give the store a softer boutique look.</p>
         <div class="about-points">
            <div><i class="fas fa-shirt"></i> New styles</div>
            <div><i class="fas fa-heart"></i> Curated picks</div>
            <div><i class="fas fa-bag-shopping"></i> Easy shopping</div>
         </div>
      </div>
   </div>
</section>

<section class="categories">
   <div class="sub-title">shop by mood</div>
   <h1 class="title">Featured Categories</h1>

   <div class="category-grid">
      <div class="category-card">
         <img src="images/dresses.jpg" alt="dresses">
         <h3>Dresses</h3>
         <a href="category.php?category=dresses">Shop Dresses <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="category-card">
         <img src="images/tops.jpg" alt="tops">
         <h3>Tops</h3>
         <a href="category.php?category=tops">Shop Tops <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="category-card">
         <img src="images/pant.jpg" alt="bottoms">
         <h3>Bottoms</h3>
         <a href="category.php?category=bottoms">Shop Bottoms <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="category-card">
         <img src="images/jacket.jpg" alt="jackets">
         <h3>Jackets</h3>
         <a href="category.php?category=jackets">Shop Jackets <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="category-card">
         <img src="images/sets.jpg" alt="sets">
         <h3>Sets</h3>
         <a href="category.php?category=sets">Shop Sets <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="category-card">
         <img src="images/swimwear.jpg" alt="swimwear">
         <h3>Swimwear</h3>
         <a href="category.php?category=swimwear">Shop Swimwear <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="category-card">
         <img src="images/e3.jpg" alt="skirts">
         <h3>Skirts</h3>
         <a href="category.php?category=skirts">Shop Skirts <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="category-card">
         <img src="images/d3.jpg" alt="shorts">
         <h3>Shorts</h3>
         <a href="category.php?category=shorts">Shop Shorts <i class="fas fa-arrow-right"></i></a>
      </div>
   </div>
</section>

<section class="products" id="new-arrivals">
   <div class="sub-title">latest edit</div>
   <h1 class="title">New Arrivals</h1>

   <div class="box-container">
      <?php
         $select_products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 4") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <form action="" method="POST" class="box">
         <a href="view_page.php?pid=<?php echo clean_output($fetch_products['id']); ?>" class="fas fa-eye"></a>
         <img src="uploaded_img/<?php echo clean_output($fetch_products['image']); ?>" alt="<?php echo clean_output($fetch_products['name']); ?>">
         <div class="name"><?php echo clean_output($fetch_products['name']); ?></div>
         <div class="price">Rs. <span><?php echo clean_output($fetch_products['price']); ?></span></div>

         <input type="hidden" name="pid" value="<?php echo clean_output($fetch_products['id']); ?>">
         <input type="hidden" name="p_name" value="<?php echo clean_output($fetch_products['name']); ?>">
         <input type="hidden" name="p_price" value="<?php echo clean_output($fetch_products['price']); ?>">
         <input type="hidden" name="p_image" value="<?php echo clean_output($fetch_products['image']); ?>">
         <input type="number" min="1" value="1" name="p_qty" class="qty">

         <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
         <input type="submit" value="add to cart" class="btn" name="add_to_cart">
      </form>
      <?php
            }
         }else{
            echo '<p class="empty">no products added yet!</p>';
         }
      ?>
   </div>
</section>

<div class="marquee">
   <span>Blush tones and warm brown details &nbsp; • &nbsp; New arrivals now available &nbsp; • &nbsp; Blush tones and warm brown details &nbsp; • &nbsp; New arrivals now available &nbsp; • &nbsp;</span>
</div>

<div class="feature-banner">
   <img src="images/best.jpg" alt="best sellers">
   <div>
      <div class="sub-title">style that sells</div>
      <h2>Best Sellers</h2>
      <p>Explore the most loved outfits and accessories from our collection.</p>
      <a href="shop.php" class="btn">shop now</a>
   </div>
</div>

<section class="products">
   <h1 class="title">Best Sellers</h1>

   <div class="box-container">
      <?php
         $best_sellers = mysqli_query($conn, "SELECT * FROM products LIMIT 4") or die('query failed');
         if(mysqli_num_rows($best_sellers) > 0){
            while($fetch_products = mysqli_fetch_assoc($best_sellers)){
      ?>
      <form action="" method="POST" class="box">
         <a href="view_page.php?pid=<?php echo clean_output($fetch_products['id']); ?>" class="fas fa-eye"></a>
         <img src="uploaded_img/<?php echo clean_output($fetch_products['image']); ?>" alt="<?php echo clean_output($fetch_products['name']); ?>">
         <div class="name"><?php echo clean_output($fetch_products['name']); ?></div>
         <div class="price">Rs. <span><?php echo clean_output($fetch_products['price']); ?></span></div>

         <input type="hidden" name="pid" value="<?php echo clean_output($fetch_products['id']); ?>">
         <input type="hidden" name="p_name" value="<?php echo clean_output($fetch_products['name']); ?>">
         <input type="hidden" name="p_price" value="<?php echo clean_output($fetch_products['price']); ?>">
         <input type="hidden" name="p_image" value="<?php echo clean_output($fetch_products['image']); ?>">
         <input type="number" min="1" value="1" name="p_qty" class="qty">

         <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
         <input type="submit" value="add to cart" class="btn" name="add_to_cart">
      </form>
      <?php
            }
         }else{
            echo '<p class="empty">no products added yet!</p>';
         }
      ?>
   </div>
</section>

<section class="profile-section" id="profile">
   <div class="sub-title">your profile</div>
   <h1 class="title">Account Overview</h1>

   <div class="profile-panel">
      <div class="profile-image">
         <?php if(!empty($user_image) && !empty($user_id)){ ?>
            <img src="uploaded_img/<?php echo clean_output($user_image); ?>" alt="profile image">
         <?php }else{ ?>
            <div class="profile-initial"><i class="fas fa-user-circle"></i></div>
         <?php } ?>
      </div>

      <div class="profile-details">
         <?php if(!empty($user_id)){ ?>
            <h2><?php echo clean_output($user_name); ?></h2>
            <p><strong>Email:</strong> <?php echo clean_output($user_email); ?></p>
            <p><strong>Wishlist:</strong> <?php echo $wishlist_count; ?> items</p>
            <p><strong>Cart:</strong> <?php echo $cart_count; ?> items</p>

            <div class="profile-actions">
               <a href="wishlist.php" class="option-btn">view wishlist</a>
               <a href="cart.php" class="btn">view cart</a>
               <a href="logout.php" class="delete-btn">logout</a>
            </div>
         <?php }else{ ?>
            <h2>Welcome Guest</h2>
            <p>Please log in or register to manage your profile, view wishlist/cart, and place orders.</p>

            <div class="profile-actions">
               <a href="login.php" class="btn">login now</a>
               <a href="register.php" class="option-btn">register now</a>
            </div>
         <?php } ?>
      </div>
   </div>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>


<script src="js/script.js"></script>

</body>
</html>
