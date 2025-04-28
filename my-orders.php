<?php include('partials-front/menu.php'); 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Make sure this path is correct?>

<?php
if (!isset($_SESSION['username'])) {
    $_SESSION['login_required'] = "Please log in to view your orders.";
    header("Location: login.php");
    exit();
}

if(isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    
    // Check if order belongs to this user
    $check_sql = "SELECT * FROM tbl_order WHERE id = '$cancel_id' AND user_id = '$_SESSION[user_id]'";
    $check_res = mysqli_query($conn, $check_sql);
    
    if(mysqli_num_rows($check_res) == 1) {
        // Only allow cancellation if status is "Ordered"
        $order = mysqli_fetch_assoc($check_res);
        if($order['status'] == 'ordered') {
            $update_sql = "UPDATE tbl_order SET status = 'cancelled' WHERE id = '$cancel_id'";
            $update_res = mysqli_query($conn, $update_sql);
            
            if($update_res) {
                $_SESSION['order_cancel'] = "<div class='success'>Order cancelled successfully.</div>";
                
                // Send cancellation email
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
                    $mail->addAddress($order['customer_email'], $order['customer_name']);
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Order #'.$cancel_id.' has been cancelled';
                    $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; }
                                .header { color: #d9534f; }
                                .order-details { background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
                            </style>
                        </head>
                        <body>
                            <h2 class='header'>Order Cancellation Confirmation</h2>
                            <p>Dear {$order['customer_name']},</p>
                            <p>Your order has been successfully cancelled.</p>
                            
                            <div class='order-details'>
                                <h3>Order Details:</h3>
                                <p><strong>Order ID:</strong> {$cancel_id}</p>
                                <p><strong>Items:</strong> {$order['food']}</p>
                                <p><strong>Total Amount:</strong> ₹{$order['total']}</p>
                                <p><strong>Order Date:</strong> {$order['order_date']}</p>
                            </div>
                            
                            <p>If this was a mistake or you need any assistance, please contact our support team.</p>
                            <p>Thank you for choosing Yum Yard!</p>
                        </body>
                        </html>
                    ";
                    
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mailer Error: {$mail->ErrorInfo}");
                }
            } else {
                $_SESSION['order_cancel'] = "<div class='error'>Failed to cancel order.</div>";
            }
        } else {
            $_SESSION['order_cancel'] = "<div class='error'>Order cannot be cancelled at this stage.</div>";
        }
    }
    header("Location: my-orders.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM tbl_order WHERE user_id = '$user_id' ORDER BY id DESC";

$res = mysqli_query($conn, $sql);
?>


    <div class="container">
        <h2 class="text-center">My Orders</h2>
        <?php 
    if(isset($_SESSION['order_cancel'])) {
        echo $_SESSION['order_cancel'];
        unset($_SESSION['order_cancel']);
    }
    ?>

<?php
    if(mysqli_num_rows($res) > 0) {
        while($row = mysqli_fetch_assoc($res)) { 
            $can_cancel = ($row['status'] == 'ordered');
            ?>
            <div class="order-box" style="display:flex; gap:20px; margin-bottom:20px; padding:15px; border:1px solid #ddd; border-radius:5px;">
                <!-- Thumbnail -->
                <div style="flex:0 0 110px;">
                    <?php if($row['image_name'] !== ''){ ?>
                        <img src="<?php echo SITEURL;?>images/food/<?php echo $row['image_name'];?>"
                             alt="<?php echo $row['food'];?>"
                             width="110" class="img-curve">
                    <?php }else{ ?>
                        <img src="<?php echo SITEURL;?>images/placeholder.png"
                             alt="No image" width="110" class="img-curve">
                    <?php } ?>
                </div>
        
                <!-- Details -->
                <div style="flex:1;">
                    <h4><?php echo nl2br($row['food']);?></h4>
                    <p><strong>Total:</strong> ₹<?php echo $row['total'];?></p>
                    <p><strong>Status:</strong> 
                        <span style="color: 
                            <?php 
                                switch($row['status']) {
                                    case 'Ordered': echo '#000'; break;
                                    case 'On-delivery': echo 'orange'; break;
                                    case 'Delivered': echo 'green'; break;
                                    case 'cancelled': echo 'red'; break;
                                    default: echo '#000';
                                }
                            ?>">
                            <?php echo ucfirst($row['status']);?>
                        </span>
                    </p>
                    <p><strong>Ordered On:</strong> <?php echo $row['order_date'];?></p><br>
                    
                    <!-- Cancel Button -->
                    <?php if($can_cancel) { ?>
                        <a href="my-orders.php?cancel_id=<?php echo $row['id'];?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to cancel this order?')"
                           style="background:#d9534f; color:white; padding:5px 10px; border-radius:3px; text-decoration:none;">
                            Cancel Order
                        </a>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<p class='text-center'>You haven't placed any orders yet.</p>";
    }
    ?>
</div>

<?php include('partials-front/footer.php'); ?>