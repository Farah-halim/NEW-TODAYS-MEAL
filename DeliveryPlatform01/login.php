<?php
/**
 * Login page
 */
require_once 'includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Initialize variables
$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $delivery_id = isset($_POST['delivery_id']) ? trim($_POST['delivery_id']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate input
    if (empty($delivery_id) || empty($password)) {
        $error = 'Please enter both delivery ID and password';
    } else {
        // Attempt to authenticate user
        $user = authenticate_delivery($delivery_id, $password);
        
        if ($user) {
            // Login successful
            login($user['id'], $remember);
            redirect('dashboard.php');
        } else {
            // Login failed
            $error = 'Invalid delivery ID or password';
        }
    }
}

// Set page title
$pageTitle = 'Login';

// Include minimal header
include 'includes/minimal-header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10">
            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-12">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h1 class="h4 mb-2">Welcome to NutriDelivery</h1>
                                    <p class="mb-4">Enter your credentials to log in</p>
                                </div>
                                
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                                </div>
                                <?php endif; ?>
                                
                                <form class="user" method="post" action="login.php">
                                    <div class="mb-3">
                                        <label for="delivery_id" class="form-label">Delivery ID</label>
                                        <input type="text" class="form-control" id="delivery_id" name="delivery_id" 
                                               value="<?php echo isset($_POST['delivery_id']) ? htmlspecialchars($_POST['delivery_id']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                                               <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="remember">Remember Me</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 py-2">
                                        <i class="fas fa-sign-in-alt me-2"></i> Login
                                    </button>
                                </form>
                                
                                <hr>
                                
                                <div class="text-center">
                                    <p class="small mb-0">Demo account:</p>
                                    <p class="small">Delivery ID: <code>DEL2023001</code> / Password: <code>delivery123</code></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include minimal footer
include 'includes/minimal-footer.php';
?>
