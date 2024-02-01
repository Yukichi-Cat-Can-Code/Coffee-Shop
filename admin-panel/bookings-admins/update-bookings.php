<?php
    require "../layouts/header.php" ;
    require "../../config/config.php" ;
    
    if(!isset($_SESSION['admin_name'])){
        header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
      }
    $error_message = " ";


    $booking_id = $_GET['booking_id'];
    if(isset($_GET['booking_id'])){
        if(isset($_POST['submit'])){
            $status = $_POST['status'];
            if($status == "Choose Type"){
                $error_message = "Choose a status";
            }else{
                $order_query = $conn->query("UPDATE bookings SET status ='$status' WHERE ID='$booking_id'");
                $order_query->execute();
                header("location: http://localhost/coffee-Shop/admin-panel/bookings-admins/show-bookings.php");
            }

        }

    }

?>

    <div class="container-fluid">

          <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-5 d-inline">Update Booking Status</h5>
                <form action="update-bookings.php?order_id=<?php echo $booking_id ?>" method="POST">
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