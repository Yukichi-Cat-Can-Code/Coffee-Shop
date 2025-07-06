<?php

ob_start();

// Include config file
require_once "../../config/config.php";

// Kiểm tra quyền admin - sử dụng requireAdminLogin thay vì requireAdmin
requireAdminLogin();

// Process status change
if (isset($_POST['change_status']) && !empty($_POST['review_id'])) {
    $review_id = (int)$_POST['review_id'];
    $new_status = $_POST['new_status'] === 'Approved' ? 'Approved' : 'Rejected';

    try {
        $update = $conn->prepare("UPDATE reviews SET status = :status WHERE ID = :id");
        $update->execute([
            ':status' => $new_status,
            ':id' => $review_id
        ]);

        session()->setFlash('success_message', "Đã cập nhật trạng thái đánh giá thành " . $new_status);
    } catch (PDOException $e) {
        session()->setFlash('error_message', "Lỗi: Không thể cập nhật trạng thái");
        error_log("Error updating review status: " . $e->getMessage());
    }

    // Redirect to prevent form resubmission
    redirect(ADMINAPPURL . "/reviews-admins/show-reviews.php");
}

// Process review deletion
if (isset($_POST['delete_review']) && !empty($_POST['review_id'])) {
    $review_id = (int)$_POST['review_id'];

    try {
        $delete = $conn->prepare("DELETE FROM reviews WHERE ID = :id");
        $delete->execute([':id' => $review_id]);

        session()->setFlash('success_message', "Đã xóa đánh giá thành công");
    } catch (PDOException $e) {
        session()->setFlash('error_message', "Lỗi: Không thể xóa đánh giá");
        error_log("Error deleting review: " . $e->getMessage());
    }

    // Redirect to prevent form resubmission
    redirect(ADMINAPPURL . "/reviews-admins/show-reviews.php");
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $records_per_page;

// Filter setup
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Sort options
$allowed_sort_fields = ['ID', 'user_name', 'rating', 'created_at', 'status'];
$allowed_order_types = ['ASC', 'DESC'];
$sort = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_fields)) ? $_GET['sort'] : 'created_at';
$order = (isset($_GET['order']) && in_array(strtoupper($_GET['order']), $allowed_order_types)) ? strtoupper($_GET['order']) : 'DESC';

// Build query
$query = "SELECT r.*, u.user_name AS username 
          FROM reviews r 
          LEFT JOIN users u ON r.user_id = u.ID 
          WHERE 1=1";

$count_query = "SELECT COUNT(*) FROM reviews r LEFT JOIN users u ON r.user_id = u.ID WHERE 1=1";
$params = [];

if (!empty($status_filter)) {
    $query .= " AND r.status = :status";
    $count_query .= " AND r.status = :status";
    $params[':status'] = $status_filter;
}

if ($rating_filter > 0 && $rating_filter <= 5) {
    $query .= " AND r.rating = :rating";
    $count_query .= " AND r.rating = :rating";
    $params[':rating'] = $rating_filter;
}

if (!empty($search_term)) {
    $query .= " AND (r.review LIKE :search OR r.user_name LIKE :search OR u.user_name LIKE :search)";
    $count_query .= " AND (r.review LIKE :search OR r.user_name LIKE :search OR u.username LIKE :search)";
    $params[':search'] = "%$search_term%";
}

$query .= " ORDER BY r.$sort $order LIMIT :offset, :limit";

// Get total records for pagination
try {
    $count_stmt = $conn->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error counting reviews: " . $e->getMessage());
    $total_records = 0;
}

$total_pages = ceil($total_records / $records_per_page);

// Get reviews
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    error_log("Error fetching reviews: " . $e->getMessage());
    $reviews = [];
}

// Helper function for building URLs
function buildUrl($page = null, $sort = null, $order = null)
{
    global $status_filter, $search_term, $rating_filter, $records_per_page;

    $current_sort = $sort ?? $_GET['sort'] ?? 'created_at';
    $current_order = $order ?? ($_GET['order'] ?? 'DESC');
    $current_page = $page ?? $_GET['page'] ?? 1;

    return "?page=$current_page&sort=$current_sort&order=$current_order&status=" .
        urlencode($status_filter) . "&search=" . urlencode($search_term) .
        "&rating=" . $rating_filter . "&limit=" . $records_per_page;
}

// Include header
require_once "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <!-- Page header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Quản Lý Đánh Giá</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Đánh Giá</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php
    // Display flash messages
    if (function_exists('session')) {
        $success_message = session()->getFlash('success_message');
        $error_message = session()->getFlash('error_message');

        if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif;

        if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
    <?php endif;
    }
    ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <!-- Search -->
                <div class="col-md-4 mb-2 mb-md-0">
                    <form action="" method="GET" class="d-flex">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                        <input type="hidden" name="rating" value="<?= $rating_filter ?>">
                        <input type="hidden" name="limit" value="<?= $records_per_page ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search_term) ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Status filter -->
                <div class="col-md-3 mb-2 mb-md-0">
                    <form action="" method="GET" id="statusForm">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>">
                        <input type="hidden" name="rating" value="<?= $rating_filter ?>">
                        <input type="hidden" name="limit" value="<?= $records_per_page ?>">
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Đã từ chối</option>
                        </select>
                    </form>
                </div>

                <!-- Rating filter -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <form action="" method="GET" id="ratingForm">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                        <input type="hidden" name="limit" value="<?= $records_per_page ?>">
                        <select class="form-select" name="rating" onchange="this.form.submit()">
                            <option value="0">Tất cả sao</option>
                            <option value="5" <?= $rating_filter === 5 ? 'selected' : '' ?>>5 sao</option>
                            <option value="4" <?= $rating_filter === 4 ? 'selected' : '' ?>>4 sao</option>
                            <option value="3" <?= $rating_filter === 3 ? 'selected' : '' ?>>3 sao</option>
                            <option value="2" <?= $rating_filter === 2 ? 'selected' : '' ?>>2 sao</option>
                            <option value="1" <?= $rating_filter === 1 ? 'selected' : '' ?>>1 sao</option>
                        </select>
                    </form>
                </div>

                <!-- Records per page -->
                <div class="col-md-2 mb-2 mb-md-0">
                    <form action="" method="GET" id="limitForm">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                        <input type="hidden" name="rating" value="<?= $rating_filter ?>">
                        <select class="form-select" name="limit" onchange="this.form.submit()">
                            <option value="10" <?= $records_per_page == 10 ? 'selected' : '' ?>>10 mục</option>
                            <option value="25" <?= $records_per_page == 25 ? 'selected' : '' ?>>25 mục</option>
                            <option value="50" <?= $records_per_page == 50 ? 'selected' : '' ?>>50 mục</option>
                            <option value="100" <?= $records_per_page == 100 ? 'selected' : '' ?>>100 mục</option>
                        </select>
                    </form>
                </div>

                <!-- Result count -->
                <div class="col-md-1 text-end mb-2 mb-md-0">
                    <span class="badge bg-info">
                        <?= $total_records ?> đánh giá
                    </span>
                </div>
            </div>

            <!-- Filter pills and clear button -->
            <?php if (!empty($search_term) || !empty($status_filter) || $rating_filter > 0): ?>
                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted me-2">Bộ lọc:</span>

                    <?php if (!empty($search_term)): ?>
                        <span class="badge bg-light text-dark border">
                            Tìm kiếm: <?= htmlspecialchars($search_term) ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($status_filter)): ?>
                        <span class="badge bg-light text-dark border">
                            Trạng thái:
                            <?php if ($status_filter === 'Pending'): ?>
                                <span class="text-warning">Chờ duyệt</span>
                            <?php elseif ($status_filter === 'Approved'): ?>
                                <span class="text-success">Đã duyệt</span>
                            <?php elseif ($status_filter === 'Rejected'): ?>
                                <span class="text-danger">Đã từ chối</span>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($rating_filter > 0): ?>
                        <span class="badge bg-light text-dark border">
                            Đánh giá: <?= $rating_filter ?> sao
                        </span>
                    <?php endif; ?>

                    <a href="<?= ADMINAPPURL ?>/reviews-admins/show-reviews.php" class="btn btn-sm btn-outline-secondary ms-auto">
                        <i class="fas fa-times me-1"></i> Xóa bộ lọc
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-body p-0">
            <!-- Reviews Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>
                                <a href="<?= buildUrl(null, 'ID', ($sort == 'ID' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                    #ID
                                    <?php if ($sort == 'ID'): ?>
                                        <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= buildUrl(null, 'user_name', ($sort == 'user_name' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                    Người đánh giá
                                    <?php if ($sort == 'user_name'): ?>
                                        <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Nội dung</th>
                            <th>
                                <a href="<?= buildUrl(null, 'rating', ($sort == 'rating' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                    Đánh giá
                                    <?php if ($sort == 'rating'): ?>
                                        <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= buildUrl(null, 'created_at', ($sort == 'created_at' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                    Ngày tạo
                                    <?php if ($sort == 'created_at'): ?>
                                        <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?= buildUrl(null, 'status', ($sort == 'status' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                    Trạng thái
                                    <?php if ($sort == 'status'): ?>
                                        <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reviews) > 0): ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $review->ID ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($review->username ?: $review->user_name ?: 'Khách hàng') ?></strong>
                                        <?php if ($review->user_id): ?>
                                            <br>
                                            <span class="badge bg-secondary">ID: <?= $review->user_id ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo truncateString(htmlspecialchars($review->review), 100);
                                        ?>
                                        <a href="#" class="small ms-2" data-bs-toggle="modal" data-bs-target="#reviewDetailModal<?= $review->ID ?>">
                                            <i class="fas fa-eye"></i> Xem chi tiết
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $rating = isset($review->rating) ? (int)$review->rating : 5;
                                        for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?= ($i <= $rating) ? ' text-warning' : '-o text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($review->created_at)) ?>
                                    </td>
                                    <td>
                                        <?php if ($review->status === 'Pending'): ?>
                                            <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                        <?php elseif ($review->status === 'Approved'): ?>
                                            <span class="badge bg-success">Đã duyệt</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Từ chối</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <?php if ($review->status !== 'Approved'): ?>
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="review_id" value="<?= $review->ID ?>">
                                                    <input type="hidden" name="new_status" value="Approved">
                                                    <button type="submit" name="change_status" class="btn btn-sm btn-success" title="Duyệt đánh giá">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($review->status !== 'Rejected'): ?>
                                                <form action="" method="POST" class="d-inline">
                                                    <input type="hidden" name="review_id" value="<?= $review->ID ?>">
                                                    <input type="hidden" name="new_status" value="Rejected">
                                                    <button type="submit" name="change_status" class="btn btn-sm btn-warning" title="Từ chối đánh giá">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <form action="" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                                                <input type="hidden" name="review_id" value="<?= $review->ID ?>">
                                                <button type="submit" name="delete_review" class="btn btn-sm btn-danger" title="Xóa đánh giá">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>

                                            <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#reviewDetailModal<?= $review->ID ?>" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>

                                        <!-- Modal chi tiết đánh giá -->
                                        <div class="modal fade" id="reviewDetailModal<?= $review->ID ?>" tabindex="-1" aria-labelledby="reviewDetailModalLabel<?= $review->ID ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="reviewDetailModalLabel<?= $review->ID ?>">Chi tiết đánh giá #<?= $review->ID ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <h5 class="border-bottom pb-2">Thông tin đánh giá</h5>
                                                                <div class="mb-3">
                                                                    <p class="fw-bold mb-1">Người đánh giá:</p>
                                                                    <p><?= htmlspecialchars($review->username ?: $review->user_name ?: 'Khách hàng') ?></p>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <p class="fw-bold mb-1">Đánh giá:</p>
                                                                    <div>
                                                                        <?php
                                                                        for ($i = 1; $i <= 5; $i++): ?>
                                                                            <i class="fas fa-star<?= ($i <= $rating) ? ' text-warning' : '-o text-muted' ?> fa-lg"></i>
                                                                        <?php endfor; ?>
                                                                        <span class="ms-2">(<?= $rating ?> sao)</span>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <p class="fw-bold mb-1">Nội dung đánh giá:</p>
                                                                    <div class="border rounded p-3 bg-light">
                                                                        <?= nl2br(htmlspecialchars($review->review)) ?>
                                                                    </div>
                                                                </div>

                                                                <?php if ($review->order_id): ?>
                                                                    <div class="mb-3">
                                                                        <p class="fw-bold mb-1">Đơn hàng:</p>
                                                                        <p><a href="<?= ADMINAPPURL ?>/orders-admins/show-order.php?order_id=<?= $review->order_id ?>" target="_blank">#<?= $review->order_id ?></a></p>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <h5 class="border-bottom pb-2">Thông tin khác</h5>

                                                                <div class="mb-3">
                                                                    <p class="fw-bold mb-1">Trạng thái:</p>
                                                                    <?php if ($review->status === 'Pending'): ?>
                                                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                                                    <?php elseif ($review->status === 'Approved'): ?>
                                                                        <span class="badge bg-success">Đã duyệt</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger">Từ chối</span>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <p class="fw-bold mb-1">Thời gian tạo:</p>
                                                                    <p><?= date('d/m/Y H:i:s', strtotime($review->created_at)) ?></p>
                                                                </div>

                                                                <?php if ($review->user_id): ?>
                                                                    <div class="mb-3">
                                                                        <p class="fw-bold mb-1">User ID:</p>
                                                                        <p><?= $review->user_id ?></p>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>

                                                        <?php if ($review->status !== 'Approved'): ?>
                                                            <form action="" method="POST" class="d-inline">
                                                                <input type="hidden" name="review_id" value="<?= $review->ID ?>">
                                                                <input type="hidden" name="new_status" value="Approved">
                                                                <button type="submit" name="change_status" class="btn btn-success">
                                                                    <i class="fas fa-check me-1"></i> Duyệt đánh giá
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <?php if ($review->status !== 'Rejected'): ?>
                                                            <form action="" method="POST" class="d-inline">
                                                                <input type="hidden" name="review_id" value="<?= $review->ID ?>">
                                                                <input type="hidden" name="new_status" value="Rejected">
                                                                <button type="submit" name="change_status" class="btn btn-warning">
                                                                    <i class="fas fa-ban me-1"></i> Từ chối
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                        <h5>Không tìm thấy đánh giá nào</h5>
                                        <p class="text-muted">Thay đổi bộ lọc hoặc tìm kiếm để xem kết quả</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white py-3">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        <!-- Nút Previous -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildUrl($page - 1) ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php
                        // Hiển thị tối đa 5 trang
                        $start_page = max(1, min($page - 2, $total_pages - 4));
                        $end_page = min($total_pages, $start_page + 4);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= buildUrl($i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Nút Next -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildUrl($page + 1) ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once "../layouts/footer.php";
ob_end_flush();
?>