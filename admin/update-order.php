<?php
include('partials/menu.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Path to autoload.php
?>

<div class="main-content">
    <div class="wrapper">
        <h1>Update order</h1>
        <br><br><br>
        <?php
        if(isset($_SESSION['update']))
        {
            echo $_SESSION['update'];
            unset($_SESSION['update']);
        }
        ?>
        <br>

        <?php
        if(isset($_GET['id']))
        {
            $id=$_GET['id'];
            $sql="SELECT*FROM tbl_order WHERE id='$id'";
            $res=mysqli_query($conn,$sql);
            $count=mysqli_num_rows($res);
            if($count==1)
            {
                $row=mysqli_fetch_assoc($res);
                $food=$row['food'];
                $price=$row['price'];
                if (is_numeric($row['qty'])) {
                    $qty = $row['qty'];
                    $price_to_store = $row['price'];
                } else {
                    $qty = 1;
                    $price_to_store = $row['total']; // Because cart order total is already final
                }
                

                $status=$row['status'];
                $customer_name=$row['customer_name'];
                $customer_contact=$row['customer_contact'];
                $customer_email=$row['customer_email'];
                $customer_address=$row['customer_address'];
            }
            else
            {
                header('location:'.SITEURL.'admin/manage-order.php');
            }
        }
        else
        {
            header('location:'.SITEURL.'admin/manage-order.php');
        }
        ?>

        <form action="" method="POST">
            <table class="tbl-30">
                <tr>
                    <td>Food name:</td>
                    <td><b><?php echo $food; ?></b></td>
                </tr>
                <tr>
                    <td>Price:</td>
                    <?php
                        $price_to_store = is_numeric($qty) ? $price : $row['total']; // <- Use stored total if cart
                    ?>
                    <input type="hidden" name="price" value="<?php echo $price_to_store; ?>">


                </tr>
                <tr>
                    <td>Qty:</td>
                        <td>
                            <input type="number" name="qty" value="<?php echo $qty; ?>" min="1" required>
                        </td>


                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <select name="status">
                            <option <?php if($status=="Ordered"){echo"selected";}  ?> value="Ordered">Ordered</option>
                            <option <?php if($status=="On-delivery"){echo"selected";}  ?> value="On-delivery">On Delivery</option>
                            <option <?php if($status=="Delivered"){echo"selected";}  ?> value="Delivered">Delivered</option>
                            <option <?php if($status=="Cancelled"){echo"selected";}  ?> value="Cancelled">Cancelled</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Customer name:</td>
                    <td>
                        <input type="text" name="customer_name" value="<?php echo $customer_name;  ?>">
                    </td>
                </tr>
                <tr>
                    <td>Customer contact:</td>
                    <td>
                        <input type="text" name="customer_contact" value="<?php echo $customer_contact;  ?>">
                    </td>
                </tr>
                <tr>
                    <td>Customer email:</td>
                    <td>
                        <input type="text" name="customer_email" value="<?php echo $customer_email;  ?>">
                    </td>
                </tr>
                <tr>
                    <td>Customer address:</td>
                    <td>
                        <textarea name="customer_address" cols="30" rows="5"><?php echo $customer_address;  ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="hidden" name="id" value="<?php echo $id;  ?>">
                        <input type="hidden" name="price" value="<?php echo $price;  ?>">
                        <input type="submit" name="submit" value="Update Order" class="btn-secondary">

                    </td>
                </tr>
                

            </table>
        </form>
        <?php
        if(isset($_POST['submit']))
        {
            $id=$_POST['id'];
            $price=$_POST['price'];
            $qty = $_POST['qty'];
            $old_status = $status; // Get the old status before update

            $total = $price * $qty;
            
            $new_status=$_POST['status'];
            $customer_name=$_POST['customer_name'];
            $customer_contact=$_POST['customer_contact'];
            $customer_email=$_POST['customer_email'];
            $customer_address=$_POST['customer_address'];

            $sql2="UPDATE tbl_order SET
                   qty='$qty',
                   total=$total,
                   status='$new_status',
                   customer_name='$customer_name',
                   customer_contact='$customer_contact',
                   customer_email='$customer_email',
                   customer_address='$customer_address'
                   WHERE id=$id";

            $res2=mysqli_query($conn,$sql2);
            
            if($res2==true)
            {
                // Only send email if status changed
                if($old_status != $new_status) {
                    // Create PHPMailer instance
                    $mail = new PHPMailer(true);
                    
                    try {
                        // Server settings (use your existing SMTP credentials)
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
                        $mail->Subject = 'Your Order Status Update - Yum Yard';
                        
                        // HTML email body
                        $mail->Body = "
                        <html>
                        <head>
                            <title>Order Status Update</title>
                            <style>
                                body { font-family: Arial, sans-serif; }
                                .header { color: #4CAF50; }
                                .status-update { background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
                            </style>
                        </head>
                        <body>
                            <h2 class='header'>Order Status Update</h2>
                            <p>Dear $customer_name,</p>
                            <p>The status of your order has been updated.</p>
                            
                            <div class='status-update'>
                                <h3>Order Details:</h3>
                                <p><strong>Order ID:</strong> $id</p>
                                <p><strong>Items:</strong> $food</p>
                                <p><strong>Total Amount:</strong> ₹$total</p>
                    
                                <p><strong>Your order for $food is <span style='color: " . getStatusColor($new_status) . ";'>$new_status</span></p>
                            </div>
                            
                            <p>If you have any questions, please reply to this email.</p>
                            <p>Thank you for choosing Yum Yard!</p>
                        </body>
                        </html>
                        ";
                        
                        // Plain text version
                        $mail->AltBody = "Order Status Update\n\n" .
                                         "Dear $customer_name,\n\n" .
                                         "The status of your order has been updated.\n\n" .
                                         "Order ID: $id\n" .
                                         "Items: $food\n" .
                                         "Total Amount: ₹$total\n" .
                                         "Previous Status: $old_status\n" .
                                         "New Status: $new_status\n\n" .
                                         "If you have any questions, please reply to this email.\n\n" .
                                         "Thank you for choosing Yum Yard!";
                        
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Mailer Error: {$mail->ErrorInfo}");
                    }
                }
                
                $_SESSION['update']="<div class='success'>Order updated successfully</div>";
                header('location:'.SITEURL.'admin/manage-order.php');
            }
            else
            {
                $_SESSION['update']="<div class='error'>Failed to update</div>";
                header('location:'.SITEURL.'admin/manage-order.php');
            }
        }

        // Helper function to get color based on status
        function getStatusColor($status) {
            switch($status) {
                case 'Ordered': return '#000000';
                case 'On-delivery': return 'orange';
                case 'Delivered': return 'green';
                case 'cancelled': return 'red';
                default: return '#000000';
            }
        }
        ?>
    </div>
</div>

