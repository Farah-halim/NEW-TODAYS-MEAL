<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_cloud_kitchen_dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple admin credentials (in production, use proper authentication)
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_name'] = 'Admin User';
        header('Location: admin_cloud_kitchen_dashboard.php');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Cloud Kitchen Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #fff7e5 0%, #f5e6d3 100%);
            --secondary-gradient: linear-gradient(135deg, #f5e6d3 0%, #e8d5c4 100%);
            --orange-gradient: linear-gradient(135deg, #e57e24 0%, #d67517 100%);
            --warm-gradient: linear-gradient(135deg, #fff7e5 0%, #f0dcc9 50%, #e8d5c4 100%);
            --glass-bg: rgba(255, 247, 229, 0.15);
            --glass-border: rgba(255, 247, 229, 0.25);
            --text-primary: #6a4125;
            --text-secondary: #8b6f47;
            --shadow-lg: 0 25px 50px -12px rgba(106, 65, 37, 0.15);
            --shadow-xl: 0 35px 60px -12px rgba(106, 65, 37, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fff7e5 0%, #f5e6d3 25%, #e8d5c4 50%, #dbc4a2 75%, #d4b896 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Elements */
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .floating-shape {
            position: absolute;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(229, 126, 36, 0.2);
            animation: float 6s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(229, 126, 36, 0.6);
            font-size: 1.5rem;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
            background: rgba(245, 230, 211, 0.3);
        }

        .shape-1::before {
            content: '\f2e7';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            border-radius: 30px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
            background: rgba(232, 213, 196, 0.3);
        }

        .shape-2::before {
            content: '\f0f5';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
            background: rgba(219, 196, 162, 0.3);
        }

        .shape-3::before {
            content: '\f562';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            top: 10%;
            right: 30%;
            animation-delay: 1s;
            background: rgba(212, 184, 150, 0.3);
        }

        .shape-4::before {
            content: '\f0f5';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(10px) rotate(240deg); }
        }

        /* Main Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        /* Glassmorphism Card */
        .login-card {
            background: rgba(255, 247, 229, 0.2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 247, 229, 0.3);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header Section */
        .login-header {
            text-align: center;
            padding: 3rem 2rem 2rem;
            position: relative;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .logo-bg {
            width: 80px;
            height: 80px;
            background: var(--orange-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 15px 35px rgba(229, 126, 36, 0.4);
            animation: pulse 2s ease-in-out infinite;
            position: relative;
            padding: 10px;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-bg img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        /* Remove the old icon styles */
        .logo-bg i.fa-utensils {
            display: none;
        }

        .logo-bg::before {
            display: none;
        }

        .login-title {
            color: var(--text-primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(106, 65, 37, 0.1);
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
        }

        /* Form Section */
        .login-body {
            padding: 0 2rem 3rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .input-container {
            position: relative;
        }

        .form-control {
            background: rgba(255, 247, 229, 0.3);
            border: 1px solid rgba(229, 126, 36, 0.2);
            border-radius: 16px;
            padding: 1rem 1rem 1rem 3.5rem;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .form-control:focus {
            background: rgba(255, 247, 229, 0.4);
            border-color: #e57e24;
            box-shadow: 0 0 0 3px rgba(229, 126, 36, 0.1);
            outline: none;
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 1.1rem;
            z-index: 5;
        }

        /* Login Button */
        .btn-login {
            background: var(--orange-gradient);
            border: none;
            border-radius: 16px;
            padding: 1rem 2rem;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(229, 126, 36, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(229, 126, 36, 0.4);
            color: white;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        /* Alert Styling */
        .alert {
            background: rgba(248, 215, 218, 0.3);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 12px;
            color: #721c24;
            backdrop-filter: blur(10px);
            margin-bottom: 1.5rem;
        }

        /* Demo Info */
        .demo-info {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(255, 247, 229, 0.2);
            border-radius: 12px;
            border: 1px solid rgba(229, 126, 36, 0.2);
        }

        .demo-info small {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                max-width: 380px;
                padding: 15px;
            }
            
            .login-header {
                padding: 2rem 1.5rem 1.5rem;
            }
            
            .login-body {
                padding: 0 1.5rem 2rem;
            }
            
            .login-title {
                font-size: 1.75rem;
            }
        }

        /* Loading Animation */
        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="floating-shape shape-4"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <div class="logo-bg">
                        <img src="logo1.png" alt="Cloud Kitchen Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <i class="fas fa-utensils" style="display: none; font-size: 2.5rem; color: white;"></i>
                    </div>
                </div>
                <h1 class="login-title">Admin Portal</h1>
                <p class="login-subtitle">Cloud Kitchen Management System</p>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <div class="input-container">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   class="form-control" 
                                   name="username" 
                                   placeholder="Enter your username"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-container">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   class="form-control" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Access Dashboard
                    </button>
                </form>

                <div class="demo-info">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Demo Credentials: <strong>admin</strong> / <strong>admin123</strong>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add loading animation on form submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.innerHTML = '<span style="opacity: 0;">Processing...</span>';
        });

        // Add focus animations
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Particle effect on click
        document.addEventListener('click', function(e) {
            createRipple(e.pageX, e.pageY);
        });

        function createRipple(x, y) {
            const ripple = document.createElement('div');
            ripple.style.position = 'fixed';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.width = '10px';
            ripple.style.height = '10px';
            ripple.style.background = 'rgba(255, 255, 255, 0.3)';
            ripple.style.borderRadius = '50%';
            ripple.style.transform = 'translate(-50%, -50%)';
            ripple.style.animation = 'ripple 0.6s ease-out';
            ripple.style.pointerEvents = 'none';
            ripple.style.zIndex = '9999';
            
            document.body.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: translate(-50%, -50%) scale(20);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html> 