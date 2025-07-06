<?php

require_once "../config/config.php";

// Nếu chưa đăng nhập
if (function_exists('session') && !session()->isLoggedIn()) {
    // Nếu là AJAX request thì trả về JSON
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này!']);
        exit;
    }

    // Nếu là request thường thì chuyển hướng
    session()->setFlash('error_message', 'Bạn cần đăng nhập để truy cập trang này!');
    redirect(APPURL);
    // Lưu ý: hàm redirect() đã có exit() bên trong
}

// Nếu là trang từ chối truy cập (người dùng không có quyền)
else {
    $pageTitle = "Không có quyền truy cập";
    require_once "../includes/header.php";
?>

    <section class="home-slider owl-carousel">
        <div class="slider-item" style="background-image: url(<?php echo APPURL; ?>/images/bg_1.jpg);" data-stellar-background-ratio="0.5">
            <div class="overlay"></div>
            <div class="container">
                <div class="row slider-text justify-content-center align-items-center">
                    <div class="col-md-7 col-sm-12 text-center ftco-animate">
                        <h1 class="mb-3 mt-5 bread">Không có quyền truy cập</h1>
                        <p class="breadcrumbs">
                            <span class="mr-2"><a href="<?php echo APPURL; ?>">Trang Chủ</a></span>
                            <span>Từ chối truy cập</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center ftco-animate">
                    <div class="alert alert-danger p-5">
                        <h3><i class="fas fa-exclamation-triangle fa-2x mb-3"></i></h3>
                        <h4 class="mb-4">Bạn không có quyền truy cập trang này!</h4>
                        <p>Bạn cần quyền truy cập phù hợp để xem trang này.</p>
                        <a href="<?php echo APPURL; ?>" class="btn btn-primary mt-3">Quay về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
    // Include footer
    require_once "../includes/footer.php";
}
?>