<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: manage_categories.php');
    exit();
}
require_once 'db_connect.php';

// Get statistics for insights cards
$statsQuery = "SELECT 
               (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE status = 'active') as active_kitchens,
               (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE status = 'suspended') as suspended_kitchens,
               (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE status = 'blocked') as blocked_kitchens,
               (SELECT AVG(average_rating) FROM cloud_kitchen_owner WHERE average_rating > 0) as avg_rating,
               (SELECT SUM(orders_count) FROM cloud_kitchen_owner) as total_orders,
               (SELECT COUNT(*) FROM meals) as total_meals";

$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get top-performing kitchens
$topKitchensQuery = "SELECT cko.user_id, cko.business_name, cko.orders_count, cko.average_rating,
                     cko.status, u.mail, cat.c_name as speciality
                     FROM cloud_kitchen_owner cko
                     JOIN users u ON cko.user_id = u.user_id
                     JOIN category cat ON cko.speciality_id = cat.cat_id
                     ORDER BY cko.orders_count DESC
                     LIMIT 5";

$topKitchensStmt = $pdo->query($topKitchensQuery);
$topKitchens = $topKitchensStmt->fetchAll(PDO::FETCH_ASSOC);

// Get filter parameters
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : null;
$tagFilter = isset($_GET['tag']) ? (int)$_GET['tag'] : null;
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;

// Fetch filter options
$categories = $pdo->query("SELECT * FROM category")->fetchAll(PDO::FETCH_ASSOC);
$tags = $pdo->query("SELECT * FROM dietary_tags")->fetchAll(PDO::FETCH_ASSOC);

// Build main query with updated table names
$query = "
    SELECT c.*, u.u_name, u.phone, u.mail, 
           GROUP_CONCAT(DISTINCT dt.tag_name) as tags,
           GROUP_CONCAT(DISTINCT cat.c_name) as categories
    FROM cloud_kitchen_owner c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN cloud_kitchen_specialist_category csc ON c.user_id = csc.cloud_kitchen_id
    LEFT JOIN category cat ON csc.cat_id = cat.cat_id
    LEFT JOIN caterer_tags ct ON c.user_id = ct.user_id
    LEFT JOIN dietary_tags dt ON ct.tag_id = dt.tag_id
    WHERE 1=1
";

$params = [];

if ($categoryFilter) {
    $query .= " AND csc.cat_id = ?";
    $params[] = $categoryFilter;
}

if ($tagFilter) {
    $query .= " AND EXISTS (SELECT 1 FROM caterer_tags WHERE user_id = c.user_id AND tag_id = ?)";
    $params[] = $tagFilter;
}

if ($statusFilter) {
    $query .= " AND c.status = ?";
    $params[] = $statusFilter;
}

$query .= " GROUP BY c.user_id";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$kitchens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Cloud Kitchens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --background: #fff7e5;
            --primary: #e57e24;
            --secondary: #dab98b;
            --accent: #3d6f5d;
            --tertiary: #f5e0c2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --light: #ffffff;
            --dark: #1f2937;
            --shadow: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.15);
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.15s ease;
        }

        /* Enhanced Typography */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body { 
            background: linear-gradient(135deg, #fff7e5 0%, #fef3dc 50%, #f5e0c2 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #6a4125;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Enhanced Navbar */
        .navbar { 
            background: linear-gradient(135deg, #e57e24 0%, #d16919 100%);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(229, 126, 36, 0.3);
            z-index: 1050;
            backdrop-filter: blur(10px);
        }

        /* Enhanced Sidebar */
        .sidebar {
            position: fixed;
            top: 56px;
            left: 0;
            height: calc(100vh - 56px); 
            width: 250px;
            background: linear-gradient(180deg, #fef3dc 0%, #f5e0c2 100%);
            padding-top: 1rem;
            overflow-y: auto;
            z-index: 1000;
            border-right: 1px solid rgba(229, 126, 36, 0.2);
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: #3d6f5d;
            border-radius: 8px;
            margin: 4px 12px;
            padding: 12px 16px;
            transition: var(--transition);
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background: linear-gradient(135deg, #fff7e5 0%, #e57e24 100%);
            color: white;
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.3);
        }

        .sidebar-heading {
            color: #8b5e3c;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 1rem 0 0.5rem 1rem;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            margin-top: 56px;
            min-height: calc(100vh - 56px);
        }

        /* Enhanced Cards */
        .card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            overflow: hidden;
            background: white;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(var(--secondary) 0%, var(--primary) 100%);
            color: white;
            border-bottom: none;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Enhanced Table */
        .table-container {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            background: white;
        }

        .table {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background: linear-gradient(135deg, #f5e0c2 0%, #dab98b 100%);
            color: #6a4125;
            font-weight: 600;
            padding: 18px 15px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody tr {
            transition: var(--transition);
            border-bottom: 1px solid #f1f3f4;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #fff7e5 0%, rgba(245, 224, 194, 0.3) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            border: none;
        }

        /* Enhanced Status Badges */
        .badge {
            padding: 8px 16px !important;
            border-radius: 20px !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid transparent !important;
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .badge.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%) !important;
            color: white !important;
            border-color: #10b981 !important;
        }

        .badge.bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
            color: white !important;
            border-color: #f59e0b !important;
        }

        .badge.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%) !important;
            color: white !important;
            border-color: #ef4444 !important;
        }

        .badge:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }

        /* Enhanced Buttons */
        .btn {
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 18px !important;
            border: none !important;
            transition: var(--transition) !important;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25) !important;
        }

        .btn:active {
            transform: translateY(-1px) !important;
        }

        /* Button Variants */
        .btn-primary {
            background: linear-gradient(135deg, #3d6f5d 0%, #2c5547 100%) !important;
            color: white !important;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2c5547 0%, #1e3b32 100%) !important;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: white !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: white !important;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
            color: white !important;
        }

        .btn-outline-danger {
            background: white !important;
            border: 2px solid #ef4444 !important;
            color: #ef4444 !important;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2) !important;
        }

        .btn-outline-danger:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: white !important;
            transform: translateY(-3px) !important;
        }

        /* Action Buttons Container */
        .kitchen-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .kitchen-actions .btn {
            min-width: 90px;
            justify-content: center;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Enhanced Modals */
        .modal-content {
            border: none !important;
            border-radius: var(--border-radius-lg) !important;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3) !important;
            backdrop-filter: blur(10px);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%) !important;
            color: white !important;
            border-bottom: none !important;
            padding: 25px 30px !important;
        }

        .modal-title {
            font-weight: 600 !important;
            font-size: 1.3rem !important;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-body {
            padding: 30px !important;
            background: #f8f9fa !important;
        }

        .modal-footer {
            padding: 20px 30px !important;
            background: white !important;
            border-top: 1px solid #e9ecef !important;
        }

        /* Enhanced Form Elements */
        .form-control {
            border-radius: 10px !important;
            border: 2px solid #e5e7eb !important;
            padding: 12px 16px !important;
            font-size: 0.95rem !important;
            transition: var(--transition) !important;
            background: white !important;
        }

        .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(229, 126, 36, 0.25) !important;
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600 !important;
            color: var(--accent) !important;
            margin-bottom: 8px !important;
            font-size: 0.9rem;
        }

        .form-select {
            border-radius: 10px !important;
            border: 2px solid #e5e7eb !important;
            padding: 12px 16px !important;
            transition: var(--transition) !important;
        }

        .form-select:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(229, 126, 36, 0.25) !important;
        }

        /* Enhanced Alerts */
        .alert {
            border: none !important;
            border-radius: var(--border-radius) !important;
            padding: 20px !important;
            box-shadow: var(--shadow) !important;
            border-left: 4px solid transparent !important;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff7e6 0%, #fef3c7 100%) !important;
            color: #92400e !important;
            border-left-color: #f59e0b !important;
        }

        .alert-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
            color: #1e40af !important;
            border-left-color: #3b82f6 !important;
        }

        /* Stats Cards Enhancement */
        .stats-card {
            text-align: center;
            padding: 25px;
            transition: var(--transition);
            height: 100%;
            background: white;
            border-radius: var(--border-radius);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .stats-icon {
            font-size: 2.8rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .stats-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .rating {
            color: #fbbf24;
            font-size: 1.1rem;
        }

        /* Enhanced Filter Section */
        .active-filter {
            background: linear-gradient(135deg, var(--tertiary) 0%, var(--secondary) 100%) !important;
            border: 2px solid var(--primary) !important;
            color: var(--accent) !important;
            font-weight: 600 !important;
        }

        /* Toast Enhancements */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            min-width: 350px;
        }

        .toast {
            border: none !important;
            border-radius: var(--border-radius) !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2) !important;
            margin-bottom: 1rem;
            overflow: hidden;
            backdrop-filter: blur(10px);
            transition: var(--transition);
        }

        .toast.show {
            opacity: 1;
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%) !important;
            color: white !important;
        }

        .toast-danger {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%) !important;
            color: white !important;
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
            color: white !important;
        }

        .toast-body {
            padding: 1.2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        /* Custom Warning Dialog Styling */
        .delete-warning-modal .modal-header {
            background: linear-gradient(135deg, var(--tertiary) 0%, var(--secondary) 100%);
          border-bottom: 2px solid var(--primary);
            color: var(--accent);
        }

        .delete-warning-modal .modal-title {
            color: var(--accent);
          font-weight: 600;
        }

        .delete-warning-modal .modal-body {
           background-color: var(--background);
           color: var(--primary);
        }

        .delete-warning-modal .warning-icon {
           color: var(--primary);
           font-size: 1.5rem;
           margin-right: 0.8rem;
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
                transition: left 0.3s ease;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .sidebar.active {
                left: 0;
            }
            
            .kitchen-actions {
                flex-direction: column;
                gap: 6px;
            }
            
            .kitchen-actions .btn {
                width: 100%;
                min-width: auto;
            }
        }

        /* Loading States */
        .btn .spinner-border {
            width: 1rem !important;
            height: 1rem !important;
            margin-right: 8px;
        }

        /* Enhanced Table Row Hover */
        .table tbody tr:hover .kitchen-actions .btn {
            transform: scale(1.02);
        }

        /* Enhanced Business Name Styling */
        .table tbody td strong {
            color: var(--accent);
            font-size: 1.05rem;
            font-weight: 600;
        }

        .table tbody td .text-muted {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #6b7280;
        }

        /* Enhanced Icons */
        .fas, .far {
            transition: var(--transition-fast);
        }

        .btn:hover .fas,
        .btn:hover .far {
            transform: scale(1.1);
        }

        /* Micro Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card, .table-container {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4 sticky-top">
        <div class="container">

        

            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-store me-2"></i>Cloud Kitchens Management System
            </span>
            <div  >
                <a href="manage_categories.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-tags me-2"></i>Categories & Tags
                </a>
                <a href="admin_dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

         <div class="collapse navbar-collapse" id="navbarNav">
         <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
         </button>
        <div>
        </div>
    </nav>

    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="container">

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['active_kitchens'] ?? 0); ?></div>
                        <div class="stats-label">Active Kitchens</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['suspended_kitchens'] ?? 0); ?></div>
                        <div class="stats-label">Suspended Kitchens</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['blocked_kitchens'] ?? 0); ?></div>
                        <div class="stats-label">Blocked Kitchens</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
                        <div class="stats-label">Total Orders</div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Stats -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></div>
                        <div class="stats-label">Average Rating</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['total_meals'] ?? 0); ?></div>
                        <div class="stats-label">Total Meals</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <select class="form-select" id="categoryFilter">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?= $category['cat_id'] ?>" <?= ($categoryFilter == $category['cat_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['c_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select class="form-select" id="tagFilter">
                                <option value="">All Tags</option>
                                <?php foreach($tags as $tag): ?>
                                    <option value="<?= $tag['tag_id'] ?>" <?= ($tagFilter == $tag['tag_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tag['tag_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= ($statusFilter === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                                <option value="blocked" <?= ($statusFilter === 'blocked') ? 'selected' : '' ?>>Blocked</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-secondary w-100" onclick="clearFilters()">
                                <i class="fas fa-times me-2"></i>Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kitchen List -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Kitchen List
                            <?php if ($categoryFilter || $tagFilter || $statusFilter): ?>
                                <span class="badge bg-primary ms-2">
                                    <?= count($kitchens) ?> result(s)
                                </span>
                            <?php endif; ?>
                        </h5>
                        <a href="kitchen_list.php" class="btn" style="background-color: #e57e24; color: white;">
                            <i class="fas fa-th-list me-2"></i>View Detailed List
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kitchen Details</th>
                                    <th>Category</th>
                                    <th>Tags</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($kitchens as $kitchen): 
                                    $status = $kitchen['status'] ?? 'active';
                                    $statusClass = match($status) {
                                        'active' => 'success',
                                        'suspended' => 'warning',
                                        'blocked' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>
                                    <tr data-kitchen-id="<?= $kitchen['user_id'] ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($kitchen['business_name']) ?></strong><br>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($kitchen['u_name']) ?><br>
                                                <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($kitchen['mail']) ?><br>
                                                <i class="fas fa-phone me-1"></i><?= htmlspecialchars($kitchen['phone']) ?>
                                            </small>
                                        </td>
                                        <td><?= htmlspecialchars($kitchen['categories']) ?></td>
                                        <td>
                                            <?php if (!empty($kitchen['tags'])): ?>
                                                <?php foreach(explode(',', $kitchen['tags']) as $tag): ?>
                                                    <span class="badge bg-success me-1 mb-1"><?= htmlspecialchars($tag) ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No tags</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $statusClass ?> status-badge">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="kitchen-actions d-flex gap-2">
                                                <?php if ($kitchen['status'] === 'active'): ?>
                                                    <!-- Suspend Button (Active kitchens only) -->
                                                    <button class="btn btn-warning btn-sm suspend-btn" 
                                                            data-kitchen-id="<?= $kitchen['user_id'] ?>"
                                                            data-kitchen-name="<?= htmlspecialchars($kitchen['business_name']) ?>">
                                                        <i class="fas fa-pause"></i> Suspend
                                                </button>

                                                    <!-- Block Switch (Active kitchens) -->
                                                    <button class="btn btn-danger btn-sm block-btn" 
                                                            data-kitchen-id="<?= $kitchen['user_id'] ?>">
                                                        <i class="fas fa-ban"></i> Block
                                                 </button>
                                                    
                                                <?php elseif ($kitchen['status'] === 'suspended'): ?>
                                                    <!-- Unsuspend Button -->
                                                    <button class="btn btn-success btn-sm unsuspend-btn" 
                                                            data-kitchen-id="<?= $kitchen['user_id'] ?>">
                                                        <i class="fas fa-play"></i> Unsuspend
                                                </button>
                                                    
                                                    <!-- Block Switch (Suspended kitchens) -->
                                                    <button class="btn btn-danger btn-sm block-btn" 
                                                            data-kitchen-id="<?= $kitchen['user_id'] ?>">
                                                        <i class="fas fa-ban"></i> Block
                                                    </button>
                                                    
                                                    <!-- Show suspension info -->
                                                    <div class="suspension-info">
                                                        <small class="text-muted">
                                                            Suspended: <?= date('M j, Y', strtotime($kitchen['suspension_date'])) ?><br>
                                                            Reason: <?= htmlspecialchars($kitchen['suspension_reason']) ?><br>
                                                            
                                                        </small>
                                                    </div>
                                                    
                                                <?php elseif ($kitchen['status'] === 'blocked'): ?>
                                                    <!-- Unblock Button -->
                                                    <button class="btn btn-secondary btn-sm unblock-btn" 
                                                            data-kitchen-id="<?= $kitchen['user_id'] ?>">
                                                        <i class="fas fa-unlock"></i> Unblock
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <!-- Delete Button (Always available) -->
                                                <button class="btn btn-outline-danger btn-sm delete-btn" 
                                                        data-kitchen-id="<?= $kitchen['user_id'] ?>"
                                                        data-kitchen-name="<?= htmlspecialchars($kitchen['business_name']) ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($kitchens)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No kitchens found with the selected filters
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

         
            
            <!-- Top Performing Kitchens -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>Top Performing Kitchens
                        </h5>
                        <a href="kitchen_list.php" class="btn" style="background-color: #e57e24; color: white;">
                            <i class="fas fa-th-list me-2"></i>View Detailed List
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kitchen</th>
                                    <th>Speciality</th>
                                    <th>Orders</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topKitchens)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No kitchens found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($topKitchens as $kitchen): 
                                        $statusClass = match($kitchen['status']) {
                                            'active' => 'success',
                                            'suspended' => 'warning',
                                            'blocked' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($kitchen['business_name']); ?></td>
                                            <td><?php echo htmlspecialchars($kitchen['speciality']); ?></td>
                                            <td><?php echo number_format($kitchen['orders_count']); ?></td>
                                            <td>
                                                <div class="rating">
                                                    <?php
                                                    $rating = round($kitchen['average_rating']);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<i class="fas fa-star"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star"></i>';
                                                        }
                                                    }
                                                    ?>
                                                    <small class="text-muted">(<?php echo number_format($kitchen['average_rating'], 1); ?>)</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($kitchen['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="kitchen_details.php?id=<?php echo $kitchen['user_id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="kitchen_actions.js"></script>
    <script src="block_functions.js"></script>
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            
            const sidebar = document.querySelector('.sidebar');
            const navbarCollapse = document.getElementById('navbarNav');
    
            // Sync sidebar with navbar collapse
            if (navbarCollapse) {
                navbarCollapse.addEventListener('show.bs.collapse', () => {
                    sidebar.classList.add('active');
                });
    
                navbarCollapse.addEventListener('hide.bs.collapse', () => {
                    sidebar.classList.remove('active');
                });
            }
        });

        // Initialize all event listeners
        function initializeEventListeners() {
            // Suspend buttons
            document.querySelectorAll('.suspend-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const kitchenId = this.dataset.kitchenId;
                    const kitchenName = this.dataset.kitchenName;
                    showSuspensionModal(kitchenId, kitchenName);
                });
            });

            // Unsuspend buttons
            document.querySelectorAll('.unsuspend-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const kitchenId = this.dataset.kitchenId;
                    unsuspendKitchen(kitchenId);
                });
            });

            // Block buttons
            document.querySelectorAll('.block-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const kitchenId = this.dataset.kitchenId;
                    blockKitchen(kitchenId);
                });
            });

            // Unblock buttons
            document.querySelectorAll('.unblock-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const kitchenId = this.dataset.kitchenId;
                    unblockKitchen(kitchenId);
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const kitchenId = this.dataset.kitchenId;
                    const kitchenName = this.dataset.kitchenName;
                    deleteKitchen(kitchenId, kitchenName);
                });
            });
        }

        function applyFilters() {
            const category = document.getElementById('categoryFilter').value;
            const tag = document.getElementById('tagFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            const params = [];
            if (category) params.push(`category=${category}`);
            if (tag) params.push(`tag=${tag}`);
            if (status) params.push(`status=${status}`);
            
            window.location.href = 'manage_kitchens.php' + (params.length ? `?${params.join('&')}` : '');
        }

        function clearFilters() {
            window.location.href = 'manage_kitchens.php';
        }

        // Initialize filter event listeners
        document.querySelectorAll('#categoryFilter, #tagFilter, #statusFilter').forEach(filter => {
            filter.addEventListener('change', applyFilters);
        });

        // Suspension Modal
        function showSuspensionModal(kitchenId, kitchenName) {
            const modal = new bootstrap.Modal(document.getElementById('suspensionModal'));
            document.getElementById('suspensionKitchenName').textContent = kitchenName;
            document.getElementById('suspensionKitchenId').value = kitchenId;
            document.getElementById('suspensionReason').value = '';
            modal.show();
        }

        // Suspend Kitchen
        function confirmSuspension() {
            const kitchenId = document.getElementById('suspensionKitchenId').value;
            const reason = document.getElementById('suspensionReason').value.trim();
            const btn = document.getElementById('confirmSuspensionBtn');

            if (!reason) {
                showToast('Please provide a suspension reason', 'warning');
                return;
            }

            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Suspending...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'suspend');
            formData.append('kitchen_id', kitchenId);
            formData.append('reason', reason);

            fetch('manage_kitchen_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateKitchenStatus(kitchenId, 'suspended');
                    showToast(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('suspensionModal')).hide();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'danger');
            })
            .finally(() => {
                btn.innerHTML = '<i class="fas fa-pause me-2"></i>Suspend Kitchen';
                btn.disabled = false;
            });
        }

        // Unsuspend Kitchen
        function unsuspendKitchen(kitchenId) {
            if (!confirm('Are you sure you want to unsuspend this kitchen?')) return;

            const formData = new FormData();
            formData.append('action', 'unsuspend');
            formData.append('kitchen_id', kitchenId);

            fetch('manage_kitchen_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateKitchenStatus(kitchenId, 'active');
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'danger');
            });
        }

        // Block Kitchen
        function blockKitchen(kitchenId) {
            // Get kitchen name for the modal
            const row = document.querySelector(`tr[data-kitchen-id="${kitchenId}"]`);
            const kitchenName = row ? row.querySelector('strong').textContent : 'this kitchen';
            
            const modal = new bootstrap.Modal(document.getElementById('blockConfirmModal'));
            document.getElementById('blockKitchenName').textContent = kitchenName;
            document.getElementById('blockKitchenId').value = kitchenId;
            modal.show();
        }

        // Confirm Block
        function confirmBlock() {
            const kitchenId = document.getElementById('blockKitchenId').value;
            const btn = document.getElementById('confirmBlockBtn');

            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Blocking...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'block');
            formData.append('kitchen_id', kitchenId);

            fetch('manage_kitchen_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateKitchenStatus(kitchenId, 'blocked');
                    showToast(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('blockConfirmModal')).hide();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'danger');
            })
            .finally(() => {
                btn.innerHTML = '<i class="fas fa-ban me-2"></i>Block Kitchen';
                btn.disabled = false;
            });
        }

        // Unblock Kitchen
        function unblockKitchen(kitchenId) {
            if (!confirm('Are you sure you want to unblock this kitchen?')) return;

            const formData = new FormData();
            formData.append('action', 'unblock');
            formData.append('kitchen_id', kitchenId);

            fetch('manage_kitchen_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateKitchenStatus(kitchenId, 'active');
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'danger');
            });
        }
    
// Delete Kitchen Function
         function deleteKitchen(kitchenId, kitchenName) {
    const modal = new bootstrap.Modal(document.getElementById('deleteWarningModal'));
    const warningText = document.querySelector('#deleteWarningModal .modal-body p');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Clear previous event listeners
    confirmBtn.replaceWith(confirmBtn.cloneNode(true));
    const newConfirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Set warning message
    warningText.innerHTML = `Are you sure you want to permanently delete <strong>${kitchenName}</strong>?`;
    
    newConfirmBtn.onclick = function() {
        const btn = this;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Deleting...';
        btn.disabled = true;

                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('kitchen_id', kitchenId);

                fetch('manage_kitchen_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row with animation
                const row = document.querySelector(`tr[data-kitchen-id="${kitchenId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                showToast(data.message, 'success');
            } else {
                        showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
                    showToast('Network error occurred', 'danger');
        })
        .finally(() => {
            modal.hide();
            btn.innerHTML = '<i class="fas fa-trash me-2"></i>Permanently Delete';
            btn.disabled = false;
        });
    };
    
    modal.show();
}

        // Update Kitchen Status in UI
        function updateKitchenStatus(kitchenId, newStatus) {
            const row = document.querySelector(`tr[data-kitchen-id="${kitchenId}"]`);
            if (!row) return;

            // Update status badge
            const statusBadge = row.querySelector('.status-badge');
            const statusClass = newStatus === 'active' ? 'success' : 
                              newStatus === 'suspended' ? 'warning' : 'danger';
            statusBadge.className = `badge bg-${statusClass} status-badge`;
            statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

            // Update action buttons
            const actionsCell = row.querySelector('.kitchen-actions');
            let buttonsHtml = '';

            if (newStatus === 'active') {
                buttonsHtml = `
                    <button class="btn btn-warning btn-sm suspend-btn" 
                            data-kitchen-id="${kitchenId}"
                            data-kitchen-name="${row.querySelector('strong').textContent}">
                        <i class="fas fa-pause"></i> Suspend
                    </button>
                    <button class="btn btn-danger btn-sm block-btn" 
                            data-kitchen-id="${kitchenId}">
                        <i class="fas fa-ban"></i> Block
                    </button>
                `;
            } else if (newStatus === 'suspended') {
                buttonsHtml = `
                    <button class="btn btn-success btn-sm unsuspend-btn" 
                            data-kitchen-id="${kitchenId}">
                        <i class="fas fa-play"></i> Unsuspend
                    </button>
                    <button class="btn btn-danger btn-sm block-btn" 
                            data-kitchen-id="${kitchenId}">
                        <i class="fas fa-ban"></i> Block
                    </button>
                `;
            } else if (newStatus === 'blocked') {
                buttonsHtml = `
                    <button class="btn btn-secondary btn-sm unblock-btn" 
                            data-kitchen-id="${kitchenId}">
                        <i class="fas fa-unlock"></i> Unblock
                    </button>
                `;
            }

            // Always add delete button
            buttonsHtml += `
                <button class="btn btn-outline-danger btn-sm delete-btn" 
                        data-kitchen-id="${kitchenId}"
                        data-kitchen-name="${row.querySelector('strong').textContent}">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;

            actionsCell.innerHTML = buttonsHtml;
            
            // Reinitialize event listeners for the updated buttons
            initializeEventListeners();
}

// Toast Notification Function
         function showToast(message, type = 'info') {
    // Create container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Set icon based on type
    const icon = type === 'success' ? 'fa-check-circle' : 
                                 type === 'danger' ? 'fa-exclamation-circle' : 
                                 type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${icon} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after delay
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    }, 5000);
    
    // Add click handler for close button
    toast.querySelector('.btn-close').addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    });
}

        function editKitchen(kitchenId) {
            window.location.href = `edit_kitchen.php?id=${kitchenId}`;
        }

        function viewDetails(kitchenId) {
            window.location.href = `kitchen_details.php?id=${kitchenId}`;
        }
    </script>
</body>

<!-- Suspension Modal -->
<div class="modal fade" id="suspensionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--tertiary); border-bottom: 2px solid var(--primary);">
                <h5 class="modal-title" style="color: var(--primary); font-weight: 600;">
                    <i class="fas fa-pause me-2"></i>Suspend Kitchen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background-color: var(--background);">
                <p>You are about to suspend: <strong id="suspensionKitchenName"></strong></p>
                <input type="hidden" id="suspensionKitchenId">
                
                <div class="mb-3">
                    <label for="suspensionReason" class="form-label">
                        <i class="fas fa-comment me-2"></i>Suspension Reason <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="suspensionReason" rows="3" 
                              placeholder="Please provide a detailed reason for suspension..."
                              required></textarea>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Suspension Effects:</strong>
                    <ul class="mb-0 mt-2">
                        <li>This kitchen will be unable to receive <strong>new orders</strong></li>
                        <li>Live/ongoing orders will <strong>continue as normal</strong></li>
                        <li>The kitchen can be unsuspended at any time</li>
                        <li>Kitchen will be notified of the suspension</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmSuspensionBtn" onclick="confirmSuspension()">
                    <i class="fas fa-pause me-2"></i>Suspend Kitchen
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade delete-warning-modal" id="deleteWarningModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle warning-icon"></i>
                    Confirm Permanent Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This action will permanently delete:</p>
                <ul>
                    <li>The cloud kitchen's profile</li>
                    <li>All associated menu items</li>
                    <li>Order history and reviews</li>
                </ul>
                <div class="alert alert-warning mt-3 p-2">
                    <i class="fas fa-info-circle me-2"></i>
                    This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>Permanently Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Block Confirmation Modal -->
<div class="modal fade" id="blockConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--tertiary); border-bottom: 2px solid var(--primary);">
                <h5 class="modal-title" style="color: var(--primary); font-weight: 600;">
                    <i class="fas fa-ban me-2"></i>Block Kitchen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background-color: var(--background);">
                <p>You are about to block: <strong id="blockKitchenName"></strong></p>
                <input type="hidden" id="blockKitchenId">
                
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    This kitchen will be temporarily blocked and forbiddened from signing in.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmBlockBtn" onclick="confirmBlock()">
                    <i class="fas fa-ban me-2"></i>Block Kitchen
                </button>
            </div>
        </div>
    </div>
</div>

</html>