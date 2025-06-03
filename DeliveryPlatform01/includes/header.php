<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Today's Meal</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" integrity="sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=" crossorigin="" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="sidebar bg-dark text-white p-3">
        <div class="d-flex flex-column h-100">
            <!-- App Logo and Title -->
            <a href="dashboard.php" class="d-flex align-items-center mb-3 text-white text-decoration-none border-bottom pb-3">
                <span class="fs-4 fw-semibold">Today's Meal</span>
            </a>
            
            <!-- User Info -->
            <?php $user = get_logged_in_user(); ?>
            <div class="my-3 d-flex align-items-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <span class="fs-6"><?php echo htmlspecialchars($user['name']); ?></span>
                    <div class="small text-muted">Delivery Personnel</div>
                </div>
            </div>
            
            <!-- Navigation -->
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="deliveries.php" class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'deliveries.php' ? 'active' : ''; ?>">
                        <i class="fas fa-motorcycle me-2"></i>
                        Active Deliveries
                    </a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'history.php' ? 'active' : ''; ?>">
                        <i class="fas fa-history me-2"></i>
                        Delivery History
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle me-2"></i>
                        My Profile
                    </a>
                </li>
            </ul>
            
            <!-- Logout button -->
            <div class="border-top pt-3 mt-auto">
                <a href="logout.php" class="d-flex align-items-center text-white text-decoration-none">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    <strong>Logout</strong>
                </a>
            </div>
        </div>
    </div>
    
    <div class="b-example-divider"></div>
    
    <div class="main-content p-4">
        <!-- Flash Message Display -->
        <?php 
        $flash = get_flash_message();
        if ($flash): 
            $alert_class = 'alert-info';
            if ($flash['type'] === 'success') $alert_class = 'alert-success';
            if ($flash['type'] === 'error') $alert_class = 'alert-danger';
            if ($flash['type'] === 'warning') $alert_class = 'alert-warning';
        ?>
        <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>