<?php
require "../../config/config.php";
requireAdminLogin();

// Phân trang
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

// Sắp xếp
$allowed_sort_fields = ['ID', 'product_title', 'price', 'type', 'created_at'];
$allowed_order_types = ['ASC', 'DESC'];
$sort = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort_fields)) ? $_GET['sort'] : 'ID';
$order = (isset($_GET['order']) && in_array(strtoupper($_GET['order']), $allowed_order_types)) ? strtoupper($_GET['order']) : 'ASC';

// Xây dựng điều kiện WHERE
$where_clause = [];
$params = [];

if (!empty($search)) {
  $where_clause[] = "(product_title LIKE ? OR description LIKE ? OR ID LIKE ?)";
  $search_param = "%$search%";
  $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if (!empty($type_filter)) {
  $where_clause[] = "type = ?";
  $params[] = $type_filter;
}

$where_sql = !empty($where_clause) ? "WHERE " . implode(' AND ', $where_clause) : "";

// Đếm tổng số sản phẩm
$count_query = $conn->prepare("SELECT COUNT(*) FROM product $where_sql");
$count_query->execute($params);
$total_products = $count_query->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Lấy danh sách sản phẩm
$query = "SELECT * FROM product $where_sql ORDER BY $sort $order LIMIT $offset, $limit";
$products_query = $conn->prepare($query);
$products_query->execute($params);
$products = $products_query->fetchAll(PDO::FETCH_OBJ);

// Hiển thị thông báo
$message = '';
$message_type = '';
if (session()->has('product_message')) {
  $message = session()->getFlash('product_message');
  $message_type = session()->getFlash('product_message_type') ?? 'success';
}

// Xây dựng URL cho phân trang và sắp xếp
function buildUrl($page = null, $sort = null, $order = null)
{
  global $search, $type_filter, $limit;
  $current_sort = $sort ?? $_GET['sort'] ?? 'ID';
  $current_order = $order ?? ($_GET['order'] ?? 'ASC');
  $current_page = $page ?? $_GET['page'] ?? 1;

  return "?page=$current_page&sort=$current_sort&order=$current_order&search=" .
    urlencode($search) . "&type=" . urlencode($type_filter) . "&limit=$limit";
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
  <!-- Page header -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">Quản Lý Sản Phẩm</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
          <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
          <li class="breadcrumb-item active">Sản phẩm</li>
        </ol>
      </nav>
    </div>
    <div>
      <a href="create-products.php" class="btn btn-sm btn-success me-2">
        <i class="fas fa-plus-circle"></i> Thêm sản phẩm mới
      </a>
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

        <!-- Type filter -->
        <div class="col-md-3 mb-2 mb-md-0">
          <form action="" method="GET" id="typeForm">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <select class="form-select" name="type" onchange="this.form.submit()">
              <option value="">Tất cả loại</option>
              <option value="drink" <?= $type_filter == 'drink' ? 'selected' : '' ?>>Đồ uống</option>
              <option value="dessert" <?= $type_filter == 'dessert' ? 'selected' : '' ?>>Tráng miệng</option>
              <option value="food" <?= $type_filter == 'food' ? 'selected' : '' ?>>Đồ ăn</option>
            </select>
          </form>
        </div>

        <!-- Results per page -->
        <div class="col-md-2 mb-2 mb-md-0">
          <form action="" method="GET" id="limitForm">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type_filter) ?>">
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
            if ($total_products > 0) {
              $showing_from = $offset + 1;
              $showing_to = min($offset + $limit, $total_products);
              echo "Hiển thị $showing_from-$showing_to / $total_products sản phẩm";
            } else {
              echo "0 sản phẩm";
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
              <th>Hình ảnh</th>
              <th>
                <a href="<?= buildUrl(null, 'product_title', ($sort == 'product_title' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Tên sản phẩm
                  <?php if ($sort == 'product_title'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>
                <a href="<?= buildUrl(null, 'price', ($sort == 'price' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Giá
                  <?php if ($sort == 'price'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th>
                <a href="<?= buildUrl(null, 'type', ($sort == 'type' && $order == 'ASC') ? 'DESC' : 'ASC') ?>" class="text-dark text-decoration-none d-flex align-items-center">
                  Loại
                  <?php if ($sort == 'type'): ?>
                    <i class="fas fa-sort-<?= $order == 'DESC' ? 'down' : 'up' ?> ms-1"></i>
                  <?php endif; ?>
                </a>
              </th>
              <th class="text-center">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($products) > 0): ?>
              <?php foreach ($products as $product): ?>
                <tr>
                  <td class="fw-bold">#<?= $product->ID ?></td>
                  <td>
                    <img src="<?= ADMINAPPURL ?>/../images/<?= htmlspecialchars($product->image) ?>"
                      class="rounded shadow-sm"
                      alt="<?= htmlspecialchars($product->product_title) ?>"
                      width="60" height="60"
                      onerror="this.src='<?= ADMINAPPURL ?>/../images/default-product.jpg'">
                  </td>
                  <td><?= htmlspecialchars($product->product_title) ?></td>
                  <td><?= number_format($product->price, 0, ',', '.') ?> đ</td>
                  <td>
                    <?php
                    $type_class = '';
                    $type_text = '';

                    switch ($product->type) {
                      case 'drink':
                        $type_class = 'primary';
                        $type_text = 'Đồ uống';
                        break;
                      case 'dessert':
                        $type_class = 'success';
                        $type_text = 'Tráng miệng';
                        break;
                      case 'food':
                        $type_class = 'warning';
                        $type_text = 'Đồ ăn';
                        break;
                      default:
                        $type_class = 'secondary';
                        $type_text = $product->type;
                    }
                    ?>
                    <span class="badge bg-<?= $type_class ?>"><?= $type_text ?></span>
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2">
                      <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#productDetailModal<?= $product->ID ?>">
                        <i class="fas fa-eye"></i>
                      </a>
                      <a href="edit-products.php?product_id=<?= $product->ID ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $product->ID ?>, '<?= htmlspecialchars($product->product_title) ?>')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>

                    <!-- Modal chi tiết sản phẩm -->
                    <div class="modal fade" id="productDetailModal<?= $product->ID ?>" tabindex="-1" aria-labelledby="productDetailModalLabel<?= $product->ID ?>" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="productDetailModalLabel<?= $product->ID ?>">Chi tiết sản phẩm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="row">
                              <div class="col-md-4 text-center">
                                <img src="<?= ADMINAPPURL ?>/../images/<?= htmlspecialchars($product->image) ?>"
                                  class="img-fluid rounded mb-3"
                                  alt="<?= htmlspecialchars($product->product_title) ?>"
                                  onerror="this.src='<?= ADMINAPPURL ?>/../images/default-product.jpg'">
                              </div>
                              <div class="col-md-8">
                                <h4><?= htmlspecialchars($product->product_title) ?></h4>
                                <p class="text-muted">ID: <?= $product->ID ?></p>
                                <h5 class="mb-3"><?= number_format($product->price, 0, ',', '.') ?> đ</h5>

                                <span class="badge bg-<?= $type_class ?> mb-3"><?= $type_text ?></span>

                                <h6>Mô tả sản phẩm:</h6>
                                <p><?= nl2br(htmlspecialchars($product->description)) ?></p>

                                <p class="text-muted mt-3 mb-0">
                                  <small>Ngày tạo: <?= date('d/m/Y H:i', strtotime($product->created_at)) ?></small>
                                </p>
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <a href="edit-products.php?product_id=<?= $product->ID ?>" class="btn btn-primary">Sửa</a>
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
                    <h5>Không tìm thấy sản phẩm nào</h5>
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
    <?php if ($total_products > 0): ?>
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
    // Hàm xác nhận xóa
    window.confirmDelete = function(productId, productName) {
      if (confirm('⚠️ XÁC NHẬN XÓA\n\nBạn có chắc chắn muốn xóa sản phẩm "' + productName + '" (ID: ' + productId + ')?\nHành động này không thể khôi phục!')) {
        window.location.href = 'delete-products.php?product_id=' + productId;
      }
    };
  });
</script>

<?php require "../layouts/footer.php"; ?>