<?php
// filepath: c:\xampp\htdocs\Coffee-Shop\admin-panel\membership-admin\edit-member.php
require "../../config/config.php";
requireAdminLogin();

// Check ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: members.php?error=invalid_id");
    exit;
}

$memberId = intval($_GET['id']);

// Helper functions
function getMembershipTierName($tierCode, $tiers = [])
{
    foreach ($tiers as $tier) {
        if ($tier['tier_key'] === $tierCode) {
            return $tier['tier_name'];
        }
    }

    switch ($tierCode) {
        case 'bronze':
            return 'Bronze Tier';
        case 'silver':
            return 'Silver Tier';
        case 'gold':
            return 'Gold Tier';
        case 'none':
            return 'No Tier';
        default:
            return ucfirst($tierCode);
    }
}

function getMembershipBadgeClass($tierCode)
{
    switch ($tierCode) {
        case 'bronze':
            return 'bronze';
        case 'silver':
            return 'secondary';
        case 'gold':
            return 'gold';
        default:
            return 'light';
    }
}

function updateMembershipTier($conn, $userId, $points)
{
    try {
        // Get appropriate tier based on points
        $stmt = $conn->prepare("
            SELECT tier_key
            FROM membership_tiers 
            WHERE status = 'active' AND min_points <= :points
            ORDER BY min_points DESC
            LIMIT 1
        ");
        $stmt->bindParam(':points', $points, PDO::PARAM_INT);
        $stmt->execute();

        $newTier = 'none';
        if ($stmt->rowCount() > 0) {
            $newTier = $stmt->fetch(PDO::FETCH_ASSOC)['tier_key'];
        }

        // Update member tier
        $updateStmt = $conn->prepare("
            UPDATE users 
            SET membership_tier = :tier, tier_updated_at = CURRENT_TIMESTAMP
            WHERE ID = :id
        ");
        $updateStmt->bindParam(':tier', $newTier, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $updateStmt->execute();

        return $newTier;
    } catch (Exception $e) {
        error_log('Error updating membership tier: ' . $e->getMessage());
        return null;
    }
}

try {
    // Get member information
    $stmt = $conn->prepare("
        SELECT * FROM users WHERE ID = :id LIMIT 1
    ");
    $stmt->bindParam(':id', $memberId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        throw new Exception("Cannot find member with ID: $memberId");
    }

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get available tiers
    $tiersStmt = $conn->query("SELECT * FROM membership_tiers WHERE status = 'active' ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get points history
    $pointsStmt = $conn->prepare("
        SELECT * FROM membership_point_history 
        WHERE user_id = :user_id
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $pointsStmt->bindParam(':user_id', $memberId, PDO::PARAM_INT);
    $pointsStmt->execute();
    $pointsHistory = $pointsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle form update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
        $conn->beginTransaction();

        try {
            $tier = $_POST['tier'];
            $points = floatval($_POST['points']);
            $notes = trim($_POST['notes']);

            // Validate points
            if ($points < 0) {
                throw new Exception("Points cannot be negative");
            }

            $originalPoints = $member['membership_points'];
            $originalTier = $member['membership_tier'];

            // Update member information
            $updateStmt = $conn->prepare("
                UPDATE users 
                SET membership_points = :points,
                    membership_tier = :tier,
                    tier_updated_at = CURRENT_TIMESTAMP
                WHERE ID = :id
            ");
            $updateStmt->bindParam(':points', $points, PDO::PARAM_INT);
            $updateStmt->bindParam(':tier', $tier, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $memberId, PDO::PARAM_INT);
            $updateStmt->execute();

            // Add to history if there are changes
            if ($points != $originalPoints || !empty($notes)) {
                $pointChange = $points - $originalPoints;
                $action = 'adjusted';

                if ($pointChange > 0) {
                    $action = 'earned';
                } elseif ($pointChange < 0) {
                    $action = 'used';
                }

                $historyNotes = $notes;
                if ($tier != $originalTier) {
                    $historyNotes .= ($historyNotes ? '; ' : '') . "Tier changed from {$originalTier} to {$tier}";
                }
                if ($pointChange != 0) {
                    $historyNotes .= ($historyNotes ? '; ' : '') . "Points adjusted by " . ($pointChange >= 0 ? '+' : '') . $pointChange;
                }

                $historyStmt = $conn->prepare("
                    INSERT INTO membership_point_history (user_id, points, action, notes, admin_id, created_at)
                    VALUES (:user_id, :points_change, :action, :notes, :admin_id, NOW())
                ");
                $historyStmt->bindParam(':user_id', $memberId, PDO::PARAM_INT);
                $historyStmt->bindParam(':points_change', $pointChange, PDO::PARAM_INT);
                $historyStmt->bindParam(':action', $action, PDO::PARAM_STR);
                $historyStmt->bindParam(':notes', $historyNotes, PDO::PARAM_STR);
                $historyStmt->bindParam(':admin_id', $_SESSION['admin_id'], PDO::PARAM_INT);
                $historyStmt->execute();
            }

            $conn->commit();
            $successMessage = "Member information updated successfully!";

            // Refresh member data
            $stmt->execute();
            $member = $stmt->fetch(PDO::FETCH_ASSOC);

            // Refresh points history
            $pointsStmt->execute();
            $pointsHistory = $pointsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}

require "../layouts/header.php";
?>

<style>
    .badge-bronze {
        background-color: #CD7F32;
        color: #fff;
    }

    .badge-gold {
        background-color: #FFD700;
        color: #000;
    }

    .badge-silver {
        background-color: #C0C0C0;
        color: #000;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Member</h1>
        <div>
            <a href="member-detail.php?id=<?= $memberId ?>" class="btn btn-info mr-2">
                <i class="fas fa-eye mr-1"></i> View Details
            </a>
            <a href="members.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= $errorMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> <?= $successMessage ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($member)): ?>
        <div class="row">
            <div class="col-xl-4">
                <!-- Member Information Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-edit mr-2"></i>Edit Member Information
                        </h6>
                        <span class="badge badge-<?= getMembershipBadgeClass($member['membership_tier']) ?> badge-pill">
                            <?= getMembershipTierName($member['membership_tier'], $tiers) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <div class="mb-3">
                                <label class="font-weight-bold">Full Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($member['user_name']) ?>" readonly>
                                <small class="form-text text-muted">Member ID: #<?= str_pad($member['ID'], 6, '0', STR_PAD_LEFT) ?></small>
                            </div>

                            <div class="mb-3">
                                <label class="font-weight-bold">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($member['user_email']) ?>" readonly>
                            </div>

                            <?php if (!empty($member['user_phone'])): ?>
                                <div class="mb-3">
                                    <label class="font-weight-bold">Phone</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($member['user_phone']) ?>" readonly>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="font-weight-bold">Join Date</label>
                                <input type="text" class="form-control" value="<?= date('d/m/Y H:i', strtotime($member['created_at'])) ?>" readonly>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-coins mr-1 text-warning"></i>Membership Points
                                </label>
                                <input type="number" class="form-control" name="points"
                                    value="<?= htmlspecialchars($member['membership_points']) ?>"
                                    step="1" min="0" required>
                                <small class="form-text text-muted">
                                    Current: <?= number_format($member['membership_points']) ?> points
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-medal mr-1 text-primary"></i>Membership Tier
                                </label>
                                <select class="form-control" name="tier">
                                    <option value="none" <?= ($member['membership_tier'] == 'none' || empty($member['membership_tier'])) ? 'selected' : '' ?>>
                                        No Tier
                                    </option>
                                    <?php foreach ($tiers as $tier): ?>
                                        <option value="<?= $tier['tier_key'] ?>"
                                            <?= $member['membership_tier'] == $tier['tier_key'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tier['tier_name']) ?>
                                            (<?= number_format($tier['min_points']) ?>+ points, <?= $tier['discount_percent'] ?>% discount)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">
                                    Auto-assigned based on points, but can be manually overridden
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-sticky-note mr-1 text-info"></i>Change Notes
                                </label>
                                <textarea class="form-control" name="notes" rows="3"
                                    placeholder="Enter reason for point/tier changes (optional)"></textarea>
                                <small class="form-text text-muted">
                                    This will be recorded in the member's history
                                </small>
                            </div>

                            <div class="mt-4">
                                <button type="submit" name="update_member" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Update Member
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <!-- Points History Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history mr-2"></i>Points History
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 150px;">Date & Time</th>
                                        <th style="width: 100px;">Points Change</th>
                                        <th style="width: 100px;">Action</th>
                                        <th>Notes</th>
                                        <th style="width: 100px;">Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($pointsHistory)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                No points history available for this member.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($pointsHistory as $record): ?>
                                            <tr>
                                                <td class="small">
                                                    <?= date('d/m/Y', strtotime($record['created_at'])) ?><br>
                                                    <span class="text-muted"><?= date('H:i', strtotime($record['created_at'])) ?></span>
                                                </td>
                                                <td class="text-center font-weight-bold <?= $record['points'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= $record['points'] > 0 ? '+' : '' ?><?= number_format($record['points']) ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $badgeClass = 'secondary';
                                                    switch ($record['action']) {
                                                        case 'earned':
                                                            $badgeClass = 'success';
                                                            break;
                                                        case 'used':
                                                            $badgeClass = 'info';
                                                            break;
                                                        case 'adjusted':
                                                            $badgeClass = 'warning';
                                                            break;
                                                        case 'expired':
                                                            $badgeClass = 'danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $badgeClass ?>">
                                                        <?= ucfirst($record['action']) ?>
                                                    </span>
                                                </td>
                                                <td class="small">
                                                    <?= htmlspecialchars($record['notes'] ?: 'No notes') ?>
                                                </td>
                                                <td class="text-center small">
                                                    <?= $record['admin_id'] ? 'Admin #' . $record['admin_id'] : 'System' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (count($pointsHistory) >= 20): ?>
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Showing recent 20 records.
                                    <a href="member-detail.php?id=<?= $memberId ?>">View all history</a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Member Statistics Card -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-bar mr-2"></i>Member Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="border-left-success shadow h-100 py-3 px-3">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Current Points
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($member['membership_points']) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-left-info shadow h-100 py-3 px-3">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Current Tier
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= getMembershipTierName($member['membership_tier'], $tiers) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="border-left-warning shadow h-100 py-3 px-3">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Last Tier Update
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= $member['tier_updated_at'] ? date('d/m/Y', strtotime($member['tier_updated_at'])) : 'Never' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border-left-primary shadow h-100 py-3 px-3">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Points Reset Date
                                    </div>
                                    <div class="h6 mb-0 font-weight-bold text-gray-800">
                                        <?= $member['points_reset_date'] ? date('d/m/Y', strtotime($member['points_reset_date'])) : 'Not set' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-suggest tier based on points
        const pointsInput = document.querySelector('input[name="points"]');
        const tierSelect = document.querySelector('select[name="tier"]');

        if (pointsInput && tierSelect) {
            pointsInput.addEventListener('input', function() {
                const points = parseInt(this.value) || 0;
                const tiers = <?= json_encode($tiers) ?>;

                // Find appropriate tier
                let suggestedTier = 'none';
                for (let i = tiers.length - 1; i >= 0; i--) {
                    if (points >= tiers[i].min_points) {
                        suggestedTier = tiers[i].tier_key;
                        break;
                    }
                }

                // Highlight suggested tier option
                Array.from(tierSelect.options).forEach(option => {
                    option.style.background = '';
                    option.style.fontWeight = '';
                    if (option.value === suggestedTier) {
                        option.style.background = '#e3f2fd';
                        option.style.fontWeight = 'bold';
                    }
                });
            });
        }

        // Confirm before major changes
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const originalPoints = <?= $member['membership_points'] ?>;
                const newPoints = parseInt(pointsInput.value) || 0;
                const pointsDiff = Math.abs(newPoints - originalPoints);

                if (pointsDiff > 100) {
                    if (!confirm(`You are changing points by ${pointsDiff}. Are you sure you want to continue?`)) {
                        e.preventDefault();
                    }
                }
            });
        }
    });
</script>

<?php require "../layouts/footer.php"; ?>