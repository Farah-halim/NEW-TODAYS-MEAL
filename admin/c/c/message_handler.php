<?php
/**
 * Message Handler Utility
 * Prevents session messages from appearing on wrong pages
 */

function displayMessages($page_context = '') {
    $message_key_success = $page_context ? "success_$page_context" : 'success';
    $message_key_error = $page_context ? "error_$page_context" : 'error';
    
    // Display success message
    if (isset($_SESSION[$message_key_success])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-check-circle me-2"></i>';
        echo htmlspecialchars($_SESSION[$message_key_success]);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION[$message_key_success]);
    }
    
    // Display error message
    if (isset($_SESSION[$message_key_error])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-exclamation-circle me-2"></i>';
        echo htmlspecialchars($_SESSION[$message_key_error]);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION[$message_key_error]);
    }
    
    // Also clear any generic messages to prevent cross-page display
    if ($page_context && isset($_SESSION['success'])) {
        unset($_SESSION['success']);
    }
    if ($page_context && isset($_SESSION['error'])) {
        unset($_SESSION['error']);
    }
}

function setMessage($type, $message, $page_context = '') {
    $message_key = $page_context ? "{$type}_$page_context" : $type;
    $_SESSION[$message_key] = $message;
}

function clearAllMessages() {
    $keys_to_clear = [];
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'success') === 0 || strpos($key, 'error') === 0) {
            $keys_to_clear[] = $key;
        }
    }
    foreach ($keys_to_clear as $key) {
        unset($_SESSION[$key]);
    }
}
?> 