<?php
require "../../config/config.php";
requireAdminLogin();

require "../layouts/header.php";

// Check for admin messages (delete, update, etc.)
$admin_message = '';
$admin_message_type = '';

if (session()->has('admin_message')) {
  $admin_message = session()->getFlash('admin_message');
  $admin_message_type = session()->getFlash('admin_message_type') ?? 'success';
}

// Show notification for admin creation if set in session
$show_success_message = false;
if (session()->has('admin_created') && session()->getFlash('admin_created') === true) {
  $show_success_message = true;
}

// Pagination setup
$records_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $records_per_page;

// Sorting functionality
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'ID';
$sort_direction = isset($_GET['dir']) ? (($_GET['dir'] === 'desc') ? 'DESC' : 'ASC') : 'ASC';
$allowed_columns = ['ID', 'admin_name', 'admin_email', 'created_at'];

if (!in_array($sort_column, $allowed_columns)) {
  $sort_column = 'ID';
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$params = [];

if (!empty($search)) {
  $search_condition = "WHERE admin_name LIKE ? OR admin_email LIKE ?";
  $params = ["%$search%", "%$search%"];
}

// Count total records for pagination
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM admins $search_condition");
$count_query->execute($params);
$total_records = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get admins with pagination, sorting and search
$query = "SELECT * FROM admins $search_condition ORDER BY $sort_column $sort_direction LIMIT ?, ?";
$admin = $conn->prepare($query);

// Bind parameters including pagination limits
foreach ($params as $index => $param) {
  $admin->bindValue($index + 1, $param);
}
$admin->bindValue(count($params) + 1, $start_from, PDO::PARAM_INT);
$admin->bindValue(count($params) + 2, $records_per_page, PDO::PARAM_INT);

$admin->execute();
$all_admins = $admin->fetchAll(PDO::FETCH_OBJ);
?>

<div class="container-fluid py-2">
  <!-- Admin creation success message -->
  <?php if ($show_success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i> Tài khoản admin tạo thành công!
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Admin message display (for delete, edit, etc) -->
  <?php if (!empty($admin_message)): ?>
    <div class="alert alert-<?php echo $admin_message_type; ?> alert-dismissible fade show" role="alert">
      <i class="fas fa-<?php echo $admin_message_type == 'success' ? 'check-circle' : ($admin_message_type == 'danger' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
      <?php echo htmlspecialchars($admin_message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm mb-3">
    <!-- Header Section with Title and Stats -->
    <div class="card-body py-3">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <!-- Left Side: Title and Breadcrumb -->
        <div class="d-flex align-items-center mb-3 mb-md-0">
          <div class="admin-icon d-flex justify-content-center align-items-center me-3">
            <i class="fas fa-user-shield text-primary"></i>
          </div>
          <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">Quản lý admin</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>" class="text-decoration-none">Bảng điều khiển</a></li>
                <li class="breadcrumb-item active" aria-current="page">Danh sách admin</li>
              </ol>
            </nav>
          </div>
        </div>

        <!-- Right Side: Stats Display (moved inside the same card) -->
        <div class="d-flex">
          <div class="stat-box bg-light rounded-3 px-3 py-2 me-3 text-center">
            <div class="h4 mb-0 text-primary fw-bold"><?= number_format($total_records) ?></div>
            <div class="small text-muted">Tổng số admin</div>
          </div>
          <div class="stat-box bg-light rounded-3 px-3 py-2 text-center">
            <div class="h4 mb-0 text-primary fw-bold"><?= isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : '-' ?></div>
            <div class="small text-muted">ID của bạn</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Toolbar -->
    <div class="admin-toolbar card mb-4">
      <!-- Search and Actions Section -->
      <div class="card-body border-top py-3">
        <div class="row g-2 align-items-center">
          <!-- Search Form -->
          <div class="col-md-7">
            <form class="d-flex" method="GET" action="">
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0"
                  placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_column) ?>">
                <input type="hidden" name="dir" value="<?= htmlspecialchars($sort_direction) ?>">
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
              </div>
            </form>
          </div>

          <!-- Action Buttons -->
          <div class="col-md-5 d-flex justify-content-md-end">
            <button id="refreshAdmins" class="btn btn-outline-secondary me-2">
              <i class="fas fa-sync-alt"></i> Làm mới
            </button>
            <div class="btn-group me-2">
              <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-filter"></i> Lọc
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                  <h6 class="dropdown-header">Số bản ghi trên trang</h6>
                </li>
                <li><a class="dropdown-item <?= $records_per_page === 10 ? 'active' : '' ?>"
                    href="?<?= http_build_query(array_merge($_GET, ['limit' => 10, 'page' => 1])) ?>">10 bản ghi</a></li>
                <li><a class="dropdown-item <?= $records_per_page === 25 ? 'active' : '' ?>"
                    href="?<?= http_build_query(array_merge($_GET, ['limit' => 25, 'page' => 1])) ?>">25 bản ghi</a></li>
                <li><a class="dropdown-item <?= $records_per_page === 50 ? 'active' : '' ?>"
                    href="?<?= http_build_query(array_merge($_GET, ['limit' => 50, 'page' => 1])) ?>">50 bản ghi</a></li>
              </ul>
            </div>
            <a href="create-admins.php" class="btn btn-success">
              <i class="fas fa-plus-circle"></i> Tạo admin mới
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Admins Data Card -->
  <div class="card border-0 shadow-sm">
    <?php if (count($all_admins) > 0): ?>
      <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
          <thead>
            <tr class="table-light">
              <th scope="col" class="sortable-column">
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'ID', 'dir' => ($sort_column === 'ID' && $sort_direction === 'ASC') ? 'desc' : 'asc'])) ?>" class="d-flex align-items-center text-decoration-none">
                  # ID
                  <?php if ($sort_column === 'ID'): ?>
                    <i class="fas fa-sort-<?= $sort_direction === 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th scope="col" class="sortable-column">
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'admin_name', 'dir' => ($sort_column === 'admin_name' && $sort_direction === 'ASC') ? 'desc' : 'asc'])) ?>" class="d-flex align-items-center text-decoration-none">
                  Tên Admin
                  <?php if ($sort_column === 'admin_name'): ?>
                    <i class="fas fa-sort-<?= $sort_direction === 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th scope="col" class="sortable-column">
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'admin_email', 'dir' => ($sort_column === 'admin_email' && $sort_direction === 'ASC') ? 'desc' : 'asc'])) ?>" class="d-flex align-items-center text-decoration-none">
                  Email
                  <?php if ($sort_column === 'admin_email'): ?>
                    <i class="fas fa-sort-<?= $sort_direction === 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th scope="col" class="sortable-column d-none d-lg-table-cell">
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'created_at', 'dir' => ($sort_column === 'created_at' && $sort_direction === 'ASC') ? 'desc' : 'asc'])) ?>" class="d-flex align-items-center text-decoration-none">
                  Ngày tạo
                  <?php if ($sort_column === 'created_at'): ?>
                    <i class="fas fa-sort-<?= $sort_direction === 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th scope="col" class="text-end">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_admins as $index => $admin): ?>
              <?php
              // Determine if this is the current logged-in admin
              $is_current_user = ($_SESSION['admin_id'] == $admin->ID);
              ?>
              <tr class="<?= $is_current_user ? 'table-active' : '' ?>">
                <td class="fw-medium"><?= $admin->ID ?></td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="admin-avatar <?= $is_current_user ? 'current-user' : '' ?>" data-id="<?= $admin->ID ?>">
                      <?= strtoupper(substr($admin->admin_name, 0, 1)) ?>
                    </div>
                    <div class="ms-3">
                      <?= htmlspecialchars($admin->admin_name) ?>
                      <?php if ($is_current_user): ?>
                        <span class="badge bg-secondary ms-1">Bạn</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><?= htmlspecialchars($admin->admin_email) ?></td>
                <td class="d-none d-lg-table-cell">
                  <i class="far fa-calendar-alt me-1 text-muted"></i>
                  <?= date('d M Y', strtotime($admin->created_at)) ?>
                </td>
                <td class="text-end">
                  <div class="btn-group">
                    <a href="edit-admin.php?id=<?= $admin->ID ?>" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <?php if (!$is_current_user): ?>
                      <button type="button" class="btn btn-sm btn-outline-danger delete-admin"
                        data-id="<?= $admin->ID ?>"
                        data-name="<?= htmlspecialchars($admin->admin_name) ?>">
                        <i class="fas fa-trash-alt"></i> Xóa
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Card Footer with Pagination -->
      <div class="card-footer bg-white border-0 px-4 py-3">
        <div class="row align-items-center">
          <div class="col-md-6 small text-muted mb-2 mb-md-0">
            Showing <?= min(($page - 1) * $records_per_page + 1, $total_records) ?> to
            <?= min($page * $records_per_page, $total_records) ?> of
            <?= $total_records ?> administrators
          </div>

          <?php if ($total_pages > 1): ?>
            <div class="col-md-6">
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-md-end mb-0">
                  <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                      <i class="fas fa-angle-left"></i>
                    </a>
                  </li>

                  <?php
                  // Calculate range of pages to show
                  $range = 2;
                  $showLeft = $page - $range;
                  $showRight = $page + $range;

                  // Always show page 1
                  if ($showLeft > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a></li>';
                    if ($showLeft > 2) {
                      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                  }

                  // Show pages around current page
                  for ($i = max(1, $showLeft); $i <= min($total_pages, $showRight); $i++) {
                    echo '<li class="page-item ' . (($page == $i) ? 'active' : '') . '">
                            <a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '">' . $i . '</a>
                          </li>';
                  }

                  // Always show last page
                  if ($showRight < $total_pages) {
                    if ($showRight < $total_pages - 1) {
                      echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '">' . $total_pages . '</a></li>';
                  }
                  ?>

                  <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                      <i class="fas fa-angle-right"></i>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <?php else: ?>
      <!-- Empty State -->
      <div class="card-body p-5 text-center">
        <div class="empty-state">
          <?php if (!empty($search)): ?>
            <div class="empty-state-icon search-empty mb-4">
              <i class="fas fa-search"></i>
            </div>
            <h3>Không có kết quả phù hợp</h3>
            <p class="text-muted mb-4">
              Chúng tôi không thể tìm thấy bất kỳ quản trị viên nào phù hợp với từ khóa tìm kiếm của bạn.<br>
              Hãy thử các từ khóa khác hoặc xóa tìm kiếm của bạn.
            </p>
            <a href="admins.php" class="btn btn-outline-primary px-4">
              <i class="fas fa-redo me-1"></i> Xóa tìm kiếm
            </a>
          <?php else: ?>
            <div class="empty-state-icon mb-4">
              <i class="fas fa-user-shield"></i>
            </div>
            <h3>Chưa có quản trị viên nào</h3>
            <p class="text-muted mb-4">
              Bắt đầu bằng cách thêm tài khoản quản trị viên đầu tiên của bạn.<br>
              Các quản trị viên có thể quản lý hệ thống quán cà phê.
            </p>
            <a href="create-admins.php" class="btn btn-success px-4">
              <i class="fas fa-plus-circle me-1"></i> Thêm quản trị viên đầu tiên
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Delete Admin Confirmation Modal -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="fas fa-trash-alt me-2"></i> Xóa quản trị viên
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4">
          <div class="icon-circle bg-danger-light mb-3">
            <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
          </div>
          <h4>Bạn có chắc chắn không?</h4>
          <p class="mb-1">Bạn sắp xóa quản trị viên này:</p>
          <p class="fw-bold fs-5 mb-3" id="admin-name-to-delete"></p>
          <div class="alert alert-warning">
            <i class="fas fa-info-circle me-2"></i>
            Hành động này không thể hoàn tác. Quản trị viên sẽ mất tất cả quyền truy cập vào hệ thống.
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 bg-light">
        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Hủy</button>
        <form id="deleteAdminForm" action="delete-admin.php" method="POST" class="d-inline">
          <input type="hidden" name="admin_id" id="admin-id-to-delete">
          <button type="submit" class="btn btn-danger px-4">Xóa quản trị viên</button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  html,
  body {
    height: 100%;
    margin: 0;
  }

  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  footer {
    flex-shrink: 0;
    width: 100%;
    background-color: #2c2c2c;
    color: #e0e0e0;
    padding: 0.75rem 0;
    margin-top: auto;
    z-index: 1000;
  }

  .footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1rem;
  }

  .footer-links {
    display: flex;
    gap: 1.5rem;
  }

  .footer-links a {
    color: #e0e0e0;
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.2s;
  }

  .footer-links a:hover {
    color: #ffffff;
    text-decoration: underline;
  }

  .scroll-to-top {
    font-size: 1rem;
    color: #e0e0e0;
    cursor: pointer;
    transition: color 0.2s;
    background: none;
    border: none;
    padding: 0.25rem 0.5rem;
  }

  .scroll-to-top:hover {
    color: #ffffff;
  }

  .main-content-wrapper {
    flex: 1 0 auto;
  }

  /* Main Layout & Cards */
  .container-fluid {
    max-width: 1600px;
  }

  .card {
    border-radius: 0.5rem;
    overflow: hidden;
    transition: box-shadow 0.2s;
    border: none;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
  }

  .card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    padding: 1rem 1.25rem;
  }

  .card-footer {
    background-color: #fff;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
  }

  /* Page Header Styling */
  .page-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: #2c3e50;
  }

  .page-icon-box,
  .admin-icon {
    width: 54px;
    height: 54px;
    background-color: rgba(13, 110, 253, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #0d6efd;
  }

  /* Breadcrumb styling */
  .breadcrumb {
    font-size: 0.85rem;
    margin-bottom: 0;
  }

  .breadcrumb-item a {
    color: #0d6efd;
    text-decoration: none;
  }

  .breadcrumb-item.active {
    color: #6c757d;
  }

  .breadcrumb-item+.breadcrumb-item::before {
    color: #6c757d;
  }

  /* Stats boxes */
  .admin-stats {
    display: flex;
    gap: 1.25rem;
  }

  .stat-box,
  .stat-card {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 0.7rem 1.25rem;
    min-width: 110px;
    text-align: center;
    border: 1px solid #e9ecef;
    transition: transform 0.15s, box-shadow 0.15s;
  }

  .stat-box:hover,
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
  }

  .stat-value,
  .h4.text-primary {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0d6efd !important;
    line-height: 1.2;
  }

  .stat-label,
  .small.text-muted {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
  }

  /* Admin toolbar */
  .admin-toolbar {
    border-radius: 8px;
    background-color: #fff;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
    margin-bottom: 1rem;
  }

  /* Search form styling */
  .input-group {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
  }

  .input-group-text {
    background-color: #fff;
    border-color: #dee2e6;
  }

  .form-control {
    height: 42px;
    border-color: #dee2e6;
  }

  .form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    border-color: #86b7fe;
  }

  .btn {
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-weight: 500;
    padding-left: 1rem;
    padding-right: 1rem;
    border-radius: 0.25rem;
    transition: all 0.2s;
    margin-right: 0.75rem;
  }

  .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }

  .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
  }

  .btn-success {
    background-color: #198754;
    border-color: #198754;
  }

  .btn-success:hover {
    background-color: #157347;
    border-color: #146c43;
  }

  .btn-outline-primary {
    color: #0d6efd;
    border-color: #0d6efd;
  }

  .btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
  }

  .btn:last-child {
    margin-right: 0;
  }

  .btn-outline-secondary {
    color: #5a8dee;
    border-color: #c2d8ff;
    background-color: #f0f5ff;
  }

  .btn-outline-secondary:hover {
    background-color: #e0ebff;
    border-color: #a0c0ff;
    color: #4375d6;
  }

  .btn-outline-secondary:focus {
    box-shadow: 0 0 0 0.25rem rgba(90, 141, 238, 0.15);
  }

  .btn-outline-secondary.dropdown-toggle {
    color: #5a8dee;
    border-color: #c2d8ff;
    background-color: #f0f5ff;
  }

  .btn-outline-secondary.dropdown-toggle:hover {
    background-color: #e0ebff;
    border-color: #a0c0ff;
    color: #4375d6;
  }

  .btn-outline-secondary.show,
  .btn-outline-secondary.active,
  .btn-outline-secondary.dropdown-toggle.show {
    background-color: #e0ebff;
    border-color: #a0c0ff;
    color: #4375d6;
  }

  .btn-outline-secondary .fas.fa-filter {
    color: #5a8dee;
  }

  .btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
  }

  .btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
  }

  .btn-sm {
    height: 32px;
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
  }

  /* Dropdown menu styling */
  .dropdown-menu {
    padding: 0.75rem 0;
    font-size: 0.875rem;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1);
    min-width: 200px;
    margin-top: 0.5rem;
    background-color: white;
  }

  .dropdown-header {
    color: rgb(230, 232, 236);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    padding: 0.5rem 1.25rem;
    margin-bottom: 0.25rem;
  }

  .dropdown-item {
    padding: 0.6rem 1.25rem;
    color: #495057;
    font-weight: 500;
    border-left: 3px solid transparent;
  }

  .dropdown-item:hover {
    background-color: #f5f9ff;
    color: #5a8dee;
    border-left-color: #5a8dee;
  }

  .dropdown-item.active {
    background-color: #f0f5ff;
    color: #4375d6;
    border-left-color: #4375d6;
  }

  /* Improve spacing in button groups */
  .btn-group {
    margin-right: 0.75rem;
  }

  .btn-group:last-child {
    margin-right: 0;
  }

  /* Specific adjustments for the action buttons row */
  .col-md-5.d-flex.justify-content-md-end {
    gap: 0.2rem;
  }

  /* Make buttons more consistent */
  .btn-success {
    padding-left: 1.25rem;
    padding-right: 1.25rem;
  }

  @media (max-width: 767.98px) {
    .btn {
      margin-right: 0.1rem;
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }

    .btn-group {
      margin-right: 0.1rem;
    }

    .col-md-5.d-flex.justify-content-md-end {
      gap: 0.2rem;
    }
  }

  /* Table styling */
  .table-responsive {
    border-radius: 0.5rem;
    overflow: hidden;
  }

  .admin-table {
    margin-bottom: 0;
    border-collapse: collapse;
  }

  .admin-table th {
    font-weight: 600;
    color: #495057;
    border-bottom-width: 1px;
    padding: 1rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background-color: #f8f9fa;
  }

  .admin-table td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom-width: 1px;
    border-color: rgba(0, 0, 0, 0.05);
  }

  .admin-table .sortable-column a {
    color: #495057;
    text-decoration: none;
    display: flex;
    align-items: center;
  }

  .admin-table .sortable-column a:hover {
    color: #0d6efd;
  }

  .admin-table .sortable-column i {
    font-size: 0.8rem;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.04);
  }

  .table-active {
    background-color: rgba(13, 110, 253, 0.075) !important;
  }

  /* Admin avatar styling */
  .admin-avatar {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background-color: #6c5ce7;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
    flex-shrink: 0;
  }

  .admin-avatar.current-user {
    border: 2px solid #0d6efd;
  }

  /* Badge styling */
  .badge {
    padding: 0.35em 0.65em;
    font-weight: 500;
    font-size: 0.75em;
    border-radius: 0.25rem;
  }

  .badge.bg-secondary {
    background-color: #6c757d !important;
  }

  /* Pagination styling */
  .pagination {
    margin-bottom: 0;
  }

  .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    min-width: 32px;
    padding: 0 0.5rem;
    font-size: 0.875rem;
    color: #0d6efd;
    background-color: #fff;
    border: 1px solid #dee2e6;
  }

  .page-link:hover {
    z-index: 2;
    color: #0a58ca;
    background-color: #e9ecef;
    border-color: #dee2e6;
  }

  .page-link:focus {
    z-index: 3;
    color: #0a58ca;
    background-color: #e9ecef;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  }

  .page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
  }

  .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
  }

  /* Empty state styling */
  .empty-state {
    padding: 3rem 0;
  }

  .empty-state-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(13, 110, 253, 0.1);
    font-size: 2.2rem;
    color: #0d6efd;
  }

  .empty-state-icon.search-empty {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
  }

  .empty-state h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.75rem;
  }

  .empty-state p {
    max-width: 500px;
    margin: 0 auto;
  }

  /* Delete modal styling */
  .modal-content {
    border: none;
    border-radius: 0.5rem;
    overflow: hidden;
  }

  .modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  }

  .modal-header.bg-danger {
    background-color: #dc3545 !important;
  }

  .modal-body {
    padding: 1.5rem;
  }

  .modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
  }

  .icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    margin-bottom: 1.5rem;
  }

  .bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
  }

  .alert {
    border-radius: 0.375rem;
  }

  .alert-warning {
    background-color: rgba(255, 193, 7, 0.15);
    border-color: rgba(255, 193, 7, 0.3);
    color: #664d03;
  }

  /* Improve responsive design */
  @media (max-width: 991.98px) {
    .container-fluid {
      padding-left: 1rem;
      padding-right: 1rem;
    }

    .card-body {
      padding: 1rem;
    }
  }

  @media (max-width: 767.98px) {
    .admin-stats {
      justify-content: center;
    }

    .stat-box,
    .stat-card {
      min-width: 100px;
      padding: 0.5rem 1rem;
    }

    .admin-table {
      font-size: 0.9rem;
    }

    .admin-table th,
    .admin-table td {
      padding: 0.75rem;
    }

    .admin-avatar {
      width: 36px;
      height: 36px;
      font-size: 1rem;
    }

    .btn {
      padding-left: 0.75rem;
      padding-right: 0.75rem;
    }

    .admin-icon,
    .page-icon-box {
      width: 46px;
      height: 46px;
      font-size: 1.2rem;
    }

    h1.h3 {
      font-size: 1.5rem;
    }

    .col-md-5,
    .col-md-7 {
      margin-bottom: 0.75rem;
    }

    .page-link {
      min-width: 28px;
      height: 28px;
      padding: 0 0.35rem;
      font-size: 0.8rem;
    }
  }

  @media (max-width: 575.98px) {

    .stat-value,
    .h4.text-primary {
      font-size: 1.25rem;
    }

    .btn i+span {
      display: none;
    }

    .btn-group .btn {
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }

    .admin-table th,
    .admin-table td {
      padding: 0.5rem;
    }
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Delete admin functionality
    const deleteLinks = document.querySelectorAll('.delete-admin');
    const adminNameToDelete = document.getElementById('admin-name-to-delete');
    const adminIdToDelete = document.getElementById('admin-id-to-delete');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteAdminModal'));

    deleteLinks.forEach(link => {
      link.addEventListener('click', function() {
        adminNameToDelete.textContent = this.dataset.name;
        adminIdToDelete.value = this.dataset.id;
        deleteModal.show();
      });
    });

    // Color for admin avatars based on ID
    const avatars = document.querySelectorAll('.admin-avatar');
    const colors = [
      '#4361ee', '#3a0ca3', '#7209b7', '#f72585',
      '#4cc9f0', '#4895ef', '#560bad', '#480ca8',
      '#3f37c9', '#4361ee', '#4cc9f0', '#2a9d8f',
      '#f94144', '#f3722c', '#f8961e', '#f9c74f',
      '#90be6d', '#43aa8b', '#4d908e', '#277da1'
    ];

    avatars.forEach(avatar => {
      const adminId = parseInt(avatar.dataset.id, 10);
      const colorIndex = adminId % colors.length;
      avatar.style.backgroundColor = colors[colorIndex];
    });

    // Refresh button functionality
    const refreshButton = document.getElementById('refreshAdmins');
    if (refreshButton) {
      refreshButton.addEventListener('click', function() {
        window.location.reload();
      });
    }
  });

  // Auto-dismiss success alert after 5 seconds
  const successAlert = document.querySelector('.alert-success');
  if (successAlert) {
    setTimeout(() => {
      const alert = bootstrap.Alert.getOrCreateInstance(successAlert);
      alert.close();
    }, 5000);
  }
</script>
<?php
require "../layouts/footer.php";
?>