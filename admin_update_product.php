<?php

@include 'connect.php';

session_start();

if(!isset($_SESSION['admin_id'])){
   header('location:login.php');
   exit;
}

$admin_id = $_SESSION['admin_id'];
$message = [];

function clean_output($value){
   return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function clean_input($conn, $value){
   return mysqli_real_escape_string($conn, trim($value ?? ''));
}

if(isset($_POST['update_product'])){

   $pid = (int)$_POST['pid'];
   $name = clean_input($conn, $_POST['name']);
   $price = clean_input($conn, $_POST['price']);
   $category = clean_input($conn, $_POST['category']);
   $details = clean_input($conn, $_POST['details']);
   $sizes = clean_input($conn, $_POST['sizes']);
   $old_image = clean_input($conn, $_POST['old_image']);

   if($name == '' || $price == '' || $category == '' || $details == '' || $sizes == ''){
      $message[] = 'please fill all product details!';
   }elseif(!is_numeric($price) || $price < 0){
      $message[] = 'please enter a valid product price!';
   }else{
      mysqli_query($conn, "UPDATE products SET name = '$name', category = '$category', details = '$details', price = '$price', sizes = '$sizes' WHERE id = '$pid'") or die('query failed: '.mysqli_error($conn));
      $message[] = 'product updated successfully!';
   }

   if(!empty($_FILES['image']['name'])){
      $image = basename($_FILES['image']['name']);
      $image = preg_replace('/[^A-Za-z0-9._-]/', '_', $image);
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/'.$image;
      $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
      $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

      if(!in_array($image_ext, $allowed_ext)){
         $message[] = 'only jpg, jpeg, png, and webp images are allowed!';
      }elseif($image_size > 2000000){
         $message[] = 'image size is too large!';
      }else{
         mysqli_query($conn, "UPDATE products SET image = '$image' WHERE id = '$pid'") or die('query failed: '.mysqli_error($conn));
         move_uploaded_file($image_tmp_name, $image_folder);

         if($old_image != '' && file_exists('uploaded_img/'.$old_image)){
            unlink('uploaded_img/'.$old_image);
         }

         $message[] = 'image updated successfully!';
      }
   }
}

$update_id = isset($_GET['update']) ? (int)$_GET['update'] : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Product</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="update-product">

   <h1 class="title">update product</h1>

   <?php
      $select_products = mysqli_query($conn, "SELECT * FROM products WHERE id = '$update_id'") or die('query failed: '.mysqli_error($conn));
      if(mysqli_num_rows($select_products) > 0){
         while($fetch_products = mysqli_fetch_assoc($select_products)){
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="old_image" value="<?= clean_output($fetch_products['image']); ?>">
      <input type="hidden" name="pid" value="<?= clean_output($fetch_products['id']); ?>">

      <div class="current-product-img">
         <img src="uploaded_img/<?= clean_output($fetch_products['image']); ?>" alt="<?= clean_output($fetch_products['name']); ?>">
      </div>

      <span>product name</span>
      <input type="text" name="name" placeholder="enter product name" required class="box" value="<?= clean_output($fetch_products['name']); ?>">

      <span>product price</span>
      <input type="number" name="price" min="0" placeholder="enter product price" required class="box" value="<?= clean_output($fetch_products['price']); ?>">

      <span>product category</span>
      <select name="category" class="box" required>
         <option value="<?= clean_output($fetch_products['category']); ?>" selected><?= clean_output($fetch_products['category']); ?></option>
         <option value="dresses">dresses</option>
         <option value="tops">tops</option>
         <option value="bottoms">bottoms</option>
         <option value="jackets">jackets</option>
         <option value="sets">sets</option>
         <option value="swimwear">swimwear</option>
         <option value="skirts">skirts</option>
         <option value="shorts">shorts</option>
      </select>

      <span>product sizes</span>
      <input type="text" name="sizes" class="box" required value="<?= htmlspecialchars($fetch_products['sizes']); ?>" placeholder="enter sizes">

      <span>product details</span>
      <textarea name="details" required placeholder="enter product details" class="box" cols="30" rows="10"><?= clean_output($fetch_products['details']); ?></textarea>

      <span>change image</span>
      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp">

      <div class="flex-btn">
         <input type="submit" class="btn" value="update product" name="update_product">
         <a href="admin_products.php" class="option-btn">go back</a>
      </div>
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">no products found!</p>';
      }
   ?>

</section>

<script src="js/script.js"></script>

</body>
</html>
