<?php
require "../../config/config.php";
requireAdminLogin();

// Khởi tạo biến
$error_message = '';
$success_message = '';

// Xử lý form khi submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  // Lấy dữ liệu từ form
  $product_title = trim($_POST['product_title']);
  $product_price = trim($_POST['price']);
  $product_description = trim($_POST['description']);
  $product_type = trim($_POST['type']);

  // Validate dữ liệu
  $errors = [];

  if (empty($product_title)) {
    $errors[] = "Tên sản phẩm không được để trống";
  }

  if (empty($product_price) || !is_numeric($product_price) || $product_price <= 0) {
    $errors[] = "Giá sản phẩm phải là số dương";
  }

  if (empty($product_type) || $product_type === "choose") {
    $errors[] = "Vui lòng chọn loại sản phẩm";
  }

  // // Xử lý upload ảnh
  // $image_name = 'default-product.jpg'; // Ảnh mặc định nếu không upload

  // if (!empty($_FILES['image']['name'])) {
  //   $image = $_FILES['image']['name'];
  //   $temp_image = $_FILES['image']['tmp_name'];
  //   $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
  //   $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

  //   if (!in_array($image_ext, $allowed_extensions)) {
  //     $errors[] = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)";
  //   } else {
  //     // Tạo tên file duy nhất để tránh trùng lặp
  //     $image_name = "product_" . time() . "_" . uniqid() . "." . $image_ext;
  //     $upload_path = $_SERVER['DOCUMENT_ROOT'] . "/coffee-Shop/images/" . $image_name;

  //     if (!move_uploaded_file($temp_image, $upload_path)) {
  //       $errors[] = "Không thể upload ảnh. Vui lòng thử lại";
  //     }
  //   }
  // }

  // Xử lý upload ảnh
  $image_name = ''; // Mặc định là rỗng để kiểm tra sau
  $has_image = false;

  // Kiểm tra nếu có ảnh được chọn từ thư viện
  if (!empty($_POST['selected_image'])) {
    $selected_image = trim($_POST['selected_image']);
    // Kiểm tra xem file có tồn tại trong thư mục images
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/Coffee-Shop/images/" . $selected_image)) {
      $image_name = $selected_image;
      $has_image = true;
    } else {
      $errors[] = "Ảnh đã chọn không tồn tại trong thư viện";
    }
  }
  // Nếu người dùng tải lên ảnh mới (ưu tiên cao hơn nếu cả hai được chọn)
  else if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    $temp_image = $_FILES['image']['tmp_name'];
    $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($image_ext, $allowed_extensions)) {
      $errors[] = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)";
    } else {
      // Tạo tên file duy nhất để tránh trùng lặp
      $image_name = "product_" . time() . "_" . uniqid() . "." . $image_ext;
      $upload_path = $_SERVER['DOCUMENT_ROOT'] . "/Coffee-Shop/images/" . $image_name;

      if (move_uploaded_file($temp_image, $upload_path)) {
        $has_image = true;
      } else {
        $errors[] = "Không thể upload ảnh. Vui lòng thử lại";
      }
    }
  }

  // Kiểm tra xem có ảnh được chọn hoặc tải lên hay không
  if (!$has_image) {
    $errors[] = "Vui lòng chọn ảnh sản phẩm từ thư viện hoặc tải lên ảnh mới";
  }

  // Nếu không có lỗi, tiến hành tạo sản phẩm
  if (empty($errors)) {
    try {
      $insert_stmt = $conn->prepare("INSERT INTO product (product_title, image, description, price, type) 
                                      VALUES (?, ?, ?, ?, ?)");

      $insert_result = $insert_stmt->execute([
        $product_title,
        $image_name,
        $product_description,
        $product_price,
        $product_type
      ]);

      if ($insert_result) {
  $user_id = session()->get('admin_id'); // giả sử bạn lưu id admin trong session
  $desc = "Product added: {$product_title} (Price: {$product_price}đ, type: {$product_type})";

  require_once "../admins/audit.php"; // nếu chưa include
  logAudit($conn, $user_id, 'create_product', $desc);

  session()->setFlash('product_message', "Đã thêm sản phẩm mới thành công");
  session()->setFlash('product_message_type', "success");
  redirect(ADMINAPPURL . "/products-admins/show-products.php");
  exit;
} else {
        $error_message = "Không thể thêm sản phẩm";
      }
    } catch (PDOException $e) {
      $error_message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
    }
  } else {
    $error_message = implode("<br>", $errors);
  }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
  <!-- Page header -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">Thêm Sản Phẩm Mới</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
          <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>/products-admins/show-products.php">Sản phẩm</a></li>
          <li class="breadcrumb-item active">Thêm sản phẩm mới</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">
      <!-- Thông báo lỗi -->
      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i>
          <?= $error_message ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
          <h5 class="card-title mb-0">Thông tin sản phẩm mới</h5>
        </div>
        <div class="card-body">
          <form action="create-products.php" method="POST" enctype="multipart/form-data">
            <div class="row">
              <div class="col-md-8">
                <div class="mb-3">
                  <label for="product_title" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="product_title" name="product_title"
                    value="<?= isset($product_title) ? htmlspecialchars($product_title) : '' ?>" required>
                </div>

                <div class="mb-3">
                  <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="price" name="price"
                      value="<?= isset($product_price) ? $product_price : '' ?>" min="0" step="0.01" required>
                    <span class="input-group-text">đ</span>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="type" class="form-label">Loại sản phẩm <span class="text-danger">*</span></label>
                  <select class="form-select" id="type" name="type" required>
                    <option value="choose" selected>-- Chọn loại sản phẩm --</option>
                    <option value="drink" <?= isset($product_type) && $product_type === 'drink' ? 'selected' : '' ?>>Đồ uống</option>
                    <option value="dessert" <?= isset($product_type) && $product_type === 'dessert' ? 'selected' : '' ?>>Tráng miệng</option>
                    <option value="food" <?= isset($product_type) && $product_type === 'food' ? 'selected' : '' ?>>Đồ ăn</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="description" class="form-label">Mô tả sản phẩm <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="description" name="description" rows="5"><?= isset($product_description) ? htmlspecialchars($product_description) : '' ?></textarea>
                </div>
              </div>

              <!-- Thêm tab cho phép chọn giữa "Upload ảnh mới" và "Chọn ảnh có sẵn" -->
              <div class="col-md-4">
                <div class="mb-3 text-center">
                  <label class="form-label d-block">Hình ảnh sản phẩm</label>
                  <div class="image-preview mb-3">
                    <img src="<?= ADMINAPPURL ?>/../images/default-product.jpg"
                      class="img-fluid rounded shadow-sm"
                      id="imagePreview"
                      style="max-height: 250px; max-width: 100%;"
                      alt="Xem trước hình ảnh sản phẩm">
                  </div>

                  <!-- Tab navigation -->
                  <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-panel"
                        type="button" role="tab" aria-selected="true">Tải lên</button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery-panel"
                        type="button" role="tab" aria-selected="false">Thư viện</button>
                    </li>
                  </ul>

                  <!-- Tab content -->
                  <div class="tab-content">
                    <!-- Upload tab -->
                    <div class="tab-pane fade show active" id="upload-panel" role="tabpanel">
                      <div class="input-group mb-3">
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <button class="btn btn-outline-secondary" type="button" id="resetImageBtn">Reset</button>
                      </div>
                      <small class="text-muted">Tải lên ảnh từ máy tính của bạn</small>
                    </div>

                    <!-- Gallery tab -->
                    <div class="tab-pane fade" id="gallery-panel" role="tabpanel">
                      <input type="hidden" name="selected_image" id="selected_image" value="">
                      <div class="image-gallery" id="imageGallery" style="max-height: 250px; overflow-y: auto;">
                        <div class="text-center p-2">
                          <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                          </div>
                        </div>
                      </div>
                      <small class="text-muted">Chọn ảnh có sẵn từ thư viện</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
              <a href="<?= ADMINAPPURL ?>/products-admins/show-products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
              </a>
              <button type="submit" name="submit" class="btn btn-success">
                <i class="fas fa-plus-circle me-1"></i> Thêm sản phẩm
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Preview image before upload
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const resetImageBtn = document.getElementById('resetImageBtn');
    const defaultImageSrc = imagePreview.src;

    imageInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          imagePreview.src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
      }
    });

    // Reset image preview
    resetImageBtn.addEventListener('click', function() {
      imageInput.value = '';
      imagePreview.src = defaultImageSrc;
    });
  });
</script> -->

<style>
  .gallery-image {
    border: 2px solid transparent;
    transition: all 0.2s;
  }

  .gallery-image:hover {
    transform: scale(1.05);
  }

  .gallery-image.selected {
    border: 2px solid #28a745;
  }
</style>

<!-- Thêm script để tải và hiển thị gallery ảnh -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Preview image before upload
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const resetImageBtn = document.getElementById('resetImageBtn');
    const defaultImageSrc = imagePreview.src;
    const selectedImageInput = document.getElementById('selected_image');
    const galleryTab = document.getElementById('gallery-tab');

    // Xử lý upload ảnh mới
    imageInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          imagePreview.src = e.target.result;
          selectedImageInput.value = ''; // Xóa ảnh đã chọn từ gallery
        }
        reader.readAsDataURL(this.files[0]);
      }
    });

    // Reset image preview
    resetImageBtn.addEventListener('click', function() {
      imageInput.value = '';
      imagePreview.src = defaultImageSrc;
      selectedImageInput.value = '';
    });

    // Tải danh sách ảnh khi click vào tab gallery
    galleryTab.addEventListener('click', loadImageGallery);

    function loadImageGallery() {
      const gallery = document.getElementById('imageGallery');

      // Gửi AJAX request để lấy danh sách ảnh
      fetch('get-images.php')
        .then(response => response.json())
        .then(images => {
          let html = '';

          if (images.length === 0) {
            html = '<p class="text-center">Không có ảnh nào trong thư viện</p>';
          } else {
            html = '<div class="row g-2">';
            images.forEach(image => {
              html += `
                        <div class="col-4 col-sm-3 mb-2">
                            <div class="gallery-image" onclick="selectGalleryImage('${image.path}', '${image.name}')" 
                                 data-image="${image.name}" style="cursor:pointer;">
                                <img src="${image.path}" class="img-thumbnail" alt="${image.name}">
                            </div>
                        </div>`;
            });
            html += '</div>';
          }

          gallery.innerHTML = html;
        })
        .catch(error => {
          gallery.innerHTML = '<p class="text-danger">Không thể tải thư viện ảnh</p>';
          console.error('Error loading image gallery:', error);
        });
    }

    // Định nghĩa hàm chọn ảnh từ gallery ở phạm vi global
    window.selectGalleryImage = function(imagePath, imageName) {
      imagePreview.src = imagePath;
      selectedImageInput.value = imageName;
      imageInput.value = ''; // Xóa file đã chọn từ input

      // Đánh dấu ảnh được chọn
      const galleryImages = document.querySelectorAll('.gallery-image');
      galleryImages.forEach(img => {
        img.classList.remove('selected');
        if (img.dataset.image === imageName) {
          img.classList.add('selected');
        }
      });
    }
  });
</script>

<?php require "../layouts/footer.php"; ?>