<?php
require_once "../DB_connection.php";
date_default_timezone_set('Africa/Cairo');

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Check if email exists in database
    $stmt = $conn->prepare("SELECT user_id, mail FROM users WHERE mail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generate a unique token (32 characters)
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 hour expiration
        
        // Store token in database
        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?");
        $update_stmt->bind_param("ssi", $token, $expiry, $user['user_id']);
        
        if ($update_stmt->execute()) {
            // Use your exact URL path with proper encoding
            $reset_link = "http://localhost/NEW-TODAYS-MEAL/Register&Login/reset_password.php?token=" . $token;
          
            header("Location: $reset_link");
            exit();
        } else {
            $message = "Error generating reset token. Please try again.";
            $message_type = 'error';
        }
        
        $update_stmt->close();
    } else {
        $message = "No account found with that email address.";
        $message_type = 'error';
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="forgot_password.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <?php if (!empty($message)): ?>
  <script>
    Swal.fire({
      icon: '<?php echo $message_type === 'success' ? 'success' : 'error'; ?>',
      title: '<?php echo $message_type === 'success' ? 'Success' : 'Error'; ?>',
      text: '<?php echo addslashes($message); ?>',
      confirmButtonColor: '#3085d6',
      confirmButtonText: 'OK'
    });
  </script>
  <?php endif; ?>

  <main class="page-container">
    <section class="card-container">
      <a href="login.php" class="back-link">
        <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg" class="back-icon">
          <path d="M7.99996 13.0667L3.3333 8.39999L7.99996 3.73332" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M12.6666 8.39999H3.3333" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <span class="back-text">Back to Login</span>
      </a>

      <header class="header-content">
        <h1 class="page-title">Forgot Password</h1>
        <p class="page-description">
          Enter your email address and we'll send you instructions<br>
          to reset your password.
        </p>
      </header>

      <form class="reset-form" id="reset-form" method="POST" action="">
        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <div class="input-container">
            <div class="input-icon-wrapper">
              <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg" class="email-icon">
                <g clip-path="url(#clip0_1_24)">
                  <path d="M15 3.20001H2.99997C2.17154 3.20001 1.49997 3.87159 1.49997 4.70001V13.7C1.49997 14.5284 2.17154 15.2 2.99997 15.2H15C15.8284 15.2 16.5 14.5284 16.5 13.7V4.70001C16.5 3.87159 15.8284 3.20001 15 3.20001Z" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M16.5 5.45001L9.77247 9.72501C9.54092 9.87008 9.27321 9.94702 8.99997 9.94702C8.72673 9.94702 8.45902 9.87008 8.22747 9.72501L1.49997 5.45001" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                </g>
                <defs>
                  <clipPath id="clip0_1_24">
                    <rect width="18" height="18" fill="white" transform="translate(-3.05176e-05 0.200012)"></rect>
                  </clipPath>
                </defs>
              </svg>
            </div>
            <input type="email" id="email" name="email" placeholder="Enter your email" class="email-input" required>
          </div>
        </div>
        <button type="submit" class="reset-button">Reset Password</button>
      </form>
    </section>
  </main>
</body>
</html>