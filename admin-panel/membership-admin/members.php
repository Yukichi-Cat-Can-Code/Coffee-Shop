<?php
require "../../config/config.php";
requireAdminLogin();

// Get search and pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(5, min(50, (int)$_GET['limit'])) : 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tierFilter = isset($_GET['tier']) ? trim($_GET['tier']) : '';
$sortField = isset($_GET['sort']) ? trim($_GET['sort']) : 'membership_points';
$sortDirection = isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc' ? 'ASC' : 'DESC';

// Valid sort fields list
$validSortFields = ['ID', 'user_name', 'membership_points', 'membership_tier', 'created_at', 'last_order_date'];
if (!in_array($sortField, $validSortFields)) {
    $sortField = 'membership_points';
}

try {
    // Build base query - Sửa lỗi thiếu cột cần thiết
    $query = "FROM users WHERE 1=1";
    $params = [];

    // Add search conditions
    if (!empty($search)) {
        $query .= " AND (user_name LIKE :search OR user_email LIKE :search OR user_phone LIKE :search)";
        $params[':search'] = "%$search%";
    }

    // Filter by tier
    if ($tierFilter === 'NULL' || $tierFilter === 'none') {
        $query .= " AND (membership_tier IS NULL OR membership_tier = 'none' OR membership_tier = '')";
    } elseif (!empty($tierFilter)) {
        $query .= " AND membership_tier = :tier";
        $params[':tier'] = $tierFilter;
    }

    // Count total records
    $countStmt = $conn->prepare("SELECT COUNT(*) as total " . $query);
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Query member data with sorting and pagination - Sửa query này
    $stmt = $conn->prepare("SELECT ID, user_name, user_email, user_phone, 
                           membership_tier, membership_points, points_reset_date,
                           tier_updated_at, last_order_date, created_at " . $query .
        " ORDER BY " . $sortField . " " . $sortDirection .
        " LIMIT :offset, :limit");

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get tier list for filter display
    $tiersStmt = $conn->query("SELECT tier_key, tier_name, tier_color FROM membership_tiers 
                              WHERE status = 'active' ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get member statistics by tier - Sửa query này để tương thích với DB
    $statsStmt = $conn->query("SELECT 
                                CASE 
                                    WHEN membership_tier IS NULL OR membership_tier = '' THEN 'none'
                                    ELSE membership_tier
                                END AS tier, 
                                COUNT(*) AS member_count,
                                SUM(membership_points) AS total_points
                              FROM users 
                              GROUP BY 
                                CASE 
                                    WHEN membership_tier IS NULL OR membership_tier = '' THEN 'none'
                                    ELSE membership_tier
                                END
                              ORDER BY 
                                CASE 
                                  WHEN membership_tier = 'gold' THEN 1
                                  WHEN membership_tier = 'silver' THEN 2
                                  WHEN membership_tier = 'bronze' THEN 3
                                  ELSE 4
                                END");
    $tierStats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Membership Error: ' . $e->getMessage());
    error_log('Query Error Details: ' . print_r($e->errorInfo, true));
    $errorMessage = "Database error occurred. Error details: " . $e->getMessage();
    $members = [];
    $totalPages = 1;
    $totalRecords = 0;
    $tiers = [];
    $tierStats = [];
}

// Helper function to get tier name from code
function getMembershipTierName($tierCode, $tiers = [])
{
    foreach ($tiers as $tier) {
        if ($tier['tier_key'] === $tierCode) {
            return $tier['tier_name'];
        }
    }

    if ($tierCode === 'none' || empty($tierCode)) {
        return 'No Tier';
    }

    return ucfirst($tierCode);
}

// Helper function to get badge class for each tier
function getMembershipBadgeClass($tierCode)
{
    switch ($tierCode) {
        case 'bronze':
            return 'bronze';
        case 'silver':
            return 'silver';
        case 'gold':
            return 'gold';
        default:
            return 'light';
    }
}

// Helper function to get color for each tier
function getTierColor($tierCode, $tiers = [])
{
    foreach ($tiers as $tier) {
        if ($tier['tier_key'] === $tierCode) {
            return $tier['tier_color'];
        }
    }

    return '#6c757d';
}

require "../layouts/header.php";

echo '<style>
    .badge-gold {
        background-color: #FFD700;
        color: #000;
    }
    .badge-silver {
        background-color: #C0C0C0;
        color: #000;
    }
    .badge-bronze {
        background-color: #CD7F32;
        color: #fff;
    }
    .tier-icon {
        color: inherit !important;
    }
</style>';
?>

<div class="container-fluid py-4">
    <!-- Page title and back button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Member Management</h1>
        <div>
            <a href="export-members.php" class="btn btn-sm btn-success mr-2">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
            <a href="index.php" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-1"></i> <?= $errorMessage ?>
        </div>
    <?php endif; ?>

    <!-- Overview statistics -->
    <div class="row mb-4">
        <?php foreach ($tierStats as $stat): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-<?= getMembershipBadgeClass($stat['tier']) ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1"
                                    style="color: <?= getTierColor($stat['tier'], $tiers) ?>">
                                    <?= getMembershipTierName($stat['tier'], $tiers) ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stat['member_count'] ?> members</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-medal fa-2x text-gray-300"
                                    style="color: <?= getTierColor($stat['tier'], $tiers) ?> !important;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter and search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="mb-0">
                <div class="form-row align-items-center">
                    <div class="col-md-4 mb-2">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" class="form-control" name="search"
                                placeholder="Search by name, email, phone" value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>

                    <div class="col-md-3 mb-2">
                        <select name="tier" class="form-control">
                            <option value="">- All Tiers -</option>
                            <option value="none" <?= $tierFilter === 'none' ? 'selected' : '' ?>>No Tier</option>
                            <?php foreach ($tiers as $tier): ?>
                                <option value="<?= $tier['tier_key'] ?>"
                                    <?= $tierFilter === $tier['tier_key'] ? 'selected' : '' ?>
                                    style="color: <?= $tier['tier_color'] ?>; font-weight: bold;">
                                    <?= htmlspecialchars($tier['tier_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <select name="limit" class="form-control">
                            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15 items</option>
                            <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25 items</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 items</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="members.php" class="btn btn-secondary">
                            <i class="fas fa-redo-alt mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Member list -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Member List (<?= $totalRecords ?> results)</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                    id="dropdownSortMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-sort mr-1"></i> Sort
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownSortMenu">
                    <a class="dropdown-item <?= $sortField == 'membership_points' && $sortDirection == 'DESC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=membership_points&dir=desc">
                        <i class="fas fa-sort-amount-down mr-1"></i> Highest Points
                    </a>
                    <a class="dropdown-item <?= $sortField == 'membership_points' && $sortDirection == 'ASC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=membership_points&dir=asc">
                        <i class="fas fa-sort-amount-up mr-1"></i> Lowest Points
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item <?= $sortField == 'last_order_date' && $sortDirection == 'DESC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=last_order_date&dir=desc">
                        <i class="fas fa-shopping-cart mr-1"></i> Recent Orders
                    </a>
                    <a class="dropdown-item <?= $sortField == 'created_at' && $sortDirection == 'DESC' ? 'active' : '' ?>"
                        href="?search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=created_at&dir=desc">
                        <i class="fas fa-user-plus mr-1"></i> Newest Members
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($members)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> No members found matching the search criteria.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 60px">ID</th>
                                <th>Member Name</th>
                                <th>Contact</th>
                                <th>Membership Tier</th>
                                <th>Points</th>
                                <th>Expiry Date</th>
                                <th style="width: 100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td class="text-center font-weight-bold"><?= $member['ID'] ?></td>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($member['user_name']) ?></div>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt mr-1"></i>
                                            Joined: <?= date('d/m/Y', strtotime($member['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div><i class="far fa-envelope mr-1"></i> <?= htmlspecialchars($member['user_email']) ?></div>
                                        <?php if (!empty($member['user_phone'])): ?>
                                            <div><i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($member['user_phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($member['membership_tier']) && $member['membership_tier'] !== 'none'): ?>
                                            <?php
                                            $badgeClass = getMembershipBadgeClass($member['membership_tier']);
                                            $tierColor = getTierColor($member['membership_tier'], $tiers);
                                            ?>
                                            <span class="badge badge-pill badge-<?= $badgeClass ?>"
                                                style="font-size: 90%; padding: 5px 10px; <?= $badgeClass == 'light' ? "background-color: $tierColor;" : "" ?>">
                                                <i class="fas fa-medal mr-1 tier-icon"></i>
                                                <?= getMembershipTierName($member['membership_tier'], $tiers) ?>
                                            </span>
                                            <?php if ($member['tier_updated_at']): ?>
                                                <div class="text-muted small mt-1">
                                                    Updated: <?= date('d/m/Y', strtotime($member['tier_updated_at'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-pill badge-light text-muted">
                                                No Tier
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <span class="font-weight-bold"><?= number_format($member['membership_points']) ?></span>
                                        <?php if ($member['last_order_date']): ?>
                                            <div class="text-muted small">
                                                <i class="fas fa-shopping-cart mr-1"></i>
                                                <?= date('d/m/Y', strtotime($member['last_order_date'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($member['points_reset_date']): ?>
                                            <?php
                                            $resetDate = new DateTime($member['points_reset_date']);
                                            $today = new DateTime();
                                            $diff = $today->diff($resetDate);
                                            $daysLeft = $diff->days;
                                            $isPast = $today > $resetDate;
                                            ?>

                                            <?php if ($isPast): ?>
                                                <span class="badge badge-danger">Expired</span>
                                            <?php elseif ($daysLeft <= 30): ?>
                                                <span class="badge badge-warning">
                                                    <?= $daysLeft ?> days
                                                </span>
                                            <?php else: ?>
                                                <?= date('d/m/Y', strtotime($member['points_reset_date'])) ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="member-detail.php?id=<?= $member['ID'] ?>" class="btn btn-sm btn-info" title="View Details">
                                            <!-- <i class="fas fa-eye"></i> -->
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- <button type="button" class="btn btn-sm btn-primary edit-points-btn"
                                            data-toggle="modal" data-target="#editPointsModal"
                                            data-id="<?= $member['ID'] ?>"
                                            data-name="<?= htmlspecialchars($member['user_name']) ?>"
                                            data-points="<?= $member['membership_points'] ?>"
                                            title="Edit Points">
                                            <i class="fas fa-edit"></i>
                                        </button> -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>

                            <?php
                            $startPage = max(1, min($page - 2, $totalPages - 4));
                            $endPage = min($totalPages, $startPage + 4);
                            ?>

                            <?php if ($startPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">1</a>
                                </li>
                                <?php if ($startPage > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                        <?= $totalPages ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&tier=<?= urlencode($tierFilter) ?>&sort=<?= $sortField ?>&dir=<?= $sortDirection ?>&limit=<?= $limit ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Points Modal -->
<div class="modal fade" id="editPointsModal" tabindex="-1" role="dialog" aria-labelledby="editPointsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="adjust-points.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPointsModalLabel">Adjust Member Points</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editUserID">
                    <div class="form-group">
                        <label>Member</label>
                        <input type="text" class="form-control" id="editUserName" readonly>
                    </div>
                    <div class="form-group">
                        <label>Current Points</label>
                        <input type="text" class="form-control" id="currentPoints" readonly>
                    </div>
                    <div class="form-group">
                        <label for="pointAdjustment">Adjust Points</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <select class="form-control" name="adjustment_type" id="adjustmentType">
                                    <option value="add">+</option>
                                    <option value="subtract">-</option>
                                    <option value="set">Set</option>
                                </select>
                            </div>
                            <input type="number" class="form-control" id="pointAdjustment" name="points" min="0" step="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="adjustmentReason">Reason</label>
                        <select class="form-control" id="adjustmentReason" name="reason">
                            <option value="manual_adjust">Manual Adjustment</option>
                            <option value="customer_service">Customer Service</option>
                            <option value="promotion">Promotion</option>
                            <option value="correction">Correction</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="adjustmentNote">Notes</label>
                        <textarea class="form-control" id="adjustmentNote" name="note" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Handle edit points modal
        $('.edit-points-btn').click(function() {
            var userId = $(this).data('id');
            var userName = $(this).data('name');
            var points = $(this).data('points');

            $('#editUserID').val(userId);
            $('#editUserName').val(userName);
            $('#currentPoints').val(points.toLocaleString('en-US'));
            $('#pointAdjustment').val('');
        });

        // Highlight rows on hover
        $('tbody tr').hover(
            function() {
                $(this).addClass('bg-light');
            },
            function() {
                $(this).removeClass('bg-light');
            }
        );
    });
</script>

<?php require "../layouts/footer.php"; ?>