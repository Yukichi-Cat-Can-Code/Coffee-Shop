<?php 
    require "../layouts/header.php" ;
    require "../../config/config.php" ;

    if(!isset($_SESSION['admin_name'])){
        header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
      }
    $product = $conn->query("SELECT * FROM product");
    $product->execute();
    $all_product = $product->fetchAll(PDO::FETCH_OBJ);

    

?>
    <div class="container-fluid">

          <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4 d-inline">Available Foods</h5>
              <a  href="create-products.php" class="btn btn-primary mb-4 text-center float-right">Create Products</a>

              <table class="table">
                <thead>
                  <tr class="text-center">
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Image</th>
                    <th scope="col">Price</th>
                    <th scope="col">Type</th>
                    <th scope="col">Delete</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($all_product  as $all_product ): ?>
                  <tr class="text-center">
                     <th scope="row"><?php echo $all_product->ID ?></th>
                     <td><?php echo $all_product->product_title ?></td>
                     <td><img src="http://localhost/coffee-Shop/images/<?php echo $all_product->image ?>" alt="<?php echo $all_product->image ?>" width="50" height="50" srcset=""></td>
                     <td>$<?php echo $all_product->price ?></td>
                     <td><?php echo $all_product->type ?></td>
                     <td><a href="delete-products.php?product_id=<?php echo $all_product->ID ?>" class="btn btn-danger  text-center ">delete</a></td>
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