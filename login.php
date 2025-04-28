<?php
include('config/constants.php');

if(isset($_SESSION['username'])) {
    header("Location: ".SITEURL."index.php");
    exit();
}


if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        
        if(!$row['is_verified']) {
            $error = "Account not verified. Please check your email for verification.";
        }
       
        elseif(password_verify($password, $row['password'])) {
          
            $_SESSION['username'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            
           
            header("Location: ".SITEURL."index.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css/styles1.css">
</head>
<body style="background: url(images/backg.jpg)no-repeat; background-size: cover;">
    <div class="container">
        <h2>Login</h2>
        
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        <p>Forgot password? <a href="forgot-password.php">Reset it here</a></p>
        <p>Or <a href="index.php">skip login</a> and explore the food items directly.</p>
    </div>
</body>
</html>