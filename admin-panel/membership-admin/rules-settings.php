<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\admin-panel\membership-admin\rules-settings.php
require "../../config/config.php";
requireAdminLogin();

// Get current rules
try {
    $stmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
    $rules = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no rules exist, create default ones
    if (!$rules) {
        $conn->exec("INSERT INTO membership_rules (points_per_order, reset_period_months, retention_threshold_percent) 
                    VALUES (0.1, 12, 30)");
        $stmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
        $rules = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Rules Settings Error: ' . $e->getMessage());
    $rules = [
        'points_per_order' => 0.1,
        'reset_period_months' => 12,
        'retention_threshold_percent' => 30
    ];
}

// Handle rules update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rules'])) {
    try {
        $pointsPerOrder = floatval($_POST['points_per_order']);
        $resetPeriod = intval($_POST['reset_period_months']);
        $retentionThreshold = intval($_POST['retention_threshold']);

        // Validate values
        if ($pointsPerOrder <= 0) {
            throw new Exception("Points accumulation must be greater than 0");
        }

        if ($resetPeriod <= 0) {
            throw new Exception("Reset period must be greater than 0 months");
        }

        if ($retentionThreshold < 0 || $retentionThreshold > 100) {
            throw new Exception("Tier retention threshold must be between 0-100%");
        }

        // Update to database
        $stmt = $conn->prepare("
            UPDATE membership_rules 
            SET points_per_order = :points_per_order, 
                reset_period_months = :reset_period, 
                retention_threshold_percent = :retention_threshold
            WHERE id = :id
        ");

        $stmt->bindParam(':points_per_order', $pointsPerOrder);
        $stmt->bindParam(':reset_period', $resetPeriod);
        $stmt->bindParam(':retention_threshold', $retentionThreshold);
        $stmt->bindParam(':id', $rules['id']);
        $stmt->execute();

        $successMessage = "Rules updated successfully!";

        // Refresh rules data
        $stmt = $conn->query("SELECT * FROM membership_rules LIMIT 1");
        $rules = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Membership Rules Settings</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> <?= $successMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= $errorMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs mr-2"></i>General Rules Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="points_per_order">
                                        <i class="fas fa-coins mr-1 text-warning"></i>Points Accumulation Rate
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0.01" class="form-control"
                                            id="points_per_order" name="points_per_order"
                                            value="<?= htmlspecialchars($rules['points_per_order']) ?>" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">points / 1,000 VND</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Number of points earned for every 1,000 VND spent</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reset_period_months">
                                        <i class="fas fa-calendar-alt mr-1 text-info"></i>Points Reset Period
                                    </label>
                                    <div class="input-group">
                                        <input type="number" min="1" class="form-control"
                                            id="reset_period_months" name="reset_period_months"
                                            value="<?= htmlspecialchars($rules['reset_period_months']) ?>" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">months</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Number of months before points reset if retention threshold is not met</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="retention_threshold">
                                        <i class="fas fa-shield-alt mr-1 text-success"></i>Tier Retention Threshold
                                    </label>
                                    <div class="input-group">
                                        <input type="number" min="0" max="100" class="form-control"
                                            id="retention_threshold" name="retention_threshold"
                                            value="<?= htmlspecialchars($rules['retention_threshold_percent']) ?>" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Percentage of tier's minimum points required to maintain tier status</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" name="save_rules" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Save Settings
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetToDefault()">
                                <i class="fas fa-undo mr-1"></i> Reset to Default
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Rules Preview Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-eye mr-2"></i>Current Rules Preview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">
                            <i class="fas fa-coins mr-1"></i>Points Earning Rate:
                        </h6>
                        <p class="mb-0">
                            <strong><?= number_format($rules['points_per_order'] * 1000, 0) ?></strong> points
                            for every <strong>1,000 VND</strong> spent
                        </p>
                        <small class="text-muted">
                            Example: 50,000 VND order = <?= number_format($rules['points_per_order'] * 50000, 0) ?> points
                        </small>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-info">
                            <i class="fas fa-calendar-alt mr-1"></i>Reset Period:
                        </h6>
                        <p class="mb-0">
                            Points are evaluated every <strong><?= $rules['reset_period_months'] ?> months</strong>
                        </p>
                        <small class="text-muted">
                            Members inactive for this period may be downgraded
                        </small>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="fas fa-shield-alt mr-1"></i>Retention Threshold:
                        </h6>
                        <p class="mb-0">
                            Members need <strong><?= $rules['retention_threshold_percent'] ?>%</strong> of tier's minimum points to maintain status
                        </p>
                        <small class="text-muted">
                            Below this threshold, members may be downgraded
                        </small>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-question-circle mr-2"></i>How It Works
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            Members earn points with each purchase
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            Points determine membership tier level
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            Inactive members are evaluated periodically
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            Tiers may be downgraded if retention threshold is not met
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function resetToDefault() {
        if (confirm('Are you sure you want to reset all rules to default values? This action cannot be undone.')) {
            document.getElementById('points_per_order').value = '0.1';
            document.getElementById('reset_period_months').value = '12';
            document.getElementById('retention_threshold').value = '30';
        }
    }

    // Real-time preview update
    document.addEventListener('DOMContentLoaded', function() {
        const pointsInput = document.getElementById('points_per_order');
        const resetInput = document.getElementById('reset_period_months');
        const thresholdInput = document.getElementById('retention_threshold');

        function updatePreview() {
            // Update points preview
            const pointsValue = parseFloat(pointsInput.value) || 0;
            const exampleOrder = 50000;
            const earnedPoints = Math.floor(pointsValue * exampleOrder);

            // You can add more dynamic preview updates here if needed
        }

        pointsInput.addEventListener('input', updatePreview);
        resetInput.addEventListener('input', updatePreview);
        thresholdInput.addEventListener('input', updatePreview);
    });
</script>

<?php require "../layouts/footer.php"; ?>