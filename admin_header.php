<?php

if(isset($message) && is_array($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.htmlspecialchars((string)$msg, ENT_QUOTES, 'UTF-8').'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>

<header class="header">

   <div class="flex">

      <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_page.php">home</a>
         <a href="admin_products.php">products</a>
         <a href="admin_orders.php">orders</a>
         <a href="admin_users.php">users</a>
         <a href="admin_contacts.php">messages</a>
      </nav>

      <div class="icons">
      <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
   <?php
      $select_profile = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$admin_id'") or die('query failed');
      $fetch_profile = mysqli_fetch_assoc($select_profile);
   ?>

   <?php if(!empty($fetch_profile['image'])){ ?>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="">
   <?php }else{ ?>
      <div class="profile-initial"><?= strtoupper(substr($fetch_profile['name'], 0, 1)); ?></div>
   <?php } ?>

   <p><?= htmlspecialchars($fetch_profile['name']); ?></p>

   <a href="admin_update_profile.php" class="btn">update profile</a>
   <a href="logout.php" class="delete-btn">logout</a>
</div>

   </div>

</header>