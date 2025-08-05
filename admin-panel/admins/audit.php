<?php
function logAudit($conn, $user_id, $action, $description = null) {
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

  $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, description, ip_address)
                          VALUES (?, ?, ?, ?)");
  $stmt->execute([$user_id, $action, $description, $ip]);
}
