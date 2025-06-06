<?php


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Enhanced Category Styles -->
    <link rel="stylesheet" href="enhanced-kitchen-styles.css">

    <style>
        /* Sidebar positioning - keep as original */
        .sidebar {
            position: fixed;
            top: 56px; /* Navbar height */
            left: 0;
            bottom: 0;
            width: 250px;
            overflow-y: auto;
            z-index: 1000;
            background: linear-gradient(135deg, #e57e24 0%, #ff9948 100%);
        }

        .main-content {
            margin-left: 250px;
            margin-top: 56px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                left: 0;
            }
        }

        /* Category-specific enhancements */
        .category-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 12px;
            margin-bottom: 1rem;
            padding: 1.2rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(229, 126, 36, 0.08);
            position: relative;
            overflow: hidden;
        }

        .category-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, #e57e24, #ff9948);
            transition: width 0.3s ease;
        }

        .category-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(229, 126, 36, 0.15);
            border-color: rgba(229, 126, 36, 0.3);
        }

        .category-item:hover::before {
            width: 100%;
            opacity: 0.05;
        }

        .category-header {
            position: relative;
            z-index: 2;
        }

        .sub-category {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid rgba(229, 126, 36, 0.08);
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            padding: 0.8rem;
        }

        .sub-category::before {
            background: linear-gradient(45deg, #ffa500, #ffb347);
        }

        .tags-container .tag-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 10px;
            padding: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 6px rgba(229, 126, 36, 0.08);
            position: relative;
            overflow: hidden;
        }

        .tags-container .tag-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(45deg, #17a2b8, #20c997);
            transition: width 0.3s ease;
        }

        .tags-container .tag-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.15);
            border-color: rgba(23, 162, 184, 0.3);
        }

        .tags-container .tag-item:hover::before {
            width: 100%;
            opacity: 0.05;
        }

        .form-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.1);
            overflow: hidden;
        }

        .form-container .card-body {
            padding: 2rem;
        }

        .modal-preview {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(229, 126, 36, 0.2);
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            padding: 2rem;
            z-index: 1050;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal-preview.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .kitchen-preview-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .kitchen-preview-item:hover {
            transform: translateX(5px);
            border-color: rgba(229, 126, 36, 0.3);
            box-shadow: 0 4px 8px rgba(229, 126, 36, 0.1);
        }

        .section-header {
            background: linear-gradient(135deg, #e57e24 0%, #ff9948 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 12px 12px 0 0;
            margin: -1rem -1rem 1.5rem -1rem;
            position: relative;
            overflow: hidden;
        }

        .section-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            opacity: 0.3;
        }

        .section-header h2 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stats-overview {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid rgba(229, 126, 36, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.08);
        }

        .stats-item {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            transition: all 0.3s ease;
        }

        .stats-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(229, 126, 36, 0.1);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #e57e24;
            display: block;
        }

        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .fade-in {
            animation: fadeInUp 0.6s ease-out;
        }

        .page-header {
            background: linear-gradient(135deg, #e57e24 0%, #ff9948 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(229, 126, 36, 0.3);
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .page-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .header-actions .btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .header-actions .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .modal-preview {
                width: 95%;
                padding: 1.5rem;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body class="bg-light">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>Category Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="fas fa-chart-line me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_kitchens.php">
                            <i class="fas fa-store me-1"></i>Kitchens
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="main-content">
        <!-- Enhanced Page Header -->
        <div class="page-header fade-in">
            <h1><i class="fas fa-tags me-3"></i>Category Management</h1>
            <p>Organize and manage your food categories, subcategories, and dietary tags</p>
            <div class="header-actions">
                <button class="btn" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
                <button class="btn" onclick="location.reload()">
                    <i class="fas fa-refresh me-2"></i>Refresh Data
                </button>
            </div>
        </div>

        <div class="container-fluid py-4">
            <?php 
            // Display any flash messages
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($_SESSION['error']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($_SESSION['success']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['success']);
            }
            
            include 'categories.php'; 
            ?>
        </div>

        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Custom Scripts -->
        <script src="scripts.js"></script>
    </div>
</body>
</html>
