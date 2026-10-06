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

/* delete message */
if(isset($_GET['delete'])){

   $delete_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);

   if($delete_id){
      $delete_message = mysqli_prepare($conn, "DELETE FROM `message` WHERE id = ?");
      mysqli_stmt_bind_param($delete_message, "i", $delete_id);
      mysqli_stmt_execute($delete_message);
      mysqli_stmt_close($delete_message);
   }

   header('location:admin_contacts.php');
   exit;
}

/* reply to client */
if(isset($_POST['send_reply'])){

   $reply_id = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);
   $reply_text = trim($_POST['reply_text'] ?? '');

   if(!$reply_id){
      $message[] = 'invalid message!';
   }elseif($reply_text == ''){
      $message[] = 'reply message cannot be empty!';
   }else{

      $select_msg = mysqli_prepare($conn, "SELECT name, email, message FROM `message` WHERE id = ? LIMIT 1");
      mysqli_stmt_bind_param($select_msg, "i", $reply_id);
      mysqli_stmt_execute($select_msg);
      $result = mysqli_stmt_get_result($select_msg);

      if(mysqli_num_rows($result) > 0){

         $client = mysqli_fetch_assoc($result);
         $client_name = $client['name'];
         $client_email = $client['email'];
         $client_message = $client['message'];

         $update_reply = mysqli_prepare($conn, "UPDATE `message` SET reply = ?, replied_at = NOW() WHERE id = ?");
         mysqli_stmt_bind_param($update_reply, "si", $reply_text, $reply_id);
         mysqli_stmt_execute($update_reply);
         mysqli_stmt_close($update_reply);

         $subject = "Reply from E-CLOTHING";
         $body = "Hello ".$client_name.",\n\n";
         $body .= "Thank you for contacting E-CLOTHING.\n\n";
         $body .= "Your message:\n".$client_message."\n\n";
         $body .= "Our reply:\n".$reply_text."\n\n";
         $body .= "Regards,\nE-CLOTHING Team";

         $headers = "From: E-CLOTHING <noreply@eclothing.com>\r\n";
         $headers .= "Reply-To: noreply@eclothing.com\r\n";

         $mail_sent = @mail($client_email, $subject, $body, $headers);

         if($mail_sent){
            $message[] = 'reply saved and email sent!';
         }else{
            $message[] = 'reply saved, but email was not sent. Configure SMTP/mail in XAMPP.';
         }

      }else{
         $message[] = 'message not found!';
      }

      mysqli_stmt_close($select_msg);
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Messages</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="messages">

   <h1 class="title">Messages</h1>

   <div class="box-container">

      <?php
         $select_message = mysqli_query($conn, "SELECT * FROM `message` ORDER BY id DESC") or die('query failed');

         if(mysqli_num_rows($select_message) > 0){
            while($fetch_message = mysqli_fetch_assoc($select_message)){
      ?>

      <div class="box">
         <p>user id : <span><?= e($fetch_message['user_id']); ?></span></p>
         <p>name : <span><?= e($fetch_message['name']); ?></span></p>
         <p>number : <span><?= e($fetch_message['number']); ?></span></p>
         <p>email : <span><?= e($fetch_message['email']); ?></span></p>
         <p class="message-text">message : <span><?= e($fetch_message['message']); ?></span></p>

         <?php if(!empty($fetch_message['reply'])){ ?>
            <div class="admin-reply-box">
               <strong>Admin reply:</strong>
               <p><?= nl2br(e($fetch_message['reply'])); ?></p>
               <small>Replied at: <?= e($fetch_message['replied_at']); ?></small>
            </div>
         <?php } ?>

         <form action="" method="POST" class="reply-form">
            <input type="hidden" name="message_id" value="<?= e($fetch_message['id']); ?>">
            <textarea name="reply_text" class="box" required placeholder="reply to client"></textarea>
            <input type="submit" name="send_reply" value="send reply" class="option-btn">
         </form>

         <a href="admin_contacts.php?delete=<?= e($fetch_message['id']); ?>" onclick="return confirm('delete this message?');" class="delete-btn">delete</a>
      </div>

      <?php
            }
         }else{
            echo '<p class="empty">you have no messages!</p>';
         }
      ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>