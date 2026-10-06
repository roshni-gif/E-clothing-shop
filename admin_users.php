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

if(isset($_GET['delete'])){

   $delete_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);

   if($delete_id && $delete_id != $admin_id){
      $delete_users = mysqli_prepare($conn, "DELETE FROM `users` WHERE id = ?");
      mysqli_stmt_bind_param($delete_users, "i", $delete_id);
      mysqli_stmt_execute($delete_users);
      mysqli_stmt_close($delete_users);
   }

   header('location:admin_users.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Users</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="user-accounts">

   <h1 class="title">User Accounts</h1>

   <div class="box-container">

      <?php
         $select_users = mysqli_query($conn, "SELECT * FROM `users` ORDER BY id DESC") or die('query failed');

         if(mysqli_num_rows($select_users) > 0){
            while($fetch_users = mysqli_fetch_assoc($select_users)){
               if($fetch_users['id'] == $admin_id){
                  continue;
               }

               $user_image = !empty($fetch_users['image']) ? $fetch_users['image'] : 'default-avatar.png';
      ?>
      <div class="box">
         <img src="uploaded_img/<?= e($user_image); ?>" alt="">
         <p>user id : <span><?= e($fetch_users['id']); ?></span></p>
         <p>username : <span><?= e($fetch_users['name']); ?></span></p>
         <p>email : <span><?= e($fetch_users['email']); ?></span></p>
         <p>user type : <span style="color:<?= $fetch_users['user_type'] == 'admin' ? 'orange' : 'var(--black)'; ?>;">
            <?= e($fetch_users['user_type']); ?>
         </span></p>
         <a href="admin_users.php?delete=<?= e($fetch_users['id']); ?>" onclick="return confirm('delete this user?');" class="delete-btn">delete</a>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty">no user accounts found!</p>';
         }
      ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
