<?php include('partials-front/menu.php'); ?>

<?php
if(!isset($_SESSION['username'])) {
    header('location:'.SITEURL.'login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$cart_query = "SELECT SUM(price * quantity) as total 
              FROM tbl_shopping_cart 
              WHERE user_id = $user_id";
$cart_res = mysqli_query($conn, $cart_query);
$cart_data = mysqli_fetch_assoc($cart_res);
$grand_total = $cart_data['total'] ?? 0;

if($grand_total <= 0) {
    $_SESSION['checkout-error'] = "<div class='error'>Your cart is empty.</div>";
    header('location:'.SITEURL.'cart.php');
    exit();
}


if(isset($_POST['submit'])) {
 
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
 
    $order_date = date("Y-m-d H:i:s");
    $status = "Ordered";
    
    $insert_order = "INSERT INTO tbl_order (customer_name, customer_contact, customer_email, customer_address, total, order_date, status)
                    VALUES ('$full_name', '$contact', '$email', '$address', '$grand_total', '$order_date', '$status')";
    
    $order_res = mysqli_query($conn, $insert_order);
    
    if($order_res) {
        $order_id = mysqli_insert_id($conn);
        
    
        $cart_items_query = "SELECT * FROM tbl_shopping_cart WHERE user_id = $user_id";
        $cart_items_res = mysqli_query($conn, $cart_items_query);
        
        while($cart_item = mysqli_fetch_assoc($cart_items_res)) {
            $food_id = $cart_item['food_id'];
            $quantity = $cart_item['quantity'];
            $price = $cart_item['price'];
            
            $insert_item = "INSERT INTO tbl_order_items (order_id, food_id, quantity, price)
                          VALUES ('$order_id', '$food_id', '$quantity', '$price')";
            mysqli_query($conn, $insert_item);
        }
        
      
        $clear_cart = "DELETE FROM tbl_shopping_cart WHERE user_id = $user_id";
        mysqli_query($conn, $clear_cart);
        
        $_SESSION['order-success'] = "<div class='success text-center'>Order placed successfully!</div>";
        header('location:'.SITEURL.'order-success.php?id='.$order_id);
        exit();
    } else {
        $_SESSION['checkout-error'] = "<div class='error'>Failed to place order. Please try again.</div>";
        header('location:'.SITEURL.'cart.php');
        exit();
    }
}
?>

<div class="main-content">
    <div class="wrapper">
        <h1 class="text-center">Checkout</h1>
        
        <?php 
        if(isset($_SESSION['checkout-error'])) {
            echo $_SESSION['checkout-error'];
            unset($_SESSION['checkout-error']);
        }
        ?>
        
        <div class="checkout-container">
            <div class="order-summary">
                <h3>Order Summary</h3>
                <p>Total Items: <?php 
                    $count_query = "SELECT SUM(quantity) as total_items FROM tbl_shopping_cart WHERE user_id = $user_id";
                    $count_res = mysqli_query($conn, $count_query);
                    $count_data = mysqli_fetch_assoc($count_res);
                    echo $count_data['total_items'] ?? 0;
                ?></p>
                <p>Grand Total: ₹<?php echo $grand_total; ?></p>
            </div>
            
            <form action="" method="POST" class="checkout-form">
                <h3>Delivery Details</h3>
                
                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" name="full_name" required class="input-responsive">
                </div>
                
                <div class="form-group">
                    <label for="contact">Phone Number:</label>
                    <input type="tel" name="contact" required class="input-responsive">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" required class="input-responsive">
                </div>
                
                <div class="form-group">
                    <label for="address">Delivery Address:</label>
                    <textarea name="address" rows="5" required class="input-responsive"></textarea>
                </div>
                
                <input type="submit" name="submit" value="Place Order" class="btn btn-primary">
                <a href="<?php echo SITEURL; ?>cart.php" class="btn btn-secondary">Back to Cart</a>
            </form>
        </div>
    </div>
</div>

<?php include('partials-front/footer.php'); ?>