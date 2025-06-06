<style>
      

    .sidebar .nav-link {
        color: #3d6f5d; /* dark warm brown */
    }

    .sidebar .nav-link:hover {
        background-color: #ffe8c4;
        color: #3d6f5d;
    }

    .sidebar-heading {
        color: #8b5e3c; /* warm muted brown */
    }

        
</style>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-shopping-cart me-2"></i>Orders Management System
            </span>
            <div>
                <button class="btn btn-outline-light d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="../admin_dashboard.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav id="sidebarMenu" class="sidebar collapse d-md-block">
        <div class="position-sticky pt-3">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>Management Pages</span>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="../../manage_orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Orders Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_kitchens.php">
                        <i class="fas fa-store me-2"></i>
                        Cloud Kitchens
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_delivery.php">
                        <i class="fas fa-truck me-2"></i>
                        Delivery Management
                    </a>
                </li>
            </ul>
            
            <hr>
            <div class="px-3">
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Quick Tip</h6>
                    <p class="small mb-0">Use filters to quickly find specific orders by status, date, or customer.</p>
                </div>

                <div class="text-center mt-4">
                    <br>
                    <br>
                    <br>
                    <br>
                   
                    
                    <img src="logo1.png" alt="Logo" style="max-width: 100px; opacity: 0.75;">
                </div>
            </div>
        </div>
</nav>
