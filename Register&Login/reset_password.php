<?php
require_once "../DB_connection.php";
date_default_timezone_set('Africa/Cairo');


$message = '';
$message_type = ''; // 'success' or 'error'
$show_form = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $show_form = true;
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            
            if ($new_password !== $confirm_password) {
                $message = "Passwords do not match.";
                $message_type = 'error';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user['user_id']);
                
                if ($update_stmt->execute()) {
                    $message = "Your password has been updated successfully!";
                    $message_type = 'success';
                } else {
                    $message = "Error updating password. Please try again.";
                    $message_type = 'error';
                }
            }
        }
    } else {
        $message = "Invalid or expired reset link. Please request a new password reset.";
        $message_type = 'error';
    }
} else {
    $message = "No reset token provided.";
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
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
    }).then((result) => {
      <?php if ($message_type === 'success'): ?>
        window.location.href = 'login.php';
      <?php endif; ?>
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
        <h1 class="page-title">Reset Your Password</h1>
        <p class="page-description">
          Enter your new password below.
        </p>
      </header>

      <?php if ($show_form): ?>
      <form class="reset-form" id="reset-form" method="POST" action="">
        <div class="form-group">
          <label for="password" class="form-label">New Password</label>
          <div class="input-container">
            <div class="input-icon-wrapper">
              <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg" class="email-icon">
                <path d="M14.25 8.45001H3.75C2.92157 8.45001 2.25 9.12158 2.25 9.95001V14.45C2.25 15.2784 2.92157 15.95 3.75 15.95H14.25C15.0784 15.95 15.75 15.2784 15.75 14.45V9.95001C15.75 9.12158 15.0784 8.45001 14.25 8.45001Z" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5.25 8.45001V5.45001C5.25 4.55696 5.60558 3.70042 6.23851 3.06749C6.87144 2.43456 7.72798 2.07898 8.62103 2.07898C9.51408 2.07898 10.3706 2.43456 11.0035 3.06749C11.6365 3.70042 11.992 4.55696 11.992 5.45001V8.45001" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <input type="password" id="password" name="password" placeholder="Enter new password" class="email-input" required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <div class="input-container">
            <div class="input-icon-wrapper">
              <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg" class="email-icon">
                <path d="M14.25 8.45001H3.75C2.92157 8.45001 2.25 9.12158 2.25 9.95001V14.45C2.25 15.2784 2.92157 15.95 3.75 15.95H14.25C15.0784 15.95 15.75 15.2784 15.75 14.45V9.95001C15.75 9.12158 15.0784 8.45001 14.25 8.45001Z" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M5.25 8.45001V5.45001C5.25 4.55696 5.60558 3.70042 6.23851 3.06749C6.87144 2.43456 7.72798 2.07898 8.62103 2.07898C9.51408 2.07898 10.3706 2.43456 11.0035 3.06749C11.6365 3.70042 11.992 4.55696 11.992 5.45001V8.45001" stroke="#6A4125" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" class="email-input" required>
          </div>
        </div>
        
        <button type="submit" class="reset-button">Update Password</button>
      </form>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>