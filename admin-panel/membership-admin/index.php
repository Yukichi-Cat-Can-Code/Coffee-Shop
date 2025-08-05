<?php
require "../../config/config.php";
requireAdminLogin();

// Get total members by membership tiers
$stats = [];
try {
    // Total members
    $totalStmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_email IS NOT NULL");
    $stats['total'] = $totalStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

    // Statistics by membership tier
    $tierStmt = $conn->query("
        SELECT 
            COALESCE(membership_tier, 'none') as membership_tier, 
            COUNT(*) as count
        FROM users
        WHERE user_email IS NOT NULL
        GROUP BY membership_tier
        ORDER BY 
            CASE membership_tier 
                WHEN 'gold' THEN 1
                WHEN 'silver' THEN 2
                WHEN 'bronze' THEN 3
                ELSE 4
            END
    ");
    $tierData = $tierStmt->fetchAll(PDO::FETCH_ASSOC);

    // Process data for display
    $stats['tiers'] = [];
    foreach ($tierData as $tier) {
        $tierKey = $tier['membership_tier'] ?? 'none';
        $stats['tiers'][$tierKey] = $tier['count'];
    }

    // Get current rules information
    $rulesStmt = $conn->query("SELECT * FROM membership_rules WHERE is_active = 1 LIMIT 1");
    $rules = $rulesStmt->fetch(PDO::FETCH_ASSOC);

    // If no rules exist, create default values
    if (!$rules) {
        $rules = [
            'points_per_order' => 0.1,
            'reset_period_months' => 12,
            'retention_threshold_percent' => 30
        ];
    }

    // Get membership tier information with full validation
    $tiersStmt = $conn->query("SELECT * FROM membership_tiers WHERE status = 'active' ORDER BY min_points ASC");
    $tiers = $tiersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get detailed statistics
    $advancedStatsStmt = $conn->query("
        SELECT 
            COALESCE(membership_tier, 'none') as tier,
            COUNT(*) as member_count,
            SUM(membership_points) as total_points,
            AVG(membership_points) as avg_points,
            MAX(membership_points) as max_points,
            MIN(membership_points) as min_points
        FROM users 
        WHERE user_email IS NOT NULL
        GROUP BY membership_tier
    ");
    $advancedStats = $advancedStatsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error
    error_log('Membership Dashboard Error: ' . $e->getMessage());
    $stats['total'] = 0;
    $stats['tiers'] = [];
    $rules = [
        'points_per_order' => 0.1,
        'reset_period_months' => 12,
        'retention_threshold_percent' => 30
    ];
    $tiers = [];
    $advancedStats = [];
}

// Helper function to get tier display name
function getTierDisplayName($tierKey)
{
    switch ($tierKey) {
        case 'bronze':
            return 'Bronze Tier';
        case 'silver':
            return 'Silver Tier';
        case 'gold':
            return 'Gold Tier';
        case 'none':
        case null:
        case '':
            return 'No Tier';
        default:
            return ucfirst($tierKey);
    }
}

// Helper function to get tier color
function getTierColor($tierKey)
{
    switch ($tierKey) {
        case 'bronze':
            return '#CD7F32';
        case 'silver':
            return '#C0C0C0';
        case 'gold':
            return '#FFD700';
        default:
            return '#6c757d';
    }
}

// Helper function to get tier icon
function getTierIcon($tierKey)
{
    switch ($tierKey) {
        case 'bronze':
            return 'fa-medal';
        case 'silver':
            return 'fa-medal';
        case 'gold':
            return 'fa-crown';
        default:
            return 'fa-user';
    }
}

require "../layouts/header.php";
?>

<style>
    .tier-card {
        transition: transform 0.2s;
    }

    .tier-card:hover {
        transform: translateY(-5px);
    }

    .stats-number {
        font-size: 2rem;
        font-weight: bold;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-crown mr-2"></i>Membership Management
        </h1>
        <div>
            <span class="badge badge-info">Total: <?= number_format($stats['total']) ?> members</span>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <!-- Total Members -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 tier-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Members</div>
                            <div class="stats-number text-gray-800"><?= number_format($stats['total']) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gold Tier Members -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 tier-card" style="border-left: 4px solid #FFD700 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #B8860B;">
                                Gold Tier</div>
                            <div class="stats-number text-gray-800">
                                <?= number_format($stats['tiers']['gold'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x" style="color: #FFD700;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Silver Tier Members -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 tier-card" style="border-left: 4px solid #C0C0C0 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #708090;">
                                Silver Tier</div>
                            <div class="stats-number text-gray-800">
                                <?= number_format($stats['tiers']['silver'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x" style="color: #C0C0C0;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bronze Tier Members -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 tier-card" style="border-left: 4px solid #CD7F32 !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #8B4513;">
                                Bronze Tier</div>
                            <div class="stats-number text-gray-800">
                                <?= number_format($stats['tiers']['bronze'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x" style="color: #CD7F32;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature List -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-users mr-2"></i>Member Management
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Manage member list and view detailed information for each member.</p>
                    <a href="members.php" class="btn btn-primary btn-block">
                        <i class="fas fa-users mr-1"></i> Member List
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-cog mr-2"></i>Tier Settings
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Configure membership tiers and their corresponding discount percentages.</p>
                    <a href="tier-settings.php" class="btn btn-success btn-block">
                        <i class="fas fa-medal mr-1"></i> Tier Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2 -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-list-ol mr-2"></i>Rules Settings
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">Set up point accumulation rules, reset period and tier retention conditions.</p>
                    <a href="rules-settings.php" class="btn btn-info btn-block">
                        <i class="fas fa-ruler mr-1"></i> Rules Settings
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar mr-2"></i>Membership Reports
                    </h6>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="flex-grow-1">View reports and statistics about the membership program.</p>
                    <a href="reports.php" class="btn btn-secondary btn-block">
                        <i class="fas fa-chart-line mr-1"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Rules Information Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-rules mr-2"></i>Current Membership Rules
                    </h6>
                    <a href="rules-settings.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <tbody>
                                <tr>
                                    <th style="width: 40%;">
                                        <i class="fas fa-coins mr-2 text-warning"></i>Points per 10,000 VND
                                    </th>
                                    <td class="font-weight-bold">
                                        <?= number_format(($rules['points_per_order'] ?? 0.1) * 10000, 0) ?> points
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <i class="fas fa-calendar-alt mr-2 text-info"></i>Points Reset Period
                                    </th>
                                    <td class="font-weight-bold">
                                        <?= $rules['reset_period_months'] ?? 12 ?> months
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <i class="fas fa-percentage mr-2 text-success"></i>Tier Retention Threshold
                                    </th>
                                    <td class="font-weight-bold">
                                        <?= $rules['retention_threshold_percent'] ?? 30 ?>% of tier's maximum points
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Membership Tiers Information Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy mr-2"></i>Membership Tiers
                    </h6>
                    <a href="tier-settings.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($tiers)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            No membership tiers have been configured yet.
                            <a href="tier-settings.php" class="alert-link">Click here to configure</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th><i class="fas fa-tag mr-1"></i>Tier Name</th>
                                        <th><i class="fas fa-star mr-1"></i>Points Required</th>
                                        <th><i class="fas fa-percentage mr-1"></i>Discount</th>
                                        <th><i class="fas fa-toggle-on mr-1"></i>Status</th>
                                        <th><i class="fas fa-users mr-1"></i>Members</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tiers as $tier): ?>
                                        <tr>
                                            <td>
                                                <span style="color: <?= htmlspecialchars($tier['tier_color'] ?? getTierColor($tier['tier_key'])) ?>">
                                                    <i class="fas <?= htmlspecialchars($tier['tier_icon'] ?? getTierIcon($tier['tier_key'])) ?> mr-2"></i>
                                                    <strong><?= htmlspecialchars($tier['tier_name']) ?></strong>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold"><?= number_format($tier['min_points']) ?></span>
                                                <?php if (isset($tier['max_points']) && $tier['max_points']): ?>
                                                    - <?= number_format($tier['max_points']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">and above</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning" style="background-color: #ffc107; color: #212529;">
                                                    <?= number_format($tier['discount_percent'], 1) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($tier['status'] == 'active'): ?>
                                                    <span class="badge badge-success" style="background-color: #28a745; color: #fff;">
                                                        <i class="fas fa-check mr-1"></i>Active
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger" style="background-color: #dc3545; color: #fff;">
                                                        <i class="fas fa-pause mr-1"></i>Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold">
                                                    <?= number_format($stats['tiers'][$tier['tier_key']] ?? 0) ?>
                                                </span>
                                                <small class="text-muted">members</small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require "../layouts/footer.php"; ?>