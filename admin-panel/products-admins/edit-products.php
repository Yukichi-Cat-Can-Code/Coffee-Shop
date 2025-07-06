<?php
require "../../config/config.php";
requireAdminLogin();

// Khởi tạo biến
$error_message = '';
$success_message = '';
$product = null;
$product_id = 0;

// Kiểm tra và xử lý product_id
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Lấy thông tin sản phẩm hiện tại
    $stmt = $conn->prepare("SELECT * FROM product WHERE ID = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$product) {
        session()->setFlash('product_message', "Không tìm thấy thông tin sản phẩm #$product_id");
        session()->setFlash('product_message_type', "danger");
        redirect(ADMINAPPURL . "/products-admins/show-products.php");
        exit;
    }

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

        if (empty($product_description)) {
            $errors[] = "Mô tả sản phẩm không được để trống";
        }

        if (empty($product_type) || $product_type === "choose") {
            $errors[] = "Vui lòng chọn loại sản phẩm";
        }

        // Xử lý upload ảnh nếu có
        $image_name = $product->image; // Giữ nguyên ảnh cũ nếu không có ảnh mới

        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $temp_image = $_FILES['image']['tmp_name'];
            $image_ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($image_ext, $allowed_extensions)) {
                $errors[] = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)";
            } else {
                // Tạo tên file duy nhất để tránh trùng lặp
                $image_name = "product_" . time() . "_" . uniqid() . "." . $image_ext;
                $upload_path = $_SERVER['DOCUMENT_ROOT'] . "/coffee-Shop/images/" . $image_name;

                if (!move_uploaded_file($temp_image, $upload_path)) {
                    $errors[] = "Không thể upload ảnh. Vui lòng thử lại";
                }
            }
        }

        // Nếu không có lỗi, tiến hành cập nhật
        if (empty($errors)) {
            try {
                $update_stmt = $conn->prepare("UPDATE product SET 
                    product_title = ?, 
                    price = ?, 
                    description = ?, 
                    image = ?, 
                    type = ? 
                    WHERE ID = ?");

                $update_result = $update_stmt->execute([
                    $product_title,
                    $product_price,
                    $product_description,
                    $image_name,
                    $product_type,
                    $product_id
                ]);

                if ($update_result) {
                    // Xóa ảnh cũ nếu đã upload ảnh mới và ảnh cũ không phải ảnh mặc định
                    if ($image_name !== $product->image && !empty($_FILES['image']['name'])) {
                        $old_image_path = $_SERVER['DOCUMENT_ROOT'] . "/coffee-Shop/images/" . $product->image;
                        if (file_exists($old_image_path) && $product->image !== 'default-product.jpg') {
                            @unlink($old_image_path);
                        }
                    }

                    session()->setFlash('product_message', "Cập nhật sản phẩm #$product_id thành công");
                    session()->setFlash('product_message_type', "success");
                    redirect(ADMINAPPURL . "/products-admins/show-products.php");
                    exit;
                } else {
                    $error_message = "Không thể cập nhật sản phẩm";
                }
            } catch (PDOException $e) {
                $error_message = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    }
} else {
    session()->setFlash('product_message', "ID sản phẩm không hợp lệ");
    session()->setFlash('product_message_type', "danger");
    redirect(ADMINAPPURL . "/products-admins/show-products.php");
    exit;
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <!-- Page header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chỉnh Sửa Sản Phẩm</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>/products-admins/show-products.php">Sản phẩm</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa sản phẩm #<?= $product_id ?></li>
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

            <!-- Thông báo thành công -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Thông tin sản phẩm</h5>
                </div>
                <div class="card-body">
                    <form action="edit-products.php?product_id=<?= $product_id ?>" method="POST" enctype="multipart/form-data">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="product_title" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="product_title" name="product_title" value="<?= htmlspecialchars($product->product_title) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="price" name="price" value="<?= $product->price ?>" min="0" step="1000" required>
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label">Loại sản phẩm <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="choose" <?= empty($product->type) ? 'selected' : '' ?>>-- Chọn loại sản phẩm --</option>
                                        <option value="drink" <?= $product->type === 'drink' ? 'selected' : '' ?>>Đồ uống</option>
                                        <option value="dessert" <?= $product->type === 'dessert' ? 'selected' : '' ?>>Tráng miệng</option>
                                        <option value="food" <?= $product->type === 'food' ? 'selected' : '' ?>>Đồ ăn</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả sản phẩm <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($product->description) ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3 text-center">
                                    <label for="image" class="form-label d-block">Hình ảnh sản phẩm</label>
                                    <div class="image-preview mb-3">
                                        <img src="<?= ADMINAPPURL ?>/../images/<?= htmlspecialchars($product->image) ?>"
                                            class="img-fluid rounded shadow-sm"
                                            id="imagePreview"
                                            style="max-height: 250px; max-width: 100%;"
                                            alt="<?= htmlspecialchars($product->product_title) ?>"
                                            onerror="this.src='<?= ADMINAPPURL ?>/../images/default-product.jpg'">
                                    </div>

                                    <div class="input-group mb-3">
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                        <button class="btn btn-outline-secondary" type="button" id="resetImageBtn">Reset</button>
                                    </div>
                                    <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= ADMINAPPURL ?>/products-admins/show-products.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview image before upload
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const resetImageBtn = document.getElementById('resetImageBtn');
        const originalImageSrc = imagePreview.src;

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
            imagePreview.src = originalImageSrc;
        });
    });
</script>

<?php require "../layouts/footer.php"; ?>