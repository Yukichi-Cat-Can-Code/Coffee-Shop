<?php
require "../../config/config.php";
use Dompdf\Dompdf;
use Dompdf\Options;

// Lấy ngày lọc từ GET hoặc gán mặc định
$fromDate = $_GET['from'] ?? date('Y-m-01');
$toDate = $_GET['to'] ?? date('Y-m-d');
$action = $_GET['action'] ?? 'filter';

// Truy vấn dữ liệu doanh thu
try {
  $sql = "SELECT DATE(created_at) as day, SUM(final_amount) as total
          FROM pos_orders
          WHERE created_at >= :from AND created_at < DATE_ADD(:to, INTERVAL 1 DAY)
          AND payment_status = 'Đã thanh toán'
          GROUP BY day ORDER BY day ASC";

  $stmt = $conn->prepare($sql);
  $stmt->execute([
    ':from' => $fromDate . ' 00:00:00',
    ':to' => $toDate . ' 23:59:59',
  ]);
  $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $salesData = [];
}

// Xử lý export
if ($action === 'excel') {
  exportToExcel($salesData);
  exit;
}
if ($action === 'pdf') {
  exportToPDF($salesData);
  exit;
}

// Export Excel
function exportToExcel($data) {
  header("Content-Type: application/vnd.ms-excel");
  header("Content-Disposition: attachment; filename=sales_report_" . date('Ymd') . ".xls");

  $total = 0;
  echo "<table border='1'>";
  echo "<tr><th>Date</th><th>Total Revenue (VND)</th></tr>";
  foreach ($data as $row) {
    $total += $row['total'];
    echo "<tr>";
    echo "<td>" . date('n/j/Y', strtotime($row['day'])) . "</td>";
    echo "<td>" . $row['total'] . "</td>";
    echo "</tr>";
  }
  // Thêm hàng tổng
  echo "<tr>";
  echo "<th>Total</th>";
  echo "<th>" . number_format($total, 3, '.', '') . "</th>";
  echo "</tr>";

  echo "</table>";
}


// Export PDF (dùng dompdf)


function exportToPDF($data) {
 require __DIR__ . '/../../vendor/autoload.php';

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);

    $total = 0;
    $today = date("Y-m-d H:i:s");

    $logoPath = realpath(__DIR__ . '/logo.png');
    if ($logoPath && file_exists($logoPath)) {
        $logoPath = 'file://' . $logoPath;
    } else {
        $logoPath = '';
    }

  $html = "
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
    .header {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }
    .header img {
      height: 50px;
      margin-right: 15px;
    }
    h2 { margin: 0; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #999; padding: 8px 10px; text-align: center; }
    th { background-color: #f2f2f2; }
    tfoot th { background-color: #dff0d8; }
    .footer { margin-top: 30px; font-style: italic; text-align: right; }
  </style>

  <div class='header'>";
  if ($logoPath) {
        $html .= "<img src='{$logoPath}' alt='Logo' style='height: 50px; margin-right: 15px;'/>";
  }
  $html .= "<h2>Revenue Report</h2>
  </div>

  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Revenue (VND)</th>
      </tr>
    </thead>
    <tbody>";

  foreach ($data as $row) {
    $total += $row['total'];
    $html .= "<tr>
                <td>{$row['day']}</td>
                <td>" . number_format($row['total'], 0, ',', '.') . "</td>
              </tr>";
  }

  $html .= "</tbody>
    <tfoot>
      <tr>
        <th>Total</th>
        <th>" . number_format($total, 0, ',', '.') . "</th>
      </tr>
    </tfoot>
  </table>

  <div class='footer'>
    Exported at: {$today}
  </div>";

  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream("sales_report_" . date('Ymd') . ".pdf");
}



require "../layouts/header.php";

?>

<!-- Interface -->
<div class="container-fluid">
  <h3 class="mb-4 fw-bold"><i class="fas fa-chart-line me-2 text-info"></i>Sales Report</h3>

  <!-- Date Filter -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label for="from" class="form-label">From Date</label>
      <input type="date" name="from" id="from" class="form-control" value="<?= htmlspecialchars($fromDate) ?>">
    </div>
    <div class="col-md-4">
      <label for="to" class="form-label">To Date</label>
      <input type="date" name="to" id="to" class="form-control" value="<?= htmlspecialchars($toDate) ?>">
    </div>
    <div class="col-md-4 align-self-end d-flex gap-2">
      <button type="submit" class="btn btn-primary" name="action" value="filter">
        <i class="fas fa-filter me-1"></i> Filter
      </button>
      <button type="submit" class="btn btn-success" name="action" value="excel">
        <i class="fas fa-file-excel me-1"></i> Export to Excel
      </button>
      <button type="submit" class="btn btn-danger" name="action" value="pdf">
        <i class="fas fa-file-pdf me-1"></i> Export to PDF
      </button>
    </div>
  </form>

  <!-- Revenue Chart -->
  <div class="card shadow">
    <div class="card-body">
      <h5 class="card-title mb-3">POS Revenue Chart</h5>
      <canvas id="salesChart" height="100"></canvas>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const salesData = <?= json_encode($salesData) ?>;
  const labels = salesData.map(item => item.day);
  const data = salesData.map(item => item.total);

  const ctx = document.getElementById('salesChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Revenue (VND)',
        data: data,
        borderColor: '#4e73df',
        backgroundColor: 'rgba(78, 115, 223, 0.1)',
        fill: true,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top' },
        tooltip: {
          callbacks: {
            label: ctx => Number(ctx.parsed.y).toLocaleString('vi-VN') + 'đ'
          }
        }
      },
      scales: {
        y: {
          ticks: {
            callback: value => value.toLocaleString('vi-VN') + 'đ'
          }
        }
      }
    }
  });
</script>


<?php require "../layouts/footer.php"; ?>
