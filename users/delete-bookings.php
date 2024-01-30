<?php

    session_start();
    define("APPURl", "http://localhost/coffee-Shop");
	require "../config/config.php";
	require "../auth/not-access.php";
?>
<?php
    if(isset($_GET['booking_id'])){
        $delete_booking = $conn->query("DELETE FROM bookings WHERE ID ='$_GET[booking_id]' AND user_id='$_SESSION[user_id]'");
        $delete_booking->execute();
        header("location:".APPURl."/users/bookings.php");
    }
?>