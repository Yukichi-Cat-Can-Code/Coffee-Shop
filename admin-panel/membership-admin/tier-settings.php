<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\admin-panel\membership-admin\tier-settings.php
require "../../config/config.php";
requireAdminLogin();

// Handle tier update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tiers'])) {
    try {
        $conn->beginTransaction();

        // Process existing tiers
        foreach ($_POST['tier'] as $id => $tier) {
            // Check table structure before update
            $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);

            if (in_array('max_points', $checkColumns)) {
                // If max_points column exists
                $stmt = $conn->prepare("
                    UPDATE membership_tiers
                    SET tier_name = :name,
                        min_points = :min_points,
                        max_points = :max_points,
                        discount_percent = :discount,
                        status = :status
                    WHERE id = :id
                ");

                $maxPoints = !empty($tier['max_points']) ? $tier['max_points'] : null;
                $stmt->bindParam(':max_points', $maxPoints, PDO::PARAM_INT);
            } else {
                // If max_points column doesn't exist
                $stmt = $conn->prepare("
                    UPDATE membership_tiers
                    SET tier_name = :name,
                        min_points = :min_points,
                        discount_percent = :discount,
                        status = :status
                    WHERE id = :id
                ");
            }

            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $tier['name'], PDO::PARAM_STR);
            $stmt->bindParam(':min_points', $tier['min_points'], PDO::PARAM_INT);
            $stmt->bindParam(':discount', $tier['discount'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $tier['status'], PDO::PARAM_STR);
            $stmt->execute();
        }

        // Add new tier if provided
        if (!empty($_POST['new_tier']['name'])) {
            $newTier = $_POST['new_tier'];
            $tierKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $newTier['name']));
            $icon = !empty($newTier['icon']) ? $newTier['icon'] : 'fas fa-medal';
            $color = !empty($newTier['color']) ? $newTier['color'] : '#6c757d';

            // Check if tier_code or tier_key column exists
            $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);

            if (in_array('max_points', $checkColumns)) {
                // Has max_points column
                if (in_array('tier_code', $checkColumns)) {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_code, tier_name, min_points, max_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :max_points, :discount, :icon, :color, :status)
                    ");
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_key, tier_name, min_points, max_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :max_points, :discount, :icon, :color, :status)
                    ");
                }

                $maxPoints = !empty($newTier['max_points']) ? $newTier['max_points'] : null;
                $stmt->bindParam(':max_points', $maxPoints, PDO::PARAM_INT);
            } else {
                // No max_points column
                if (in_array('tier_code', $checkColumns)) {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_code, tier_name, min_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :discount, :icon, :color, :status)
                    ");
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO membership_tiers 
                        (tier_key, tier_name, min_points, discount_percent, tier_icon, tier_color, status)
                        VALUES (:code, :name, :min_points, :discount, :icon, :color, :status)
                    ");
                }
            }

            $stmt->bindParam(':code', $tierKey, PDO::PARAM_STR);
            $stmt->bindParam(':name', $newTier['name'], PDO::PARAM_STR);
            $stmt->bindParam(':min_points', $newTier['min_points'], PDO::PARAM_INT);
            $stmt->bindParam(':discount', $newTier['discount'], PDO::PARAM_STR);
            $stmt->bindParam(':icon', $icon, PDO::PARAM_STR);
            $stmt->bindParam(':color', $color, PDO::PARAM_STR);
            $stmt->bindParam(':status', $newTier['status'], PDO::PARAM_STR);
            $stmt->execute();
        }

        $conn->commit();
        $successMessage = "Membership tiers updated successfully!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Handle tier deletion
if (isset($_POST['delete_tier'])) {
    try {
        $tierId = (int)$_POST['delete_tier'];

        // Check table structure to determine column name
        $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);
        $tierCodeColumn = in_array('tier_code', $checkColumns) ? 'tier_code' : 'tier_key';

        // Check if any members are using this tier
        $checkStmt = $conn->prepare("
            SELECT {$tierCodeColumn} FROM membership_tiers WHERE id = :id
        ");
        $checkStmt->bindParam(':id', $tierId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $tierCode = $checkStmt->fetch(PDO::FETCH_ASSOC)[$tierCodeColumn];

            $countStmt = $conn->prepare("
                SELECT COUNT(*) as count FROM users WHERE membership_tier = :tier_code
            ");
            $countStmt->bindParam(':tier_code', $tierCode, PDO::PARAM_STR);
            $countStmt->execute();
            $memberCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($memberCount > 0) {
                $errorMessage = "Cannot delete this tier because {$memberCount} members are currently using it!";
            } else {
                $deleteStmt = $conn->prepare("DELETE FROM membership_tiers WHERE id = :id");
                $deleteStmt->bindParam(':id', $tierId, PDO::PARAM_INT);
                $deleteStmt->execute();
                $successMessage = "Membership tier deleted successfully!";
            }
        } else {
            $errorMessage = "Tier not found for deletion!";
        }
    } catch (PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Get list of tiers
try {
    $stmt = $conn->query("SELECT * FROM membership_tiers ORDER BY min_points ASC");
    $tiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check table structure to know if max_points column exists
    $checkColumns = $conn->query("DESCRIBE membership_tiers")->fetchAll(PDO::FETCH_COLUMN);
    $hasMaxPoints = in_array('max_points', $checkColumns);
} catch (PDOException $e) {
    $tiers = [];
    $hasMaxPoints = false;
    $errorMessage = "Error loading data: " . $e->getMessage();
}

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-medal mr-2"></i>Membership Tier Settings
        </h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i><?= $successMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i><?= $errorMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-cog mr-2"></i>Tier Configuration
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tierTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="20%">Tier Name</th>
                                <th width="15%">Minimum Points</th>
                                <?php if ($hasMaxPoints): ?>
                                    <th width="15%">Maximum Points</th>
                                <?php endif; ?>
                                <th width="15%">Discount (%)</th>
                                <th width="15%">Status</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiers as $tier): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (isset($tier['tier_icon'])): ?>
                                                <i class="<?= htmlspecialchars($tier['tier_icon']) ?>"
                                                    style="color: <?= htmlspecialchars($tier['tier_color'] ?? '#6c757d') ?>; margin-right: 8px;"></i>
                                            <?php endif; ?>
                                            <input type="text" class="form-control"
                                                name="tier[<?= $tier['id'] ?>][name]"
                                                value="<?= htmlspecialchars($tier['tier_name']) ?>"
                                                required>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control"
                                            name="tier[<?= $tier['id'] ?>][min_points]"
                                            value="<?= $tier['min_points'] ?>"
                                            min="0" step="1" required>
                                    </td>
                                    <?php if ($hasMaxPoints): ?>
                                        <td>
                                            <input type="number" class="form-control"
                                                name="tier[<?= $tier['id'] ?>][max_points]"
                                                value="<?= $tier['max_points'] ?? '' ?>"
                                                min="0" step="1"
                                                placeholder="No limit">
                                            <small class="form-text text-muted">Leave empty = no limit</small>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" class="form-control"
                                                name="tier[<?= $tier['id'] ?>][discount]"
                                                value="<?= $tier['discount_percent'] ?>"
                                                min="0" max="100" step="0.1" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-control" name="tier[<?= $tier['id'] ?>][status]">
                                            <option value="active" <?= $tier['status'] === 'active' ? 'selected' : '' ?>>
                                                Active
                                            </option>
                                            <option value="inactive" <?= $tier['status'] === 'inactive' ? 'selected' : '' ?>>
                                                Inactive
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger delete-tier-btn"
                                            data-toggle="modal" data-target="#deleteTierModal"
                                            data-tier-id="<?= $tier['id'] ?>"
                                            data-tier-name="<?= htmlspecialchars($tier['tier_name']) ?>">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- Add new tier -->
                            <tr class="table-light">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-plus-circle text-success mr-2"></i>
                                        <input type="text" class="form-control"
                                            name="new_tier[name]" placeholder="New tier name">
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control"
                                        name="new_tier[min_points]" min="0" step="1" placeholder="Minimum points">
                                </td>
                                <?php if ($hasMaxPoints): ?>
                                    <td>
                                        <input type="number" class="form-control"
                                            name="new_tier[max_points]" min="0" step="1" placeholder="Maximum points">
                                        <small class="form-text text-muted">Leave empty = no limit</small>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            name="new_tier[discount]" min="0" max="100" step="0.1" placeholder="% discount">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="form-control" name="new_tier[status]">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fas fa-plus mr-1"></i>New Tier
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Important Notes:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Each tier must have different minimum points</li>
                        <li>When points overlap, the system will prioritize the tier with the highest minimum points</li>
                        <li>Maximum points can be left empty to indicate "no limit"</li>
                        <li>Discount percentage ranges from 0% to 100%</li>
                    </ul>
                </div>

                <div class="text-center">
                    <button type="submit" name="save_tiers" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    <a href="index.php" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- User Guide -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-question-circle mr-2"></i>User Guide
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">How to Setup Tiers:</h6>
                    <ol>
                        <li>Enter tier name (e.g., Bronze, Silver, Gold)</li>
                        <li>Set minimum points required to reach that tier</li>
                        <li>Configure discount percentage for the tier</li>
                        <li>Choose active/inactive status</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Example Tier Setup:</h6>
                    <ul>
                        <li><strong>Bronze:</strong> 0 - 199 points (5% discount)</li>
                        <li><strong>Silver:</strong> 200 - 499 points (10% discount)</li>
                        <li><strong>Gold:</strong> 500+ points (15% discount)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Tier Confirmation Modal -->
<div class="modal fade" id="deleteTierModal" tabindex="-1" role="dialog" aria-labelledby="deleteTierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteTierModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the tier <span id="tierNameToDelete" class="font-weight-bold text-danger"></span>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-warning mr-1"></i>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <input type="hidden" name="delete_tier" id="tierIdToDelete">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>Confirm Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Setup data for delete modal
        const deleteTierBtns = document.querySelectorAll('.delete-tier-btn');
        deleteTierBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tierId = this.getAttribute('data-tier-id');
                const tierName = this.getAttribute('data-tier-name');

                document.getElementById('tierIdToDelete').value = tierId;
                document.getElementById('tierNameToDelete').textContent = tierName;
            });
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const minPointInputs = document.querySelectorAll('input[name*="[min_points]"]');
            const minPoints = [];

            minPointInputs.forEach(input => {
                if (input.value.trim() !== '') {
                    minPoints.push(parseInt(input.value));
                }
            });

            // Check for duplicate minimum points
            const duplicates = minPoints.filter((item, index) => minPoints.indexOf(item) !== index);
            if (duplicates.length > 0) {
                e.preventDefault();
                alert('Duplicate minimum points found. Please check again!');
                return false;
            }
        });
    });
</script>

<?php require "../layouts/footer.php"; ?>