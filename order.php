<?php include('partials-front/menu.php'); ?>
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Make sure this path is correct

if(!isset($_SESSION['username'])) {
    $_SESSION['login_required'] = "Please log in to place an order.";
    header("Location: login.php");
    exit();
}

$cart_mode = isset($_GET['mode']) && $_GET['mode'] === 'cart';
$cart_items = $_SESSION['cart_items_data'] ?? [];
$cart_total = $_SESSION['cart_total'] ?? 0;

if(!$cart_mode && isset($_GET['food_id'])) {
    $food_id = $_GET['food_id'];
    $sql = "SELECT * FROM tbl_food WHERE id=$food_id";
    $res = mysqli_query($conn, $sql);

    if(mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        $title = $row['title'];
        $price = $row['price'];
        $image_name = $row['image_name'];
    } else {
        header('location:'.SITEURL);
        exit();
    }
} elseif(!$cart_mode) {
    header('location:'.SITEURL);
    exit();
}
?>

<section class="food-search">
    <div class="container">
        <h2 class="text-center text-white">Fill this form to confirm your order.</h2>

        <form action="" method="POST" class="order">
            <fieldset>
                <legend>Selected Food</legend>
                <?php if($cart_mode && !empty($cart_items)): ?>
                    <?php foreach($cart_items as $item): ?>
                        <div class="cart-food-item" style="display: flex; margin-bottom: 15px;">
                            <div style="margin-right: 15px;">
                                <?php if($item['image_name'] != ""): ?>
                                    <img src="<?php echo SITEURL; ?>images/food/<?php echo $item['image_name']; ?>" alt="<?php echo $item['title']; ?>" width="100" class="img-curve">
                                <?php else: ?>
                                    <div class="error">Image not available</div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3><?php echo $item['title']; ?> (x<?php echo $item['quantity']; ?>)</h3>
                                <p class="food-price">₹<?php echo $item['price']; ?> x <?php echo $item['quantity']; ?> = ₹<?php echo $item['total']; ?></p>
                                <input type="hidden" name="cart_items[]" value="<?php echo $item['title']; ?> x<?php echo $item['quantity']; ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <p><strong>Grand Total:</strong> ₹<?php echo $cart_total; ?></p>
                    <input type="hidden" name="cart_mode" value="1">
                    <input type="hidden" name="grand_total" value="<?php echo $cart_total; ?>">
                <?php else: ?>
                    <div class="food-menu-img">
                        <?php if($image_name == ""): ?>
                            <div class='error'>Image not available</div>
                        <?php else: ?>
                            <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" class="img-responsive img-curve">
                        <?php endif; ?>
                    </div>
                    <div class="food-menu-desc">
                        <h3><?php echo $title; ?></h3>
                        <input type="hidden" name="food" value="<?php echo $title; ?>">
                        <input type="hidden" name="image_name" value="<?php echo $image_name; ?>">
                        <p class="food-price">₹<?php echo $price; ?></p>
                        <input type="hidden" name="price" value="<?php echo $price; ?>">
                        <div class="order-label">Quantity</div>
                        <input type="number" name="qty" class="input-responsive" value="1" min="1" max="100" required>
                    </div>
                <?php endif; ?>
            </fieldset>

            <fieldset>
                <legend>Delivery Details</legend>
                <div class="order-label">Full Name</div>
                <input type="text" name="full-name" placeholder="Enter your full name" class="input-responsive" required>

                <div class="order-label">Phone Number</div>
                <input type="tel" name="contact" placeholder="Enter your phone number" class="input-responsive" required>

                <div class="order-label">Email</div>
                <input type="email" name="email" placeholder="Enter your email" class="input-responsive" required>
                
                <div class="order-label">Payment Mode</div>
                <select name="payment_mode" id="payment_mode" class="input-responsive" onchange="toggleCardDetails()" required>
                    <option value="Cash on Delivery">Cash on Delivery</option>
                    <option value="Debit Card">Debit Card</option>
                </select>

                <div id="card-details" style="display:none; margin-top: 15px;">
                    <div class="order-label">Card Number</div>
                    <input type="text" name="card_number" placeholder="Enter card number" class="input-responsive">

                    <div class="order-label">Card Expiry</div>
                    <input type="text" name="card_expiry" placeholder="MM/YY" class="input-responsive">

                    <div class="order-label">CVV</div>
                    <input type="password" name="card_cvv" placeholder="Enter CVV" class="input-responsive">
                </div>

                <script>
                function toggleCardDetails() {
                    var paymentMode = document.getElementById('payment_mode').value;
                    var cardDetails = document.getElementById('card-details');

                    if (paymentMode === 'Debit Card') {
                        cardDetails.style.display = 'block';
                    } else {
                        cardDetails.style.display = 'none';
                    }
                }
                </script>

                <div class="order-label">Address</div>
                <textarea name="address" rows="10" placeholder="E.g. Street, City, Country" class="input-responsive" required></textarea>

                <input type="submit" name="submit" value="Confirm Order" class="btn btn-primary">
            </fieldset>
        </form>

        <?php
        if(isset($_POST['submit'])) {
            $order_date = date("Y-m-d h:i:sa");
            $status = "ordered";
            $customer_name = $_POST['full-name'];
            $customer_contact = $_POST['contact'];
            $customer_email = $_POST['email'];
            $customer_address = $_POST['address'];
            $user_id = $_SESSION['user_id'];
            $payment_mode = $_POST['payment_mode'];

            if (strpos(strtolower($customer_address), "moodbidri") === false) {
                echo "<script>alert('Sorry, delivery not available at $customer_address. We only deliver to Moodbidri.');</script>";
                exit();
            }

            if(isset($_POST['cart_mode']) && $_POST['cart_mode'] == "1") {
                // Cart checkout mode
                $cart_items = $_SESSION['cart_items_data'] ?? [];
                $grand_total = $_POST['grand_total'] ?? 0;

                $order_summary = "";
                $first_image = $cart_items[0]['image_name'] ?? '';
                foreach($cart_items as $item) {
                    $order_summary .= $item['title'] . " x" . $item['quantity'] . " (₹" . $item['total'] . ")\n";
                }

                $sql2 = "INSERT INTO tbl_order SET 
                    user_id='$user_id',
                    food='".mysqli_real_escape_string($conn, $order_summary)."',
                    price='$grand_total',
                    qty='Multiple',
                    total='$grand_total',
                    order_date='$order_date',
                    status='$status',
                    customer_name='$customer_name',
                    customer_contact='$customer_contact',
                    customer_email='$customer_email',
                    customer_address='$customer_address',
                    payment_mode='$payment_mode'";

                $res2 = mysqli_query($conn, $sql2);
                
                if($payment_mode == 'Debit Card') {
                    $card_number = $_POST['card_number'];
                    $card_expiry = $_POST['card_expiry'];
                    $card_cvv = $_POST['card_cvv'];
                
                    $sql_card = "INSERT INTO dummy_card_payments SET 
                        user_id='$user_id',
                        card_number='$card_number',
                        card_expiry='$card_expiry',
                        card_cvv='$card_cvv',
                        payment_date='$order_date'";
                
                    mysqli_query($conn, $sql_card);
                }

                // Clear cart
                $user_id = $_SESSION['user_id'];
                mysqli_query($conn, "DELETE FROM tbl_shopping_cart WHERE user_id = $user_id");

                unset($_SESSION['cart_items_data']);
                unset($_SESSION['cart_total']);
            } else {
                // Single item order
                $food = $_POST['food'];
                $price = $_POST['price'];
                $qty = $_POST['qty'];
                $total = $price * $qty;
                $image_name = $_POST['image_name'];
                $payment_mode = $_POST['payment_mode'];

                $sql2 = "INSERT INTO tbl_order SET
                    user_id='$user_id', 
                    food='".mysqli_real_escape_string($conn, $food)."',
                    image_name = '$image_name',
                    price='$price',
                    qty='$qty',
                    total='$total',
                    order_date='$order_date',
                    status='$status',
                    customer_name='$customer_name',
                    customer_contact='$customer_contact',
                    customer_email='$customer_email',
                    customer_address='$customer_address',
                    payment_mode='$payment_mode'";

                $res2 = mysqli_query($conn, $sql2);
                
                if($payment_mode == 'Debit Card') {
                    $card_number = $_POST['card_number'];
                    $card_expiry = $_POST['card_expiry'];
                    $card_cvv = $_POST['card_cvv'];
                
                    $sql_card = "INSERT INTO dummy_card_payments SET 
                        user_id='$user_id',
                        card_number='$card_number',
                        card_expiry='$card_expiry',
                        card_cvv='$card_cvv',
                        payment_date='$order_date'";
                
                    mysqli_query($conn, $sql_card);
                }
            }

            if($res2 == true) {
                // Prepare email content for both cart and single item orders
                if(isset($_POST['cart_mode']) && $_POST['cart_mode'] == "1") {
                    $order_summary = "";
                    foreach($cart_items as $item) {
                        $order_summary .= $item['title'] . " x" . $item['quantity'] . " (₹" . $item['total'] . ")\n";
                    }
                    $grand_total = $_POST['grand_total'] ?? 0;
                } else {
                    $order_summary = $food . " x" . $qty . " (₹" . $total . ")\n";
                    $grand_total = $total;
                }
            
                // Create PHPMailer instance
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'loweldsouza38@gmail.com';
                    $mail->Password = 'comkdqphqsqwfzle';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    
                    // Recipients
                    $mail->setFrom('noreply@yumyard.com', 'Yum Yard');
                    $mail->addAddress($customer_email, $customer_name);
                    $mail->addReplyTo('support@yumyard.com', 'Support');
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Thank you for your order - Yum Yard';
                    
                    // HTML email body
                    $mail->Body = "
                    <html>
                    <head>
                        <title>Order Confirmation</title>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .header { color: #4CAF50; }
                            .summary { background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
                        </style>
                    </head>
                    <body>
                        <h2 class='header'>Thank you for your order!</h2>
                        <p>Dear $customer_name,</p>
                        <p>Your order has been received and is being processed.</p>
                        
                        <div class='summary'>
                            <h3>Order Summary:</h3>
                            <pre>$order_summary</pre>
                            <p><strong>Grand Total: ₹$grand_total</strong></p>
                            <p>Order Date: $order_date</p>
                        </div>
                        
                        <p>We'll notify you once your order is out for delivery.</p>
                        <p>Thank you for choosing Yum Yard!</p>
                    </body>
                    </html>
                    ";
                    
                    // Plain text version
                    $mail->AltBody = "Thank you for your order, $customer_name!\n\n" .
                                     "Order Summary:\n" .
                                     "$order_summary\n" .
                                     "Grand Total: ₹$grand_total\n" .
                                     "Order Date: $order_date\n\n" .
                                     "We'll notify you once your order is out for delivery.";
                    
                    $mail->send();
                    
                    echo "<script>alert('Order placed successfully'); window.location.href = '".SITEURL."';</script>";
                    exit();
                } catch (Exception $e) {
                    error_log("Mailer Error: {$mail->ErrorInfo}");
                    echo "<script>alert('Order placed successfully (email confirmation failed to send)'); window.location.href = '".SITEURL."';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Failed to place order'); window.location.href = '".SITEURL."';</script>";
                exit();
            }
        }
        ?>
    </div>
</section>

<?php include('partials-front/footer.php'); ?>