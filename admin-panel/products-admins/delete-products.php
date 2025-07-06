<?php
require "../../config/config.php";
requireAdminLogin();

// Xử lý xóa sản phẩm
if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    try {
        // Bắt đầu transaction
        $conn->beginTransaction();

        // Lấy thông tin sản phẩm trước khi xóa
        $select = $conn->prepare("SELECT * FROM product WHERE ID = ?");
        $select->execute([$product_id]);
        $product = $select->fetch(PDO::FETCH_OBJ);

        if ($product) {
            // Xóa ảnh sản phẩm nếu không phải ảnh mặc định
            if (!empty($product->image) && $product->image !== 'default-product.jpg') {
                $image_path = "../../images/" . $product->image;
                if (file_exists($image_path)) {
                    @unlink($image_path);
                }
            }

            // Thực hiện xóa sản phẩm
            $delete_query = $conn->prepare("DELETE FROM product WHERE ID = ?");
            $delete_query->execute([$product_id]);

            // Commit transaction nếu mọi thứ OK
            $conn->commit();

            session()->setFlash('product_message', "Đã xóa sản phẩm #" . $product_id . " thành công");
            session()->setFlash('product_message_type', "success");
        } else {
            session()->setFlash('product_message', "Không tìm thấy sản phẩm #" . $product_id);
            session()->setFlash('product_message_type', "danger");
        }
    } catch (PDOException $e) {
        // Rollback khi có lỗi
        $conn->rollBack();

        session()->setFlash('product_message', "Lỗi khi xóa sản phẩm: " . $e->getMessage());
        session()->setFlash('product_message_type', "danger");
    }

    // Chuyển hướng về trang danh sách sản phẩm
    redirect(ADMINAPPURL . "/products-admins/show-products.php");
    exit;
}

// Nếu không có ID sản phẩm, chuyển hướng về trang danh sách
session()->setFlash('product_message', "Không tìm thấy ID sản phẩm cần xóa");
session()->setFlash('product_message_type', "warning");
redirect(ADMINAPPURL . "/products-admins/show-products.php");
exit;
