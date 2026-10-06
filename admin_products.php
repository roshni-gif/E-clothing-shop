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

if(isset($_POST['add_product'])){

   $name = clean_input($conn, $_POST['name']);
   $price = clean_input($conn, $_POST['price']);
   $category = clean_input($conn, $_POST['category']);
   $sizes = clean_input($conn, $_POST['sizes']);
   $details = clean_input($conn, $_POST['details']);

   $image = basename($_FILES['image']['name']);
   $image = preg_replace('/[^A-Za-z0-9._-]/', '_', $image);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;
   $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
   $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

   if($name == '' || $price == '' || $category == '' || $sizes == '' || $details == ''){
      $message[] = 'please fill all product details!';
   }elseif(!is_numeric($price) || $price < 0){
      $message[] = 'please enter a valid product price!';
   }elseif(!in_array($image_ext, $allowed_ext)){
      $message[] = 'only jpg, jpeg, png, and webp images are allowed!';
   }elseif($image_size > 2000000){
      $message[] = 'image size is too large!';
   }else{
      $select_products = mysqli_query($conn, "SELECT id FROM products WHERE name = '$name'") or die('query failed: '.mysqli_error($conn));

      if(mysqli_num_rows($select_products) > 0){
         $message[] = 'product name already exists!';
      }else{
         $insert_products = mysqli_query($conn, "INSERT INTO products(name, category, details, price, sizes, image) VALUES('$name', '$category', '$details', '$price', '$sizes', '$image')") or die('query failed: '.mysqli_error($conn));

         if($insert_products){
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'new product added successfully!';
         }
      }
   }
}

if(isset($_GET['delete'])){

   $delete_id = (int)$_GET['delete'];

   $select_delete_image = mysqli_query($conn, "SELECT image FROM products WHERE id = '$delete_id'") or die('query failed: '.mysqli_error($conn));
   $fetch_delete_image = mysqli_fetch_assoc($select_delete_image);

   if($fetch_delete_image && !empty($fetch_delete_image['image']) && file_exists('uploaded_img/'.$fetch_delete_image['image'])){
      unlink('uploaded_img/'.$fetch_delete_image['image']);
   }

   mysqli_query($conn, "DELETE FROM products WHERE id = '$delete_id'") or die('query failed: '.mysqli_error($conn));
   mysqli_query($conn, "DELETE FROM wishlist WHERE pid = '$delete_id'") or die('query failed: '.mysqli_error($conn));
   mysqli_query($conn, "DELETE FROM cart WHERE pid = '$delete_id'") or die('query failed: '.mysqli_error($conn));

   header('location:admin_products.php');
   exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Products</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="add-products">

   <h1 class="title">add new product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>product name</span>
            <input type="text" name="name" class="box" required placeholder="enter product name">
         </div>

         <div class="inputBox">
            <span>product category</span>
            <select name="category" class="box" required>
               <option value="" selected disabled>select category</option>
               <option value="dresses">dresses</option>
               <option value="tops">tops</option>
               <option value="bottoms">bottoms</option>
               <option value="jackets">jackets</option>
               <option value="sets">sets</option>
               <option value="swimwear">swimwear</option>
               <option value="skirts">skirts</option>
               <option value="shorts">shorts</option>
            
            </select>
         </div>
         <div class="inputBox">
            <span>product price</span>
            <input type="number" min="0" name="price" class="box" required placeholder="enter product price">
         </div>

         <div class="inputBox">
            <span>product sizes</span>
            <input type="text" name="sizes" class="box" required placeholder="enter sizes, example: S,M,L,XL">
         </div>

         <div class="inputBox">
            <span>product image</span>
            <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png, image/webp">
         </div>
      </div>

      <span class="form-label">product details</span>
      <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>

      <input type="submit" class="btn" value="add product" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="title">products added</h1>

   <div class="box-container">

   <?php
      $show_products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC") or die('query failed: '.mysqli_error($conn));
      if(mysqli_num_rows($show_products) > 0){
         while($fetch_products = mysqli_fetch_assoc($show_products)){
   ?>
   <div class="box">
      <div class="price">Rs. <?= clean_output($fetch_products['price']); ?></div>
      <img src="uploaded_img/<?= clean_output($fetch_products['image']); ?>" alt="<?= clean_output($fetch_products['name']); ?>">
      <div class="name"><?= clean_output($fetch_products['name']); ?></div>
      <div class="cat"><?= clean_output($fetch_products['category']); ?></div>
      <div class="sizes" style="font-size: 1.6rem; color: var(--light-color); margin: 0.5rem 0;">Sizes: <?= clean_output($fetch_products['sizes']); ?></div>
      <div class="details"><?= clean_output($fetch_products['details']); ?></div>
      <div class="flex-btn">
         <a href="admin_update_product.php?update=<?= clean_output($fetch_products['id']); ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?= clean_output($fetch_products['id']); ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
