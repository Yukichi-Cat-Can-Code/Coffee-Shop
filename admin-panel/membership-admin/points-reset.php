<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\admin-panel\membership-admin\points-reset.php
require "../../config/config.php";
requireAdminLogin();

// Only super admin can access this tool
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] != 'super_admin') {
    header("Location: index.php");
    exit;
}

// Get membership rules
$rulesStmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
$rules = $rulesStmt->fetch(PDO::FETCH_ASSOC);

// Get all tiers for calculation
$tiersStmt = $conn->query("SELECT * FROM membership_tiers ORDER BY min_points ASC");
$tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

$results = [];
$downgradedCount = 0;
$maintainedCount = 0;
$upgradedCount = 0;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_reset'])) {
    try {
        // Get specific date for reset (evaluate from this date backwards)
        $resetDate = $_POST['reset_date'] ?? date('Y-m-d');
        $retentionThreshold = $rules['retention_threshold_percent'] / 100;
        $resetMonths = $rules['reset_period_months'];

        // Find users who need re-evaluation (no orders within specified period)
        $stmt = $conn->prepare("
            SELECT ID, user_name, user_email, membership_points, membership_tier 
            FROM users 
            WHERE user_email IS NOT NULL 
            AND (
                last_order_date IS NULL 
                OR last_order_date < DATE_SUB(:reset_date, INTERVAL :reset_months MONTH)
            )
        ");
        $stmt->bindParam(':reset_date', $resetDate);
        $stmt->bindParam(':reset_months', $resetMonths, PDO::PARAM_INT);
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process each user
        foreach ($users as $user) {
            $currentPoints = $user['membership_points'];
            $currentTier = $user['membership_tier'];

            // Find current tier and minimum points
            $currentTierKey = $user['membership_tier'];
            $currentTierMinPoints = 0;

            foreach ($tiers as $tier) {
                if ($tier['tier_key'] == $currentTierKey) {
                    $currentTierMinPoints = $tier['min_points'];
                    break;
                }
            }

            // Calculate retention threshold points
            $retentionThresholdPoints = $currentTierMinPoints * $retentionThreshold;

            // If points are insufficient to maintain tier
            if ($currentPoints < $retentionThresholdPoints) {
                // Determine new tier based on points
                $newTier = 'none';
                foreach ($tiers as $tier) {
                    if ($currentPoints >= $tier['min_points']) {
                        $newTier = $tier['tier_key'];
                    } else {
                        break;
                    }
                }

                // Update tier if different from current tier
                if ($newTier != $currentTier) {
                    $updateStmt = $conn->prepare("
                        UPDATE users 
                        SET membership_tier = :new_tier,
                            points_reset_date = :reset_date
                        WHERE ID = :user_id
                    ");
                    $updateStmt->bindParam(':new_tier', $newTier);
                    $updateStmt->bindParam(':reset_date', $resetDate);
                    $updateStmt->bindParam(':user_id', $user['ID']);
                    $updateStmt->execute();

                    // Add to history
                    $reason = "Membership tier re-evaluation due to insufficient points for retention";
                    $pointsStmt = $conn->prepare("
                        INSERT INTO membership_point_history (user_id, points, action, notes, created_at)
                        VALUES (:user_id, 0, 'adjusted', :reason, NOW())
                    ");
                    $pointsStmt->bindParam(':user_id', $user['ID']);
                    $pointsStmt->bindParam(':reason', $reason);
                    $pointsStmt->execute();

                    // Determine if downgraded or upgraded
                    $tierOrder = ['none' => 0, 'bronze' => 1, 'silver' => 2, 'gold' => 3];
                    $currentTierOrder = $tierOrder[$currentTier] ?? 0;
                    $newTierOrder = $tierOrder[$newTier] ?? 0;

                    if ($newTierOrder < $currentTierOrder) {
                        $downgradedCount++;
                    } else {
                        $upgradedCount++;
                    }

                    $results[] = [
                        'user' => $user['user_name'] . ' (' . $user['user_email'] . ')',
                        'old_tier' => $currentTier,
                        'new_tier' => $newTier,
                        'points' => $currentPoints,
                        'threshold' => $retentionThresholdPoints
                    ];
                } else {
                    $maintainedCount++;
                }
            } else {
                $maintainedCount++;
            }
        }

        $successMessage = "Completed membership tier re-evaluation for " . count($users) . " members.";
    } catch (Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Points Reset & Tier Evaluation Tool</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= $errorMessage ?>
        </div>
    <?php endif; ?>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-1"></i> <?= $successMessage ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-12">
            <!-- Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools mr-2"></i>Membership Tier Reset & Re-evaluation Tool
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Important Notice!</strong> This tool will check and downgrade members who have been inactive for
                        <strong><?= $rules['reset_period_months'] ?> months</strong>, if their points do not meet
                        <strong><?= $rules['retention_threshold_percent'] ?>%</strong> of the tier retention threshold.
                    </div>

                    <form method="post" action="" class="mb-4">
                        <div class="form-group">
                            <label for="reset_date">
                                <i class="fas fa-calendar-alt mr-1"></i>Evaluate up to date:
                            </label>
                            <input type="date" class="form-control" id="reset_date" name="reset_date" value="<?= date('Y-m-d') ?>" required>
                            <small class="form-text text-muted">
                                Members with no orders before this date minus <?= $rules['reset_period_months'] ?> months will be evaluated.
                            </small>
                        </div>
                        <button type="submit" name="run_reset" class="btn btn-primary"
                            onclick="return confirm('Are you sure you want to run the membership tier re-evaluation? This action cannot be undone.')">
                            <i class="fas fa-sync mr-1"></i> Run Evaluation Tool
                        </button>
                    </form>

                    <?php if (!empty($results)): ?>
                        <div class="mt-4">
                            <h5 class="mb-3">
                                <i class="fas fa-chart-bar mr-2"></i>Evaluation Results
                            </h5>

                            <!-- Statistics Cards -->
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5 class="card-title mb-1">Downgraded</h5>
                                                    <p class="card-text h2 mb-0"><?= $downgradedCount ?></p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-arrow-down fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5 class="card-title mb-1">Maintained</h5>
                                                    <p class="card-text h2 mb-0"><?= $maintainedCount ?></p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-equals fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5 class="card-title mb-1">Upgraded</h5>
                                                    <p class="card-text h2 mb-0"><?= $upgradedCount ?></p>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="fas fa-arrow-up fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Results Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th><i class="fas fa-user mr-1"></i>Member</th>
                                            <th><i class="fas fa-star mr-1"></i>Current Points</th>
                                            <th><i class="fas fa-shield-alt mr-1"></i>Retention Threshold</th>
                                            <th><i class="fas fa-medal mr-1"></i>Previous Tier</th>
                                            <th><i class="fas fa-trophy mr-1"></i>New Tier</th>
                                            <th><i class="fas fa-exchange-alt mr-1"></i>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $result): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($result['user']) ?></td>
                                                <td class="text-right">
                                                    <span class="font-weight-bold"><?= number_format($result['points']) ?></span>
                                                </td>
                                                <td class="text-right">
                                                    <span class="text-muted"><?= number_format($result['threshold']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary"><?= ucfirst($result['old_tier']) ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $tierOrder = ['none' => 0, 'bronze' => 1, 'silver' => 2, 'gold' => 3];
                                                    $oldOrder = $tierOrder[$result['old_tier']] ?? 0;
                                                    $newOrder = $tierOrder[$result['new_tier']] ?? 0;

                                                    if ($newOrder > $oldOrder) {
                                                        $badgeClass = 'success';
                                                    } elseif ($newOrder < $oldOrder) {
                                                        $badgeClass = 'danger';
                                                    } else {
                                                        $badgeClass = 'info';
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $badgeClass ?>"><?= ucfirst($result['new_tier']) ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($newOrder > $oldOrder): ?>
                                                        <span class="text-success"><i class="fas fa-arrow-up mr-1"></i>Upgraded</span>
                                                    <?php elseif ($newOrder < $oldOrder): ?>
                                                        <span class="text-danger"><i class="fas fa-arrow-down mr-1"></i>Downgraded</span>
                                                    <?php else: ?>
                                                        <span class="text-info"><i class="fas fa-equals mr-1"></i>No Change</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-deck .card {
        margin-bottom: 1rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }

    .badge {
        font-size: 0.875em;
    }
</style>

<?php require "../layouts/footer.php"; ?>