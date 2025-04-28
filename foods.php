<?php include('partials-front/menu.php');  ?>
    <section class="food-search text-center">
        <div class="container">
            
            <form action="<?php echo SITEURL;  ?>food-search.php" method="POST">
                <input type="search" name="search" placeholder="Search for Food.." required>
                <input type="submit" name="submit" value="Search" class="btn btn-primary">
            </form>

        </div>
    </section>
    <!-- fOOD sEARCH Section Ends Here -->



    <!-- fOOD MEnu Section Starts Here -->
    <section class="food-menu">
        <div class="container">
            <h2 class="text-center">Food Menu</h2>

            <?php
            $sql="SELECT*FROM tbl_food WHERE active='Yes'";

            $res=mysqli_query($conn,$sql);

            $count=mysqli_num_rows($res);

            if($count>0)
            {
                while($row=mysqli_fetch_assoc($res))
                {
                   $id=$row['id'];
                   $title=$row['title'];
                   $description=$row['description'];
                   $price=$row['price'];
                   $image_name=$row['image_name'];
                   ?>
                   <div class="food-menu-box">
                   <div class="food-menu-img">

                   <?php
                   if($image_name=="")
                   {

                    echo"<div class='error'>Image not available</div>";
                   }
                   else
                   {
                    ?>
                     <img src="<?php echo SITEURL; ?>images/food/<?php echo $image_name; ?>" alt="Chicke Hawain Pizza" class="img-responsive img-curve">


                    <?php

                   }

                   ?>
                   
                </div>

                <div class="food-menu-desc">
                    <h4><?php echo $title; ?></h4>
                    <p class="food-price">₹<?php echo $price; ?></p>
                    <p class="food-detail">
                    <?php echo $description; ?>
                    </p>
                    <br>

                    <div class="food-actions">
                            <a href="<?php echo SITEURL; ?>order.php?food_id=<?php echo $id; ?>" class="btn btn-primary">Order Now</a>
                            
                            <?php if(isset($_SESSION['username'])): ?>
                                <form action="<?php echo SITEURL; ?>add-to-cart.php" method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="food_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="price" value="<?php echo $price; ?>">
                                    <input type="number" name="quantity" value="1" min="1" class="quantity-input">
                                    <button type="submit" name="submit" class="btn btn-secondary">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <a href="<?php echo SITEURL; ?>login.php" class="btn btn-secondary">Login to Add to Cart</a>
                            <?php endif; ?>
                        </div>
                </div>
            </div>


                   <?php
                }

            }
            else
            {
                echo"<div class='error'>Food not found.</div>";
            }

            ?>

            

            



            <div class="clearfix"></div>

            

        </div>

    </section>
    <!-- fOOD Menu Section Ends Here -->

    <!-- social Section Starts Here -->
 
    <?php include('partials-front/footer.php'); ?>