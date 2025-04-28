<?php include('config/constants.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Important to make website responsive -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Website</title>

    <!-- Link our CSS file -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- Font Awesome for cart icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body style="background-color:#dfe4ea;">
    <!-- Navbar Section Starts Here -->
    <section class="navbar">
        <div class="container">
            <div class="logo">
                <a href="#" title="Logo">
                    <img height="90px" src="images/yumyard1.png" class="img-responsive">
                </a>
            </div>

            <div class="menu text-right">
                <ul>
                    <li>
                        <a href="<?php echo SITEURL; ?>">Home</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>categories.php">Categories</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>foods.php">Foods</a>
                    </li>
                    
                    <?php if(isset($_SESSION['username'])): ?>
                        <li>
                            <a href="<?php echo SITEURL; ?>cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <?php
                                // Get cart item count
                                if(isset($_SESSION['user_id'])) {
                                    $user_id = $_SESSION['user_id'];
                                    $cart_count = mysqli_query($conn, "SELECT SUM(quantity) as total FROM tbl_shopping_cart WHERE user_id = $user_id");
                                    $count_data = mysqli_fetch_assoc($cart_count);
                                    $total_items = $count_data['total'] ?? 0;
                                    
                                    if($total_items > 0) {
                                        echo '<span class="cart-count">'.$total_items.'</span>';
                                    }
                                }
                                ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITEURL; ?>my-orders.php">My Orders</a>
                        </li>

                        <li><a href="<?php echo SITEURL; ?>logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITEURL; ?>login.php">Login</a></li>
                        <li><a href="<?php echo SITEURL; ?>signup.php">Sign Up</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="#">Contact</a>
                    </li>
                </ul>
            </div>

            <div class="clearfix"></div>
        </div>
    </section>