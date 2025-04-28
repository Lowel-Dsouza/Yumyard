<?php 
include('partials-front/menu.php');
include('config/constants.php');

if(!isset($_SESSION['username'])) {
    $_SESSION['add-to-cart'] = "<div class='error'>Please login to add items to cart.</div>";
    header('location:'.SITEURL.'login.php');
    exit();
}

if(isset($_POST['submit'])) {
    $food_id = $_POST['food_id'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id']; 

   
    $check_cart = "SELECT * FROM tbl_shopping_cart WHERE user_id = '$user_id' AND food_id = '$food_id'";
    $cart_result = mysqli_query($conn, $check_cart);

    if(mysqli_num_rows($cart_result) > 0) {
        
        $update_cart = "UPDATE tbl_shopping_cart SET quantity = quantity + $quantity WHERE user_id = '$user_id' AND food_id = '$food_id'";
    } else {
       
        $update_cart = "INSERT INTO tbl_shopping_cart (user_id, food_id, quantity, price) 
                       VALUES ('$user_id', '$food_id', '$quantity', '$price')";
    }

    $cart_exec = mysqli_query($conn, $update_cart);

    if($cart_exec) {
        $_SESSION['add-to-cart'] = "<div class='success text-center'>Item added to cart successfully!</div>";
    } else {
        $_SESSION['add-to-cart'] = "<div class='error text-center'>Failed to add item to cart.</div>";
    }

    header('location:'.SITEURL.'index.php');
}
?>