<?php
session_start();

// Include database connection
require_once 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

// Get pending registrations (waiting for approval)
$pendingQuery = "SELECT cko.user_id, u.u_name, u.mail, u.phone, cko.business_name, 
                 cko.years_of_experience, cko.registration_date, cat.c_name as speciality,
                 eu.address, z.name as zone_name
                 FROM cloud_kitchen_owner cko
                 JOIN users u ON cko.user_id = u.user_id
                 JOIN external_user eu ON cko.user_id = eu.user_id
                 LEFT JOIN zones z ON eu.zone_id = z.zone_id
                 JOIN category cat ON cko.speciality_id = cat.cat_id
                 WHERE cko.is_approved = 0
                 ORDER BY cko.registration_date DESC";

$pendingResult = $conn->query($pendingQuery);
$pendingKitchens = [];
if ($pendingResult->num_rows > 0) {
    while ($row = $pendingResult->fetch_assoc()) {
        $pendingKitchens[] = $row;
    }
}
$pendingCount = count($pendingKitchens);

// Get recent admin actions (we'll need to create this table)
$actionsQuery = "SELECT aa.action_type, aa.action_target, aa.created_at, u.u_name as admin_name
                 FROM admin_actions aa
                 JOIN admin a ON aa.admin_id = a.user_id
                 JOIN users u ON a.user_id = u.user_id
                 ORDER BY aa.created_at DESC
                 LIMIT 10";

$actionsResult = $conn->query($actionsQuery);
$recentActions = [];
if ($actionsResult->num_rows > 0) {
    while ($row = $actionsResult->fetch_assoc()) {
        $recentActions[] = $row;
    }
}

// Get general statistics
$statsQuery = "SELECT 
               (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE is_approved = 1 AND status = 'active') as approved_kitchens,
               (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE is_approved = 0) as pending_kitchens,
               (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE status = 'blocked') as blocked_kitchens,
               (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE status = 'suspended') as suspended_kitchens,
               (SELECT COUNT(*) FROM customer) as total_customers,
               (SELECT COUNT(*) FROM orders) as total_orders,
               (SELECT COUNT(*) FROM meals) as total_meals,
               (SELECT SUM(total_price) FROM orders) as total_revenue,
               (SELECT SUM(total_price) * 0.15 FROM orders) as total_earnings";

$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get top performing kitchens
$topKitchensQuery = "SELECT cko.user_id, cko.business_name, cko.orders_count, cko.average_rating,
                     u.u_name, cat.c_name as speciality
                     FROM cloud_kitchen_owner cko
                     JOIN users u ON cko.user_id = u.user_id
                     JOIN category cat ON cko.speciality_id = cat.cat_id
                     WHERE cko.is_approved = 1 AND cko.status = 'active'
                     ORDER BY cko.orders_count DESC
                     LIMIT 5";

$topKitchensResult = $conn->query($topKitchensQuery);
$topKitchens = [];
if ($topKitchensResult->num_rows > 0) {
    while ($row = $topKitchensResult->fetch_assoc()) {
        $topKitchens[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Kitchen Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-orange: #e57e24;
            --secondary-orange: #f39c12;
            --light-orange: #fff7e5;
            --dark-green: #3d6f5d;
            --light-green: #f0f8f5;
            --warm-brown: #6a4125;
            --cream: #f5e0c2;
        }

        body {
            background-color: var(--light-orange);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--warm-brown);
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            padding: 1rem 0;
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.3);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.3rem;
            color: white !important;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dashboard-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(61, 111, 93, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 2rem;
            background: white;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(61, 111, 93, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--cream), #fff);
            border-bottom: 2px solid var(--primary-orange);
            padding: 1.2rem;
            font-weight: 600;
            color: var(--warm-brown);
        }

        .stats-card {
            text-align: center;
            padding: 1.2rem 0.8rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            border: none;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 140px;
            background: linear-gradient(135deg, var(--cream), #fff);
            border: 2px solid var(--primary-orange);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(229, 126, 36, 0.2);
            color: var(--warm-brown);
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(229, 126, 36, 0.05) 0%, transparent 70%);
            opacity: 0.6;
            z-index: 1;
        }

        .stats-card-primary {
            border-color: var(--primary-orange);
        }

        .stats-card-secondary {
            border-color: #c19660;
        }

        .stats-card-success {
            border-color: var(--dark-green);
        }

        .stats-card-warning {
            border-color: var(--secondary-orange);
        }

        .stats-card-info {
            border-color: var(--warm-brown);
        }

        .stats-card-danger {
            border-color: #d2691e;
        }

        .stats-card-muted {
            border-color: #8b7355;
        }

        .stats-icon-container {
            background: rgba(229, 126, 36, 0.1);
            border-radius: 50%;
            padding: 0.8rem;
            margin: 0 auto 0.8rem;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(229, 126, 36, 0.2);
            position: relative;
            z-index: 2;
        }

        .stats-icon {
            font-size: 1.4rem;
            color: var(--primary-orange);
        }

        .stats-content {
            text-align: center;
            position: relative;
            z-index: 2;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stats-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            color: var(--warm-brown);
            line-height: 1.2;
        }

        .stats-label {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.1rem;
            color: var(--warm-brown);
        }

        .stats-subtitle {
            color: rgba(106, 65, 37, 0.7);
            font-size: 0.7rem;
            font-weight: 400;
            margin-bottom: 0.3rem;
        }

        .stats-trend {
            margin-top: 0.3rem;
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            background: rgba(229, 126, 36, 0.1);
            padding: 0.2rem 0.5rem;
            border-radius: 15px;
            margin-left: auto;
            margin-right: auto;
            width: fit-content;
        }

        .stats-trend i {
            font-size: 0.7rem;
        }

        .stats-trend small {
            font-weight: 500;
            font-size: 0.7rem;
            color: var(--warm-brown);
        }

        .stats-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .stats-card-warning .stats-trend small,
        .stats-card-warning .stats-subtitle,
        .stats-card-warning .stats-label,
        .stats-card-warning .stats-value {
            color: var(--warm-brown);
        }

        .stats-card-warning .stats-icon {
            color: var(--secondary-orange);
        }

        .btn-approve {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-reject {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-view {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.3);
        }

        .pending-item {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            margin-bottom: 0.5rem;
            padding: 1rem;
            border-radius: 0 8px 8px 0;
        }

        .action-item {
            border-left: 4px solid var(--primary-orange);
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0 12px 12px 0;
            background: linear-gradient(135deg, #fff, var(--light-orange));
            box-shadow: 0 2px 8px rgba(229, 126, 36, 0.1);
            transition: all 0.3s ease;
        }

        .action-item:hover {
            background: linear-gradient(135deg, var(--light-orange), #fff);
            box-shadow: 0 4px 15px rgba(229, 126, 36, 0.2);
            transform: translateX(5px);
        }

        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            background: var(--cream);
            color: var(--warm-brown);
            font-weight: 600;
            border: none;
        }

        .table td {
            border-color: #f1f3f4;
        }

        .rating-stars {
            color: #ffc107;
        }

        .alert-panel {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 1.5rem;
        }

        .nav-pills .nav-link.active {
            background: var(--primary-orange);
            border-radius: 8px;
        }

        .nav-pills .nav-link {
            color: var(--warm-brown);
            border-radius: 8px;
            margin-right: 0.5rem;
        }

        .badge-pending {
            background: #ffc107;
            color: #000;
        }

        .badge-approved {
            background: #28a745;
        }

        .badge-rejected {
            background: #dc3545;
        }

        .action-log-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            min-height: 600px;
            max-height: 700px;
        }

        .action-log-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.5rem;
            margin: 0;
            position: relative;
        }

        .action-log-body::-webkit-scrollbar {
            width: 8px;
        }

        .action-log-body::-webkit-scrollbar-track {
            background: var(--light-orange);
            border-radius: 10px;
            border: 1px solid rgba(229, 126, 36, 0.1);
        }

        .action-log-body::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 10px;
            border: 2px solid var(--light-orange);
        }

        .action-log-body::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-orange), var(--primary-orange));
            transform: scale(1.1);
        }

        .right-column {
            height: fit-content;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>
                Cloud Kitchen Admin
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item position-relative me-3">
                    <a href="#pendingSection" class="nav-link text-white">
                        <i class="fas fa-bell fa-lg"></i>
                        <?php if ($pendingCount > 0): ?>
                            <span class="notification-badge"><?php echo $pendingCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <a href="manage_kitchens.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-store me-1"></i> Manage Kitchens
                </a>
                <a href="logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Recent Alerts Section -->
        <?php if ($pendingCount > 0): ?>
        <div id="pendingSection" class="alert-panel mb-4">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Recent Alerts</h4>
            <p class="mb-2">You have <strong><?php echo $pendingCount; ?></strong> cloud kitchen registration(s) waiting for approval.</p>
            <a href="#pendingRegistrations" class="btn btn-warning">
                <i class="fas fa-eye me-1"></i> Review Pending Registrations
            </a>
        </div>
        <?php endif; ?>

        <!-- Statistics Overview -->
        <div class="row mb-4">
            <!-- Primary Business Metrics -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card dashboard-card stats-card stats-card-primary">
                    <div class="stats-icon-container">
                        <i class="fas fa-store stats-icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-value"><?php echo number_format($stats['approved_kitchens']); ?></div>
                        <div class="stats-label">Active Kitchens</div>
                        <div class="stats-subtitle">Approved & Operating</div>
                    </div>
                    <div class="stats-trend">
                        <i class="fas fa-arrow-up text-success"></i>
                        <small class="text-success">+<?php echo number_format($stats['pending_kitchens']); ?> pending</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card dashboard-card stats-card stats-card-secondary">
                    <div class="stats-icon-container">
                        <i class="fas fa-utensils stats-icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-value"><?php echo number_format($stats['total_meals']); ?></div>
                        <div class="stats-label">Total Meals</div>
                        <div class="stats-subtitle">Available Menu Items</div>
                    </div>
                    <div class="stats-trend">
                        <i class="fas fa-chart-line text-info"></i>
                        <small class="text-info">Menu Diversity</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card dashboard-card stats-card stats-card-success">
                    <div class="stats-icon-container">
                        <i class="fas fa-shopping-bag stats-icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-value"><?php echo number_format($stats['total_orders']); ?></div>
                        <div class="stats-label">Total Orders</div>
                        <div class="stats-subtitle">Platform Orders</div>
                    </div>
                    <div class="stats-trend">
                        <i class="fas fa-dollar-sign text-warning"></i>
                        <small class="text-warning">EGP <?php echo number_format($stats['total_revenue'] ?? 0); ?></small>
                    </div>
                    <div class="stats-trend mt-2">
                        <i class="fas fa-coins text-success"></i>
                        <small class="text-success">Earnings: EGP <?php echo number_format($stats['total_earnings'] ?? 0); ?></small>
                    </div>
                </div>
            </div>

            <!-- Secondary Metrics Row -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card dashboard-card stats-card stats-card-warning">
                    <div class="stats-icon-container">
                        <i class="fas fa-clock stats-icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-value"><?php echo number_format($stats['pending_kitchens']); ?></div>
                        <div class="stats-label">Pending Review</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card dashboard-card stats-card stats-card-info">
                    <div class="stats-icon-container">
                        <i class="fas fa-users stats-icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-value"><?php echo number_format($stats['total_customers']); ?></div>
                        <div class="stats-label">Customers</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card dashboard-card stats-card stats-card-danger">
                    <div class="stats-icon-container">
                        <i class="fas fa-ban stats-icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-value"><?php echo number_format($stats['blocked_kitchens']); ?></div>
                        <div class="stats-label">Blocked</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card dashboard-card stats-card stats-card-muted">
                    <div class="stats-icon-container">
                        <i class="fas fa-pause stats-icon"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-value"><?php echo number_format($stats['suspended_kitchens']); ?></div>
                        <div class="stats-label">Suspended</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Pending Registrations -->
            <div class="col-lg-8">
                <div id="pendingRegistrations" class="card dashboard-card">
                    <div class="card-header">
                        <h5><i class="fas fa-user-clock me-2"></i>Pending Cloud Kitchen Registrations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingKitchens)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5>No Pending Registrations</h5>
                                <p class="text-muted">All cloud kitchen registrations have been reviewed.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Business Details</th>
                                            <th>Owner Info</th>
                                            <th>Experience</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingKitchens as $kitchen): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($kitchen['business_name']); ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-utensils me-1"></i><?php echo htmlspecialchars($kitchen['speciality']); ?><br>
                                                    <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($kitchen['zone_name'] ?? 'N/A'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($kitchen['u_name']); ?></strong><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($kitchen['mail']); ?><br>
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($kitchen['phone']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($kitchen['years_of_experience']); ?></span>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($kitchen['registration_date'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm">
                                                    <button class="btn btn-view mb-1" onclick="viewKitchen(<?php echo $kitchen['user_id']; ?>)">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-approve mb-1" onclick="approveKitchen(<?php echo $kitchen['user_id']; ?>)">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-reject" onclick="rejectKitchen(<?php echo $kitchen['user_id']; ?>)">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Top Performing Kitchens -->
                <div class="card dashboard-card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-trophy me-2"></i>Top Performing Cloud Kitchens</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topKitchens)): ?>
                            <p class="text-muted text-center">No performance data available yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Business Name</th>
                                            <th>Owner</th>
                                            <th>Speciality</th>
                                            <th>Orders</th>
                                            <th>Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topKitchens as $index => $kitchen): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-warning text-dark">#<?php echo $index + 1; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($kitchen['business_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($kitchen['u_name']); ?></td>
                                            <td><?php echo htmlspecialchars($kitchen['speciality']); ?></td>
                                            <td><?php echo number_format($kitchen['orders_count']); ?></td>
                                            <td>
                                                <div class="rating-stars">
                                                    <?php 
                                                    $rating = round($kitchen['average_rating']);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                    }
                                                    ?>
                                                    <small class="text-muted ms-1">(<?php echo number_format($kitchen['average_rating'], 1); ?>)</small>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card dashboard-card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="manage_kitchens.php" class="btn btn-view">
                                <i class="fas fa-store me-2"></i>Manage All Kitchens
                            </a>
                            <a href="kitchen_list.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>Browse Kitchen List
                            </a>
                            <a href="manage_categories.php" class="btn btn-outline-secondary">
                                <i class="fas fa-tags me-2"></i>Manage Categories
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Log -->
            <div class="col-lg-4">
                <div class="card dashboard-card action-log-container">
                    <div class="card-header">
                        <h5><i class="fas fa-history me-2"></i>Action Log</h5>
                    </div>
                    <div class="card-body action-log-body">
                        <?php if (empty($recentActions)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-list fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No recent actions to display.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentActions as $action): ?>
                            <div class="action-item">
                                <small class="text-muted"><?php echo date('M j, H:i', strtotime($action['created_at'])); ?></small><br>
                                <strong><?php echo htmlspecialchars($action['admin_name']); ?></strong><br>
                                <span class="text-primary"><?php echo htmlspecialchars($action['action_type']); ?></span>
                                <?php echo htmlspecialchars($action['action_target']); ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Kitchen Details -->
    <div class="modal fade" id="kitchenModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cloud Kitchen Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="kitchenModalBody">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewKitchen(kitchenId) {
            console.log('Loading kitchen details for ID:', kitchenId); // Debug log
            
            // Show loading state in modal
            const modalBody = document.getElementById('kitchenModalBody');
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading kitchen details...</p>
                </div>
            `;
            
            // Load kitchen details in modal
            fetch('get_kitchen_details.php?id=' + kitchenId)
                .then(response => {
                    console.log('Kitchen details response:', response.status); // Debug log
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(data => {
                    console.log('Kitchen details loaded, content length:', data.length); // Debug log
                    modalBody.innerHTML = data;
                    
                    // Execute any scripts in the loaded content
                    const scripts = modalBody.querySelectorAll('script');
                    scripts.forEach(script => {
                        console.log('Executing script from modal content'); // Debug log
                        const newScript = document.createElement('script');
                        if (script.src) {
                            newScript.src = script.src;
                        } else {
                            newScript.textContent = script.textContent;
                        }
                        document.body.appendChild(newScript);
                        document.body.removeChild(newScript);
                    });
                    
                    // Show the modal
                    new bootstrap.Modal(document.getElementById('kitchenModal')).show();
                    
                    console.log('Modal shown, checking for initialization functions'); // Debug log
                    
                    // Try to initialize document tabs if the function exists
                    setTimeout(() => {
                        if (typeof window.initializeDocumentTabs === 'function') {
                            console.log('Calling initializeDocumentTabs'); // Debug log
                            window.initializeDocumentTabs();
                        } else {
                            console.log('initializeDocumentTabs function not found'); // Debug log
                        }
                    }, 200);
                })
                .catch(error => {
                    console.error('Error loading kitchen details:', error); // Debug log
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading kitchen details: ${error.message}
                        </div>
                    `;
                });
        }

        function approveKitchen(kitchenId) {
            if (confirm('Are you sure you want to approve this cloud kitchen?')) {
                fetch('update_kitchen_approval.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=approve&kitchen_id=' + kitchenId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        function rejectKitchen(kitchenId) {
            if (confirm('Are you sure you want to reject this cloud kitchen registration?')) {
                fetch('update_kitchen_approval.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=reject&kitchen_id=' + kitchenId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        // Auto-refresh notifications every 30 seconds
        setInterval(function() {
            fetch('get_notification_count.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.notification-badge');
                    if (data.count > 0) {
                        if (!badge) {
                            // Create badge if it doesn't exist
                            const bell = document.querySelector('.fa-bell').parentElement;
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.count;
                            bell.appendChild(newBadge);
                        } else {
                            badge.textContent = data.count;
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                });
        }, 30000);
    </script>
</body>
</html> 