<?php 
  require "../layouts/header.php" ;
  require "../../config/config.php" ;

  if(!isset($_SESSION['admin_name'])){
    header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
  }
?>

<?php
    //query for Admins
    $admin = $conn->query("SELECT * FROM admins");
    $admin->execute();
    $all_admins = $admin->fetchAll(PDO::FETCH_OBJ);
?>
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4 d-inline">Admins</h5>
             <a  href="create-admins.php" class="btn btn-primary mb-4 text-center float-right">Create Admins</a>
              <table class="table">
                <thead>
                  
                  <tr class="text-center">
                    <th scope="col">#</th>
                    <th scope="col">username</th>
                    <th scope="col">email</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($all_admins as $all_admins): ?>
                  <tr class="text-center">
                    <th scope="row"><?php echo $all_admins->ID; ?></th>
                    <td><?php echo $all_admins->admin_name; ?></td>
                    <td><?php echo $all_admins->admin_email; ?></td>
                   
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