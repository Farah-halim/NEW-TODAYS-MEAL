<?php
/**
 * Authentication and user-related functions
 */
require_once 'db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Require user to be logged in, redirect to login page if not
 */
function require_login() {
    if (!is_logged_in()) {
        // Store the current URL for redirection after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

        // Redirect to login page
        redirect('login.php');
        exit;
    }
}

/**
 * Authenticate user credentials
 * 
 * @param string $delivery_id Delivery ID
 * @param string $password Plain text password
 * @return array|false User data if authentication successful, false otherwise
 */
function authenticate_delivery($delivery_id, $password) {
    global $db;

    $query = "SELECT * FROM users WHERE username = ?";
    $result = db_query($query, [$delivery_id]);

    if ($result && count($result) > 0) {
        $user = $result[0];
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }

    return false;
}

/**
 * Verify password against stored hash
 * 
 * @param string $password Plain text password
 * @param string $hash Stored password hash
 * @return bool True if password is correct, false otherwise
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Hash a password for storage
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Log in a user
 * 
 * @param int $user_id User ID
 * @param bool $remember Whether to set a remember me cookie
 * @return bool True if login successful
 */
function login($user_id, $remember = false) {
    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['last_activity'] = time();

    // Set remember me cookie if requested
    if ($remember) {
        $token = generate_remember_token($user_id);
        if ($token) {
            $secure = isset($_SERVER['HTTPS']);
            $httponly = true;

            setcookie(
                'remember_token',
                $token,
                time() + (30 * 24 * 60 * 60), // 30 days
                '/',
                '',
                $secure,
                $httponly
            );
        }
    }

    // Update last login time
    $sql = "UPDATE users SET last_login = NOW() WHERE id = :user_id";
    db_execute($sql, [':user_id' => $user_id]);

    return true;
}

/**
 * Generate and store a remember me token
 * 
 * @param int $user_id User ID
 * @return string|false Token string or false on failure
 */
function generate_remember_token($user_id) {
    // Generate a unique token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days

    // Delete any existing tokens for this user
    $sql = "DELETE FROM user_tokens WHERE user_id = :user_id AND type = 'remember'";
    db_execute($sql, [':user_id' => $user_id]);

    // Store the new token
    $sql = "INSERT INTO user_tokens (user_id, token, type, expires_at) 
            VALUES (:user_id, :token, 'remember', :expires)";
    $result = db_execute($sql, [
        ':user_id' => $user_id,
        ':token' => $token,
        ':expires' => $expires
    ]);

    return $result ? $token : false;
}

/**
 * Log out the current user
 */
function logout() {
    // Delete remember me token if it exists
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];

        // Delete token from database
        $sql = "DELETE FROM user_tokens WHERE token = :token AND type = 'remember'";
        db_execute($sql, [':token' => $token]);

        // Delete the cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }

    // Destroy the session
    session_unset();
    session_destroy();
}

/**
 * Get current logged in user data
 * 
 * @return array|false User data or false if not logged in
 */
function get_logged_in_user() {
    if (!is_logged_in()) {
        return false;
    }

    $sql = "SELECT * FROM users WHERE id = :user_id LIMIT 1";
    $user = db_query_single($sql, [':user_id' => $_SESSION['user_id']]);

    if ($user) {
        // Remove password from result
        unset($user['password']);
    }

    return $user;
}

/**
 * Redirect to a specific URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set a flash message to be displayed on the next page
 * 
 * @param string $type Message type (success, error, info, warning)
 * @param string $message Message text
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message array or null if none exists
 */
function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>