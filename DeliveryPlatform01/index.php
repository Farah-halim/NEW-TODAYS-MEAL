<?php
/**
 * Main entry point - redirects to appropriate page
 */
require_once 'includes/auth.php';

// Redirect based on authentication status
if (is_logged_in()) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>