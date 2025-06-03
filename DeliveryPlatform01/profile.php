<?php
/**
 * User profile page
 */
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure user is logged in
require_login();

// Get user data
$user = get_logged_in_user();

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($phone)) {
        $message = 'Please fill in all required fields';
        $messageType = 'danger';
    } 
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
        $messageType = 'danger';
    }
    // Check if changing password
    elseif (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        // Validate current password
        if (empty($currentPassword)) {
            $message = 'Please enter your current password';
            $messageType = 'danger';
        }
        // Validate new password minimum length
        elseif (strlen($newPassword) < 8) {
            $message = 'New password must be at least 8 characters long';
            $messageType = 'danger';
        }
        // Validate password confirmation
        elseif ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match';
            $messageType = 'danger';
        }
        // Validate current password is correct
        elseif (!verify_password($currentPassword, $user['password'])) {
            $message = 'Current password is incorrect';
            $messageType = 'danger';
        }
        else {
            // Update user profile with new password
            $result = update_user_profile($user['id'], $name, $email, $phone, $newPassword);
            
            if ($result) {
                $message = 'Profile updated successfully';
                $messageType = 'success';
                // Get updated user data
                $user = get_logged_in_user();
            } else {
                $message = 'Failed to update profile';
                $messageType = 'danger';
            }
        }
    } else {
        // Update user profile without changing password
        $result = update_user_profile($user['id'], $name, $email, $phone);
        
        if ($result) {
            $message = 'Profile updated successfully';
            $messageType = 'success';
            // Get updated user data
            $user = get_logged_in_user();
        } else {
            $message = 'Failed to update profile';
            $messageType = 'danger';
        }
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">My Profile</h1>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="post" action="profile.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="form-text text-muted">Username cannot be changed</small>
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="mb-3">Change Password</h5>
                    <p class="text-muted small mb-3">Leave these fields empty if you don't want to change your password</p>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <small class="form-text text-muted">Password must be at least 8 characters long</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Account Summary -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Account Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-envelope me-2"></i> Email</span>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-phone me-2"></i> Phone</span>
                        <span><?php echo htmlspecialchars($user['phone']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fas fa-calendar-day me-2"></i> Joined</span>
                        <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </li>
                </ul>
                
                <div class="mt-4">
                    <h6 class="mb-3">Quick Links</h6>
                    <a href="dashboard.php" class="btn btn-outline-primary btn-sm mb-2 d-block">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="deliveries.php" class="btn btn-outline-primary btn-sm mb-2 d-block">
                        <i class="fas fa-motorcycle me-2"></i> Active Deliveries
                    </a>
                    <a href="history.php" class="btn btn-outline-primary btn-sm d-block">
                        <i class="fas fa-history me-2"></i> Delivery History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>