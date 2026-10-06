<?php

@include 'connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!$admin_id){
   header('location:login.php');
   exit;
}

function e($value){
   return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if(isset($_POST['update_order'])){

   $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
   $update_payment = $_POST['update_payment'] ?? '';
   $allowed_status = ['pending', 'completed'];

   if($order_id && in_array($update_payment, $allowed_status, true)){
      $update_orders = mysqli_prepare($conn, "UPDATE `orders` SET payment_status = ? WHERE id = ?");
      mysqli_stmt_bind_param($update_orders, "si", $update_payment, $order_id);
      mysqli_stmt_execute($update_orders);
      mysqli_stmt_close($update_orders);
      $message[] = 'payment status has been updated!';
   }else{
      $message[] = 'invalid payment status!';
   }
}

if(isset($_GET['delete'])){

   $delete_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);

   if($delete_id){
      $delete_orders = mysqli_prepare($conn, "DELETE FROM `orders` WHERE id = ?");
      mysqli_stmt_bind_param($delete_orders, "i", $delete_id);
      mysqli_stmt_execute($delete_orders);
      mysqli_stmt_close($delete_orders);
   }

   header('location:admin_orders.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="placed-orders">

   <h1 class="title">Placed Orders</h1>

   <div class="box-container">

      <?php
         $select_orders = mysqli_query($conn, "SELECT * FROM `orders` ORDER BY id DESC") or die('query failed');

         if(mysqli_num_rows($select_orders) > 0){
            while($fetch_orders = mysqli_fetch_assoc($select_orders)){
      ?>
      <div class="box">
         <p>user id : <span><?= e($fetch_orders['user_id']); ?></span></p>
         <p>placed on : <span><?= e($fetch_orders['placed_on']); ?></span></p>
         <p>name : <span><?= e($fetch_orders['name']); ?></span></p>
         <p>email : <span><?= e($fetch_orders['email']); ?></span></p>
         <p>number : <span><?= e($fetch_orders['number']); ?></span></p>
         <p>address : <span><?= e($fetch_orders['address']); ?></span></p>
         <p>total products : <span><?= e($fetch_orders['total_products']); ?></span></p>
         <p>total price : <span>Rs. <?= e(number_format((float)$fetch_orders['total_price'])); ?>/-</span></p>
         <p>payment method : <span><?= e($fetch_orders['method']); ?></span></p>
         <p>payment status : <span><?= e($fetch_orders['payment_status']); ?></span></p>

         <form action="" method="POST">
            <input type="hidden" name="order_id" value="<?= e($fetch_orders['id']); ?>">

            <select name="update_payment" class="drop-down" required>
               <option value="" selected disabled><?= e($fetch_orders['payment_status']); ?></option>
               <option value="pending">pending</option>
               <option value="completed">completed</option>
            </select>

            <div class="flex-btn">
               <input type="submit" name="update_order" class="option-btn" value="update">
               <a href="admin_orders.php?delete=<?= e($fetch_orders['id']); ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
            </div>
         </form>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty">no orders placed yet!</p>';
         }
      ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
