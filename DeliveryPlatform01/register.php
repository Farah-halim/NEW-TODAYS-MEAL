<?php
require_once 'includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Generate unique delivery ID
        $delivery_id = 'DEL' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $sql = "INSERT INTO users (username, password, name, email, phone) VALUES (?, ?, ?, ?, ?)";
        $result = db_execute($sql, [$delivery_id, $hashed_password, $name, $email, $phone]);
        
        if ($result) {
            $success = "Registration successful! Your Delivery ID is: $delivery_id";
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register';
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
                                    <h1 class="h4 mb-2">Register as Delivery Personnel</h1>
                                    <p class="mb-4">Create your delivery account</p>
                                </div>
                                
                                <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($success)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                                    <hr>
                                    <a href="login.php" class="btn btn-success btn-sm">Proceed to Login</a>
                                </div>
                                <?php endif; ?>
                                
                                <form method="post" action="register.php">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="+20 1xx xxxx xxx"
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Choose Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 py-2">
                                        <i class="fas fa-user-plus me-2"></i> Register
                                    </button>
                                </form>
                                
                                <hr>
                                
                                <div class="text-center">
                                    <p class="small mb-0">Already have an account?</p>
                                    <a href="login.php" class="small">Login here</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/minimal-footer.php'; ?>
