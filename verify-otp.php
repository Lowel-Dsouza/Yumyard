
<?php

include('config/constants.php');


// Check if user came from signup
if(!isset($_SESSION['signup_data'])) {
    header("Location: signup.php");
    exit();
}

// Handle OTP verification
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];
    $email = $_SESSION['signup_data']['email'];
    
    // Get stored OTP from database
    $query = "SELECT otp, otp_expiry FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $stored_otp = $row['otp'];
        $otp_expiry = $row['otp_expiry'];
        
        // Check if OTP is expired
        if(strtotime($otp_expiry) < time()) {
            $errors[] = "OTP has expired. Please sign up again.";
            // Clean up expired registration
            mysqli_query($conn, "DELETE FROM users WHERE email = '$email'");
            unset($_SESSION['signup_data']);
        } 
        // Check if OTP matches
        elseif($entered_otp == $stored_otp) {
            // Mark user as verified
            mysqli_query($conn, "UPDATE users SET is_verified = 1, otp = NULL, otp_expiry = NULL WHERE email = '$email'");
            
            // Get user data
            $user_query = "SELECT * FROM users WHERE email = '$email'";
            $user_result = mysqli_query($conn, $user_query);
            $user = mysqli_fetch_assoc($user_result);
            
            // Set session
            $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
            // Redirect to home page
            header("Location: ".SITEURL."index.php");
            exit();
        } else {
            $errors[] = "Invalid OTP";
        }
    } else {
        $errors[] = "User not found. Please sign up again.";
        unset($_SESSION['signup_data']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="css/styles1.css">
</head>
<body style="background: url(images/backg.jpg) no-repeat; background-size: cover;">
    <div class="container">
        <h2>Verify OTP</h2>

        <!-- ADD TIMER HERE -->
        <div id="timer" style="font-size: 18px; color: red; margin-bottom: 10px;"></div>
        
        <?php if(!empty($errors)): ?>
            <div class="error">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p>We've sent a 6-digit OTP to your email.</p>

        <form method="post">
            <div class="form-group">
                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" required maxlength="6" pattern="\d{6}">
            </div>
            
            <button type="submit" id="verify-btn">Verify</button> <!-- Add id to button -->
        </form>

        <p>Didn't receive OTP? <a href="resend-otp.php">Resend</a></p>
    </div>

    <!-- ADD TIMER SCRIPT HERE -->
    <script>
    let timeLeft = 150; // 2 minutes 30 seconds

    function startTimer() {
        const timerElement = document.getElementById('timer');
        const verifyButton = document.getElementById('verify-btn');

        const timerInterval = setInterval(() => {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;

            seconds = seconds < 10 ? '0' + seconds : seconds;
            minutes = minutes < 10 ? '0' + minutes : minutes;

            timerElement.innerHTML = `Time remaining: ${minutes}:${seconds}`;

            timeLeft--;

            if (timeLeft < 0) {
                clearInterval(timerInterval);
                timerElement.innerHTML = "OTP expired. Please resend.";

                // Disable verify button after time is up
                verifyButton.disabled = true;
                verifyButton.style.backgroundColor = "grey";
                verifyButton.style.cursor = "not-allowed";
            }
        }, 1000);
    }

    startTimer();
    </script>
</body>

</html>