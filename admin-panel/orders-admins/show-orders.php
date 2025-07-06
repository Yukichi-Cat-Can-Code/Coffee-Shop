<?php
require "../../config/config.php";
requireAdminLogin();

// Xác định loại đơn hàng (web hoặc pos)
$order_type = isset($_GET['type']) ? $_GET['type'] : 'web';

// Phân trang đơn giản hóa
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

// Sắp xếp an toàn - điều chỉnh theo loại đơn hàng
if ($order_type == 'pos') {
  $allowed_sort_fields = ['order_id', 'created_at', 'admin_name', 'final_amount', 'payment_status', 'order_status'];
  $default_sort = 'order_id';
} else {
  $allowed_sort_fields = ['ID', 'firstname', 'lastname', 'payable_total_cost', 'status', 'order_date'];
  $default_sort = 'ID';
}
$allowed_order_types = ['ASC', 'DESC'];
$sort = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_fields)) ? $_GET['sort'] : $default_sort;
$order = (isset($_GET['order']) && in_array(strtoupper($_GET['order']), $allowed_order_types)) ? strtoupper($_GET['order']) : 'DESC';

// Xây dựng điều kiện WHERE dựa trên loại đơn hàng
$where_clause = [];
$params = [];

if ($order_type == 'pos') {
  if (!empty($search)) {
    $where_clause[] = "(order_id LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
  }
  if (!empty($status_filter)) {
    $where_clause[] = "order_status = ?";
    $params[] = $status_filter;
  }
  if (!empty($date_start)) {
    $where_clause[] = "created_at >= ?";
    $params[] = $date_start . " 00:00:00";
  }
  if (!empty($date_end)) {
    $where_clause[] = "created_at <= ?";
    $params[] = $date_end . " 23:59:59";
  }
  $where_sql = !empty($where_clause) ? "WHERE " . implode(' AND ', $where_clause) : "";
  $count_query = $conn->prepare("SELECT COUNT(*) FROM pos_orders $where_sql");
  $count_query->execute($params);
  $total_orders = $count_query->fetchColumn();
  $total_pages = ceil($total_orders / $limit);

  $query = "SELECT o.*, a.admin_name 
              FROM pos_orders o 
              LEFT JOIN admins a ON o.admin_id = a.ID 
              $where_sql 
              ORDER BY $sort $order 
              LIMIT $offset, $limit";
  $orders_query = $conn->prepare($query);
  $orders_query->execute($params);
  $orders_list = $orders_query->fetchAll(PDO::FETCH_OBJ);
} else {
  if (!empty($search)) {
    $where_clause[] = "(firstname LIKE ? OR lastname LIKE ? OR phone LIKE ? OR ID LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
  }
  if (!empty($status_filter)) {
    $where_clause[] = "status = ?";
    $params[] = $status_filter;
  }
  if (!empty($date_start)) {
    $where_clause[] = "order_date >= ?";
    $params[] = $date_start . " 00:00:00";
  }
  if (!empty($date_end)) {
    $where_clause[] = "order_date <= ?";
    $params[] = $date_end . " 23:59:59";
  }
  $where_sql = !empty($where_clause) ? "WHERE " . implode(' AND ', $where_clause) : "";
  $count_query = $conn->prepare("SELECT COUNT(*) FROM orders $where_sql");
  $count_query->execute($params);
  $total_orders = $count_query->fetchColumn();
  $total_pages = ceil($total_orders / $limit);

  $query = "SELECT * FROM orders $where_sql ORDER BY $sort $order LIMIT $offset, $limit";
  $orders_query = $conn->prepare($query);
  $orders_query->execute($params);
  $orders_list = $orders_query->fetchAll(PDO::FETCH_OBJ);
}

// Hiển thị thông báo từ flash session
$message = session()->getFlash('success_message') ?? session()->getFlash('error_message');
$message_type = session()->getFlash('success_message') ? 'success' : (session()->getFlash('error_message') ? 'danger' : '');

require "../layouts/header.php";

// Xây dựng URL cho phân trang và sắp xếp
function buildUrl($page = null, $sort = null, $order = null)
{
  global $search, $status_filter, $limit, $order_type, $date_start, $date_end;
  $current_sort = $sort ?? $_GET['sort'] ?? 'ID';
  $current_order = $order ?? ($_GET['order'] ?? 'DESC');
  $current_page = $page ?? $_GET['page'] ?? 1;

  return "?type=$order_type&page=$current_page&sort=$current_sort&order=$current_order&search=" .
    urlencode($search) . "&status=" . urlencode($status_filter) . "&limit=$limit" .
    "&date_start=" . urlencode($date_start) . "&date_end=" . urlencode($date_end);
}
?>

<div class="container-fluid py-4">
  <!-- Page header -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">Quản Lý Đơn Hàng</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
          <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Đơn hàng</li>
        </ol>
      </nav>
    </div>
    <div class="d-flex gap-2">
      <!-- Nút Tạo đơn hàng mới -->
      <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" id="createOrderDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fas fa-plus-circle me-1"></i> Tạo đơn hàng mới
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createOrderDropdown">
          <li>
            <a class="dropdown-item" href="<?= ADMINAPPURL ?>/pos-admins/index.php">
              <i class="fas fa-cash-register me-2"></i> Đơn hàng tại quán (POS)
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="<?= ADMINAPPURL ?>/orders-admins/create-orders.php">
              <i class="fas fa-shopping-cart me-2"></i> Đơn hàng online
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Messages -->
<?php if (!empty($message)): ?>
  <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
    <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
    <?= $message ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<!-- Order Type Selector -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link <?= $order_type == 'web' ? 'active' : '' ?>" href="?type=web">
      <i class="fas fa-shopping-cart me-1"></i> Đơn hàng Online
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $order_type == 'pos' ? 'active' : '' ?>" href="?type=pos">
      <i class="fas fa-cash-register me-1"></i> Đơn hàng POS
    </a>
  </li>
</ul>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3">
    <div class="row g-3 align-items-center">
      <!-- Search -->
      <div class="col-md-4">
        <form action="" method="GET" class="d-flex">
          <input type="hidden" name="type" value="<?= $order_type ?>">
          <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </form>
      </div>

      <!-- Date range -->
      <div class="col-md-4">
        <form id="dateRangeForm" action="" method="GET" class="d-flex gap-2">
          <input type="hidden" name="type" value="<?= $order_type ?>">
          <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
          <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">

          <div class="input-group input-group-sm">
            <span class="input-group-text">Từ</span>
            <input type="date" class="form-control" name="date_start" value="<?= $date_start ?>">
          </div>

          <div class="input-group input-group-sm">
            <span class="input-group-text">Đến</span>
            <input type="date" class="form-control" name="date_end" value="<?= $date_end ?>">
          </div>

          <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-filter"></i>
          </button>
        </form>
      </div>

      <!-- Status filter -->
      <div class="col-md-2">
        <form action="" method="GET" id="statusForm">
          <input type="hidden" name="type" value="<?= $order_type ?>">
          <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
          <input type="hidden" name="date_start" value="<?= $date_start ?>">
          <input type="hidden" name="date_end" value="<?= $date_end ?>">

          <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <?php if ($order_type == 'pos'): ?>
              <option value="Hoàn thành" <?= $status_filter == 'Hoàn thành' ? 'selected' : '' ?>>Hoàn thành</option>
              <option value="Đang xử lý" <?= $status_filter == 'Đang xử lý' ? 'selected' : '' ?>>Đang xử lý</option>
              <option value="Đã hủy" <?= $status_filter == 'Đã hủy' ? 'selected' : '' ?>>Đã hủy</option>
            <?php else: ?>
              <option value="Pending" <?= $status_filter == 'Pending' ? 'selected' : '' ?>>Đang xử lý</option>
              <option value="Processing" <?= $status_filter == 'Processing' ? 'selected' : '' ?>>Đang chuẩn bị</option>
              <option value="Shipped" <?= $status_filter == 'Shipped' ? 'selected' : '' ?>>Đang giao hàng</option>
              <option value="Delivered" <?= $status_filter == 'Delivered' ? 'selected' : '' ?>>Đã giao</option>
              <option value="Cancelled" <?= $status_filter == 'Cancelled' ? 'selected' : '' ?>>Đã hủy</option>
            <?php endif; ?>
          </select>
        </form>
      </div>

      <!-- Results per page -->
      <div class="col-md-2">
        <form action="" method="GET" id="limitForm">
          <input type="hidden" name="type" value="<?= $order_type ?>">
          <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
          <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
          <input type="hidden" name="date_start" value="<?= $date_start ?>">
          <input type="hidden" name="date_end" value="<?= $date_end ?>">

          <select class="form-select form-select-sm" name="limit" onchange="this.form.submit()">
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10 mục</option>
            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25 mục</option>
            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 mục</option>
            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 mục</option>
          </select>
        </form>
      </div>
    </div>

    <!-- Results Summary -->
    <div class="mt-3 text-muted small">
      <?php
      if ($total_orders > 0) {
        $showing_from = $offset + 1;
        $showing_to = min($offset + $limit, $total_orders);
        echo "Hiển thị $showing_from-$showing_to / $total_orders đơn hàng";
      } else {
        echo "0 đơn hàng";
      }
      ?>
    </div>
  </div>

  <div class="card-body p-0">
    <!-- Table -->
    <div class="table-responsive">
      <?php if ($order_type == 'pos'): ?>
        <!-- POS Orders Table -->
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="py-3">
                <a href="<?= buildUrl(null, 'order_id', ($sort == 'order_id' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  #Mã đơn
                  <?php if ($sort == 'order_id'): ?>
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
              <th>Khách hàng</th>
              <th>
                <a href="<?= buildUrl(null, 'admin_name', ($sort == 'admin_name' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Thu ngân
                  <?php if ($sort == 'admin_name'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>
                <a href="<?= buildUrl(null, 'final_amount', ($sort == 'final_amount' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Tổng tiền
                  <?php if ($sort == 'final_amount'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>Thanh toán</th>
              <th>
                <a href="<?= buildUrl(null, 'order_status', ($sort == 'order_status' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Trạng thái
                  <?php if ($sort == 'order_status'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th class="text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($orders_list) > 0): ?>
              <?php foreach ($orders_list as $order): ?>
                <tr>
                  <td class="fw-bold">#<?= $order->order_id ?></td>
                  <td>
                    <?= date('d/m/Y H:i', strtotime($order->created_at)) ?>
                  </td>
                  <td>
                    <?php if (!empty($order->customer_name)): ?>
                      <?= htmlspecialchars($order->customer_name) ?>
                      <?php if (!empty($order->customer_phone)): ?>
                        <div class="small text-muted"><?= htmlspecialchars($order->customer_phone) ?></div>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted">Khách vãng lai</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($order->admin_name) ?></td>
                  <td class="fw-bold"><?= number_format($order->final_amount, 0, ',', '.') ?> ₫</td>
                  <td>
                    <?php
                    // Payment method badge
                    $payment_icon = 'money-bill-wave';
                    $payment_class = 'success';

                    // Detect payment method
                    if (stripos($order->payment_method ?? '', 'thẻ') !== false || stripos($order->payment_method ?? '', 'card') !== false) {
                      $payment_icon = 'credit-card';
                      $payment_class = 'info';
                    } elseif (
                      stripos($order->payment_method ?? '', 'momo') !== false ||
                      stripos($order->payment_method ?? '', 'zalopay') !== false ||
                      stripos($order->payment_method ?? '', 'banking') !== false
                    ) {
                      $payment_icon = 'mobile-alt';
                      $payment_class = 'primary';
                    }
                    ?>
                    <span class="badge bg-<?= $payment_class ?> rounded-pill">
                      <i class="fas fa-<?= $payment_icon ?> me-1"></i>
                      <?= htmlspecialchars($order->payment_method ?? 'Tiền mặt') ?>
                    </span>
                  </td>
                  <td>
                    <?php
                    // Xác định trạng thái và icon tương ứng
                    switch ($order->order_status) {
                      case 'Hoàn thành':
                        $status_class = 'success';
                        $icon = 'check-circle';
                        break;
                      case 'Đang xử lý':
                        $status_class = 'warning';
                        $icon = 'clock';
                        break;
                      case 'Đã hủy':
                        $status_class = 'danger';
                        $icon = 'times-circle';
                        break;
                      default:
                        $status_class = 'secondary';
                        $icon = 'circle';
                    }
                    ?>
                    <span class="status-badge bg-<?= $status_class ?>">
                      <i class="fas fa-<?= $icon ?> me-1"></i>
                      <?= $order->order_status ?>
                    </span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2">
                      <button type="button" class="btn btn-sm btn-outline-primary view-pos-order" data-order-id="<?= $order->order_id ?>">
                        <i class="fas fa-eye"></i>
                      </button>
                      <a href="<?= ADMINAPPURL ?>/pos-admins/receipt.php?order_id=<?= $order->order_id ?>" class="btn btn-sm btn-outline-success" title="Xem hóa đơn">
                        <i class="fas fa-receipt"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-danger delete-pos-order" data-order-id="<?= $order->order_id ?>">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>

                    <!-- POS Order Detail Modal -->
                    <div class="modal fade" id="posOrderModal<?= $order->order_id ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Chi tiết đơn hàng POS #<?= $order->order_id ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body" id="pos-order-detail-<?= $order->order_id ?>">
                            <div class="text-center">
                              <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                              </div>
                              <p>Đang tải thông tin đơn hàng...</p>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <a href="<?= ADMINAPPURL ?>/pos-admins/print-receipt.php?order_id=<?= $order->order_id ?>&format=a5&preview=1" class="btn btn-primary" target="_blank">
                              <i class="fas fa-print me-1"></i> In hóa đơn
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- POS Delete Confirmation Modal -->
                    <div class="modal fade" id="deletePosModal<?= $order->order_id ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="text-center mb-3">
                              <i class="fas fa-trash-alt text-danger fa-3x mb-3"></i>
                              <h5>Bạn có chắc chắn muốn xóa đơn hàng POS #<?= $order->order_id ?>?</h5>
                            </div>
                            <div class="alert alert-warning">
                              <i class="fas fa-exclamation-circle me-2"></i>
                              <strong>Cảnh báo:</strong> Tất cả thông tin đơn hàng sẽ bị xóa vĩnh viễn và không thể khôi phục.
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                              <i class="fas fa-times me-1"></i> Hủy
                            </button>
                            <button type="button" class="btn btn-danger" onclick="confirmDeletePosOrder(<?= $order->order_id ?>)">
                              <i class="fas fa-trash me-1"></i> Xóa
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="text-center py-4">
                  <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5>Không tìm thấy đơn hàng POS nào</h5>
                    <p class="text-muted">Thay đổi bộ lọc hoặc tìm kiếm để xem kết quả</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php else: ?>
        <!-- Web Orders Table -->
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="py-3">
                <a href="<?= buildUrl(null, 'ID', ($sort == 'ID' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  #ID
                  <?php if ($sort == 'ID'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>Họ tên khách hàng</th>
              <th>Liên hệ</th>
              <th>
                <a href="<?= buildUrl(null, 'payable_total_cost', ($sort == 'payable_total_cost' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Tổng tiền
                  <?php if ($sort == 'payable_total_cost'): ?>
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
            <?php if (count($orders_list) > 0): ?>
              <?php foreach ($orders_list as $order): ?>
                <tr>
                  <td class="fw-bold">#<?= $order->ID ?></td>
                  <td>
                    <?= htmlspecialchars($order->firstname) ?> <?= htmlspecialchars($order->lastname) ?>
                    <div class="small text-muted"><?= htmlspecialchars($order->towncity) ?></div>
                  </td>
                  <td>
                    <div><?= htmlspecialchars($order->phone) ?></div>
                    <div class="small text-muted text-truncate" style="max-width: 200px;"><?= htmlspecialchars($order->streetaddress) ?></div>
                  </td>
                  <td class="fw-bold"><?= number_format($order->payable_total_cost, 0, ',', '.') ?> ₫</td>
                  <td>
                    <?php
                    // Xác định trạng thái và icon tương ứng
                    switch ($order->status) {
                      case 'Pending':
                        $status_class = 'warning';
                        $status_text = 'Đang xử lý';
                        $icon = 'clock';
                        break;
                      case 'Processing':
                        $status_class = 'info';
                        $status_text = 'Đang chuẩn bị';
                        $icon = 'cog';
                        break;
                      case 'Shipped':
                        $status_class = 'primary';
                        $status_text = 'Đang giao';
                        $icon = 'shipping-fast';
                        break;
                      case 'Delivered':
                        $status_class = 'success';
                        $status_text = 'Đã giao';
                        $icon = 'check-circle';
                        break;
                      case 'Cancelled':
                        $status_class = 'danger';
                        $status_text = 'Đã hủy';
                        $icon = 'times-circle';
                        break;
                      default:
                        $status_class = 'secondary';
                        $status_text = $order->status;
                        $icon = 'circle';
                    }
                    ?>
                    <span class="status-badge bg-<?= $status_class ?>">
                      <i class="fas fa-<?= $icon ?> me-1"></i>
                      <?= $status_text ?>
                    </span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2">
                      <button type="button" class="btn btn-sm btn-outline-primary view-order" data-order-id="<?= $order->ID ?>">
                        <i class="fas fa-eye"></i>
                      </button>
                      <a href="update-orders.php?order_id=<?= $order->ID ?>" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-outline-danger delete-order" data-order-id="<?= $order->ID ?>">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>

                    <!-- Order Detail Modal -->
                    <div class="modal fade" id="orderModal<?= $order->ID ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">
                              <i class="fas fa-shopping-cart text-primary me-2"></i>
                              Chi tiết đơn hàng #<?= $order->ID ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body" id="web-order-detail-<?= $order->ID ?>">
                            <div class="text-center py-4">
                              <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                              </div>
                              <p class="mt-2">Đang tải thông tin đơn hàng...</p>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <a href="update-orders.php?order_id=<?= $order->ID ?>" class="btn btn-primary">
                              <i class="fas fa-edit me-1"></i> Cập nhật
                            </a>
                            <?php if ($order->status != 'Delivered' && $order->status != 'Cancelled'): ?>
                              <button type="button" class="btn btn-success" onclick="updateOrderStatus(<?= $order->ID ?>, 'Delivered')">
                                <i class="fas fa-check-circle me-1"></i> Đánh dấu đã giao
                              </button>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteModal<?= $order->ID ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="text-center mb-3">
                              <i class="fas fa-trash-alt text-danger fa-3x mb-3"></i>
                              <h5>Bạn có chắc chắn muốn xóa đơn hàng #<?= $order->ID ?>?</h5>
                            </div>
                            <div class="alert alert-warning">
                              <i class="fas fa-exclamation-circle me-2"></i>
                              <strong>Cảnh báo:</strong> Tất cả thông tin đơn hàng sẽ bị xóa vĩnh viễn và không thể khôi phục.
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                              <i class="fas fa-times me-1"></i> Hủy
                            </button>
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteOrder(<?= $order->ID ?>)">
                              <i class="fas fa-trash me-1"></i> Xóa
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center py-4">
                  <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5>Không tìm thấy đơn hàng nào</h5>
                    <p class="text-muted">Thay đổi bộ lọc hoặc tìm kiếm để xem kết quả</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Phân trang -->
  <?php if ($total_orders > 0): ?>
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

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-export me-2"></i>Xuất báo cáo đơn hàng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="exportForm" action="export-orders.php" method="post">
          <div class="mb-3">
            <label class="form-label">Loại đơn hàng</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="export_type" id="exportTypePos" value="pos" checked>
              <label class="form-check-label" for="exportTypePos">
                Đơn hàng POS
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="export_type" id="exportTypeWeb" value="web">
              <label class="form-check-label" for="exportTypeWeb">
                Đơn hàng Online
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="export_type" id="exportTypeAll" value="all">
              <label class="form-check-label" for="exportTypeAll">
                Tất cả đơn hàng
              </label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Khoảng thời gian</label>
            <div class="row g-2">
              <div class="col">
                <label class="form-label small">Từ ngày</label>
                <input type="date" class="form-control" name="export_date_start" value="<?= date('Y-m-01') ?>">
              </div>
              <div class="col">
                <label class="form-label small">Đến ngày</label>
                <input type="date" class="form-control" name="export_date_end" value="<?= date('Y-m-d') ?>">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Định dạng</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="export_format" id="exportFormatExcel" value="excel" checked>
              <label class="form-check-label" for="exportFormatExcel">
                <i class="fas fa-file-excel text-success me-1"></i> Excel (.xlsx)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="export_format" id="exportFormatCsv" value="csv">
              <label class="form-check-label" for="exportFormatCsv">
                <i class="fas fa-file-csv text-primary me-1"></i> CSV (.csv)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="export_format" id="exportFormatPdf" value="pdf">
              <label class="form-check-label" for="exportFormatPdf">
                <i class="fas fa-file-pdf text-danger me-1"></i> PDF (.pdf)
              </label>
            </div>
          </div>

          <div class="alert alert-info small">
            <i class="fas fa-info-circle me-1"></i>
            Báo cáo sẽ bao gồm tất cả đơn hàng trong khoảng thời gian đã chọn, cùng với thông tin chi tiết và tổng hợp thống kê.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('exportForm').submit()">
          <i class="fas fa-download me-1"></i> Xuất báo cáo
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  .status-badge {
    padding: 6px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    line-height: 1;
    white-space: nowrap;
  }

  .bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
  }

  .bg-info {
    background-color: #0dcaf0 !important;
    color: #fff;
  }

  .bg-primary {
    background-color: #0d6efd !important;
    color: #fff;
  }

  .bg-success {
    background-color: #198754 !important;
    color: #fff;
  }

  .bg-danger {
    background-color: #dc3545 !important;
    color: #fff;
  }

  .bg-secondary {
    background-color: #6c757d !important;
    color: #fff;
  }

  .nav-tabs .nav-link {
    color: #495057;
    background-color: #f8f9fa;
    border-color: #dee2e6 #dee2e6 #fff;
  }

  .nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
    border-bottom: 2px solid #0d6efd;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo và xử lý modal cho đơn hàng web
    const viewButtons = document.querySelectorAll('.view-order');
    const deleteButtons = document.querySelectorAll('.delete-order');

    viewButtons.forEach(button => {
      button.addEventListener('click', function() {
        const orderId = this.getAttribute('data-order-id');
        const modal = new bootstrap.Modal(document.getElementById('orderModal' + orderId));
        modal.show();

        // Tải dữ liệu đơn hàng web bằng Ajax
        fetch('get-web-order-details.php?order_id=' + orderId)
          .then(response => response.text())
          .then(data => {
            document.getElementById('web-order-detail-' + orderId).innerHTML = data;
          })
          .catch(error => {
            document.getElementById('web-order-detail-' + orderId).innerHTML =
              '<div class="alert alert-danger">Lỗi: Không thể tải thông tin đơn hàng</div>';
          });
      });
    });

    deleteButtons.forEach(button => {
      button.addEventListener('click', function() {
        const orderId = this.getAttribute('data-order-id');
        const modal = new bootstrap.Modal(document.getElementById('deleteModal' + orderId));
        modal.show();
      });
    });

    // Khởi tạo và xử lý modal cho đơn hàng POS
    const viewPosButtons = document.querySelectorAll('.view-pos-order');
    const deletePosButtons = document.querySelectorAll('.delete-pos-order');

    viewPosButtons.forEach(button => {
      button.addEventListener('click', function() {
        const orderId = this.getAttribute('data-order-id');
        const modal = new bootstrap.Modal(document.getElementById('posOrderModal' + orderId));
        modal.show();

        // Tải dữ liệu đơn hàng bằng Ajax
        fetch('get-pos-order-details.php?order_id=' + orderId)
          .then(response => response.text())
          .then(data => {
            document.getElementById('pos-order-detail-' + orderId).innerHTML = data;
          })
          .catch(error => {
            document.getElementById('pos-order-detail-' + orderId).innerHTML =
              '<div class="alert alert-danger">Lỗi: Không thể tải thông tin đơn hàng</div>';
          });
      });
    });

    deletePosButtons.forEach(button => {
      button.addEventListener('click', function() {
        const orderId = this.getAttribute('data-order-id');
        const modal = new bootstrap.Modal(document.getElementById('deletePosModal' + orderId));
        modal.show();
      });
    });

    // Hàm xóa đơn hàng web
    window.confirmDeleteOrder = function(orderId) {
      if (confirm('⚠️ XÁC NHẬN XÓA ĐƠN HÀNG #' + orderId + '\n\nBạn THỰC SỰ muốn xóa đơn hàng này?\n\nHÀNH ĐỘNG NÀY KHÔNG THỂ KHÔI PHỤC!')) {
        if (confirm('❗️Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng #' + orderId + '?\n\nNhấn OK để xác nhận lần cuối!')) {
          window.location.href = 'delete-orders.php?order_id=' + orderId;
        }
      }
      const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal' + orderId));
      if (modal) modal.hide();
    };

    // Hàm xóa đơn hàng POS
    window.confirmDeletePosOrder = function(orderId) {
      if (confirm('⚠️ XÁC NHẬN XÓA ĐƠN HÀNG POS #' + orderId + '\n\nBạn THỰC SỰ muốn xóa đơn hàng này?\n\nHÀNH ĐỘNG NÀY KHÔNG THỂ KHÔI PHỤC!')) {
        if (confirm('❗️Bạn có chắc chắn muốn xóa vĩnh viễn đơn hàng POS #' + orderId + '?\n\nNhấn OK để xác nhận lần cuối!')) {
          window.location.href = 'delete-pos-order.php?order_id=' + orderId;
        }
      }
      const modal = bootstrap.Modal.getInstance(document.getElementById('deletePosModal' + orderId));
      if (modal) modal.hide();
    };

    // Hàm cập nhật nhanh trạng thái đơn hàng
    window.updateOrderStatus = function(orderId, status) {
      if (confirm('Bạn có chắc chắn muốn cập nhật trạng thái đơn hàng #' + orderId + ' thành "' + status + '"?')) {
        // Thêm hiệu ứng loading
        document.getElementById('web-order-detail-' + orderId).innerHTML = `
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang cập nhật trạng thái đơn hàng...</p>
          </div>
        `;

        // Gửi request cập nhật
        fetch('update-order-status.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId + '&status=' + status
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Tải lại trang sau khi cập nhật
              window.location.reload();
            } else {
              document.getElementById('web-order-detail-' + orderId).innerHTML = `
              <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Lỗi: ${data.message || 'Không thể cập nhật trạng thái đơn hàng'}
              </div>
            `;
            }
          })
          .catch(error => {
            document.getElementById('web-order-detail-' + orderId).innerHTML = `
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-circle me-2"></i>
              Lỗi kết nối: Không thể cập nhật trạng thái đơn hàng
            </div>
          `;
          });
      }
    };
  });
</script>

<?php require "../layouts/footer.php"; ?>