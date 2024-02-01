<?php 
  require "../layouts/header.php" ;
  require "../../config/config.php" ;

?>
<?php
  $orders_query = $conn->query("SELECT * FROM orders");
  $orders_query->execute();
  $orders = $orders_query->fetchAll(PDO::FETCH_OBJ);

?>
    <div class="container-fluid">

          <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4 d-inline">Orders</h5>
            
              <table class="table">
                <thead>
                  <tr class="text-center">
                    <th scope="col">#</th>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">town</th>
                    <th scope="col">Street Address</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Total Price</th>
                    <th scope="col">Status</th>
                    <th scope="col">Update</th>
                    <th scope="col">Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($orders as $orders): ?>
                    <tr class="text-center">
                      <th scope="row text-center"><?php echo $orders->ID ?></th>
                      <td><?php echo $orders->firstname ?></td>
                      <td><?php echo $orders->lastname ?></td>
                      <td><?php echo $orders->towncity ?></td>
                      <td><?php echo $orders->streetaddress ?></td>
                      <td><?php echo $orders->phone ?></td>
                      <td>
                      <?php echo $orders->payable_total_cost ?>
                      </td>
                      <td>
                         <?php echo $orders->status ?>
                      </td>
                      <td><a type="submit" href="update-orders.php?order_id=<?php echo $orders->ID  ?>" class="btn btn-success  text-center ">Update</a></td>
                      <td><a href="delete-orders.php?order_id=<?php echo $orders->ID  ?>" class="btn btn-danger  text-center ">delete</a></td>
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