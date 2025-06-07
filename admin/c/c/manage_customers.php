<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: manage_categories.php');
    exit();
}
require_once 'db_connect.php';

// Get basic customer statistics (only 3 main ones)
$statsQuery = "SELECT 
               (SELECT COUNT(*) FROM customer WHERE status = 'active') as active_customers,
               (SELECT COUNT(*) FROM customer WHERE status = 'blocked') as blocked_customers,
               (SELECT COUNT(*) FROM customer WHERE is_subscribed = 1) as subscribed_customers";

$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;
$genderFilter = isset($_GET['gender']) ? $_GET['gender'] : null;
$subscriptionFilter = isset($_GET['subscription']) ? $_GET['subscription'] : null;
$ageFilter = isset($_GET['age_range']) ? $_GET['age_range'] : null;
$zoneFilter = isset($_GET['zone']) ? $_GET['zone'] : null;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;

// Get available zones for filter dropdown
$zonesQuery = "SELECT DISTINCT z.name FROM zones z INNER JOIN external_user eu ON z.zone_id = eu.zone_id WHERE z.name IS NOT NULL AND z.name != '' ORDER BY z.name";
$zonesStmt = $pdo->query($zonesQuery);
$availableZones = $zonesStmt->fetchAll(PDO::FETCH_COLUMN);

// Build main customer query
$query = "
    SELECT c.*, u.u_name, u.phone, u.mail, eu.address, z.name as zone_name,
           COUNT(o.order_id) as order_count,
           COALESCE(SUM(o.total_price), 0) as total_spent,
           MAX(o.order_date) as last_order_date,
           YEAR(CURDATE()) - YEAR(c.BOD) as age,
           (SELECT COUNT(*) FROM cart cart_check WHERE cart_check.customer_id = c.user_id) as cart_items_count,
           (SELECT COUNT(*) FROM customized_order co WHERE co.customer_id = c.user_id) as custom_orders_count
    FROM customer c
    JOIN users u ON c.user_id = u.user_id
    JOIN external_user eu ON c.user_id = eu.user_id
    LEFT JOIN zones z ON eu.zone_id = z.zone_id
    LEFT JOIN orders o ON c.user_id = o.customer_id
    WHERE 1=1
";

$params = [];

if ($statusFilter) {
    $query .= " AND c.status = ?";
    $params[] = $statusFilter;
}

if ($genderFilter) {
    $query .= " AND c.gender = ?";
    $params[] = $genderFilter;
}

if ($subscriptionFilter !== null) {
    $query .= " AND c.is_subscribed = ?";
    $params[] = $subscriptionFilter;
}

if ($ageFilter) {
    switch($ageFilter) {
        case '18-24':
            $query .= " AND (YEAR(CURDATE()) - YEAR(c.BOD)) BETWEEN 18 AND 24";
            break;
        case '25-34':
            $query .= " AND (YEAR(CURDATE()) - YEAR(c.BOD)) BETWEEN 25 AND 34";
            break;
        case '35-44':
            $query .= " AND (YEAR(CURDATE()) - YEAR(c.BOD)) BETWEEN 35 AND 44";
            break;
        case '45-54':
            $query .= " AND (YEAR(CURDATE()) - YEAR(c.BOD)) BETWEEN 45 AND 54";
            break;
        case '55+':
            $query .= " AND (YEAR(CURDATE()) - YEAR(c.BOD)) >= 55";
            break;
    }
}

if ($zoneFilter) {
    $query .= " AND z.name = ?";
    $params[] = $zoneFilter;
}

if ($searchQuery) {
    $query .= " AND (u.u_name LIKE ? OR u.mail LIKE ? OR u.phone LIKE ? OR eu.address LIKE ?)";
    $searchParam = "%{$searchQuery}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " GROUP BY c.user_id ORDER BY c.registration_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top customers for the top customers section
$topCustomersQuery = "
    SELECT 
        c.user_id,
        u.u_name,
        u.mail,
        c.last_login,
        COUNT(o.order_id) as total_orders,
        COALESCE(SUM(o.total_price), 0) as total_spent
    FROM customer c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN orders o ON c.user_id = o.customer_id
    WHERE c.status = 'active'
    GROUP BY c.user_id, u.u_name, u.mail, c.last_login
    HAVING total_orders > 0
    ORDER BY total_spent DESC, total_orders DESC
    LIMIT 10
";

$topCustomersStmt = $pdo->query($topCustomersQuery);
$topCustomers = $topCustomersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers</title>
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

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body { 
            background: linear-gradient(135deg, #fff7e5 0%, #fef3dc 50%, #f5e0c2 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #6a4125;
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar { 
            background: linear-gradient(135deg, #e57e24 0%, #d16919 100%);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(229, 126, 36, 0.3);
            z-index: 1050;
            backdrop-filter: blur(10px);
        }

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

        .main-content {
            margin-left: 250px;
            padding: 30px;
            margin-top: 56px;
            min-height: calc(100vh - 56px);
        }

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

        .badge.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%) !important;
            color: white !important;
            border-color: #ef4444 !important;
        }

        .badge.bg-info {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%) !important;
            color: white !important;
            border-color: #3b82f6 !important;
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
            z-index: 1;
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
            z-index: -1;
            pointer-events: none;
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
        .customer-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .customer-actions .btn {
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

        /* Search Input Styling */
        #searchInput {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 12px !important;
            transition: all 0.3s ease !important;
        }

        #searchInput:focus {
            background: white !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(61, 111, 93, 0.15) !important;
            transform: translateY(-2px);
        }

        .input-group-text {
            background: var(--primary) !important;
            border: 2px solid var(--primary) !important;
            border-radius: 12px 0 0 12px !important;
            color: white !important;
        }

        /* Enhanced Filters Card */
        .filters-card {
            border: none !important;
            border-radius: 15px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12) !important;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .filters-card .card-header {
            background: linear-gradient(135deg, #f5e0c2 0%, #dab98b 100%) !important;
            border: none !important;
            padding: 20px 25px !important;
            color: #6a4125;
        }

        .filter-header {
            display: flex;
            align-items: center;
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .filter-actions .btn {
            border-radius: 8px !important;
            font-weight: 500 !important;
            padding: 8px 16px !important;
            border: 2px solid rgba(255,255,255,0.3) !important;
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
            transition: all 0.3s ease !important;
        }

        .filter-actions .btn:hover {
            background: rgba(255,255,255,0.2) !important;
            border-color: rgba(255,255,255,0.5) !important;
            transform: translateY(-2px);
        }

        .filter-actions .btn-success {
            background: rgba(16, 185, 129, 0.2) !important;
            border-color: rgba(16, 185, 129, 0.5) !important;
        }

        .filter-actions .btn-success:hover {
            background: rgba(16, 185, 129, 0.3) !important;
        }

        /* Filter Sections */
        .filter-section {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 1.5rem;
        }

        .filter-label {
            display: block;
            font-weight: 600 !important;
            color: var(--accent) !important;
            margin-bottom: 10px !important;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group {
            position: relative;
        }

        .filter-select {
            border: 2px solid #e5e7eb !important;
            border-radius: 10px !important;
            padding: 12px 16px !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%) !important;
        }

        .filter-select:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(61, 111, 93, 0.15) !important;
            transform: translateY(-2px);
            background: white !important;
        }

        /* Search Container */
        .search-container {
            position: relative;
        }

        .search-input-group {
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1) !important;
        }

        .search-icon {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%) !important;
            border: none !important;
            color: white !important;
            font-size: 1.1rem;
            padding: 0 18px;
        }

        .search-input {
            border: none !important;
            padding: 15px 20px !important;
            font-size: 1rem !important;
            background: white !important;
            color: var(--accent) !important;
        }

        .search-input:focus {
            box-shadow: none !important;
            background: white !important;
        }

        /* Custom Warning Dialog Styling */
        .delete-warning-modal .modal-header {
            background: linear-gradient(135deg, var(--tertiary) 0%, var(--secondary) 100%) !important;
            border-bottom: 2px solid var(--primary) !important;
            color: var(--accent) !important;
        }

        .delete-warning-modal .modal-title {
            color: var(--accent) !important;
            font-weight: 600 !important;
        }

        .delete-warning-modal .modal-body {
            background-color: var(--background) !important;
            color: var(--primary) !important;
        }

        .delete-warning-modal .warning-icon {
            color: var(--primary) !important;
            font-size: 1.5rem !important;
            margin-right: 0.8rem !important;
        }

        /* Block Modal Styling */
        .block-modal .modal-header {
            background: linear-gradient(135deg, var(--tertiary) 0%, var(--secondary) 100%) !important;
            border-bottom: 2px solid var(--primary) !important;
            color: var(--accent) !important;
        }

        .block-modal .modal-title {
            color: var(--accent) !important;
            font-weight: 600 !important;
        }

        .block-modal .modal-body {
            background-color: var(--background) !important;
            color: var(--primary) !important;
        }

        /* Activate Modal Styling */
        .activate-modal .modal-header {
            background: linear-gradient(135d, #d4f6d4 0%, #a8e6a8 100%) !important;
            border-bottom: 2px solid #28a745 !important;
        }

        .activate-modal .modal-title {
            color: #155724 !important;
            font-weight: 600 !important;
        }

        .activate-modal .modal-body {
            background-color: #f8fff8 !important;
            color: #155724 !important;
        }

        .clear-search {
            border: none !important;
            background: #f8f9fa !important;
            color: #6c757d !important;
            border-radius: 0 12px 12px 0 !important;
            transition: all 0.3s ease !important;
        }

        .clear-search:hover {
            background: #e9ecef !important;
            color: #495057 !important;
        }

        /* Active Filters */
        .active-filters {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border-radius: 10px;
            padding: 15px;
            border: 2px solid #e2e8f0;
        }

        .active-filters .badge {
            font-size: 0.85rem !important;
            padding: 8px 12px !important;
            border-radius: 8px !important;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin: 2px;
            position: relative;
            transition: all 0.3s ease;
        }

        .active-filters .badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .active-filters .btn-close {
            font-size: 0.7rem !important;
            padding: 0 !important;
            margin: 0 !important;
            opacity: 0.8;
            transition: all 0.2s ease;
        }

        .active-filters .btn-close:hover {
            opacity: 1;
            transform: scale(1.2);
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .filter-actions {
                margin-top: 10px;
            }
            
            .filter-actions .btn {
                font-size: 0.85rem !important;
                padding: 6px 12px !important;
            }
            
            .filter-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .card-header .d-flex {
                flex-direction: column;
                gap: 15px;
            }
            
            .active-filters .d-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
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

        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
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
            
            .customer-actions {
                flex-direction: column;
                gap: 6px;
            }
            
            .customer-actions .btn {
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
        .table tbody tr:hover .customer-actions .btn {
            transform: scale(1.02);
        }

        /* Ensure buttons are clickable */
        .customer-actions .btn {
            position: relative;
            z-index: 2;
            pointer-events: auto;
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
                <i class="fas fa-users me-2"></i>Customer Management System
            </span>
            <div>
                <a href="customer_dashboard.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-tags me-2"></i>Customer Analytics Dashboard
                </a>
                <a href="admin_dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <div class="collapse navbar-collapse" id="navbarNav">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>

    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="container">
            <!-- Stats Cards -->
            <div class="row mb-4 justify-content-center">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['active_customers'] ?? 0); ?></div>
                        <div class="stats-label">Active Customers</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['blocked_customers'] ?? 0); ?></div>
                        <div class="stats-label">Blocked Customers</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                    <div class="card stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="stats-value"><?php echo number_format($stats['subscribed_customers'] ?? 0); ?></div>
                        <div class="stats-label">Subscribed</div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters Card -->
            <div class="card mb-4 filters-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="filter-header">
                            <i class="fas fa-filter me-2"></i>
                            <span class="filter-title">Search & Filter Customers</span>
                            <?php 
                            $activeFiltersCount = 0;
                            if ($statusFilter) $activeFiltersCount++;
                            if ($genderFilter) $activeFiltersCount++;
                            if ($subscriptionFilter !== null && $subscriptionFilter !== '') $activeFiltersCount++;
                            if ($ageFilter) $activeFiltersCount++;
                            if ($zoneFilter) $activeFiltersCount++;
                            if ($searchQuery) $activeFiltersCount++;
                            ?>
                            <?php if ($activeFiltersCount > 0): ?>
                                <span class="badge bg-primary ms-2"><?= $activeFiltersCount ?> active</span>
                            <?php endif; ?>
                        </div>
                        <div class="filter-actions">
                            <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()" title="Clear All Filters">
                                <i class="fas fa-undo me-1"></i>Reset
                            </button>
                            <button class="btn btn-success btn-sm ms-2" onclick="exportCustomers()" title="Export Filtered Results">
                                <i class="fas fa-file-export me-1"></i>Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Section -->
                    <div class="filter-section mb-4">
                        <label class="filter-label">
                            <i class="fas fa-search me-1"></i>Search Customers
                        </label>
                        <div class="search-container">
                            <div class="input-group search-input-group">
                                <span class="input-group-text search-icon">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control search-input" id="searchInput" 
                                       placeholder="Search by name, email, phone, or address..." 
                                       value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                                <?php if ($searchQuery): ?>
                                    <button class="btn btn-outline-secondary clear-search" type="button" onclick="clearSearch()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Sections -->
                    <div class="row">
                        <!-- Basic Filters -->
                        <div class="col-lg-6 mb-3">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-user-check me-1"></i>Account Status
                                </label>
                                <select class="form-select filter-select" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?= ($statusFilter === 'active') ? 'selected' : '' ?>>
                                        <i class="fas fa-check-circle"></i> Active Customers
                                    </option>
                                    <option value="blocked" <?= ($statusFilter === 'blocked') ? 'selected' : '' ?>>
                                        <i class="fas fa-ban"></i> Blocked Customers
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Demographics -->
                        <div class="col-lg-6 mb-3">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-venus-mars me-1"></i>Gender
                                </label>
                                <select class="form-select filter-select" id="genderFilter">
                                    <option value="">All Genders</option>
                                    <option value="Male" <?= ($genderFilter === 'Male') ? 'selected' : '' ?>>
                                        <i class="fas fa-mars"></i> Male
                                    </option>
                                    <option value="Female" <?= ($genderFilter === 'Female') ? 'selected' : '' ?>>
                                        <i class="fas fa-venus"></i> Female
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Subscription Status -->
                        <div class="col-lg-6 mb-3">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-crown me-1"></i>Subscription
                                </label>
                                <select class="form-select filter-select" id="subscriptionFilter">
                                    <option value="">All Customers</option>
                                    <option value="1" <?= ($subscriptionFilter === '1') ? 'selected' : '' ?>>
                                        <i class="fas fa-crown"></i> Subscribed
                                    </option>
                                    <option value="0" <?= ($subscriptionFilter === '0') ? 'selected' : '' ?>>
                                        <i class="fas fa-user"></i> Regular
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Age Range -->
                        <div class="col-lg-6 mb-3">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-birthday-cake me-1"></i>Age Range
                                </label>
                                <select class="form-select filter-select" id="ageFilter">
                                    <option value="">All Ages</option>
                                    <option value="18-24" <?= ($ageFilter === '18-24') ? 'selected' : '' ?>>18-24 years</option>
                                    <option value="25-34" <?= ($ageFilter === '25-34') ? 'selected' : '' ?>>25-34 years</option>
                                    <option value="35-44" <?= ($ageFilter === '35-44') ? 'selected' : '' ?>>35-44 years</option>
                                    <option value="45-54" <?= ($ageFilter === '45-54') ? 'selected' : '' ?>>45-54 years</option>
                                    <option value="55+" <?= ($ageFilter === '55+') ? 'selected' : '' ?>>55+ years</option>
                                </select>
                            </div>
                        </div>

                        <!-- Zone Filter -->
                        <div class="col-lg-6 mb-3">
                            <div class="filter-group">
                                <label class="filter-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Zone
                                </label>
                                <select class="form-select filter-select" id="zoneFilter">
                                    <option value="">All Zones</option>
                                    <?php foreach ($availableZones as $zone): ?>
                                        <option value="<?= htmlspecialchars($zone) ?>" <?= ($zoneFilter === $zone) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($zone) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    <?php if ($activeFiltersCount > 0): ?>
                        <div class="active-filters mt-3">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <small class="text-muted me-2">Active Filters:</small>
                                <?php if ($searchQuery): ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-search me-1"></i>"<?= htmlspecialchars($searchQuery) ?>"
                                        <button type="button" class="btn-close btn-close-white ms-1" onclick="clearSearchOnly()"></button>
                                    </span>
                                <?php endif; ?>
                                <?php if ($statusFilter): ?>
                                    <span class="badge bg-primary">
                                        Status: <?= ucfirst($statusFilter) ?>
                                        <button type="button" class="btn-close btn-close-white ms-1" onclick="clearFilter('status')"></button>
                                    </span>
                                <?php endif; ?>
                                <?php if ($genderFilter): ?>
                                    <span class="badge bg-success">
                                        Gender: <?= $genderFilter ?>
                                        <button type="button" class="btn-close btn-close-white ms-1" onclick="clearFilter('gender')"></button>
                                    </span>
                                <?php endif; ?>
                                <?php if ($subscriptionFilter !== null && $subscriptionFilter !== ''): ?>
                                    <span class="badge bg-warning">
                                        <?= $subscriptionFilter === '1' ? 'Subscribed' : 'Regular' ?>
                                        <button type="button" class="btn-close btn-close-white ms-1" onclick="clearFilter('subscription')"></button>
                                    </span>
                                <?php endif; ?>
                                <?php if ($ageFilter): ?>
                                    <span class="badge bg-secondary">
                                        Age: <?= $ageFilter ?> years
                                        <button type="button" class="btn-close btn-close-white ms-1" onclick="clearFilter('age_range')"></button>
                                    </span>
                                <?php endif; ?>
                                <?php if ($zoneFilter): ?>
                                    <span class="badge bg-info">
                                        Zone: <?= htmlspecialchars($zoneFilter) ?>
                                        <button type="button" class="btn-close btn-close-white ms-1" onclick="clearFilter('zone')"></button>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer List -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Customer List
                            <?php if ($statusFilter || $genderFilter || $subscriptionFilter || $ageFilter || $zoneFilter || $searchQuery): ?>
                                <span class="badge bg-primary ms-2">
                                    <?= count($customers) ?> result(s)
                                    <?php if ($searchQuery): ?>
                                        for "<?= htmlspecialchars($searchQuery) ?>"
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </h5>
                        <a href="customer_dashboard.php" class="btn" style="background-color: #e57e24; color: white;">
                            <i class="fas fa-chart-pie me-2"></i>Customer Analytics
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer Details</th>
                                    <th>Demographics</th>
                                    <th>Zone</th>
                                    <th>Activity</th>
                                    <th>Orders & Spending</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($customers as $customer): 
                                    $status = $customer['status'] ?? 'active';
                                    $statusClass = match($status) {
                                        'active' => 'success',
                                        'blocked' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>
                                    <tr data-customer-id="<?= $customer['user_id'] ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($customer['u_name']) ?></strong><br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($customer['mail']) ?><br>
                                                <i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone']) ?><br>
                                                <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars(substr($customer['address'], 0, 30)) ?>...
                                            </small>
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                <i class="fas fa-<?= $customer['gender'] == 'Male' ? 'mars' : 'venus' ?> me-1"></i>
                                                <?= htmlspecialchars($customer['gender']) ?>
                                            </div>
                                            <div class="mb-1">
                                                <i class="fas fa-birthday-cake me-1"></i>
                                                <?= $customer['age'] ?> years old
                                            </div>
                                            <div>
                                                <?php if ($customer['is_subscribed']): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-crown me-1"></i>Subscribed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Regular</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <?php if (!empty($customer['zone_name'])): ?>
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($customer['zone_name']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-question-circle me-1"></i>Unknown
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                <small class="text-muted">Registered:</small><br>
                                                <?= date('M j, Y', strtotime($customer['registration_date'])) ?>
                                            </div>
                                            <div class="mb-1">
                                                <small class="text-muted">Last Login:</small><br>
                                                <?= $customer['last_login'] ? date('M j, Y', strtotime($customer['last_login'])) : 'Never' ?>
                                            </div>
                                            <?php if ($customer['cart_items_count'] > 0): ?>
                                                <div>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-shopping-cart me-1"></i><?= $customer['cart_items_count'] ?> in cart
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="mb-1">
                                                <strong><?= $customer['order_count'] ?></strong> orders
                                            </div>
                                            <div class="mb-1">
                                                <strong>$<?= number_format($customer['total_spent'], 2) ?></strong> total
                                            </div>
                                            <?php if ($customer['last_order_date']): ?>
                                                <div>
                                                    <small class="text-muted">Last order:</small><br>
                                                    <?= date('M j, Y', strtotime($customer['last_order_date'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <small class="text-muted">No orders yet</small>
                                            <?php endif; ?>
                                            <?php if ($customer['custom_orders_count'] > 0): ?>
                                                <div class="mt-1">
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-paint-brush me-1"></i><?= $customer['custom_orders_count'] ?> custom
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $statusClass ?> status-badge">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="customer-actions d-flex gap-2">
                                                <?php if ($customer['status'] === 'active'): ?>
                                                    <button class="btn btn-danger btn-sm block-btn" 
                                                            data-customer-id="<?= $customer['user_id'] ?>"
                                                            data-customer-name="<?= htmlspecialchars($customer['u_name']) ?>">
                                                        <i class="fas fa-ban"></i> Block
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-success btn-sm activate-btn" 
                                                            data-customer-id="<?= $customer['user_id'] ?>">
                                                        <i class="fas fa-check"></i> Activate
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-primary btn-sm view-btn" 
                                                        data-customer-id="<?= $customer['user_id'] ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                
                                                <button class="btn btn-outline-danger btn-sm delete-btn" 
                                                        data-customer-id="<?= $customer['user_id'] ?>"
                                                        data-customer-name="<?= htmlspecialchars($customer['u_name']) ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No customers found with the selected filters
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                       <!-- Top Customers -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-trophy me-2"></i>Top Customers
                        </h5>
                        <a href="customer_dashboard.php" class="btn" style="background-color: #e57e24; color: white;">
                            <i class="fas fa-chart-pie me-2"></i>View Analytics
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($topCustomers)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No customer data found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($topCustomers as $customer): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($customer['u_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($customer['mail']); ?></small>
                                            </td>
                                            <td><?php echo number_format($customer['total_orders']); ?></td>
                                            <td>$<?php echo number_format($customer['total_spent'], 2); ?></td>
                                            <td>
                                                <?php if ($customer['last_login']): ?>
                                                    <?php echo date('M j, Y', strtotime($customer['last_login'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="customer_details.php?id=<?php echo $customer['user_id']; ?>" class="btn btn-sm btn-info">
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
    
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            
            // Initialize filter event listeners
            document.querySelectorAll('#statusFilter, #genderFilter, #subscriptionFilter, #ageFilter, #zoneFilter').forEach(filter => {
                if (filter) {
                    filter.addEventListener('change', applyFilters);
                }
            });
            
            // Search functionality with debounce
            let searchTimeout;
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(applyFilters, 300); // 300ms delay
                });
            }
        });

        // Initialize all event listeners
        function initializeEventListeners() {
            // Block buttons
            document.querySelectorAll('.block-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerId = this.dataset.customerId;
                    const customerName = this.dataset.customerName;
                    blockCustomer(customerId, customerName);
                });
            });

            // Activate buttons
            document.querySelectorAll('.activate-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerId = this.dataset.customerId;
                    activateCustomer(customerId);
                });
            });

            // View buttons
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerId = this.dataset.customerId;
                    viewCustomerDetails(customerId);
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const customerId = this.dataset.customerId;
                    const customerName = this.dataset.customerName;
                    deleteCustomer(customerId, customerName);
                });
            });
        }

        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const gender = document.getElementById('genderFilter').value;
            const subscription = document.getElementById('subscriptionFilter').value;
            const ageRange = document.getElementById('ageFilter').value;
            const zone = document.getElementById('zoneFilter').value;
            const search = document.getElementById('searchInput').value.trim();
            
            const params = [];
            if (status) params.push(`status=${status}`);
            if (gender) params.push(`gender=${gender}`);
            if (subscription) params.push(`subscription=${subscription}`);
            if (ageRange) params.push(`age_range=${ageRange}`);
            if (zone) params.push(`zone=${encodeURIComponent(zone)}`);
            if (search) params.push(`search=${encodeURIComponent(search)}`);
            
            window.location.href = 'manage_customers.php' + (params.length ? `?${params.join('&')}` : '');
        }

        function clearFilters() {
            window.location.href = 'manage_customers.php';
        }

        // Clear individual filters
        function clearFilter(filterType) {
            const params = new URLSearchParams(window.location.search);
            params.delete(filterType);
            
            const newUrl = 'manage_customers.php' + (params.toString() ? `?${params.toString()}` : '');
            window.location.href = newUrl;
        }

        // Clear search only
        function clearSearchOnly() {
            const params = new URLSearchParams(window.location.search);
            params.delete('search');
            
            const newUrl = 'manage_customers.php' + (params.toString() ? `?${params.toString()}` : '');
            window.location.href = newUrl;
        }

        // Clear search from input
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            applyFilters();
        }



        // Block Customer
        function blockCustomer(customerId, customerName) {
            // Set modal data
            document.getElementById('blockCustomerName').textContent = customerName;
            document.getElementById('blockCustomerId').value = customerId;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('blockConfirmModal'));
            modal.show();
        }

        // Confirm Block Function
        function confirmBlock() {
            const customerId = document.getElementById('blockCustomerId').value;
            const customerName = document.getElementById('blockCustomerName').textContent;

            const formData = new FormData();
            formData.append('action', 'block');
            formData.append('customer_id', customerId);

            fetch('manage_customer_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCustomerStatus(customerId, 'blocked');
                    showToast(data.message, 'success');
                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('blockConfirmModal')).hide();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'danger');
            });
        }

        // Activate Customer
        function activateCustomer(customerId) {
            // Set modal data
            document.getElementById('activateCustomerId').value = customerId;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('activateConfirmModal'));
            modal.show();
        }

        // Confirm Activate Function
        function confirmActivate() {
            const customerId = document.getElementById('activateCustomerId').value;

            const formData = new FormData();
            formData.append('action', 'activate');
            formData.append('customer_id', customerId);

            fetch('manage_customer_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCustomerStatus(customerId, 'active');
                    showToast(data.message, 'success');
                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('activateConfirmModal')).hide();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'danger');
            });
        }

        // Delete Customer
        function deleteCustomer(customerId, customerName) {
            // Set modal data
            document.getElementById('deleteCustomerId').value = customerId;
            const warningText = document.querySelector('#deleteWarningModal .modal-body p');
            warningText.textContent = `This action will permanently delete: ${customerName}`;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('deleteWarningModal'));
            modal.show();
        }

        // Confirm Delete Function
        function confirmDelete() {
            const customerId = document.getElementById('deleteCustomerId').value;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('customer_id', customerId);

            fetch('manage_customer_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row with animation
                    const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s ease';
                        row.style.opacity = '0';
                        setTimeout(() => row.remove(), 300);
                    }
                    showToast(data.message, 'success');
                    // Hide modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteWarningModal')).hide();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error occurred', 'danger');
            });
        }

        // Update Customer Status in UI
        function updateCustomerStatus(customerId, newStatus) {
            const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
            if (!row) return;

            // Update status badge
            const statusBadge = row.querySelector('.status-badge');
            const statusClass = newStatus === 'active' ? 'success' : 'danger';
            statusBadge.className = `badge bg-${statusClass} status-badge`;
            statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

            // Update action buttons
            const actionsCell = row.querySelector('.customer-actions');
            let buttonsHtml = '';

            if (newStatus === 'active') {
                buttonsHtml = `
                    <button class="btn btn-danger btn-sm block-btn" 
                            data-customer-id="${customerId}"
                            data-customer-name="${row.querySelector('strong').textContent}">
                        <i class="fas fa-ban"></i> Block
                    </button>
                `;
            } else {
                buttonsHtml = `
                    <button class="btn btn-success btn-sm activate-btn" 
                            data-customer-id="${customerId}">
                        <i class="fas fa-check"></i> Activate
                    </button>
                `;
            }

            // Always add view and delete buttons
            buttonsHtml += `
                <button class="btn btn-primary btn-sm view-btn" 
                        data-customer-id="${customerId}">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="btn btn-outline-danger btn-sm delete-btn" 
                        data-customer-id="${customerId}"
                        data-customer-name="${row.querySelector('strong').textContent}">
                    <i class="fas fa-trash"></i> Delete
                </button>
            `;

            actionsCell.innerHTML = buttonsHtml;
            
            // Reinitialize event listeners for the updated buttons
            initializeEventListeners();
        }

        // View Customer Details
        function viewCustomerDetails(customerId) {
            window.location.href = `customer_details.php?id=${customerId}`;
        }

        // Export Customers
        function exportCustomers() {
            window.location.href = 'export_customers.php';
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
    </script>
</body>

<!-- Block Confirmation Modal -->
<div class="modal fade block-modal" id="blockConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-ban me-2"></i>Block Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to block: <strong id="blockCustomerName"></strong></p>
                <input type="hidden" id="blockCustomerId">
                
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Block Effects:</strong>
                    <ul class="mb-0 mt-2">
                        <li>This customer will be unable to <strong>place orders</strong></li>
                        <li>Existing orders will <strong>continue as normal</strong></li>
                        <li>The customer can be unblocked at any time</li>
                        <li>Customer will be notified of the block</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmBlock()">
                    <i class="fas fa-ban me-2"></i>Block Customer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Activate Confirmation Modal -->
<div class="modal fade activate-modal" id="activateConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check me-2"></i>Activate Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to activate this customer.</p>
                <input type="hidden" id="activateCustomerId">
                
                <div class="alert alert-success">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Activation Effects:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Customer will be able to <strong>place orders</strong></li>
                        <li>Full access to platform features</li>
                        <li>Customer will be notified of activation</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="confirmActivate()">
                    <i class="fas fa-check me-2"></i>Activate Customer
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
                <p>This action will permanently delete the customer and:</p>
                <ul>
                    <li>All customer profile data</li>
                    <li>Order history and reviews</li>
                    <li>Account preferences</li>
                    <li>Subscription details</li>
                </ul>
                <input type="hidden" id="deleteCustomerId">
                <div class="alert alert-danger mt-3 p-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>This action cannot be undone!</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash me-2"></i>Permanently Delete
                </button>
            </div>
        </div>
    </div>
</div>

</html> 