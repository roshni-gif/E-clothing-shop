<?php

include 'connect.php';

session_start();

$message = [];

if (isset($_POST['submit'])) {

   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $pass = $_POST['pass'];
   $cpass = $_POST['cpass'];

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   // validate email
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $message[] = 'Invalid email format!';
   } else {

      // check if user exists
      $select = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'") or die('query failed');

      if (mysqli_num_rows($select) > 0) {
         $message[] = 'User email already exists!';
      } else {

         if ($pass !== $cpass) {
            $message[] = 'Confirm password does not match!';
         } else {

            // secure password hashing
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

            $insert = mysqli_query($conn, "INSERT INTO users (name, email, password, image) VALUES ('$name', '$email', '$hashed_pass', '$image')") or die('query failed');

            if ($insert) {
               if($image_size > 2000000){
                  $message[] = 'image size is too large!';
               }else{
                  move_uploaded_file($image_tmp_name, $image_folder);
                  $_SESSION['message'][] = 'registered successfully!';
                  header('location:login.php');
                  exit;
               }
            } else {
               $message[] = 'Registration failed!';
            }
         }
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/components.css">

</head>
<body>

<?php
if(isset($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<section class="form-container">

   <form action="" method="POST" enctype="multipart/form-data">
      <h3>REGISTER NOW</h3>

      <input type="text" name="name" class="box" placeholder="enter your name" required>
      <br><br>

      <input type="email" name="email" class="box" placeholder="enter your email" required>
      <br><br>

      <div style="position: relative;">
         <input type="password" name="pass" id="pass" class="box" placeholder="enter your password" required style="margin-bottom: 0;">
         <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-light); font-size: 1.8rem;"></i>
      </div>
      <br><br>

      <div style="position: relative;">
         <input type="password" name="cpass" id="cpass" class="box" placeholder="confirm your password" required style="margin-bottom: 0;">
         <i class="fas fa-eye" id="toggleCPassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-light); font-size: 1.8rem;"></i>
      </div>
      <br><br>

      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png" required>
      <br><br>

      <input type="submit" value="register now" class="btn" name="submit">

      <p>Already have an account? <a href="login.php">login now</a></p>
   </form>

</section>

<script>
   const togglePassword = document.querySelector('#togglePassword');
   const password = document.querySelector('#pass');

   togglePassword.addEventListener('click', function (e) {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('fa-eye-slash');
   });

   const toggleCPassword = document.querySelector('#toggleCPassword');
   const cpassword = document.querySelector('#cpass');

   toggleCPassword.addEventListener('click', function (e) {
      const type = cpassword.getAttribute('type') === 'password' ? 'text' : 'password';
      cpassword.setAttribute('type', type);
      this.classList.toggle('fa-eye-slash');
   });
</script>

</body>
</html>