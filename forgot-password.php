<?php
include('config/constants.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';


$error = '';
$success = '';
$show_otp_form = false;

if(isset($_POST['submit_email'])) {
    $email = $_POST['email'];
    
 
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) == 1) {
     
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
  
        $update_sql = "UPDATE users SET otp = '$otp', otp_expiry = '$otp_expiry' WHERE email = '$email'";
        mysqli_query($conn, $update_sql);
        
       
        $mail = new PHPMailer(true);
        
        try {
          
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'loweldsouza38@gmail.com';
            $mail->Password = 'comkdqphqsqwfzle';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
           
            $mail->setFrom('noreply@yumyard.com', 'Yum Yard');
            $mail->addAddress($email);
            
            
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .otp { font-size: 24px; font-weight: bold; color: #4CAF50; }
                        .note { color: #ff0000; font-style: italic; }
                    </style>
                </head>
                <body>
                    <h2>Password Reset Request</h2>
                    <p>You requested to reset your password. Here is your OTP:</p>
                    <p class='otp'>$otp</p>
                    <p class='note'>This OTP is valid for 15 minutes only.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </body>
                </html>
            ";
            
            $mail->send();
            $_SESSION['reset_email'] = $email;
            $show_otp_form = true;
            $success = "OTP has been sent to your email!";
        } catch (Exception $e) {
            $error = "Failed to send OTP. Please try again.";
        }
    } else {
        $error = "Email not found in our system!";
    }
}


if(isset($_POST['submit_otp'])) {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];
  
    if($new_password != $confirm_password) {
        $error = "Passwords do not match!";
        $show_otp_form = true;
    } else {
        
        $current_time = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM users 
                WHERE email = '$email' 
                AND otp = '$otp' 
                AND otp_expiry > '$current_time'";
        
        $result = mysqli_query($conn, $sql);
        
        if(mysqli_num_rows($result) == 1) {
           
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET 
                          password = '$hashed_password', 
                          otp = NULL, 
                          otp_expiry = NULL 
                          WHERE email = '$email'";
            
            if(mysqli_query($conn, $update_sql)) {
                unset($_SESSION['reset_email']);
                $success = "Password reset successfully! You can now <a href='login.php'>login</a> with your new password.";
            } else {
                $error = "Failed to reset password. Please try again.";
                $show_otp_form = true;
            }
        } else {
           
            $sql_check = "SELECT otp_expiry FROM users WHERE email = '$email'";
            $res = mysqli_query($conn, $sql_check);
            $row = mysqli_fetch_assoc($res);
            
            if(strtotime($row['otp_expiry']) < time()) {
                $error = "OTP has expired! Please request a new one.";
            } else {
                $error = "Invalid OTP! Please check and try again.";
            }
            $show_otp_form = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/styles1.css">
    <style>
        .otp-form { display: none; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body style="background: url(images/backg.jpg)no-repeat; background-size: cover;">
    <div class="container">
        <h2>Forgot Password</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if(!$show_otp_form): ?>
        <form method="post">
            <div class="form-group">
                <label for="email">Enter your email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" name="submit_email">Send OTP</button>
        </form>
        <?php else: ?>
        <form method="post" class="otp-form" style="display: block;">
            <div class="form-group">
                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" required maxlength="6">
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="submit_otp">Reset Password</button>
        </form>
        <?php endif; ?>
        
        <p>Remember your password? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>