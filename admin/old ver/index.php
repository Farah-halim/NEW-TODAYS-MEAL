<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Cloud Kitchen Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3d6f5d;
            --secondary: #e57e24;
            --bg-light: #fff7e5;
            --card-bg: #f5e0c2;
            --text-dark: #333;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background-color: var(--secondary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            padding: 12px 0;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .brand-logo {
            width: 36px;
            height: 36px;
            background-color: white;
            color: var(--secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .login-container {
            max-width: 480px;
            margin: auto;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            overflow: hidden;
        }
        
        .login-header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .login-body {
            padding: 30px;
        }
        
        .login-title {
            margin-bottom: 25px;
            font-weight: 600;
            color: var(--primary);
            text-align: center;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating .form-control {
            border: 1px solid #ddd;
            padding-left: 15px;
        }
        
        .form-floating .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(61, 111, 93, 0.25);
        }
        
        .form-floating label {
            color: #666;
            padding-left: 15px;
        }
        
        .input-group-text {
            background-color: transparent;
            border-left: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: #335d4e;
            border-color: #335d4e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 14px;
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: var(--border-radius);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .footer {
            background-color: rgba(0,0,0,0.05);
            text-align: center;
            padding: 15px;
            color: #666;
            font-size: 14px;
            margin-top: auto;
        }

     
        
        /* Animated placeholder effect */
        @keyframes placeholderShimmer {
            0% { background-position: -200px 0 }
            100% { background-position: 200px 0 }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container">
            <span class="navbar-brand mb-0">
                <div class="brand-logo">
                    <i class="fas fa-utensils"></i>
                </div>
                Cloud Kitchen Management System
            </span>
        </div>
    </nav>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2 class="fs-4 mb-0">Admin Portal</h2>
            </div>
            <div class="login-body">
                <h3 class="login-title">Sign In</h3>
                
                <?php if(isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    Invalid username or password. Please try again.
                </div>
                <?php endif; ?>
                
                <form action="admin_auth.php" method="POST" id="loginForm">
                    <!-- Username input field -->
                   <div class="form-floating mb-3">
                     <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                     <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                   </div>

<!-- Password input field with visibility toggle -->
                   <div class="form-floating mb-3">
                      <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                      <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                      <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent" id="togglePassword">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        <a href="#" class="text-decoration-none small">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 d-flex justify-content-center align-items-center gap-2">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </button>
                </form>
                
                <div class="login-footer">
                    <a href="index.php">Back to Homepage</a> | <a href="contact.php">Need Help?</a>
                    <br>
                    Demo credentials: <strong>admin</strong> / <strong>pass :</strong>admin123
                </div>
            </div>
        </div>
    </div>
 
    
    <footer class="footer">
        <div class="container">
            &copy; <?php echo date('Y'); ?> Cloud Kitchen Management System. All rights reserved.
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
        
        // Form validation with visual feedback
        const loginForm = document.getElementById('loginForm');
        const inputs = loginForm.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateInput(this);
            });
        });
        
        loginForm.addEventListener('submit', function(e) {
            let valid = true;
            
            inputs.forEach(input => {
                if (!validateInput(input)) {
                    valid = false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
            }
        });
        
        function validateInput(input) {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                return false;
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                return true;
            }
        }
    </script>
</body>
</html>