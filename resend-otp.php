<?php
session_start();
include('config/constants.php');

if(!isset($_SESSION['signup_data'])) {
    header("Location: signup.php");
    exit();
}

$email = $_SESSION['signup_data']['email'];
$verification_method = $_SESSION['signup_data']['verification_method'];

// Generate new OTP
$otp = rand(100000, 999999);
$otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Update OTP in database
$update_query = "UPDATE users SET otp = '$otp', otp_expiry = '$otp_expiry' WHERE email = '$email'";
mysqli_query($conn, $update_query);

// Send OTP
if($verification_method == 'email') {
    // Send OTP via email
    $subject = "Your New OTP for Verification";
    $message = "Your new OTP is: $otp";
    $headers = "From: noreply@yourdomain.com";
    
    mail($email, $subject, $message, $headers);
} else {
    // In a real app, you would send SMS OTP here
    $_SESSION['otp'] = $otp; // For testing only
}

header("Location: verify-otp.php");
exit();
?>