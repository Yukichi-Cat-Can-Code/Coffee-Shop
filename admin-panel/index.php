<?php
session_start();
require "../config/config.php";

define("ADMINAPPURL", "http://localhost/coffee-Shop/admin-panel");


if (!isset($_SESSION['admin_name'])) {
  $_SESSION['login_message'] = "Vui lòng đăng nhập để thực hiện thao tác này";
  $_SESSION['login_message_type'] = "warning";
  header("location: " . ADMINAPPURL . "/admins/login-admins.php");
  exit;
}

require "./layouts/header.php";

// Get current date info for filtering
$today = date('Y-m-d');
$current_month = date('Y-m');
$current_year = date('Y');

try {
  // Count Products
  $products = $conn->query("SELECT COUNT(*) as count FROM product");
  $products->execute();
  $products_count = $products->fetch(PDO::FETCH_OBJ)->count ?? 0;

  // Count Online Orders
  $online_orders = $conn->query("SELECT COUNT(*) as count FROM orders");
  $online_orders->execute();
  $online_orders_count = $online_orders->fetch(PDO::FETCH_OBJ)->count ?? 0;

  // Count POS Orders
  $pos_orders = $conn->query("SELECT COUNT(*) as count FROM pos_orders");
  $pos_orders->execute();
  $pos_orders_count = $pos_orders->fetch(PDO::FETCH_OBJ)->count ?? 0;

  // Total orders (online + POS)
  $total_orders_count = $online_orders_count + $pos_orders_count;

  // Count Bookings
  $bookings = $conn->query("SELECT COUNT(*) as count FROM bookings");
  $bookings->execute();
  $bookings_count = $bookings->fetch(PDO::FETCH_OBJ)->count ?? 0;

  // Count pending bookings
  $pending_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'Pending'");
  $pending_bookings->execute();
  $pending_bookings_count = $pending_bookings->fetch(PDO::FETCH_OBJ)->count ?? 0;

  // Count Admins
  $admin = $conn->query("SELECT COUNT(*) as count FROM admins");
  $admin->execute();
  $admin_count = $admin->fetch(PDO::FETCH_OBJ)->count ?? 0;

  // Calculate total sales (online orders)
  $online_sales = $conn->query("SELECT SUM(payable_total_cost) as total FROM orders");
  $online_sales->execute();
  $online_sales_total = $online_sales->fetch(PDO::FETCH_OBJ)->total ?? 0;

  // Calculate total sales (POS)
  $pos_sales = $conn->query("SELECT SUM(final_amount) as total FROM pos_orders");
  $pos_sales->execute();
  $pos_sales_total = $pos_sales->fetch(PDO::FETCH_OBJ)->total ?? 0;

  // Total sales (online + POS)
  $total_sales = $online_sales_total + $pos_sales_total;

  // Today's sales from POS
  $today_sales = $conn->query("SELECT SUM(final_amount) as total FROM pos_orders 
                               WHERE DATE(created_at) = CURDATE() 
                               AND payment_status = 'Đã thanh toán'");
  $today_sales->execute();
  $today_sales_total = $today_sales->fetch(PDO::FETCH_OBJ)->total ?? 0;

  // This month's sales from POS
  $monthly_sales = $conn->query("SELECT SUM(final_amount) as total FROM pos_orders 
                                 WHERE MONTH(created_at) = MONTH(CURDATE())
                                 AND YEAR(created_at) = YEAR(CURDATE())
                                 AND payment_status = 'Đã thanh toán'");
  $monthly_sales->execute();
  $monthly_sales_total = $monthly_sales->fetch(PDO::FETCH_OBJ)->total ?? 0;

  // Get popular products from POS orders
  $popular_products = $conn->query("SELECT p.product_title, SUM(poi.quantity) as total_quantity
                                     FROM pos_order_items poi
                                     JOIN product p ON poi.product_id = p.ID
                                     GROUP BY poi.product_id
                                     ORDER BY total_quantity DESC
                                     LIMIT 5");
  $popular_products->execute();
  $popular_items = $popular_products->fetchAll(PDO::FETCH_OBJ);

  // Get recent orders (mixed from online and POS)
  $recent_orders = $conn->query("SELECT 'POS' as order_type, po.order_id as id, 
                                   po.final_amount as total, po.created_at, po.order_status as status
                                   FROM pos_orders po
                                   UNION
                                   SELECT 'Online' as order_type, o.ID as id, 
                                   o.payable_total_cost as total, o.created_at, o.status
                                   FROM orders o
                                   ORDER BY created_at DESC
                                   LIMIT 5");
  $recent_orders->execute();
  $recent_orders_list = $recent_orders->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
  // Handle errors quietly
  error_log("Database error: " . $e->getMessage());
  // Set default values
  $products_count = $online_orders_count = $pos_orders_count = 0;
  $total_orders_count = $bookings_count = $admin_count = 0;
  $online_sales_total = $pos_sales_total = $total_sales = 0;
  $today_sales_total = $monthly_sales_total = 0;
  $popular_items = [];
  $recent_orders_list = [];
}
?>

<div class="container-fluid pt-0 pb-4">
  <!-- Dashboard Header -->
  <div class="row mb-2">
    <div class="col">
      <h2 class="fw-bold text-dark mb-1">
        <i class="fas fa-tachometer-alt me-2 text-primary"></i> Coffee Shop Dashboard
      </h2>
      <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
    </div>
    <div class="col-auto">
      <div class="d-flex align-items-center">
        <div class="dropdown me-3" style="margin-right: 20px !important;">
          <button class="btn btn-success dropdown-toggle" type="button" id="reportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-file-export me-1"></i> Reports
          </button>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="reportDropdown">
            <li><a class="dropdown-item" href="#"><i class="fas fa-chart-bar me-2"></i> Sales Report</a></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-chart-pie me-2"></i> Inventory Report</a></li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-users me-2"></i> Customer Report</a></li>
          </ul>
        </div>
        <button type="button" class="btn btn-primary">
          <i class="fas fa-sync-alt me-1"></i> Refresh Data
        </button>
      </div>
    </div>
  </div>

  <!-- KPI Cards - Row 1 (Financial) -->
  <div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-primary shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-primary fw-bold mb-0">Today's Sales</h6>
            <div class="icon-circle bg-primary-light">
              <i class="fas fa-calendar-day text-primary"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($today_sales_total, 0, ',', '.') ?>đ</div>
          <div class="mt-2 small text-muted d-flex align-items-center">
            <i class="fas fa-store me-1"></i> POS Sales
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-success shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-success fw-bold mb-0">Monthly Sales</h6>
            <div class="icon-circle bg-success-light">
              <i class="fas fa-calendar-alt text-success"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($monthly_sales_total, 0, ',', '.') ?>đ</div>
          <div class="mt-2 small text-muted d-flex align-items-center">
            <i class="fas fa-chart-line me-1"></i> <?= date('F Y') ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-warning shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-warning fw-bold mb-0">Online Orders</h6>
            <div class="icon-circle bg-warning-light">
              <i class="fas fa-shopping-cart text-warning"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($online_sales_total, 0, ',', '.') ?>đ</div>
          <div class="mt-2 small text-muted d-flex align-items-center">
            <i class="fas fa-globe me-1"></i> Website Revenue
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-info shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-info fw-bold mb-0">All-time Revenue</h6>
            <div class="icon-circle bg-info-light">
              <i class="fas fa-coins text-info"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($total_sales, 0, ',', '.') ?>đ</div>
          <div class="mt-2 small text-muted d-flex align-items-center">
            <i class="fas fa-chart-area me-1"></i> Combined Sales
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KPI Cards - Row 2 (Operational) -->
  <div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-secondary shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-secondary fw-bold mb-0">Total Orders</h6>
            <div class="icon-circle bg-secondary-light">
              <i class="fas fa-shopping-bag text-secondary"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($total_orders_count) ?></div>
          <div class="mt-2 small text-muted">
            <span class="me-3"><i class="fas fa-store me-1"></i> POS: <?= number_format($pos_orders_count) ?></span>
            <span><i class="fas fa-globe me-1"></i> Online: <?= number_format($online_orders_count) ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-danger shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-danger fw-bold mb-0">Bookings</h6>
            <div class="icon-circle bg-danger-light">
              <i class="fas fa-calendar-check text-danger"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($bookings_count) ?></div>
          <div class="mt-2 small text-muted">
            <span class="text-warning"><i class="fas fa-clock me-1"></i> <?= number_format($pending_bookings_count) ?> pending</span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-dark shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-dark fw-bold mb-0">Products</h6>
            <div class="icon-circle bg-dark-light">
              <i class="fas fa-mug-hot text-dark"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($products_count) ?></div>
          <div class="mt-2 small text-muted">
            <i class="fas fa-coffee me-1"></i> Menu Items
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card border-start-purple shadow h-100">
        <div class="card-body py-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <h6 class="text-xs text-uppercase text-purple fw-bold mb-0">Admins</h6>
            <div class="icon-circle bg-purple-light">
              <i class="fas fa-user-shield text-purple"></i>
            </div>
          </div>
          <div class="h3 mb-0 fw-bold"><?= number_format($admin_count) ?></div>
          <div class="mt-2 small text-muted">
            <i class="fas fa-users-cog me-1"></i> System Administrators
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Analytics Panels -->
  <div class="row mb-4">
    <!-- Popular Products -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 fw-bold text-primary">Popular Products</h6>
          <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
          <?php if (!empty($popular_items)): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($popular_items as $index => $item): ?>
                <li class="list-group-item px-0">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <div class="popularity-rank"><?= $index + 1 ?></div>
                      <div>
                        <h6 class="mb-0"><?= htmlspecialchars($item->product_title) ?></h6>
                        <small class="text-muted"><?= number_format($item->total_quantity) ?> orders</small>
                      </div>
                    </div>
                    <div class="progress flex-grow-1 ms-3" style="height: 6px; max-width: 200px;">
                      <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, ($item->total_quantity / 15) * 100) ?>%"
                        aria-valuenow="<?= $item->total_quantity ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="text-center py-4">
              <i class="fas fa-coffee fa-3x mb-3 text-muted"></i>
              <p>No product data available yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 fw-bold text-primary">Recent Orders</h6>
          <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
          <?php if (!empty($recent_orders_list)): ?>
            <div class="table-responsive">
              <table class="table table-borderless mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Order ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recent_orders_list as $order): ?>
                    <tr>
                      <td class="fw-medium">#<?= $order->id ?></td>
                      <td>
                        <span class="badge bg-<?= $order->order_type === 'POS' ? 'success' : 'info' ?>">
                          <?= $order->order_type ?>
                        </span>
                      </td>
                      <td><?= number_format($order->total, 0, ',', '.') ?>đ</td>
                      <td>
                        <?php
                        $status_class = 'secondary';
                        if (in_array(strtolower($order->status), ['completed', 'delivered', 'hoàn thành', 'đã thanh toán'])) {
                          $status_class = 'success';
                        } elseif (in_array(strtolower($order->status), ['pending', 'chưa thanh toán'])) {
                          $status_class = 'warning';
                        } elseif (in_array(strtolower($order->status), ['cancelled', 'đã hủy'])) {
                          $status_class = 'danger';
                        }
                        ?>
                        <span class="badge bg-<?= $status_class ?>-soft text-<?= $status_class ?>">
                          <?= htmlspecialchars($order->status) ?>
                        </span>
                      </td>
                      <td class="text-muted"><?= date('d/m/Y', strtotime($order->created_at)) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <i class="fas fa-receipt fa-3x mb-3 text-muted"></i>
              <p>No recent orders to display.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Action Cards -->
  <div class="row">
    <div class="col-12">
      <h5 class="text-dark mb-3">Quick Actions</h5>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card action-card shadow-sm h-100">
        <div class="card-body d-flex align-items-center py-3">
          <div class="action-icon bg-primary-light rounded-circle me-3">
            <i class="fas fa-plus text-primary"></i>
          </div>
          <div>
            <h6 class="mb-1">Add New Product</h6>
            <p class="text-muted small mb-0">Add coffee or food items to menu</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card action-card shadow-sm h-100">
        <div class="card-body d-flex align-items-center py-3">
          <div class="action-icon bg-success-light rounded-circle me-3">
            <i class="fas fa-cash-register text-success"></i>
          </div>
          <div>
            <h6 class="mb-1">POS System</h6>
            <p class="text-muted small mb-0">Process in-store orders</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card action-card shadow-sm h-100">
        <div class="card-body d-flex align-items-center py-3">
          <div class="action-icon bg-warning-light rounded-circle me-3">
            <i class="fas fa-clipboard-list text-warning"></i>
          </div>
          <div>
            <h6 class="mb-1">Manage Bookings</h6>
            <p class="text-muted small mb-0">View and confirm table reservations</p>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
      <div class="card action-card shadow-sm h-100">
        <div class="card-body d-flex align-items-center py-3">
          <div class="action-icon bg-info-light rounded-circle me-3">
            <i class="fas fa-chart-line text-info"></i>
          </div>
          <div>
            <h6 class="mb-1">Sales Report</h6>
            <p class="text-muted small mb-0">Review financial performance</p>
          </div>
        </div>
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

  .page-container {
    flex: 1 0 auto;
    display: flex;
    flex-direction: column;
  }

  #wrapper {
    flex: 1 0 auto;
    padding-bottom: 30px;
    /* Space for footer */
  }

  #content-wrapper {
    padding-top: 0.2rem !important;
    /* Reduce top padding in content wrapper */
  }

  .topbar {
    margin-bottom: 0.2rem !important;
    /* If there's a topbar, reduce its bottom margin */
  }

  /* Ensure headers have appropriate spacing */
  h2.fw-bold {
    margin-top: 0;
  }

  .admin-footer {
    flex-shrink: 0;
    width: 100% !important;
    margin-left: 0 !important;
    position: relative;
    z-index: 1030;
  }

  /* Ensure sidebar doesn't affect footer position */
  @media (min-width: 992px) {
    .admin-footer {
      margin-left: 230px !important;
      width: calc(100% - 230px) !important;
    }
  }

  /* Base styles */
  .container-fluid {
    max-width: 1400px;
  }

  /* Card styling with left borders */
  .border-start-primary {
    border-left: 4px solid #4e73df !important;
  }

  .border-start-success {
    border-left: 4px solid #1cc88a !important;
  }

  .border-start-info {
    border-left: 4px solid #36b9cc !important;
  }

  .border-start-warning {
    border-left: 4px solid #f6c23e !important;
  }

  .border-start-danger {
    border-left: 4px solid #e74a3b !important;
  }

  .border-start-secondary {
    border-left: 4px solid #858796 !important;
  }

  .border-start-dark {
    border-left: 4px solid #5a5c69 !important;
  }

  .border-start-purple {
    border-left: 4px solid #6f42c1 !important;
  }

  /* Icon circles */
  .icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .bg-primary-light {
    background-color: rgba(78, 115, 223, 0.1);
  }

  .bg-success-light {
    background-color: rgba(28, 200, 138, 0.1);
  }

  .bg-info-light {
    background-color: rgba(54, 185, 204, 0.1);
  }

  .bg-warning-light {
    background-color: rgba(246, 194, 62, 0.1);
  }

  .bg-danger-light {
    background-color: rgba(231, 74, 59, 0.1);
  }

  .bg-secondary-light {
    background-color: rgba(133, 135, 150, 0.1);
  }

  .bg-dark-light {
    background-color: rgba(90, 92, 105, 0.1);
  }

  .bg-purple-light {
    background-color: rgba(111, 66, 193, 0.1);
  }

  /* Card shadow */
  .shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
  }

  /* Soft badges */
  .bg-success-soft {
    background-color: rgba(28, 200, 138, 0.1);
  }

  .bg-warning-soft {
    background-color: rgba(246, 194, 62, 0.1);
  }

  .bg-danger-soft {
    background-color: rgba(231, 74, 59, 0.1);
  }

  .bg-secondary-soft {
    background-color: rgba(133, 135, 150, 0.1);
  }

  /* Text colors */
  .text-xs {
    font-size: 0.7rem;
  }

  .text-purple {
    color: #6f42c1;
  }

  /* Popularity rankings */
  .popularity-rank {
    width: 28px;
    height: 28px;
    background-color: #f8f9fc;
    border-radius: 50%;
    color: #4e73df;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    border: 2px solid rgba(78, 115, 223, 0.3);
  }

  /* Action cards */
  .action-card {
    transition: transform 0.2s;
    cursor: pointer;
  }

  .action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
  }

  .action-icon {
    height: 45px;
    width: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  /* Card hover effect for all cards */
  .card {
    transition: all 0.2s;
  }

  .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
  }

  /* Table styling */
  .table-light th {
    font-weight: 600;
    color: #495057;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
      return new bootstrap.Dropdown(dropdownToggleEl)
    });

    // Add click handlers for action cards
    const actionCards = document.querySelectorAll('.action-card');

    actionCards.forEach(card => {
      card.addEventListener('click', function() {
        const title = this.querySelector('h6').textContent;

        // Redirect based on card title
        switch (title) {
          case 'Add New Product':
            window.location.href = 'products-admins/create-products.php';
            break;
          case 'POS System':
            window.location.href = 'pos-admins/index.php';
            break;
          case 'Manage Bookings':
            window.location.href = 'bookings-admins/show-bookings.php';
            break;
          case 'Sales Report':
            window.location.href = 'reports/sales-report.php';
            break;
        }
      });
    });
  });
</script>
<?php
require "./layouts/footer.php";
?>