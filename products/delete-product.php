<?php

	require "../includes/header.php";
	require "../config/config.php";
	require "../auth/not-access.php";
?>
<?php
    if(isset($_GET['product_id'])){
        $product_id = $_GET['product_id'];
        $delete_product = $conn->query("DELETE FROM `cart` WHERE product_id ='$product_id' AND user_id='$_SESSION[user_id]'");
        $delete_product->execute();
        header("location: cart.php");
    }


?>