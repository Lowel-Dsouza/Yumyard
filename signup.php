<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; 
include('config/constants.php');


if(isset($_SESSION['username'])) {
    header("Location: ".SITEURL."index.php");
    exit();
}


if($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $verification_method = $_POST['verification_method'];
    
  
    $errors = [];
    
    if(empty($first_name)) {
        $errors[] = "First name is required";
        
    }
    
    if(empty($last_name)) {
        $errors[] = "Last name is required";
        
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
        
    }
    
    if(!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be 10 digits";
        
    }
    
    if(strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
        
    }
    
    if($password != $confirm_password) {
        $errors[] = "Passwords do not match";
       
    }
    

    $check_query = "SELECT * FROM users WHERE email = '$email' OR phone = '$phone'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $errors[] = "Email or phone number already registered";
    }
    
   
    if(empty($errors)) {
      
        $otp = rand(100000, 999999);
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
       
        $insert_query = "INSERT INTO users (first_name, last_name, email, phone, password, otp, otp_expiry) 
                         VALUES ('$first_name', '$last_name', '$email', '$phone', '$hashed_password', '$otp', '$otp_expiry')";
        
        if(mysqli_query($conn, $insert_query)) {

            $_SESSION['signup_data'] = [
                'email' => $email,
                'verification_method' => $verification_method
            ];
            
           
            if($verification_method == 'email') {
             
                require 'vendor/autoload.php';
                
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'loweldsouza38@gmail.com'; 
                    $mail->Password = 'comkdqphqsqwfzle'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
            
                    $mail->setFrom('noreply@yourdomain.com', 'YumYard');
                    $mail->addAddress($email); 
                    $mail->isHTML(true);
                    $mail->Subject = 'Your YumYard OTP';
                    $mail->Body = "Your verification code is: <b>$otp</b>";
            
                    $mail->send();
                    header("Location: verify-otp.php");
                    exit();
                } catch (Exception $e) {
                    $errors[] = "OTP could not be sent. Error: {$mail->ErrorInfo}";
                }
            }
            
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/styles1.css">
</head>
<body style="background: url(images/backg.jpg) no-repeat; background-size: cover;">
    <div class="container">
        <h2>Sign Up</h2>
        
        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required 
                       value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required 
                       value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <input type="hidden" name="verification_method" value="email">

            
            <button type="submit">Sign Up</button>
        </form>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
        <p>Or <a href="index.php">skip signup</a> and explore the food items directly.</p>
    </div>
</body>
</html>