
<?php 
    require "../layouts/header.php";
    require "../../config/config.php";

    if (!isset($_SESSION['admin_name'])) {
        header("location: http://localhost/coffee-Shop/admin-panel/admins/login-admins.php");
    }

    if (isset($_POST['submit'])) {
        if (empty($_POST['product_title']) || empty($_POST['price']) || empty($_POST['description']) || empty($_POST['type'])) {
            echo "<script> alert('One or more fields are empty !!')</script>";
        } else {
            $product_title = $_POST['product_title'];
            $price = $_POST['price'];

            // Check if 'image' key exists and there is no file upload error
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $image = $_FILES['image']['name'];
                $img_directory = "../../images/" . basename($image);

                // Check if the file was successfully uploaded before proceeding
                if (move_uploaded_file($_FILES['image']['tmp_name'], $img_directory)) {
                    $description = $_POST['description'];
                    $type = $_POST['type'];

                    // Use prepared statement
                    $create_product = $conn->prepare("INSERT INTO product(product_title, image, description, price, type) VALUES (:product_title, :image, :description, :price, :type)");
                    $create_product->execute([
                        ":product_title" => $product_title,
                        ":image" => $image,
                        ":description" => $description,
                        ":price" => $price,
                        ":type" => $type
                    ]);

                    header("location: http://localhost/coffee-Shop/admin-panel/products-admins/show-products.php");
                } else {
                    echo "<script> alert('File upload failed!')</script>";
                }
            } else {
                echo "<script> alert('Image not provided or file upload error occurred!')</script>";
            }
        }
    }
?>
    <div class="container-fluid">
       <div class="row">
        <div class="col">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-5 d-inline">Create Product</h5>
          <form method="POST" action="create-products.php" enctype="multipart/form-data">
                <!-- Email input -->
                <div class="form-outline mb-4 mt-4">
                  <input type="text" name="product_title" id="form2Example1" class="form-control" placeholder="Product Title" />
                 
                </div>
                <div class="form-outline mb-4 mt-4">
                  <input type="text" name="price" id="form2Example1" class="form-control" placeholder="price" />
                 
                </div>
                <div class="form-outline mb-4 mt-4">
                  <input type="file" name="image" id="form2Example1" class="form-control"  />
                 
                </div>
                <div class="form-group">
                  <label for="exampleFormControlTextarea1">Description</label>
                  <textarea name="description" class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                </div>
               
                <div class="form-outline mb-4 mt-4">

                  <select name="type" class="form-select  form-control" aria-label="Default select example">
                    <option selected>Choose Type</option>
                    <option value="drink">drink</option>
                    <option value="dessert">dessert</option>
                  </select>
                </div>

                <br>
              

      
                <!-- Submit button -->
                <button type="submit" name="submit" class="btn btn-primary  mb-4 text-center">create</button>

          
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