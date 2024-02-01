<?php 
    require "../layouts/header.php" ;
    require "../../config/config.php" ;

    if(!isset($_SESSION['admin_name'])){
        header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
      }

?>
<?php
    if(isset($_GET['product_id'])){
        $product_id = $_GET['product_id'];
        $select = $conn->query("SELECT * FROM product WHERE ID = '$product_id'");
        $select->execute();
        $img = $select->fetch(PDO::FETCH_OBJ);

        unlink("../../images/".$img->image."");

        $delete_query = $conn->query("DELETE FROM product WHERE ID = '$product_id'");
        $delete_query->execute();

        header("location: http://localhost/coffee-Shop/admin-panel/products-admins/show-products.php");
    }

?>