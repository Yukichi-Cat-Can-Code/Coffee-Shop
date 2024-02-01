<?php
    require "../layouts/header.php" ;
    require "../../config/config.php" ;
    $error_message = " ";
    $order_id = $_GET['order_id'];
    if(isset($_GET['order_id'])){
        if(isset($_POST['submit'])){
            $status = $_POST['status'];
            if($status == "Choose Type"){
                $error_message = "Choos a status";
            }else{
                $order_query = $conn->query("UPDATE orders SET status ='$status' WHERE ID='$order_id'");
                $order_query->execute();
                header("location: http://localhost/coffee-Shop/admin-panel/orders-admins/show-orders.php");
            }

        }

    }

?>

    <div class="container-fluid">

          <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-5 d-inline">Update Order Status</h5>
                <form action="update-orders.php?order_id=<?php echo $order_id ?>" method="POST">
                    <div class= "from-outline mb-4 mt-4">
                        <select name="status" id="status" class="from-select form-control">
                            <option selected>Choose Type</option>
                            <option value="Pending">Pending</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                        <p style="color: red;"><?php echo $error_message ; ?>&nbsp;</p>
                        <button type="submit" name="submit" class= "btn btn-success mb-4 mt-3 text-center">Update</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
      </div>



  </div>
<script type="text/javascript">

</script>
</body>
</html>