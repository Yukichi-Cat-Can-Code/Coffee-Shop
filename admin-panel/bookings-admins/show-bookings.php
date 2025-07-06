<?php

ob_start();

require_once "../../config/config.php";

// Kiểm tra quyền admin sử dụng hàm từ config
requireAdminLogin();

// Phân trang đơn giản hóa
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Sắp xếp an toàn
$allowed_sort_fields = ['ID', 'first_name', 'last_name', 'date', 'time', 'status'];
$allowed_order_types = ['ASC', 'DESC'];
$sort = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_fields)) ? $_GET['sort'] : 'ID';
$order = (isset($_GET['order']) && in_array(strtoupper($_GET['order']), $allowed_order_types)) ? strtoupper($_GET['order']) : 'ASC';

// Xây dựng điều kiện WHERE
$where_clause = [];
$params = [];

if (!empty($search)) {
  $where_clause[] = "(first_name LIKE ? OR last_name LIKE ? OR phone_number LIKE ? OR ID LIKE ?)";
  $search_param = "%$search%";
  $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

if (!empty($status_filter)) {
  $where_clause[] = "status = ?";
  $params[] = $status_filter;
}

$where_sql = !empty($where_clause) ? "WHERE " . implode(' AND ', $where_clause) : "";

// Đếm tổng số đặt bàn
$count_query = $conn->prepare("SELECT COUNT(*) FROM bookings $where_sql");
$count_query->execute($params);
$total_bookings = $count_query->fetchColumn();
$total_pages = ceil($total_bookings / $limit);

// Lấy danh sách đặt bàn
$query = "SELECT * FROM bookings $where_sql ORDER BY $sort $order LIMIT $offset, $limit";
$bookings_query = $conn->prepare($query);
$bookings_query->execute($params);
$bookings = $bookings_query->fetchAll(PDO::FETCH_OBJ);

// Hiển thị thông báo từ flash session
$success_message = '';
$error_message = '';
if (function_exists('session')) {
  $success_message = session()->getFlash('success_message');
  $error_message = session()->getFlash('error_message');
}

// Include header
require "../layouts/header.php";

// Xây dựng URL cho phân trang và sắp xếp
function buildUrl($page = null, $sort = null, $order = null)
{
  global $search, $status_filter, $limit;
  $current_sort = $sort ?? $_GET['sort'] ?? 'ID';
  $current_order = $order ?? ($_GET['order'] ?? 'ASC');
  $current_page = $page ?? $_GET['page'] ?? 1;

  return "?page=$current_page&sort=$current_sort&order=$current_order&search=" .
    urlencode($search) . "&status=" . urlencode($status_filter) . "&limit=$limit";
}
?>

<div class="container-fluid py-4">
  <!-- Page header -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">Quản Lý Đặt Bàn</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
          <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Đặt bàn</li>
        </ol>
      </nav>
    </div>
    <div>
      <a href="create-bookings.php" class="btn btn-sm btn-success me-2">
        <i class="fas fa-plus-circle"></i> Tạo đặt bàn mới
      </a>
    </div>
  </div>

  <!-- Messages -->
  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i> <?= $success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
      <div class="row align-items-center">
        <!-- Search -->
        <div class="col-md-4 mb-2 mb-md-0">
          <form action="" method="GET" class="d-flex">
            <div class="input-group">
              <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
              <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </form>
        </div>

        <!-- Status filter -->
        <div class="col-md-3 mb-2 mb-md-0">
          <form action="" method="GET" id="statusForm">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <select class="form-select" name="status" onchange="this.form.submit()">
              <option value="">Tất cả trạng thái</option>
              <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Đang xử lý</option>
              <option value="Confirmed" <?= $status_filter == 'Confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
              <option value="Cancelled" <?= $status_filter == 'Cancelled' ? 'selected' : '' ?>>Đã hủy</option>
              <option value="Completed" <?= $status_filter == 'Completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
            </select>
          </form>
        </div>

        <!-- Results per page -->
        <div class="col-md-2 mb-2 mb-md-0">
          <form action="" method="GET" id="limitForm">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <select class="form-select" name="limit" onchange="this.form.submit()">
              <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 mục</option>
              <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25 mục</option>
              <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 mục</option>
              <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 mục</option>
            </select>
          </form>
        </div>

        <div class="col-md-3 text-md-end mb-2 mb-md-0">
          <span class="text-muted">
            <?php
            if ($total_bookings > 0) {
              $showing_from = $offset + 1;
              $showing_to = min($offset + $limit, $total_bookings);
              echo "Hiển thị $showing_from-$showing_to / $total_bookings lượt đặt bàn";
            } else {
              echo "0 lượt đặt bàn";
            }
            ?>
          </span>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <!-- Table -->
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
                <a href="<?= buildUrl(null, 'first_name', ($sort == 'first_name' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Họ
                  <?php if ($sort == 'first_name'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>
                <a href="<?= buildUrl(null, 'last_name', ($sort == 'last_name' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Tên
                  <?php if ($sort == 'last_name'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>
                <a href="<?= buildUrl(null, 'date', ($sort == 'date' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Ngày
                  <?php if ($sort == 'date'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>
                <a href="<?= buildUrl(null, 'time', ($sort == 'time' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Giờ
                  <?php if ($sort == 'time'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>Điện thoại</th>
              <th>Lời nhắn</th>
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
            <?php if (count($bookings) > 0): ?>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td class="fw-bold">#<?= $booking->ID ?></td>
                  <td><?= htmlspecialchars($booking->first_name) ?></td>
                  <td><?= htmlspecialchars($booking->last_name) ?></td>
                  <td><?= htmlspecialchars($booking->date) ?></td>
                  <td><?= htmlspecialchars($booking->time) ?></td>
                  <td><?= htmlspecialchars($booking->phone_number) ?></td>
                  <td>
                    <?= truncateString(htmlspecialchars($booking->message), 50) ?>
                  </td>
                  <td>
                    <?php
                    // Xác định trạng thái và class tương ứng
                    switch ($booking->status) {
                      case 'Pending':
                        $status_class = 'warning';
                        $status_text = 'Đang xử lý';
                        break;
                      case 'Confirmed':
                        $status_class = 'primary';
                        $status_text = 'Đã xác nhận';
                        break;
                      case 'Completed':
                        $status_class = 'success';
                        $status_text = 'Đã hoàn thành';
                        break;
                      case 'Cancelled':
                        $status_class = 'danger';
                        $status_text = 'Đã hủy';
                        break;
                      default:
                        $status_class = 'secondary';
                        $status_text = $booking->status;
                    }
                    ?>
                    <span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2">
                      <a href="update-bookings.php?booking_id=<?= $booking->ID ?>" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $booking->ID ?>)">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center py-4">
                  <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5>Không tìm thấy đặt bàn nào</h5>
                    <p class="text-muted">Thay đổi bộ lọc hoặc tìm kiếm để xem kết quả</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Phân trang đơn giản hóa -->
    <?php if ($total_bookings > 0): ?>
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
            // Đơn giản hóa phân trang - hiển thị tối đa 5 trang
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Hàm xác nhận xóa
    window.confirmDelete = function(bookingId) {
      if (confirm('⚠️ XÁC NHẬN XÓA\n\nBạn có chắc chắn muốn xóa đặt bàn #' + bookingId + '?\nHành động này không thể khôi phục!')) {
        window.location.href = 'delete-bookings.php?booking_id=' + bookingId;
      }
    };
  });
</script>

<?php
require "../layouts/footer.php";
ob_end_flush();
?>