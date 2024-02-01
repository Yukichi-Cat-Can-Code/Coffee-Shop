<?php 
    require "../layouts/header.php" ;
    require "../../config/config.php" ;

    if(!isset($_SESSION['admin_name'])){
        header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
      }

?>
<?php
  $bookings_query = $conn->query("SELECT * FROM bookings");
  $bookings_query->execute();
  $bookings = $bookings_query->fetchAll(PDO::FETCH_OBJ);

?>
    <div class="container-fluid">

          <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4 d-inline">Bookings</h5>
            
              <table class="table">
                <thead>

                  <tr class="text-center">
                    <th scope="col">#</th>
                    <th scope="col">First_name</th>
                    <th scope="col">Last_name</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Message</th>
                    <th scope="col">Status</th>
                    <th scope="col">Update</th>
                    <th scope="col">Delete</th>
                  </tr>
                
                </thead>
                <tbody>
                <?php foreach($bookings as $bookings ): ?>
                  <tr class="text-center">
                    <th scope="row"><?php echo $bookings->ID; ?></th>
                    <td><?php echo $bookings->first_name; ?></td>
                    <td><?php echo $bookings->last_name; ?></td>
                    <td><?php echo $bookings->date; ?></td>
                    <td><?php echo $bookings->time; ?></td>
                    <td><?php echo $bookings->phone_number; ?></td>
                    <td><?php echo $bookings->message; ?></td>
                    <td><?php echo $bookings->status; ?></td>
                    <td><a href="update-bookings.php?booking_id=<?php echo $bookings->ID ?>" class="btn btn-success  text-center ">Update</a></td>
                    <td><a href="delete-bookings.php?booking_id=<?php echo $bookings->ID ?>" class="btn btn-danger  text-center ">delete</a></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table> 
            </div>
          </div>
        </div>
      </div>



  </div>
<script type="text/javascript">

</script>
</body>
</html>