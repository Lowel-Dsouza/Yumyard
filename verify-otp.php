
<?php

include('config/constants.php');



if(!isset($_SESSION['signup_data'])) {
    header("Location: signup.php");
    exit();
}


if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];
    $email = $_SESSION['signup_data']['email'];

    $query = "SELECT otp, otp_expiry FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $stored_otp = $row['otp'];
        $otp_expiry = $row['otp_expiry'];
        
        
        if(strtotime($otp_expiry) < time()) {
            $errors[] = "OTP has expired. Please sign up again.";
           
            mysqli_query($conn, "DELETE FROM users WHERE email = '$email'");
            unset($_SESSION['signup_data']);
        } 
       
        elseif($entered_otp == $stored_otp) {
           
            mysqli_query($conn, "UPDATE users SET is_verified = 1, otp = NULL, otp_expiry = NULL WHERE email = '$email'");

            $user_query = "SELECT * FROM users WHERE email = '$email'";
            $user_result = mysqli_query($conn, $user_query);
            $user = mysqli_fetch_assoc($user_result);
            
       
            $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
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
            
            <button type="submit" id="verify-btn">Verify</button>
        </form>

        <p>Didn't receive OTP? <a href="resend-otp.php">Resend</a></p>
    </div>

    
    <script>
    let timeLeft = 150; 

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