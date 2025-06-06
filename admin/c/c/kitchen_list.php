<?php
// Include database connection
require_once 'connection.php';

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// Build base query
$queryParams = [];
$query = "SELECT cko.user_id, cko.business_name, eu.address, cko.average_rating, 
          cko.orders_count, cko.years_of_experience, cko.status, 
          c.c_name AS speciality_name,
          u.mail, u.phone, z.name as zone_name
          FROM cloud_kitchen_owner cko
          JOIN users u ON cko.user_id = u.user_id
          JOIN external_user eu ON cko.user_id = eu.user_id
          LEFT JOIN zones z ON eu.zone_id = z.zone_id
          JOIN category c ON cko.speciality_id = c.cat_id
          WHERE 1=1";

// Add filters
if (!empty($search)) {
    $query .= " AND (cko.business_name LIKE ? OR eu.address LIKE ?)";
    $searchParam = "%$search%";
    $queryParams[] = $searchParam;
    $queryParams[] = $searchParam;
}

if ($categoryFilter > 0) {
    $query .= " AND cko.speciality_id = ?";
    $queryParams[] = $categoryFilter;
}

// Add sorting
switch ($sortBy) {
    case 'rating':
        $query .= " ORDER BY cko.average_rating DESC";
        break;
    case 'orders':
        $query .= " ORDER BY cko.orders_count DESC";
        break;
    case 'name':
    default:
        $query .= " ORDER BY cko.business_name";
}

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($queryParams)) {
    $types = str_repeat('s', count($queryParams));
    $stmt->bind_param($types, ...$queryParams);
}
$stmt->execute();
$result = $stmt->get_result();
$kitchens = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $kitchens[] = $row;
    }
}
$stmt->close();

// Get count per speciality
$specialityQuery = "SELECT c.cat_id, c.c_name, COUNT(cko.user_id) as kitchen_count
                    FROM category c
                    LEFT JOIN cloud_kitchen_owner cko ON c.cat_id = cko.speciality_id
                    GROUP BY c.cat_id
                    ORDER BY kitchen_count DESC";

$specialityResult = $conn->query($specialityQuery);
$specialities = [];
if ($specialityResult->num_rows > 0) {
    while ($row = $specialityResult->fetch_assoc()) {
        $specialities[] = $row;
    }
}

// Get top-rated kitchens
$topRatedQuery = "SELECT cko.user_id, cko.business_name, cko.average_rating
                  FROM cloud_kitchen_owner cko
                  WHERE cko.average_rating > 0
                  ORDER BY cko.average_rating DESC
                  LIMIT 5";

$topRatedResult = $conn->query($topRatedQuery);
$topRated = [];
if ($topRatedResult->num_rows > 0) {
    while ($row = $topRatedResult->fetch_assoc()) {
        $topRated[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Kitchens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="styles1.css" rel="stylesheet">
    <style>
        .kitchen-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
            height: 100%;
            border-radius: 10px;
            overflow: hidden;
        }
        .kitchen-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .rating {
            color: #ffc107;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .section-header {
            border-bottom: 2px solid #e57e24;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #343a40;
        }
        .header-section {
            background-color: var(--background);
            color: black;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .navbar {
            background-color: #e57e24;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="dashboard.php">
                <i class="fas fa-utensils me-2"></i>Cloud Kitchen Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="admin_cloud_kitchen_dashboard.php">
                            <i class="fas fa-chart-line me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="kitchen_list.php">
                            <i class="fas fa-store me-1"></i>All Kitchens
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Cloud Kitchens Directory</h1>
                    <p>Browse and discover all available cloud kitchens in our network</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="dashboard.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="card dashboard-card mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Categories</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($specialities as $speciality): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-utensils me-2"></i><?php echo htmlspecialchars($speciality['c_name']); ?></span>
                                    <span class="badge bg-primary rounded-pill"><?php echo $speciality['kitchen_count']; ?></span>
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($specialities)): ?>
                                <li class="list-group-item text-center text-muted">No categories found</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <div class="card dashboard-card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top Rated Kitchens</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($topRated as $kitchen): ?>
                                <li class="list-group-item">
                                    <a href="kitchen_details.php?id=<?php echo $kitchen['user_id']; ?>" class="text-decoration-none">
                                        <i class="fas fa-store me-2"></i><?php echo htmlspecialchars($kitchen['business_name']); ?>
                                    </a>
                                    <div class="rating mt-1">
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
                                </li>
                            <?php endforeach; ?>
                            <?php if (empty($topRated)): ?>
                                <li class="list-group-item text-center text-muted">No rated kitchens found</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card dashboard-card mb-4">
                    <div class="card-body">
                        <h4 class="section-header mb-4"><i class="fas fa-filter me-2"></i>Filter Kitchens</h4>
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Kitchen name..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($specialities as $speciality): ?>
                                        <option value="<?php echo $speciality['cat_id']; ?>" <?php echo ($categoryFilter == $speciality['cat_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($speciality['c_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sort" class="form-label">Sort By</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="name" <?php echo ($sortBy == 'name') ? 'selected' : ''; ?>>Name</option>
                                    <option value="rating" <?php echo ($sortBy == 'rating') ? 'selected' : ''; ?>>Rating</option>
                                    <option value="orders" <?php echo ($sortBy == 'orders') ? 'selected' : ''; ?>>Orders</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn" style="background-color: #e57e24; color: white;">
                                        <i class="fas fa-filter me-1"></i>Apply
                                    </button>
                                    <a href="kitchen_list.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <h4 class="section-header"><i class="fas fa-store me-2"></i>All Kitchens
                <?php if (!empty($search) || $categoryFilter > 0): ?>
                    <span class="badge" style="background-color: #e57e24;">
                        <i class="fas fa-filter me-1"></i>Filtered
                    </span>
                <?php endif; ?>
                <small class="text-muted ms-2">(<?php echo count($kitchens); ?> found)</small>
                </h4>
                <div class="row">
                    <?php if (empty($kitchens)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No cloud kitchens found.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($kitchens as $kitchen): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card kitchen-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($kitchen['business_name']); ?></h5>
                                        <div class="rating mb-2">
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
                                        <p class="card-text">
                                            <span class="badge bg-secondary"><i class="fas fa-utensils me-1"></i><?php echo htmlspecialchars($kitchen['speciality_name']); ?></span>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($kitchen['address']); ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-clock me-2"></i><?php echo htmlspecialchars($kitchen['years_of_experience']); ?>
                                        </p>
                                        <p class="card-text">
                                            <span class="badge <?php echo $kitchen['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                <i class="fas <?php echo $kitchen['status'] == 'active' ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                                <?php echo ucfirst(htmlspecialchars($kitchen['status'])); ?>
                                            </span>
                                            <span class="ms-2">
                                                <i class="fas fa-shopping-bag me-1"></i><?php echo number_format($kitchen['orders_count']); ?> orders
                                            </span>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="kitchen_details.php?id=<?php echo $kitchen['user_id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-info-circle me-2"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-utensils me-2"></i>Cloud Kitchen Management System</h5>
                    <p class="small">A comprehensive platform for managing cloud kitchens and their operations.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="dashboard.php" class="text-white text-decoration-none"><i class="fas fa-home me-1"></i> Dashboard</a></li>
                        <li><a href="kitchen_list.php" class="text-white text-decoration-none"><i class="fas fa-store me-1"></i> All Kitchens</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-1"></i> support@cloudkitchen.com</li>
                        <li><i class="fas fa-phone me-1"></i> +1 (123) 456-7890</li>
                    </ul>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <p class="small mb-0">&copy; <?php echo date('Y'); ?> Cloud Kitchen Management System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 