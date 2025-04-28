<?php include('partials-front/menu.php'); ?>

<?php
if(!isset($_SESSION['username'])) {
    $_SESSION['no-login-message'] = "<div class='error text-center'>Please login to access cart.</div>";
    header('location:'.SITEURL.'login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle item removal
if(isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];
    $delete_query = "DELETE FROM tbl_shopping_cart WHERE id = $remove_id AND user_id = $user_id";
    $delete_res = mysqli_query($conn, $delete_query);
    
    if($delete_res) {
        $_SESSION['cart-message'] = "<div class='success text-center'>Item removed from cart.</div>";
        header('location:'.SITEURL.'cart.php');
        exit();
    }
}

// Handle quantity update
if(isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = $_POST['quantity'];
    
    $update_query = "UPDATE tbl_shopping_cart SET quantity = $new_quantity 
                    WHERE id = $cart_id AND user_id = $user_id";
    $update_res = mysqli_query($conn, $update_query);
    
    if($update_res) {
        $_SESSION['cart-message'] = "<div class='success text-center'>Cart updated successfully.</div>";
    } else {
        $_SESSION['cart-message'] = "<div class='error text-center'>Failed to update cart.</div>";
    }
    
    header('location:'.SITEURL.'cart.php');
    exit();
}

// Get cart items
$cart_query = "SELECT c.*, f.title, f.image_name 
              FROM tbl_shopping_cart c
              JOIN tbl_food f ON c.food_id = f.id
              WHERE c.user_id = $user_id";
$cart_res = mysqli_query($conn, $cart_query);
?>

<div class="main-content">
    <div class="wrapper">
        <h1 class="text-center">Your Shopping Cart</h1>
        
        <?php 
        if(isset($_SESSION['cart-message'])) {
            echo $_SESSION['cart-message'];
            unset($_SESSION['cart-message']);
        }
        ?>
        
        <br><br>
        
        <?php
        $count = mysqli_num_rows($cart_res);
        if($count > 0) {
            ?>
            <table class="tbl-full">
                <tr>
                    <th>Food</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
                
                <?php
                $grand_total = 0;
                while($row = mysqli_fetch_assoc($cart_res)) {
                    $id = $row['id'];
                    $title = $row['title'];
                    $price = $row['price'];
                    $quantity = $row['quantity'];
                    $image_name = $row['image_name'];
                    $total = $price * $quantity;
                    $grand_total += $total;
                    ?>
                    
                    <tr>
                        <td>
                            <?php 
                            if($image_name != "") {
                                ?>
                                <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" width="100px">
                                <?php
                            }
                            echo $title; 
                            ?>
                        </td>
                        <td>₹<?php echo $price; ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="cart_id" value="<?php echo $id; ?>">
                                <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1" class="quantity-input">
                                <input type="submit" name="update_quantity" value="Update" class="btn btn-secondary btn-sm">
                            </form>
                        </td>
                        <td>₹<?php echo $total; ?></td>
                        <td>
                            <a href="<?php echo SITEURL; ?>cart.php?remove_id=<?php echo $id; ?>" class="btn btn-danger">Remove</a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                
                <tr>
                    <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                    <td><strong>₹<?php echo $grand_total; ?></strong></td>
                    <td>
                    <?php 
                    $_SESSION['cart_total'] = $grand_total;
                    $_SESSION['cart_items_data'] = []; // Store item details

                    mysqli_data_seek($cart_res, 0); // Reset result pointer
                    while($row = mysqli_fetch_assoc($cart_res)) {
                        $_SESSION['cart_items_data'][] = [
                            'title' => $row['title'],
                            'quantity' => $row['quantity'],
                            'price' => $row['price'],
                            'total' => $row['price'] * $row['quantity'],
                            'image_name' => $row['image_name']
                        ];
                    }
                ?>
                <a href="<?php echo SITEURL; ?>order.php?mode=cart" class="btn btn-primary">Proceed to Checkout</a>

                    </td>
                </tr>
            </table>
            <?php
        } else {
            echo "<div class='error text-center'>Your cart is empty.</div>";
        }
        ?>
    </div>
</div>

<?php include('partials-front/footer.php'); ?>