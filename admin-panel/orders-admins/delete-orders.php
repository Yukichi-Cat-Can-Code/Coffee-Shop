<?php 
    require "../layouts/header.php" ;
    require "../../config/config.php" ;

    if(!isset($_SESSION['admin_name'])){
        header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
      }

?>
<?php
    if(isset($_GET['order_id'])){
        $order_id = $_GET['order_id'];
        $delete_query = $conn->query("DELETE FROM orders WHERE ID = '$order_id'");
        $delete_query->execute();

        header("location: http://localhost/coffee-Shop/admin-panel/orders-admins/show-orders.php");
    }

?>