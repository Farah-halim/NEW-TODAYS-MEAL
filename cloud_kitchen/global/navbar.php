<?php
echo '<link rel="stylesheet" href="global/navbar.css" />';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$dbPath = __DIR__ . '/../../DB_connection.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    die("Database connection file not found at: " . htmlspecialchars($dbPath));
}

// Get user data if logged in
$userData = [];
$cloudKitchenData = [];
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    try {
        // Get user basic info
        $stmt = $conn->prepare("SELECT u.*, eu.address, eu.ext_role, cko.business_name 
                              FROM users u
                              JOIN external_user eu ON u.user_id = eu.user_id
                              LEFT JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
                              WHERE u.user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $cloudKitchenData = $userData;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
}
?>

<div class="container">
    <header class="header">
        <div class="logo">
            <h1 class="heading">𝓒𝓵𝓸𝓾𝓭 𝓚𝓲𝓽𝓬𝓱𝓮𝓷</h1>
        </div>
        <button class="mobile-menu-button">
            <img src="global/images/dropdown-menu.png" alt="menu" width="19" height="19" style="margin-right: 5px; vertical-align: middle;">
            My actions
        </button>
        <nav class="nav">
            <a href="./dashboard.php" class="nav-item active">
                <span class="nav-icon dashboard"></span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="./inventory.php" class="nav-item">
                <span class="nav-icon inventory"></span>
                <span class="nav-text">Inventory</span>
            </a>
            <a href="./orders.php" class="nav-item">
                <span class="nav-icon orders"></span>
                <span class="nav-text">Orders</span>
            </a>
            <a href="./catering.php" class="nav-item">   
                <span class="nav-icon"><img width="20" height="20" src="https://img.icons8.com/?size=100&id=mp8gOAkZetIl&format=png&color=000000" alt="purchase-order"/></span>
                <span class="nav-text">Catering</span>
            </a>
            <a href="./meal_management.php" class="nav-item">
                <span class="nav-icon meals"></span>
                <span class="nav-text">My Meals</span>
            </a>
            <a href="./support.php" class="nav-item">
                <span class="nav-icon support"></span>
                <span class="nav-text">Support</span>
            </a>
        </nav>
        <button class="settings-button" id="settings-btn">
            <img src="https://img.icons8.com/?size=100&id=UXWIv5G5mWsK&format=png&color=6a4125" alt="Settings" class="settings-icon">
        </button>
    </header>
    
    <!-- Settings Dropdown -->
    <div class="settings-dropdown" id="settings-dropdown">
        <a href="#" class="settings-item" id="account-info-btn">
            <img src="https://img.icons8.com/windows/32/gender-neutral-user.png" class="settings-item-icon" alt="Account Info">
            <span>Account Info</span>
        </a>
        <a href="#" class="settings-item" id="change-email-btn">
            <img src="https://img.icons8.com/ios/100/new-post--v1.png" class="settings-item-icon" alt="Change Email">
            <span>Change Email</span>
        </a>
        <a href="#" class="settings-item" id="change-password-btn">
            <img src="https://img.icons8.com/windows/32/key.png" class="settings-item-icon" alt="Change Password">
            <span>Change Password</span>
        </a>
        <a href="#" class="settings-item" id="logout-btn">
            <img src="https://img.icons8.com/windows/100/exit.png" class="settings-item-icon" alt="Logout"/>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- POPUPS -->
<!-- Account Info Popup -->
<div class="popup" id="account-info-popup">
    <div class="popup-content">
        <button class="close-btn">&times;</button>
        <h2>Account Info</h2>
        <form id="account-info-form" action="global/update_account.php" method="post">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userData['u_name'] ?? ''); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($userData['address'] ?? ''); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="business_name">Business Name</label>
                <input type="text" id="business_name" name="business_name" value="<?php echo htmlspecialchars($cloudKitchenData['business_name'] ?? ''); ?>" readonly>
            </div>
            <div class="form-actions">
                <button type="button" id="edit-save-btn" class="edit-btn">Edit</button>
                <button type="submit" class="save-btn" id="account-save-btn" style="display: none;">
                    <span class="btn-text">Save Changes</span>
                    <span class="loader" style="display: none;"></span>
                    <span class="checkmark" style="display: none;">✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="popup" id="change-email-popup">
    <div class="popup-content">
        <button class="close-btn">&times;</button>
        <h2>Change Email</h2>
        <form id="change-email-form" action="global/update_email.php" method="post">
            <div class="form-group">
                <label for="current-email">Current Email</label>
                <input type="email" id="current-email" name="current-email" value="<?php echo htmlspecialchars($userData['mail'] ?? ''); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="new-email">New Email</label>
                <input type="email" id="new-email" name="new-email" required>
            </div>
            <div class="form-group">
                <label for="confirm-new-email">Confirm New Email</label>
                <input type="email" id="confirm-new-email" name="confirm-new-email" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="save-btn">
                    <span class="btn-text">Save Changes</span>
                    <span class="loader" style="display: none;"></span>
                    <span class="checkmark" style="display: none;">✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="popup" id="change-password-popup">
    <div class="popup-content">
        <button class="close-btn">&times;</button>
        <h2>Change Password</h2>
        <form id="change-password-form" action="global/update_password.php" method="post">
            <div class="form-group">
                <label for="old-password">Old Password</label>
                <input type="password" id="old-password" name="old-password" required>
            </div>
            <div class="form-group">
                <label for="new-password">New Password</label>
                <input type="password" id="new-password" name="new-password" required>
            </div>
            <div class="form-group">
                <label for="confirm-new-password">Confirm New Password</label>
                <input type="password" id="confirm-new-password" name="confirm-new-password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="save-btn">
                    <span class="btn-text">Save Changes</span>
                    <span class="loader" style="display: none;"></span>
                    <span class="checkmark" style="display: none;">✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const qs = id => document.getElementById(id);
    const qsa = sel => document.querySelectorAll(sel);
    
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const nav = document.querySelector('.nav');
    
    mobileMenuButton.addEventListener('click', (e) => {
        e.stopPropagation();
        nav.classList.toggle('show');
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!nav.contains(e.target) && !mobileMenuButton.contains(e.target)) {
            nav.classList.remove('show');
        }
    });
    
    // Settings dropdown
    const settingsBtn = qs('settings-btn');
    const settingsDropdown = qs('settings-dropdown');
    
    settingsBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        settingsDropdown.classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!settingsDropdown.contains(e.target)) {
            settingsDropdown.classList.remove('show');
        }
    });
    
    // Popup handling
    const showPopup = (btnId, popupId) => {
        const btn = qs(btnId);
        const popup = qs(popupId);
        
        if (!btn || !popup) return;
        
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            popup.classList.add('show');
            settingsDropdown.classList.remove('show');
        });
        
        const closeBtn = popup.querySelector('.close-btn');
        closeBtn?.addEventListener('click', () => popup.classList.remove('show'));
        
        window.addEventListener('click', (e) => {
            if (e.target === popup) popup.classList.remove('show');
        });
    };
    
    // Show popups
    showPopup('account-info-btn', 'account-info-popup');
    showPopup('change-email-btn', 'change-email-popup');
    showPopup('change-password-btn', 'change-password-popup');
    
    // Logout button
    qs('logout-btn').addEventListener('click', (e) => {
    e.preventDefault();
    window.location.href = '/NEW-TODAYS-MEAL/Register&Login/logout.php';
});

    
    // Account info edit/save functionality
    const editSaveBtn = qs('edit-save-btn');
    const accountSaveBtn = qs('account-save-btn');
    const accountInfoForm = qs('account-info-form');
    
    if (editSaveBtn && accountSaveBtn && accountInfoForm) {
        editSaveBtn.addEventListener('click', () => {
            const isEditing = editSaveBtn.textContent === 'Edit';
            editSaveBtn.textContent = isEditing ? 'Cancel' : 'Edit';
            editSaveBtn.classList.toggle('edit-btn', !isEditing);
            editSaveBtn.classList.toggle('cancel-btn', isEditing);
            
            // Toggle readonly on inputs
            const inputs = accountInfoForm.querySelectorAll('input');
            inputs.forEach(input => {
                input.readOnly = !isEditing;
            });
            
            accountSaveBtn.style.display = isEditing ? 'block' : 'none';
            editSaveBtn.style.display = isEditing ? 'none' : 'block';
        });
    }
    
    // Form submission handler for all forms
    const handleFormSubmit = (formId, successCallback) => {
        const form = qs(formId);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const loader = submitBtn.querySelector('.loader');
            const checkmark = submitBtn.querySelector('.checkmark');
            
            // Show loading state
            btnText.style.display = 'none';
            loader.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            // Submit form via AJAX
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success state
                    loader.style.display = 'none';
                    checkmark.style.display = 'inline-block';
                    
                    // Execute success callback if provided
                    if (typeof successCallback === 'function') {
                        successCallback(data);
                    }
                    
                    // Reset form state after delay
                    setTimeout(() => {
                        checkmark.style.display = 'none';
                        btnText.style.display = 'inline-block';
                        submitBtn.disabled = false;
                        
                        // For account info form, reset edit state
                        if (formId === 'account-info-form') {
                            const editSaveBtn = qs('edit-save-btn');
                            const accountSaveBtn = qs('account-save-btn');
                            
                            editSaveBtn.textContent = 'Edit';
                            editSaveBtn.classList.add('edit-btn');
                            editSaveBtn.classList.remove('cancel-btn');
                            editSaveBtn.style.display = 'block';
                            accountSaveBtn.style.display = 'none';
                            
                            // Make inputs readonly again
                            const inputs = form.querySelectorAll('input');
                            inputs.forEach(input => {
                                input.readOnly = true;
                            });
                        }
                        
                        // Close popup if not account info
                        if (formId !== 'account-info-form') {
                            form.closest('.popup').classList.remove('show');
                        }
                    }, 1500);
                } else {
                    // Show error
                    alert(data.message || 'Error updating information');
                    loader.style.display = 'none';
                    btnText.style.display = 'inline-block';
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                loader.style.display = 'none';
                btnText.style.display = 'inline-block';
                submitBtn.disabled = false;
            });
        });
    };
    
    // Handle all forms
    handleFormSubmit('account-info-form', (data) => {
        if (data.updatedData) {
            qs('name').value = data.updatedData.name || qs('name').value;
            qs('address').value = data.updatedData.address || qs('address').value;
            qs('business_name').value = data.updatedData.business_name || qs('business_name').value;
        }
    });
    
    handleFormSubmit('change-email-form', (data) => {
        qs('current-email').value = qs('new-email').value;
        qs('new-email').value = '';
        qs('confirm-new-email').value = '';
    });
    
    handleFormSubmit('change-password-form', (data) => {
        qs('old-password').value = '';
        qs('new-password').value = '';
        qs('confirm-new-password').value = '';
    });
    
    // Form validation
    const validateForm = (formId, fields) => {
        const form = qs(formId);
        if (!form) return;
        
        // Add input event listeners for real-time validation
        fields.forEach(field => {
            const input = form.querySelector(`[name="${field.name}"]`);
            const confirmInput = field.confirmWith ? form.querySelector(`[name="${field.confirmWith}"]`) : null;
            
            if (input) {
                input.addEventListener('input', () => {
                    if (confirmInput) {
                        const isValid = input.value === confirmInput.value;
                        input.classList.toggle('error', !isValid && input.value !== '');
                        confirmInput.classList.toggle('error', !isValid && confirmInput.value !== '');
                    }
                });
            }
            
            if (confirmInput) {
                confirmInput.addEventListener('input', () => {
                    const isValid = input.value === confirmInput.value;
                    input.classList.toggle('error', !isValid && input.value !== '');
                    confirmInput.classList.toggle('error', !isValid && confirmInput.value !== '');
                });
            }
        });
    };
    
    // Validate email change form
    validateForm('change-email-form', [
        { name: 'new-email', required: true, confirmWith: 'confirm-new-email' }
    ]);
    
    // Validate password change form
    validateForm('change-password-form', [
        { name: 'new-password', required: true, confirmWith: 'confirm-new-password' }
    ]);
    
    // Navigation active state
    const navLinks = document.querySelectorAll('.nav-item');
    const currentPath = window.location.pathname.split('/').pop() || 'dashboard.php';
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href').split('/').pop();
        if (linkPath === currentPath) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});
</script>