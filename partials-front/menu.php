<?php include('config/constants.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Website</title>

   
    <link rel="stylesheet" href="css/styles.css">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body style="background-color:#dfe4ea;">
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
                        <a href="<?php echo SITEURL; ?>"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>categories.php"><i class="fas fa-th-list"></i> Categories</a>
                    </li>
                    <li>
                        <a href="<?php echo SITEURL; ?>foods.php"><i class="fas fa-hamburger"></i> Foods</a>
                    </li>
                    
                    <?php if(isset($_SESSION['username'])): ?>
                        <li>
                            <a href="<?php echo SITEURL; ?>cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <?php
                                
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
                            <a href="<?php echo SITEURL; ?>my-orders.php"><i class="fas fa-box"></i> My Orders</a>
                        </li>

                        <li><a href="<?php echo SITEURL; ?>logout.php"> <i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITEURL; ?>login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="<?php echo SITEURL; ?>signup.php"><i class="fas fa-user-plus"></i> Sign Up</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITEURL; ?>contact.php"><i class="fas fa-phone"></i> Contact</a></li>

                </ul>
            </div>

            <div class="clearfix"></div>
        </div>
    </section>