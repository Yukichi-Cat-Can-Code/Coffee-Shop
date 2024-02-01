<?php 
    require "../layouts/header.php" ;
    require "../../config/config.php" ;

    if(!isset($_SESSION['admin_name'])){
        header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
      }

?>
<?php
    if(isset($_GET['booking_id'])){
        $booking_id = $_GET['booking_id'];

        $delete_query = $conn->query("DELETE FROM bookings WHERE ID = '$booking_id'");
        $delete_query->execute();

        header("location: http://localhost/coffee-Shop/admin-panel/bookings-admins/show-bookings.php");
    }

?>