<?php
require_once "../../config/config.php";
require_once "../layouts/header.php";

// Fetch all audit logs
$stmt = $conn->query("SELECT audit_log.*, admins.admin_name AS admin_name 
                      FROM audit_log
                      JOIN admins ON audit_log.user_id = admins.id
                      ORDER BY audit_log.created_at DESC");

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
  <h3 class="mb-4">Activity History (Audit Log)</h3>

  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Timestamp</th>
        <th>Admin</th>
        <th>Action</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $index => $log): ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= date("m/d/Y H:i:s", strtotime($log['created_at'])) ?></td>
          <td><?= htmlspecialchars($log['admin_name']) ?></td>
          <td><?= strtoupper($log['action']) ?></td>
          <td><?= htmlspecialchars($log['description']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require "../layouts/footer.php"; ?>
