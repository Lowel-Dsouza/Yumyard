<?php include('config/constants.php'); ?>
<?php
session_start();
session_unset();
session_destroy();
header("Location: ".SITEURL."login.php");
exit();
?>