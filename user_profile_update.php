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
   return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function get_count($conn, $table, $user_id){
   $query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$table` WHERE user_id = '$user_id'");
   if($query){
      $row = mysqli_fetch_assoc($query);
      return $row['total'] ?? 0;
   }
   return 0;
}

$select_profile = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'") or die('query failed');
$fetch_profile = mysqli_fetch_assoc($select_profile);

if(!$fetch_profile){
   header('location:login.php');
   exit;
}

if(isset($_POST['update_profile'])){

   $name = mysqli_real_escape_string($conn, trim($_POST['name']));
   $email = mysqli_real_escape_string($conn, trim($_POST['email']));

   if($name == ''){
      $message[] = 'name cannot be empty!';
   }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $message[] = 'please enter a valid email address!';
   }else{
      $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != '$user_id'") or die('query failed');

      if(mysqli_num_rows($check_email) > 0){
         $message[] = 'email already exists!';
      }else{
         mysqli_query($conn, "UPDATE users SET name = '$name', email = '$email' WHERE id = '$user_id'") or die('query failed');
         $message[] = 'profile details updated successfully!';
      }
   }

   if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){
      $image = mysqli_real_escape_string($conn, basename($_FILES['image']['name']));
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/'.$image;
      $image_extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
      $allowed_extensions = ['jpg', 'jpeg', 'png'];
      $old_image = $fetch_profile['image'] ?? '';

      if(!in_array($image_extension, $allowed_extensions)){
         $message[] = 'only jpg, jpeg, and png images are allowed!';
      }elseif($image_size > 2000000){
         $message[] = 'image size is too large!';
      }else{
         mysqli_query($conn, "UPDATE users SET image = '$image' WHERE id = '$user_id'") or die('query failed');
         move_uploaded_file($image_tmp_name, $image_folder);

         if(!empty($old_image) && file_exists('uploaded_img/'.$old_image) && $old_image != $image){
            unlink('uploaded_img/'.$old_image);
         }

         $message[] = 'profile image updated successfully!';
      }
   }

   $current_pass = $_POST['current_pass'] ?? '';
   $new_pass = $_POST['new_pass'] ?? '';
   $confirm_pass = $_POST['confirm_pass'] ?? '';

   if($current_pass != '' || $new_pass != '' || $confirm_pass != ''){
      if($current_pass == '' || $new_pass == '' || $confirm_pass == ''){
         $message[] = 'please fill all password fields to update password!';
      }elseif(!password_verify($current_pass, $fetch_profile['password'])){
         $message[] = 'current password is incorrect!';
      }elseif($new_pass != $confirm_pass){
         $message[] = 'confirm password does not match!';
      }else{
         $hashed_pass = password_hash($confirm_pass, PASSWORD_DEFAULT);
         $hashed_pass = mysqli_real_escape_string($conn, $hashed_pass);
         mysqli_query($conn, "UPDATE users SET password = '$hashed_pass' WHERE id = '$user_id'") or die('query failed');
         $message[] = 'password updated successfully!';
      }
   }

   $select_profile = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'") or die('query failed');
   $fetch_profile = mysqli_fetch_assoc($select_profile);
}

$user_name = $fetch_profile['name'] ?? 'Cute Fits Member';
$user_email = $fetch_profile['email'] ?? '';
$user_image = $fetch_profile['image'] ?? '';
$wishlist_count = get_count($conn, 'wishlist', $user_id);
$cart_count = get_count($conn, 'cart', $user_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>

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

<div class="top-strip">Update your boutique profile details in one place.</div>

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

<section class="profile-edit-section">
   <div class="sub-title">account settings</div>
   <h1 class="title">Update Profile</h1>

   <div class="profile-edit-shell">
      <div class="profile-edit-aside">
         <div class="avatar-wrap">
            <?php if(!empty($user_image)){ ?>
               <img src="uploaded_img/<?php echo clean_output($user_image); ?>" alt="profile image">
            <?php }else{ ?>
               <div class="avatar-initial"><?php echo strtoupper(substr(clean_output($user_name), 0, 1)); ?></div>
            <?php } ?>
         </div>

         <h2><?php echo clean_output($user_name); ?></h2>
         <p><?php echo clean_output($user_email); ?></p>

         <div class="mini-stats">
            <div>
               <strong><?php echo $wishlist_count; ?></strong>
               <span>wishlist</span>
            </div>
            <div>
               <strong><?php echo $cart_count; ?></strong>
               <span>cart</span>
            </div>
         </div>

         <a href="home.php#profile" class="option-btn">back to profile</a>
      </div>

      <form action="" method="POST" enctype="multipart/form-data" class="profile-edit-form">
         <h3>Personal details</h3>

         <div class="form-grid">
            <div class="input-group">
               <span>username</span>
               <input type="text" name="name" value="<?php echo clean_output($user_name); ?>" placeholder="update username" required class="box">
            </div>

            <div class="input-group">
               <span>email address</span>
               <input type="email" name="email" value="<?php echo clean_output($user_email); ?>" placeholder="update email" required class="box">
            </div>

            <div class="input-group full">
               <span>profile picture</span>
               <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
            </div>
         </div>

         <h3>Change password</h3>

         <div class="form-grid">
            <div class="input-group">
               <span>current password</span>
               <input type="password" name="current_pass" placeholder="enter current password" class="box">
            </div>

            <div class="input-group">
               <span>new password</span>
               <input type="password" name="new_pass" placeholder="enter new password" class="box">
            </div>

            <div class="input-group full">
               <span>confirm new password</span>
               <input type="password" name="confirm_pass" placeholder="confirm new password" class="box">
            </div>
         </div>

         <div class="profile-edit-actions">
            <input type="submit" class="btn" value="update profile" name="update_profile">
            <a href="home.php" class="option-btn">go back</a>
         </div>
      </form>
   </div>
</section>

<footer class="footer">
   &copy; <?php echo date('Y'); ?> Cute Fits. All rights reserved.
</footer>

<script src="js/script.js"></script>

</body>
</html>
