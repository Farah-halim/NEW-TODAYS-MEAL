<?php
session_start();
require_once "../DB_connection.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $sql = "SELECT u.*, eu.ext_role, cko.is_approved AS kitchen_approved, 
               dm.is_approved AS delivery_approved 
               FROM users u
               LEFT JOIN external_user eu ON u.user_id = eu.user_id
               LEFT JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
               LEFT JOIN delivery_man dm ON u.user_id = dm.user_id
               WHERE u.mail = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                if ($user['u_role'] == 'delivery_man' && !$user['delivery_approved']) {
                    $error = "Your account is pending admin approval. Please wait for confirmation.";
                } 
                elseif ($user['u_role'] == 'external_user' && $user['ext_role'] == 'cloud_kitchen_owner' && !$user['kitchen_approved']) {
                    $error = "Your cloud kitchen account is pending admin approval. Please wait for confirmation.";
                } 
                else {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['u_role'] = $user['u_role'];
                    $_SESSION['ext_role'] = $user['ext_role'] ?? null;
                    $_SESSION['u_name'] = $user['u_name'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['form_token'] = bin2hex(random_bytes(32));
                    
                    switch ($user['u_role']) {
                        case 'delivery_man':
                            header("Location: delivery_dashboard.php");
                            break;
                        case 'external_user':
                            if ($user['ext_role'] == 'cloud_kitchen_owner') {
                                header("Location: ..\cloud_kitchen\dashboard.php");
                            } else {
                                header("Location: ..\customer\Home\index.php");
                            }
                            break;
                        default:
                            header("Location: ..\customer\Home\index.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid email or password. Please try again.";
            }
        } else {
            $error = "No account found with this email address.";
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="login.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background-color: #ffffff;
  box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);
  z-index: 1000;
}

.nav-content {
  max-width: 1280px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 80px;
  padding: 0 32px;
}

.logo {
  width: 110px;
  height: 110px;
  object-fit: contain;
}

.nav-links {
  display: flex;
  gap: 20px;
}

.nav-link {
  color: #8b4513;
  font-weight: 500;
  font-size: 17px;
  text-decoration: none;
}

    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <a href="#" class="logo-link">
                <img src="logo.png" alt="Today's Meal" class="logo">
            </a>
            <div class="nav-links">
                <a href="#" class="nav-link">Home</a>
                <a href="#" class="nav-link">About</a>
                <a href="#" class="nav-link">Contact</a>
            </div>
        </div>
    </nav>
    <?php if (!empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Login Error',
                text: '<?php echo addslashes($error); ?>',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('input[name="password"]').value = '';
                }
            });
        });
    </script>
    <?php endif; ?>
    
    <div class="login">
        <div class="image-container"></div>
        <div class="form-container">
            <h1>Sign In</h1>
            <form method="POST" action="" id="loginForm" onsubmit="return validateForm()">
                <div class="input-group">
                    <label>Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg viewBox="0 0 20 20" fill="#9CA3AF">
                            <path d="M18.3333 5C18.3333 4.08334 17.5833 3.33334 16.6667 3.33334H3.33333C2.41667 3.33334 1.66667 4.08334 1.66667 5V15C1.66667 15.9167 2.41667 16.6667 3.33333 16.6667H16.6667C17.5833 16.6667 18.3333 15.9167 18.3333 15V5ZM16.6667 5L10 9.16668L3.33333 5H16.6667ZM16.6667 15H3.33333V6.66668L10 10.8333L16.6667 6.66668V15Z"/>
                        </svg>
                        <input type="email" name="email" id="email" placeholder="Enter email" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg viewBox="0 0 20 20" fill="#9CA3AF">
                            <path d="M15.8333 9.16666H4.16667C3.24167 9.16666 2.5 9.90832 2.5 10.8333V16.6667C2.5 17.5917 3.24167 18.3333 4.16667 18.3333H15.8333C16.7583 18.3333 17.5 17.5917 17.5 16.6667V10.8333C17.5 9.90832 16.7583 9.16666 15.8333 9.16666ZM15.8333 16.6667H4.16667V10.8333H15.8333V16.6667ZM14.1667 5.83332C14.1667 3.53332 12.3 1.66666 10 1.66666C7.70001 1.66666 5.83334 3.53332 5.83334 5.83332V7.49999H7.50001V5.83332C7.50001 4.45832 8.62501 3.33332 10 3.33332C11.375 3.33332 12.5 4.45832 12.5 5.83332V7.49999H14.1667V5.83332Z"/>
                        </svg>
                        <input type="password" name="password" id="password" placeholder="Enter password" required>
                    </div>

                </div>
                <div class="input-group">
                    <div class="options">
                        <label><input type="checkbox" class="remember-me" name="remember"> Remember me</label>
                        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                    </div>
                </div>
                <button type="submit">Sign In</button>
            </form>
            <p class="signup">Don't have an account? <a href="customer.php">Create Account</a></p>
        </div>
    </div>

    <script>
        function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Email',
                    text: 'Please enter a valid email address',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            return true;
        }
    </script>
</body>
</html>