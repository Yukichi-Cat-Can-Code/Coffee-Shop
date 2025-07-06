<?php
require "../../config/config.php";

requireAdminLogin();

function safeCount($conn, $table)
{
    try {
        return $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

// Check if table exists
function tableExists($conn, $table)
{
    try {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Get system stats safely
$stats = [
    'admins' => safeCount($conn, 'admins'),
    'products' => 0,
    'orders' => 0,
    'users' => 0
];

if (tableExists($conn, 'product')) {
    $stats['products'] = safeCount($conn, 'product');
}
if (tableExists($conn, 'orders')) {
    $stats['orders'] = safeCount($conn, 'orders');
}
if (tableExists($conn, 'users')) {
    $stats['users'] = safeCount($conn, 'users');
}

// System info
$system_version = "1.2.0";
$last_update = "June 22, 2025";

require "../layouts/header.php";
?>

<div class="container-fluid py-4">
    <!-- Page header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="page-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
            </div>
            <div class="col">
                <h1 class="page-title">About Artisan Coffee Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= ADMINAPPURL ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">About</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- System Overview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">System Overview</h2>
                </div>
                <div class="card-body">
                    <div class="system-intro">
                        <h3>Artisan Coffee Management System</h3>
                        <p>
                            Welcome to the Artisan Coffee Shop Management System, designed specifically for coffee
                            shop operations. This system streamlines your daily business activities, from inventory
                            management to customer orders and staff scheduling.
                        </p>
                    </div>

                    <div class="row mt-4 g-4">
                        <div class="col-md-6">
                            <div class="features-section">
                                <h4>Key Features</h4>
                                <ul class="feature-list">
                                    <li><i class="fas fa-check-circle"></i> Complete inventory management</li>
                                    <li><i class="fas fa-check-circle"></i> Customer order processing</li>
                                    <li><i class="fas fa-check-circle"></i> Staff management & scheduling</li>
                                    <li><i class="fas fa-check-circle"></i> Sales analytics & reporting</li>
                                    <li><i class="fas fa-check-circle"></i> Customer loyalty program</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-section">
                                <h4>System Statistics</h4>
                                <div class="stats-grid">
                                    <div class="stat-card">
                                        <div class="stat-label">Products</div>
                                        <div class="stat-value"><?= number_format($stats['products']) ?></div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-label">Orders</div>
                                        <div class="stat-value"><?= number_format($stats['orders']) ?></div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-label">Users</div>
                                        <div class="stat-value"><?= number_format($stats['users']) ?></div>
                                    </div>
                                    <div class="stat-card">
                                        <div class="stat-label">Admins</div>
                                        <div class="stat-value"><?= number_format($stats['admins']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="system-info mt-4">
                        <h4>System Information</h4>
                        <table class="system-info-table">
                            <tr>
                                <th>Version</th>
                                <td><?= $system_version ?></td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td><?= $last_update ?></td>
                            </tr>
                            <tr>
                                <th>PHP Version</th>
                                <td><?= phpversion() ?></td>
                            </tr>
                            <tr>
                                <th>Server</th>
                                <td><?= $_SERVER['SERVER_SOFTWARE'] ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support & Updates -->
        <div class="col-lg-4">
            <!-- Support Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">Resources & Support</h2>
                </div>
                <div class="card-body">
                    <div class="support-section">
                        <h3>Help & Documentation</h3>
                        <p>Need help using the system? Check our documentation or contact support.</p>

                        <div class="resource-links">
                            <a href="#" class="resource-link">
                                <i class="fas fa-book"></i>
                                <div>
                                    <h5>User Manual</h5>
                                    <span>Complete guide to the system</span>
                                </div>
                            </a>
                            <a href="#" class="resource-link">
                                <i class="fas fa-question-circle"></i>
                                <div>
                                    <h5>FAQs</h5>
                                    <span>Frequently asked questions</span>
                                </div>
                            </a>
                            <a href="#" class="resource-link">
                                <i class="fas fa-video"></i>
                                <div>
                                    <h5>Video Tutorials</h5>
                                    <span>Step-by-step video guides</span>
                                </div>
                            </a>
                        </div>

                        <h3 class="mt-4">Support</h3>
                        <div class="support-links">
                            <a href="<?= ADMINAPPURL ?>/admins/contact.php" class="support-link">
                                <i class="fas fa-headset"></i>
                                <div>
                                    <h5>Contact Support</h5>
                                    <span>Get help from our team</span>
                                </div>
                            </a>
                            <a href="mailto:support@artisancoffee.com" class="support-link">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <h5>Email Support</h5>
                                    <span>support@artisancoffee.com</span>
                                </div>
                            </a>
                            <div class="support-link">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h5>Phone Support</h5>
                                    <span>+1 (555) 123-4567</span>
                                </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Updates Section -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">System Updates</h2>
                    </div>
                    <div class="card-body">
                        <div class="updates-section">
                            <div class="version-info">
                                <h3>Version <?= $system_version ?></h3>
                                <p class="update-date">Last updated: <?= $last_update ?></p>
                            </div>

                            <h4>What's New</h4>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-icon analytics">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h5>New Analytics Dashboard</h5>
                                        <p>Enhanced reporting with visual charts and filters</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-icon orders">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h5>Improved Order Management</h5>
                                        <p>Better workflow for handling customer orders</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-icon system">
                                        <i class="fas fa-cogs"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h5>System Optimization</h5>
                                        <p>Performance improvements and bug fixes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Page Styles */
        .page-header {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background-color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .page-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
            background-color: rgba(177, 131, 80, 0.1);
            color: #B18350;
            font-size: 1.5rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #513628;
        }

        .breadcrumb {
            margin-bottom: 0;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(177, 131, 80, 0.2);
            padding: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #513628;
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
            background-color: #fff;
        }

        /* Overview Section */
        .system-intro h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #513628;
            margin-bottom: 0.75rem;
        }

        .system-intro p {
            color: #6c757d;
            line-height: 1.6;
        }

        /* Features Section */
        .features-section h4,
        .stats-section h4,
        .system-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #513628;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .features-section h4:after,
        .stats-section h4:after,
        .system-info h4:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 3rem;
            background-color: #B18350;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-list li {
            padding: 0.5rem 0;
            color: #513628;
            display: flex;
            align-items: center;
        }

        .feature-list i {
            color: #4CAF50;
            margin-right: 0.75rem;
            font-size: 1rem;
        }

        /* Stats Section */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .stat-card {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border-left: 3px solid #B18350;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .stat-label {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #513628;
        }

        /* System Info Table */
        .system-info-table {
            width: 100%;
        }

        .system-info-table tr {
            border-bottom: 1px solid #e9ecef;
        }

        .system-info-table th,
        .system-info-table td {
            padding: 0.75rem 0;
        }

        .system-info-table th {
            font-weight: 600;
            color: #513628;
            width: 30%;
        }

        .system-info-table td {
            color: #6c757d;
        }

        /* Support Section */
        .support-section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #513628;
            margin-bottom: 0.75rem;
        }

        .support-section p {
            color: #6c757d;
            margin-bottom: 1.25rem;
        }

        .resource-links,
        .support-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .resource-link,
        .support-link {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background-color 0.2s ease;
            color: #513628;
        }

        .resource-link:hover,
        .support-link:hover {
            background-color: rgba(177, 131, 80, 0.1);
        }

        .resource-link i,
        .support-link i {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(177, 131, 80, 0.1);
            color: #B18350;
            border-radius: 50%;
            margin-right: 1rem;
            font-size: 1rem;
        }

        .resource-link div,
        .support-link div {
            flex: 1;
        }

        .resource-link h5,
        .support-link h5 {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
            color: #513628;
        }

        .resource-link span,
        .support-link span {
            font-size: 0.8rem;
            color: #6c757d;
        }

        /* Updates Section */
        .version-info {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px dashed rgba(177, 131, 80, 0.3);
        }

        .version-info h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #513628;
            margin-bottom: 0.25rem;
        }

        .update-date {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
            margin-top: 1.5rem;
        }

        .timeline:before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0.75rem;
            bottom: 0.75rem;
            width: 2px;
            background-color: rgba(177, 131, 80, 0.3);
        }

        .timeline-item {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-icon {
            position: absolute;
            left: -2rem;
            top: 0.25rem;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.7rem;
            z-index: 2;
        }

        .timeline-icon.analytics {
            background-color: #B18350;
        }

        .timeline-icon.orders {
            background-color: #4CAF50;
        }

        .timeline-icon.system {
            background-color: #2196F3;
        }

        .timeline-content {
            padding-left: 0.5rem;
        }

        .timeline-content h5 {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
            color: #513628;
        }

        .timeline-content p {
            font-size: 0.8rem;
            color: #6c757d;
            margin: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .system-info-table th {
                width: 40%;
            }
        }
    </style>

    <?php
    require "../layouts/footer.php";
    ?>