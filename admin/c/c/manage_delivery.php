<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

require_once 'config.php';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $delivery_id = (int)$_POST['delivery_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE delivery_man SET is_approved = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $delivery_id);
        
        if ($stmt->execute()) {
            // Get delivery person name for logging
            $name_query = $conn->prepare("SELECT u.u_name FROM users u WHERE u.user_id = ?");
            $name_query->bind_param("i", $delivery_id);
            $name_query->execute();
            $name_result = $name_query->get_result();
            $delivery_name = $name_result->fetch_assoc()['u_name'] ?? "Delivery Agent #$delivery_id";
            
            // Log admin action
            $log_stmt = $conn->prepare("INSERT INTO admin_actions (admin_id, action_type, action_target, created_at) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $_SESSION['admin_id'], $action_type, $action_target);
            $action_type = "Approved delivery agent";
            $action_target = $delivery_name;
            $log_stmt->execute();
            
            $_SESSION['success'] = "Delivery agent approved successfully";
        } else {
            $_SESSION['error'] = "Failed to approve delivery agent";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("DELETE FROM delivery_man WHERE user_id = ?");
        $stmt->bind_param("i", $delivery_id);
        
        if ($stmt->execute()) {
            // Get delivery person name for logging
            $name_query = $conn->prepare("SELECT u.u_name FROM users u WHERE u.user_id = ?");
            $name_query->bind_param("i", $delivery_id);
            $name_query->execute();
            $name_result = $name_query->get_result();
            $delivery_name = $name_result->fetch_assoc()['u_name'] ?? "Delivery Agent #$delivery_id";
            
            // Log admin action
            $log_stmt = $conn->prepare("INSERT INTO admin_actions (admin_id, action_type, action_target, created_at) VALUES (?, ?, ?, NOW())");
            $log_stmt->bind_param("iss", $_SESSION['admin_id'], $action_type, $action_target);
            $action_type = "Rejected delivery agent";
            $action_target = $delivery_name;
            $log_stmt->execute();
            
            // Also delete the user record
            $user_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $user_stmt->bind_param("i", $delivery_id);
            $user_stmt->execute();
            
            $_SESSION['success'] = "Delivery agent rejected and removed";
        } else {
            $_SESSION['error'] = "Failed to reject delivery agent";
        }
    }
    
    header('Location: manage_delivery.php');
    exit();
}

// Get delivery agents pending approval
$pending_query = "
    SELECT d.*, u.u_name, u.mail, u.phone, u.created_at
    FROM delivery_man d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.is_approved = 0
    ORDER BY u.created_at DESC
";
$pending_result = $conn->query($pending_query);

// Get approved delivery agents
$approved_query = "
    SELECT d.*, u.u_name, u.mail, u.phone, u.created_at
    FROM delivery_man d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.is_approved = 1
    ORDER BY u.created_at DESC
    LIMIT 20
";
$approved_result = $conn->query($approved_query);

// Get statistics
$total_delivery = $conn->query("SELECT COUNT(*) as count FROM delivery_man")->fetch_assoc()['count'];
$approved_delivery = $conn->query("SELECT COUNT(*) as count FROM delivery_man WHERE is_approved = 1")->fetch_assoc()['count'];
$pending_delivery = $conn->query("SELECT COUNT(*) as count FROM delivery_man WHERE is_approved = 0")->fetch_assoc()['count'];
$online_delivery = $conn->query("SELECT COUNT(*) as count FROM delivery_man WHERE status = 'online'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff7e5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: #e57e24;
            box-shadow: 0 4px 12px rgba(106, 65, 37, 0.15);
        }

        .stats-card {
            background: linear-gradient(135deg, #3d6f5d, #2c5547);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(61, 111, 93, 0.15);
            color: white;
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .pending-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .license-preview {
            max-width: 100px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .btn-approve {
            background: #28a745;
            border: none;
            color: white;
        }

        .btn-reject {
            background: #dc3545;
            border: none;
            color: white;
        }

        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <a href="admin_dashboard.php" class="navbar-brand">
                <i class="fas fa-motorcycle me-2"></i>
                Delivery Management
            </a>
            <div>
                <a href="admin_dashboard.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center p-4">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h3><?php echo $total_delivery; ?></h3>
                    <p class="mb-0">Total Delivery Agents</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center p-4">
                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                    <h3><?php echo $approved_delivery; ?></h3>
                    <p class="mb-0">Approved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center p-4">
                    <i class="fas fa-clock fa-2x mb-3"></i>
                    <h3><?php echo $pending_delivery; ?></h3>
                    <p class="mb-0">Pending Approval</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center p-4">
                    <i class="fas fa-circle fa-2x mb-3" style="color: #4CAF50;"></i>
                    <h3><?php echo $online_delivery; ?></h3>
                    <p class="mb-0">Currently Online</p>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3">
                    <i class="fas fa-hourglass-half me-2"></i>
                    Pending Approvals (<?php echo $pending_delivery; ?>)
                </h4>
                
                <?php if ($pending_result->num_rows > 0): ?>
                    <?php while ($delivery = $pending_result->fetch_assoc()): ?>
                        <div class="pending-card">
                            <div class="row">
                                <div class="col-md-3">
                                    <h6><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($delivery['u_name']); ?></h6>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($delivery['mail']); ?><br>
                                        <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($delivery['phone']); ?>
                                    </p>
                                    <p class="text-muted small">
                                        <i class="fas fa-calendar me-1"></i> Applied: <?php echo date('M j, Y', strtotime($delivery['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Delivery ID:</strong> <?php echo htmlspecialchars($delivery['del_id'] ?? 'N/A'); ?></p>
                                    <p><strong>National ID:</strong> <?php echo htmlspecialchars($delivery['d_n_id'] ?? 'N/A'); ?></p>
                                    <p><strong>Zone:</strong> <?php echo htmlspecialchars($delivery['d_zone']); ?></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>License:</strong></p>
                                    <?php if (!empty($delivery['d_license']) && file_exists($delivery['d_license'])): ?>
                                        <img src="<?php echo htmlspecialchars($delivery['d_license']); ?>" 
                                             alt="License" class="license-preview mb-2" 
                                             onclick="showLicenseModal('<?php echo htmlspecialchars($delivery['d_license']); ?>')">
                                        <br><small class="text-muted">Click to view full size</small>
                                    <?php else: ?>
                                        <span class="text-muted">No license uploaded</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-2 text-end">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['user_id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-approve btn-sm mb-2 w-100" 
                                                onclick="return confirm('Approve this delivery agent?')">
                                            <i class="fas fa-check me-1"></i> Approve
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delivery_id" value="<?php echo $delivery['user_id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-reject btn-sm w-100" 
                                                onclick="return confirm('Reject this delivery agent? This will permanently delete their account.')">
                                            <i class="fas fa-times me-1"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="pending-card text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>No Pending Approvals</h5>
                        <p class="text-muted">All delivery agent applications have been processed.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recently Approved -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-3">
                    <i class="fas fa-check-circle me-2"></i>
                    Recently Approved Agents
                </h4>
                
                <div class="pending-card">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Zone</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($approved_result->num_rows > 0): ?>
                                    <?php while ($delivery = $approved_result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-user me-2"></i>
                                                <?php echo htmlspecialchars($delivery['u_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($delivery['mail']); ?></td>
                                            <td><?php echo htmlspecialchars($delivery['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($delivery['d_zone']); ?></td>
                                            <td>
                                                <?php if ($delivery['status'] === 'online'): ?>
                                                    <span class="badge bg-success">Online</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Offline</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($delivery['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No approved delivery agents found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- License Modal -->
    <div class="modal fade" id="licenseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delivery License</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="licenseImage" src="" alt="License" style="max-width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLicenseModal(licensePath) {
            document.getElementById('licenseImage').src = licensePath;
            new bootstrap.Modal(document.getElementById('licenseModal')).show();
        }
    </script>
</body>
</html> 