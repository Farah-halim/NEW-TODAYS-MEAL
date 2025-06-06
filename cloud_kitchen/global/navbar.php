<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../DB_connection.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User not logged in");
}

$error = '';
$success = '';
$password_error = '';
$password_success = '';
$email_error = '';
$email_success = '';

// Fetch user data and check if customized orders are enabled
$userData = [];
$cloudKitchenData = [];
$showCatering = false; // Default to false

try {
    $stmt = $conn->prepare("SELECT u.*, eu.address, eu.ext_role, cko.business_name, cko.customized_orders 
                          FROM users u
                          JOIN external_user eu ON u.user_id = eu.user_id
                          LEFT JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
                          WHERE u.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        $cloudKitchenData = $userData;
        $showCatering = (bool)($userData['customized_orders'] ?? false);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Handle account info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $businessName = htmlspecialchars(trim($_POST['business_name'] ?? ''));

    try {
        $conn->begin_transaction();
        
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET u_name = ? WHERE user_id = ?");
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
        
        // Update external_user table
        $stmt = $conn->prepare("UPDATE external_user SET address = ? WHERE user_id = ?");
        $stmt->bind_param("si", $address, $user_id);
        $stmt->execute();
        
        // Update cloud_kitchen_owner table
        if (!empty($businessName)) {
            $stmt = $conn->prepare("UPDATE cloud_kitchen_owner SET business_name = ? WHERE user_id = ?");
            $stmt->bind_param("si", $businessName, $user_id);
            $stmt->execute();
        }
        
        $conn->commit();
        $success = "Account information updated successfully.";
        // Refresh data
        $userData['u_name'] = $name;
        $userData['address'] = $address;
        $cloudKitchenData['business_name'] = $businessName;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Update failed: " . $e->getMessage();
    }
}

// Handle email change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $newEmail = $_POST['new_email'] ?? '';
    $confirmEmail = $_POST['confirm_new_email'] ?? '';

    if ($newEmail !== $confirmEmail) {
        $email_error = "Emails do not match";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Invalid email format";
    } else {
        try {
            // Check if email exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE mail = ? AND user_id != ?");
            $stmt->bind_param("si", $newEmail, $user_id);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $email_error = "Email already in use";
            } else {
                // Update email
                $stmt = $conn->prepare("UPDATE users SET mail = ? WHERE user_id = ?");
                $stmt->bind_param("si", $newEmail, $user_id);
                $stmt->execute();
                $email_success = "Email updated successfully.";
                $userData['mail'] = $newEmail;
            }
        } catch (Exception $e) {
            $email_error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_new_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $password_error = "Password must be at least 8 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        if (!$stmt) {
            $password_error = "Failed to prepare statement. Please try again.";
        } else {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            if ($stmt->fetch()) {
                $stmt->close();
                if (!password_verify($current_password, $hashed_password)) {
                    $password_error = "Current password is incorrect.";
                } else {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    if (!$stmt) {
                        $password_error = "Failed to prepare statement. Please try again.";
                    } else {
                        $stmt->bind_param("si", $new_hashed_password, $user_id);
                        if ($stmt->execute()) {
                            $password_success = "Password changed successfully.";
                        } else {
                            $password_error = "Failed to update password. Please try again.";
                        }
                        $stmt->close();
                    }
                }
            } else {
                $stmt->close();
                $password_error = "User not found.";
            }
        }
    }
} ?>

<header class="navbar-header">
    <div class="navbar-logo">
        <h1 class="navbar-heading">𝓣𝓸𝓭𝓪𝔂'𝓼 𝓶𝓮𝓪𝓵</h1>
    </div>
    <button class="navbar-mobile-menu-button" id="navbar-mobile-menu-btn" aria-label="Toggle menu">
        <img src="global/images/dropdown-menu.png" alt="menu" width="19" height="19" style="margin-right: 5px; vertical-align: middle;">
        <span class="navbar-mobile-menu-text">My actions</span>
    </button>
    <nav class="navbar-nav" id="navbar-main-nav" aria-label="Main navigation">
        <a href="./dashboard.php" class="navbar-nav-item active">
            <span class="navbar-nav-icon dashboard"></span>
            <span class="navbar-nav-text">Dashboard</span>
        </a>
        <a href="./inventory.php" class="navbar-nav-item">
            <span class="navbar-nav-icon inventory"></span>
            <span class="navbar-nav-text">Inventory</span>
        </a>
        <a href="./orders.php" class="navbar-nav-item">
            <span class="navbar-nav-icon orders"></span>
            <span class="navbar-nav-text">Orders</span>
        </a>
        <?php if ($showCatering): ?>
        <a href="./catering.php" class="navbar-nav-item">   
            <span class="navbar-nav-icon catering"></span>
            <span class="navbar-nav-text">Catering</span>
        </a>
        <?php endif; ?>
        <a href="./meal_management.php" class="navbar-nav-item">
            <span class="navbar-nav-icon meals"></span>
            <span class="navbar-nav-text">My Meals</span>
        </a>
        <a href="./support.php" class="navbar-nav-item">
            <span class="navbar-nav-icon support"></span>
            <span class="navbar-nav-text">Support</span>
        </a>
    </nav>
    <div class="navbar-user-controls">
        <button class="navbar-settings-button" id="navbar-settings-btn" aria-haspopup="true" aria-expanded="false">
            <img src="https://img.icons8.com/?size=100&id=UXWIv5G5mWsK&format=png&color=ffffff" alt="Settings" class="navbar-settings-icon">
            <span class="navbar-user-name"><?= htmlspecialchars(explode(' ', $userData['u_name'] ?? 'User')[0]) ?></span>
        </button>
    </div>
</header>

<div class="navbar-settings-dropdown" id="navbar-settings-dropdown" aria-hidden="true" hidden>
    <div class="navbar-user-profile">
        <div class="navbar-user-avatar" aria-label="User initial">
            <?= strtoupper(substr($userData['u_name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="navbar-user-info">
            <div class="navbar-user-name"><?= htmlspecialchars($userData['u_name'] ?? 'User') ?></div>
            <div class="navbar-user-email"><?= htmlspecialchars($userData['mail'] ?? '') ?></div>
        </div>
    </div>
    <div class="navbar-settings-menu">
        <a href="#" class="navbar-settings-item" id="navbar-account-info-btn" role="menuitem">
            <img src="https://img.icons8.com/windows/32/gender-neutral-user.png" class="navbar-settings-item-icon" alt="Account Info">
            <span>Account Info</span>
        </a>
        <a href="#" class="navbar-settings-item" id="navbar-change-email-btn" role="menuitem">
            <img src="https://img.icons8.com/ios/100/new-post--v1.png" class="navbar-settings-item-icon" alt="Change Email">
            <span>Change Email</span>
        </a>
        <a href="#" class="navbar-settings-item" id="navbar-change-password-btn" role="menuitem">
            <img src="https://img.icons8.com/windows/32/key.png" class="navbar-settings-item-icon" alt="Change Password">
            <span>Change Password</span>
        </a>
        <a href="..\..\Register&Login\logout.php" class="navbar-settings-item navbar-logout" role="menuitem">
            <svg class="navbar-settings-item-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            <span>Logout</span>
        </a>
    </div>
</div>

<div class="navbar-popup" id="navbar-account-info-popup">
    <div class="navbar-popup-content">
        <button id="navbar-back-btn" class="navbar-close-btn" type="button">&times;</button>
        <h2>Account Info</h2>
        <form id="navbar-account-info-form" method="post" action="">
            <div class="navbar-form-group">
                <label for="navbar-name">Name</label>
                <input type="text" id="navbar-name" name="name" value="<?= htmlspecialchars($userData['u_name'] ?? '') ?>" readonly required>            
            </div>
            <div class="navbar-form-group">
                <label for="navbar-address">Address</label>
                <input type="text" id="navbar-address" name="address" value="<?= htmlspecialchars($userData['address'] ?? '') ?>" readonly required>
            </div>
            <div class="navbar-form-group">
                <label for="navbar-business-name">Business Name</label>
                <input type="text" id="navbar-business-name" name="business_name" value="<?= htmlspecialchars($cloudKitchenData['business_name'] ?? '') ?>" readonly>
            </div>
            <div class="navbar-form-actions">
                <button type="button" id="navbar-edit-save-btn" class="navbar-edit-btn navbar-bottom-right-btn">Edit</button>
                <button type="submit" class="navbar-save-btn navbar-bottom-right-btn" id="navbar-account-save-btn" style="display: none;" name="update_account">Save Changes</button>
            </div>
        </form>
        <?php if (!empty($error)) : ?>
            <div class="navbar-error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)) : ?>
            <div class="navbar-success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="navbar-popup" id="navbar-change-email-popup">
    <div class="navbar-popup-content">
        <button class="navbar-close-btn" type="button">&times;</button>
        <h2>Change Email</h2>
        <form id="navbar-change-email-form" method="post" action="">
            <div class="navbar-form-group">
                <label for="navbar-current-email">Current Email</label>
                <input type="email" id="navbar-current-email" value="<?= htmlspecialchars($userData['mail'] ?? '') ?>" readonly>
            </div>
            <div class="navbar-form-group">
                <label for="navbar-new-email">New Email</label>
                <input type="email" id="navbar-new-email" name="new_email" required>
            </div>
            <div class="navbar-form-group">
                <label for="navbar-confirm-new-email">Confirm New Email</label>
                <input type="email" id="navbar-confirm-new-email" name="confirm_new_email" required>
            </div>
            <div class="navbar-form-actions">
                <button type="submit" class="navbar-save-btn navbar-bottom-right-btn" name="update_email">Change Email</button>
            </div>
        </form>
        <?php if (!empty($email_error)) : ?>
            <div id="navbar-email-error" class="navbar-error-message"><?= htmlspecialchars($email_error) ?></div>
        <?php else: ?>
            <div id="navbar-email-error" class="navbar-error-message" style="display: none;"></div>
        <?php endif; ?>
        <?php if (!empty($email_success)) : ?>
            <div id="navbar-email-success" class="navbar-success-message"><?= htmlspecialchars($email_success) ?></div>
        <?php else: ?>
            <div id="navbar-email-success" class="navbar-success-message" style="display: none;"></div>
        <?php endif; ?>
    </div>
</div>

<div class="navbar-popup" id="navbar-change-password-popup">
    <div class="navbar-popup-content">
        <button class="navbar-close-btn" type="button">&times;</button>
        <h2>Change Password</h2>
        <form id="navbar-change-password-form" method="post" action="">
            <div class="navbar-form-group">
                <label for="navbar-current-password">Current Password</label>
                <input type="password" id="navbar-current-password" name="current_password" required>
            </div>
            <div class="navbar-form-group">
                <label for="navbar-new-password">New Password</label>
                <input type="password" id="navbar-new-password" name="new_password" required>
                <small class="navbar-password-hint">Password must be at least 8 characters long</small>
            </div>
            <div class="navbar-form-group">
                <label for="navbar-confirm-new-password">Confirm New Password</label>
                <input type="password" id="navbar-confirm-new-password" name="confirm_new_password" required>
            </div>
            <div class="navbar-form-actions">
                <button type="submit" class="navbar-save-btn navbar-bottom-right-btn">Change Password</button>
            </div>
        </form>
        <?php if (!empty($password_error)) : ?>
            <div id="navbar-password-error" class="navbar-error-message"><?= htmlspecialchars($password_error) ?></div>
        <?php else: ?>
            <div id="navbar-password-error" class="navbar-error-message" style="display: none;"></div>
        <?php endif; ?>
        <?php if (!empty($password_success)) : ?>
            <div id="navbar-password-success" class="navbar-success-message"><?= htmlspecialchars($password_success) ?></div>
        <?php else: ?>
            <div id="navbar-password-success" class="navbar-success-message" style="display: none;"></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuBtn = document.getElementById('navbar-mobile-menu-btn');
    const mainNav = document.getElementById('navbar-main-nav');
    const settingsBtn = document.getElementById('navbar-settings-btn');
    const settingsDropdown = document.getElementById('navbar-settings-dropdown');
    const accountInfoBtn = document.getElementById('navbar-account-info-btn');
    const accountInfoPopup = document.getElementById('navbar-account-info-popup');
    const backBtn = document.getElementById('navbar-back-btn');
    const editSaveBtn = document.getElementById('navbar-edit-save-btn');
    const accountSaveBtn = document.getElementById('navbar-account-save-btn');
    const accountForm = document.getElementById('navbar-account-info-form');
    const nameInput = accountForm.querySelector('#navbar-name');
    const addressInput = accountForm.querySelector('#navbar-address');
    const businessNameInput = accountForm.querySelector('#navbar-business-name');
    const popups = document.querySelectorAll('.navbar-popup');

    const changePasswordBtn = document.getElementById('navbar-change-password-btn');
    const changePasswordPopup = document.getElementById('navbar-change-password-popup');
    const changePasswordForm = document.getElementById('navbar-change-password-form');
    const passwordError = document.getElementById('navbar-password-error');
    const passwordSuccess = document.getElementById('navbar-password-success');

    const changeEmailBtn = document.getElementById('navbar-change-email-btn');
    const changeEmailPopup = document.getElementById('navbar-change-email-popup');
    const changeEmailForm = document.getElementById('navbar-change-email-form');
    const emailError = document.getElementById('navbar-email-error');
    const emailSuccess = document.getElementById('navbar-email-success');

    // Show Change Email popup
    changeEmailBtn.addEventListener('click', e => {
        e.preventDefault();
        settingsDropdown.classList.remove('show');
        changeEmailPopup.classList.add('show');
        document.body.style.overflow = 'hidden';
        changeEmailForm.reset();
        emailError.style.display = 'none';
        emailSuccess.style.display = 'none';
    });

    // Close Change Email popup
    changeEmailPopup.querySelector('.navbar-close-btn').addEventListener('click', () => {
        changeEmailPopup.classList.remove('show');
        document.body.style.overflow = '';
        emailError.style.display = 'none';
        emailSuccess.style.display = 'none';
    });

    // Show Change Password popup
    changePasswordBtn.addEventListener('click', e => {
        e.preventDefault();
        settingsDropdown.classList.remove('show');
        changePasswordPopup.classList.add('show');
        document.body.style.overflow = 'hidden';
        changePasswordForm.reset();
        passwordError.style.display = 'none';
        passwordSuccess.style.display = 'none';
    });

    // Close Change Password popup
    changePasswordPopup.querySelector('.navbar-close-btn').addEventListener('click', () => {
        changePasswordPopup.classList.remove('show');
        document.body.style.overflow = '';
        passwordError.style.display = 'none';
        passwordSuccess.style.display = 'none';
    });

    // Close popups on outside click
    changePasswordPopup.addEventListener('click', e => {
        if (e.target === changePasswordPopup) {
            changePasswordPopup.classList.remove('show');
            document.body.style.overflow = '';
            passwordError.style.display = 'none';
            passwordSuccess.style.display = 'none';
        }
    });

    changeEmailPopup.addEventListener('click', e => {
        if (e.target === changeEmailPopup) {
            changeEmailPopup.classList.remove('show');
            document.body.style.overflow = '';
            emailError.style.display = 'none';
            emailSuccess.style.display = 'none';
        }
    });

    // Mobile menu toggle
    mobileMenuBtn.addEventListener('click', e => {
        e.stopPropagation();
        mainNav.classList.toggle('show');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', e => {
        if (!mainNav.contains(e.target) && e.target !== mobileMenuBtn) {
            mainNav.classList.remove('show');
        }
    });

    // Settings toggle and accessibility attributes management
    settingsBtn.addEventListener('click', e => {
        e.stopPropagation();
        const isHidden = settingsDropdown.hasAttribute('hidden');
        if (isHidden) {
            settingsDropdown.removeAttribute('hidden');
            settingsDropdown.setAttribute('aria-hidden', 'false');
            settingsDropdown.classList.add('show');
            settingsBtn.setAttribute('aria-expanded', 'true');
        } else {
            settingsDropdown.setAttribute('hidden', '');
            settingsDropdown.setAttribute('aria-hidden', 'true');
            settingsDropdown.classList.remove('show');
            settingsBtn.setAttribute('aria-expanded', 'false');
        }
        mainNav.classList.remove('show');
    });

    // Close settings dropdown when clicking outside
    document.addEventListener('click', e => {
        if (!settingsDropdown.contains(e.target) && e.target !== settingsBtn) {
            settingsDropdown.classList.remove('show');
            settingsDropdown.setAttribute('hidden', '');
            settingsDropdown.setAttribute('aria-hidden', 'true');
            settingsBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // Account info popup open
    accountInfoBtn.addEventListener('click', e => {
        e.preventDefault();
        settingsDropdown.classList.remove('show');
        accountInfoPopup.classList.add('show');
        document.body.style.overflow = 'hidden';

        nameInput.readOnly = true;
        addressInput.readOnly = true;
        businessNameInput.readOnly = true;
        editSaveBtn.style.display = 'inline-block';
        accountSaveBtn.style.display = 'none';
        editSaveBtn.textContent = 'Edit';
    });

    // Account info popup close buttons
    backBtn.addEventListener('click', () => {
        accountInfoPopup.classList.remove('show');
        document.body.style.overflow = '';
    });

    accountInfoPopup.addEventListener('click', e => {
        if (e.target === accountInfoPopup) {
            accountInfoPopup.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // Edit / Cancel toggle in account info form
    editSaveBtn.addEventListener('click', () => {
        if (editSaveBtn.textContent === 'Edit') {
            nameInput.readOnly = false;
            addressInput.readOnly = false;
            businessNameInput.readOnly = false;
            editSaveBtn.textContent = 'Cancel';
            accountSaveBtn.style.display = 'inline-block';
        } else {
            nameInput.readOnly = true;
            addressInput.readOnly = true;
            businessNameInput.readOnly = true;
            editSaveBtn.textContent = 'Edit';
            accountSaveBtn.style.display = 'none';
            // Reset form to original values
            nameInput.value = '<?= addslashes($userData['u_name'] ?? '') ?>';
            addressInput.value = '<?= addslashes($userData['address'] ?? '') ?>';
            businessNameInput.value = '<?= addslashes($cloudKitchenData['business_name'] ?? '') ?>';
        }
    });

    // Close all popups on Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            popups.forEach(popup => popup.classList.remove('show'));
            document.body.style.overflow = '';
        }
    });
});
</script>

<style>
.navbar-popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(3px);
}

.navbar-popup.show {
    display: flex;
    animation: navbarFadeIn 0.3s ease;
}

.navbar-popup-content {
    background-color: white;
    padding: 25px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.navbar-popup-content h2 {
    margin-top: 0;
    color: #6a4125;
    font-size: 24px;
    margin-bottom: 20px;
    text-align: center;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.navbar-close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6a4125;
    transition: color 0.2s ease;
}

.navbar-close-btn:hover {
    color: #8b4513;
}

.navbar-form-group {
    margin-bottom: 20px;
}

.navbar-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.navbar-form-group input,
.navbar-form-group select,
.navbar-form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.navbar-form-group input:focus,
.navbar-form-group select:focus,
.navbar-form-group textarea:focus {
    border-color: #6a4125;
    outline: none;
    box-shadow: 0 0 0 2px rgba(106, 65, 37, 0.1);
}

.navbar-form-group input[readonly],
.navbar-form-group select[disabled] {
    background-color: #f9f9f9;
    color: #666;
}

.navbar-password-hint {
    color: #666;
    font-size: 12px;
    display: block;
    margin-top: 5px;
}

.navbar-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.navbar-edit-btn, .navbar-save-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
}

.navbar-edit-btn {
    background-color: #f5e0c2;
    color: #6a4125;
}

.navbar-edit-btn:hover {
    background-color: #e0c097;
}

.navbar-save-btn {
    background-color: #6a4125;
    color: white;
}

.navbar-save-btn:hover {
    background-color: #5a361f;
    transform: translateY(-1px);
}

.navbar-bottom-right-btn {
    margin-left: auto;
}

.navbar-error-message {
    color: #d9534f;
    margin-top: 15px;
    padding: 10px;
    background-color: #f8d7da;
    border-radius: 5px;
}

.navbar-success-message {
    color: #5cb85c;
    margin-top: 15px;
    padding: 10px;
    background-color: #dff0d8;
    border-radius: 5px;
}

/* Navbar header styles */
.navbar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 70px;
    padding: 0 40px;
    background-color: #6a4125;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    margin-bottom: 30px;
}

.navbar-logo {
    display: flex;
    flex-direction: column;
}

.navbar-heading {
    font-weight: 700;
    color: #f5e0c2;
    font-size: 22px;
    margin: 0;
    font-family: 'Dancing Script', cursive;
    letter-spacing: 1px;
}

.navbar-logo-subtitle {
    color: #e0c097;
    font-size: 10px;
    margin-top: -3px;
    letter-spacing: 0.5px;
}

/* Navbar navigation */
.navbar-nav {
    display: flex;
    gap: 15px;
    margin-right: 20px;
}

.navbar-nav-item {
    color: #f5e0c2;
    font-weight: 500;
    font-size: 15px;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    position: relative;
}

.navbar-nav-item:hover {
    background-color: rgba(245, 224, 194, 0.1);
    transform: translateY(-2px);
}

.navbar-nav-icon {
    width: 18px;
    height: 18px;
    margin-right: 8px;
    background-size: contain;
    background-repeat: no-repeat;
    transition: transform 0.3s ease;
}

.navbar-nav-item:hover .navbar-nav-icon {
    transform: scale(1.1);
}

/* Navbar user controls */
.navbar-user-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.navbar-settings-button {
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 10px;
    border-radius: 20px;
    transition: all 0.3s ease;
    background-color: rgba(245, 224, 194, 0.2);
}

.navbar-settings-button:hover {
    background-color: rgba(245, 224, 194, 0.3);
}

.navbar-settings-icon {
    width: 24px;
    height: 24px;
    transition: transform 0.5s ease;
}

.navbar-settings-button:hover .navbar-settings-icon {
    transform: rotate(180deg);
}

.navbar-user-name {
    color: #f5e0c2;
    font-weight: 500;
    font-size: 14px;
}

/* Navbar settings dropdown */
.navbar-settings-dropdown {
    position: absolute;
    right: 30px;
    top: 70px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    width: 280px;
    overflow: hidden;
    display: none;
    z-index: 1001;
}

.navbar-settings-dropdown.show {
    display: block;
    animation: navbarFadeIn 0.3s ease;
}

.navbar-user-profile {
    padding: 15px;
    background-color: #6a4125;
    display: flex;
    align-items: center;
    gap: 12px;
}

.navbar-user-avatar {
    width: 40px;
    height: 40px;
    background-color: #f5e0c2;
    color: #6a4125;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.navbar-user-info {
    color: white;
}

.navbar-user-info .navbar-user-name {
    font-weight: 600;
    font-size: 14px;
    color: white;
}

.navbar-user-info .navbar-user-email {
    font-size: 12px;
    color: #e0c097;
    margin-top: 2px;
}

.navbar-settings-menu {
    padding: 10px 0;
}

.navbar-settings-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s ease;
}

.navbar-settings-item:hover {
    background-color: #f5f5f5;
}

.navbar-settings-item-icon {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    filter: invert(28%) sepia(29%) saturate(1356%) hue-rotate(16deg) brightness(96%) contrast(91%);
}

.navbar-logout {
    border-top: 1px solid #eee;
    margin-top: 5px;
    padding-top: 15px;
    color: #e74c3c;
}

.navbar-logout .navbar-settings-item-icon {
    filter: invert(37%) sepia(91%) saturate(747%) hue-rotate(331deg) brightness(92%) contrast(83%);
}

/* Navbar mobile menu button */
.navbar-mobile-menu-button {
    display: none;
    background: none;
    border: none;
    color: #f5e0c2;
    font-size: 15px;
    cursor: pointer;
    padding: 8px 15px;
    font-weight: 500;
    align-items: center;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.navbar-mobile-menu-button:hover {
    background-color: rgba(245, 224, 194, 0.1);
}

/* Animations */
@keyframes navbarFadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive Styles */
@media (max-width: 1024px) {
    .navbar-header {
        padding: 0 20px;
    }
    
    .navbar-nav-item {
        padding: 8px 12px;
        font-size: 14px;
    }
}

@media (max-width: 768px) {
    .navbar-header {
        height: 60px;
        padding: 0 15px;
    }
    
    .navbar-logo .navbar-heading {
        font-size: 20px;
    }
    
    .navbar-nav {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        padding: 15px;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        z-index: 999;
        display: none;
    }
    
    .navbar-nav.show {
        display: flex;
    }
    
    .navbar-nav-item {
        color: #6a4125;
        padding: 12px 15px;
        border-radius: 6px;
        margin: 5px 0;
    }
    
    .navbar-nav-item:hover {
        background-color: rgba(106, 65, 37, 0.1);
    }
    
    .navbar-mobile-menu-button {
        display: flex;
    }
    
    .navbar-settings-dropdown {
        right: 15px;
        top: 60px;
        width: 250px;
    }
}

@media (max-width: 480px) {
    .navbar-header {
        padding: 0 10px;
    }
    
    .navbar-logo .navbar-heading {
        font-size: 18px;
    }
    
    .navbar-logo-subtitle {
        font-size: 9px;
    }
    
    .navbar-popup-content {
        padding: 20px 15px;
    }
    
    .navbar-popup-content h2 {
        font-size: 20px;
    }
    
    .navbar-settings-dropdown {
        width: 90%;
        right: 5%;
    }
}

/* Navbar icons */
.navbar-nav-icon.dashboard {
    background-image: url(https://img.icons8.com/?size=100&id=2797&format=png&color=FFFFFF);
}

.navbar-nav-icon.inventory {
    background-image: url(https://img.icons8.com/?size=100&id=59857&format=png&color=FFFFFF);
}

.navbar-nav-icon.orders {
    background-image: url(https://img.icons8.com/?size=100&id=LhRbsuC35iCh&format=png&color=FFFFFF);
}

.navbar-nav-icon.catering {
    background-image: url(https://img.icons8.com/?size=100&id=i2Os176poVX1&format=png&color=FFFFFF);
}

.navbar-nav-icon.meals {
    background-image: url(https://img.icons8.com/?size=100&id=qP17ftfJdw0t&format=png&color=FFFFFF);
}

.navbar-nav-icon.support {
    background-image: url(https://img.icons8.com/?size=100&id=112508&format=png&color=FFFFFF);
}
</style>