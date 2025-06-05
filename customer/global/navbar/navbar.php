<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
?>
<header class="header">
        <div class="logo">
            <h1 class="heading"> 𝓣𝓸𝓭𝓪𝔂'𝓼 𝓜𝓮𝓪𝓵 </h1>
        </div>
        <button class="mobile-menu-button">
            <img src="global/images/dropdown-menu.png" alt="menu" width="19" height="19" style="margin-right: 5px; vertical-align: middle;">
            My actions
        </button>
        <nav class="nav">
            <a href="\NEW-TODAYS-MEAL\customer\Home\index.php" class="nav-item">
                <span class="nav-icon home"></span>
                <span class="nav-text">Home</span>
            </a>
            <a href="\NEW-TODAYS-MEAL\customer\Custom_Order_Management\index.php" class="nav-item">
                <span class="nav-icon custom-order"></span>
                <span class="nav-text">My Customized Orders</span>
            </a>
            <a href="\NEW-TODAYS-MEAL\customer\Cart\cart.php" class="nav-item">
                <span class="nav-icon cart"></span>
                <span class="nav-text">Cart</span>
            </a>
            <a href="\NEW-TODAYS-MEAL\customer\Show_Caterers\index.php" class="nav-item">
                <span class="nav-icon meals"></span>
                <span class="nav-text">Order</span>
            </a>
            <a href="\NEW-TODAYS-MEAL\customer\support\support.php" class="nav-item">
                <span class="nav-icon support"></span>
                <span class="nav-text">Support</span>
            </a>
            
        </nav>
        <button class="settings-button" id="settings-btn">
            <img src="https://img.icons8.com/?size=100&id=S6mp3couBHct&format=png&color=6a4125" alt="Settings" class="settings-icon">
        </button>
    </header>
    <div class="settings-dropdown" id="settings-dropdown">
        <a href="#" class="settings-item" id="account-info-btn">
            <img src="https://img.icons8.com/windows/32/gender-neutral-user.png" class="settings-item-icon" alt="Account Info">
            <span>Account Info</span>
        </a>
        <a href="#" class="settings-item" id="saved-addresses-btn">
            <img src="https://img.icons8.com/forma-thin-sharp/24/address.png" class="settings-item-icon" alt="Saved Addresses">
            <span>Saved Addresses</span>
        </a>
        <a href="#" class="settings-item" id="change-email-btn">
            <img src="https://img.icons8.com/ios/100/new-post--v1.png" class="settings-item-icon" alt="Change Email">
            <span>Change Email</span>
        </a>
        <a href="#" class="settings-item" id="change-password-btn">
            <img src="https://img.icons8.com/windows/32/key.png" class="settings-item-icon" alt="Change Password">
            <span>Change Password</span>
        </a>
        <a href="#" class="settings-item">
            <svg class="settings-item-icon" viewBox="0 0 24 24">
                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            <span>Logout</span>
        </a>
    </div>

    <div class="popup" id="account-info-popup">
        <div class="popup-content">
            <button id="back-btn" class="close-btn">&times;</button>
            <h2>Account Info</h2>
            <form id="account-info-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="john@example.com" readonly>
                </div>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="John Doe" readonly>
                </div>
                <div class="form-group">
                    <label for="dob">Date of birth (optional)</label>
                    <input type="date" id="dob" name="dob" value="1990-01-01" readonly>
                </div>
                <div class="form-group">
                    <label>Gender (optional)</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="male" disabled> Male
                        </label>
                        <label>
                            <input type="radio" name="gender" value="female" checked disabled> Female
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" id="edit-save-btn" class="edit-btn bottom-right-btn">Edit</button>
                    <button type="submit" class="save-btn bottom-right-btn" id="account-save-btn" style="display: none;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="popup" id="saved-addresses-popup">
        <div class="popup-content">
            <button class="close-btn">&times;</button>
            <h2>Saved Addresses</h2>
            <form id="saved-addresses-form">
                <div class="form-group">
                    <label for="address1">Address 1</label>
                    <input type="text" id="address1" name="address1" value="123 Main St, City, Country" readonly>
                </div>
                <div class="form-group">
                    <label for="address2">Address 2 (Optional)</label>
                    <input type="text" id="address2" name="address2" value="" readonly>
                </div>
                <div class="form-actions">
                    <button type="button" class="edit-btn bottom-right-btn">Edit</button>
                    <button type="submit" class="save-btn bottom-right-btn" style="display: none;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="popup" id="change-email-popup">
        <div class="popup-content">
            <button class="close-btn">&times;</button>
            <h2>Change Email</h2>
            <form id="change-email-form">
                <div class="form-group">
                    <label for="current-email">Current Email</label>
                    <input type="email" id="current-email" name="current-email" value="john@example.com" readonly>
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
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="popup" id="change-password-popup">
        <div class="popup-content">
            <button class="close-btn">&times;</button>
            <h2>Change Password</h2>
            <form id="change-password-form">
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
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            const settingsBtn = document.getElementById('settings-btn');
            const settingsDropdown = document.getElementById('settings-dropdown');
            const accountInfoBtn = document.getElementById('account-info-btn');
            const accountInfoPopup = document.getElementById('account-info-popup');
            const backBtn = document.getElementById('back-btn');
            const editSaveBtn = document.getElementById('edit-save-btn');
            const accountInfoForm = document.getElementById('account-info-form');

            if (settingsBtn && settingsDropdown) {
                settingsBtn.addEventListener('click', function() {
                    settingsDropdown.classList.toggle('show');
                });

                window.addEventListener('click', function(e) {
                    if (!settingsBtn.contains(e.target) && !settingsDropdown.contains(e.target)) {
                        settingsDropdown.classList.remove('show');
                    }
                });
            }

            if (accountInfoBtn && accountInfoPopup && backBtn) {
                accountInfoBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    accountInfoPopup.classList.add('show');
                    settingsDropdown.classList.remove('show');
                });

                backBtn.addEventListener('click', function() {
                    accountInfoPopup.classList.remove('show');
                });

                window.addEventListener('click', function(e) {
                    if (e.target === accountInfoPopup) {
                        accountInfoPopup.classList.remove('show');
                    }
                });
            }

            const savedAddressesBtn = document.getElementById('saved-addresses-btn');
            const savedAddressesPopup = document.getElementById('saved-addresses-popup');
            const changeEmailBtn = document.getElementById('change-email-btn');
            const changeEmailPopup = document.getElementById('change-email-popup');
            const changePasswordBtn = document.getElementById('change-password-btn');
            const changePasswordPopup = document.getElementById('change-password-popup');

            function setupPopup(btn, popup) {
                if (btn && popup) {
                    const closeBtn = popup.querySelector('.close-btn');
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        popup.classList.add('show');
                        settingsDropdown.classList.remove('show');
                    });

                    closeBtn.addEventListener('click', function() {
                        popup.classList.remove('show');
                    });

                    window.addEventListener('click', function(e) {
                        if (e.target === popup) {
                            popup.classList.remove('show');
                        }
                    });
                }
            }

            setupPopup(savedAddressesBtn, savedAddressesPopup);
            setupPopup(changeEmailBtn, changeEmailPopup);
            setupPopup(changePasswordBtn, changePasswordPopup);

            const savedAddressesForm = document.getElementById('saved-addresses-form');
            const savedAddressesEditBtn = savedAddressesForm.querySelector('.edit-btn');
            const savedAddressesSaveBtn = savedAddressesForm.querySelector('.save-btn');

            if (savedAddressesEditBtn && savedAddressesSaveBtn) {
                savedAddressesEditBtn.addEventListener('click', function() {
                    if (this.textContent === 'Edit') {
                        this.textContent = 'Cancel';
                        this.classList.remove('edit-btn');
                        this.classList.add('cancel-btn');
                        enableFormEditing(savedAddressesForm, true);
                        savedAddressesSaveBtn.style.display = 'block';
                        this.style.display = 'none';
                    } else {
                        this.textContent = 'Edit';
                        this.classList.remove('cancel-btn');
                        this.classList.add('edit-btn');
                        enableFormEditing(savedAddressesForm, false);
                        savedAddressesSaveBtn.style.display = 'none';
                        this.style.display = 'block';
                    }
                });

                savedAddressesSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.classList.add('loading');
                    this.innerHTML = '<div class="loader"></div>';

                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.classList.add('saved');
                        this.innerHTML = '<span class="checkmark">✓</span>';

                        setTimeout(() => {
                            this.textContent = 'Save Changes';
                            this.classList.remove('saved');
                            this.style.display = 'none';
                            savedAddressesEditBtn.textContent = 'Edit';
                            savedAddressesEditBtn.classList.remove('cancel-btn');
                            savedAddressesEditBtn.classList.add('edit-btn');
                            savedAddressesEditBtn.style.display = 'block';
                            enableFormEditing(savedAddressesForm, false);
                        }, 1500);
                    }, 1500);
                });
            }

            const changeEmailForm = document.getElementById('change-email-form');
            const changePasswordForm = document.getElementById('change-password-form');

            function setupFormSubmission(form) {
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const submitBtn = this.querySelector('button[type="submit"]');
                        submitBtn.classList.add('loading');
                        submitBtn.innerHTML = '<div class="loader"></div>';

                        setTimeout(() => {
                            submitBtn.classList.remove('loading');
                            submitBtn.classList.add('saved');
                            submitBtn.innerHTML = '<span class="checkmark">✓</span>';

                            setTimeout(() => {
                                submitBtn.textContent = 'Save Changes';
                                submitBtn.classList.remove('saved');
                                form.reset();
                            }, 1500);
                        }, 1500);
                    });
                }
            }

            setupFormSubmission(changeEmailForm);
            setupFormSubmission(changePasswordForm);

            if (editSaveBtn && accountInfoForm) {
                const accountSaveBtn = document.getElementById('account-save-btn');
                editSaveBtn.addEventListener('click', function() {
                    if (this.textContent === 'Edit') {
                        this.textContent = 'Cancel';
                        this.classList.remove('edit-btn');
                        this.classList.add('cancel-btn');
                        enableFormEditing(accountInfoForm, true);
                        accountSaveBtn.style.display = 'block';
                        this.style.display = 'none';
                    } else {
                        this.textContent = 'Edit';
                        this.classList.remove('cancel-btn');
                        this.classList.add('edit-btn');
                        enableFormEditing(accountInfoForm, false);
                        accountSaveBtn.style.display = 'none';
                        this.style.display = 'block';
                    }
                });

                accountSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.classList.add('loading');
                    this.innerHTML = '<div class="loader"></div>';

                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.classList.add('saved');
                        this.innerHTML = '<span class="checkmark">✓</span>';

                        setTimeout(() => {
                            this.textContent = 'Save Changes';
                            this.classList.remove('saved');
                            this.style.display = 'none';
                            editSaveBtn.textContent = 'Edit';
                            editSaveBtn.classList.remove('cancel-btn');
                            editSaveBtn.classList.add('edit-btn');
                            enableFormEditing(accountInfoForm, false);
                        }, 1500);
                    }, 1500);
                });
            }

            function enableFormEditing(form, enable) {
                const inputs = form.querySelectorAll('input:not([name="email"])');
                inputs.forEach(input => {
                    input.readOnly = !enable;
                    if (input.type === 'radio') {
                        input.disabled = !enable;
                    }
                });
            }
        });
    </script>



<style>

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
  background-color: #fff7e5;
  color: #8b4513;
  line-height: 1.6;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 16px;
}

.nav-link, .nav-item {
  display: flex;
  align-items: center;
  text-decoration: none;
  transition: color 0.3s ease;
}

.nav-link {
  color: #a0522d;
  margin-bottom: 16px;
  cursor: pointer;
  transition-duration: 0.2s;
}

.nav-link:hover {
  color: #8b4513;
}

.icon, .nav-icon {
  width: 16px;
  height: 16px;
  margin-right: 8px;
}

.nav-icon {
  width: 17px;
  height: 17px;
  background-size: contain;
  background-repeat: no-repeat;
  transition: background-color 0.3s ease;
}

.header {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  height: 64px;
  padding: 0 55px;
  background-color: #6a4125;
  margin-bottom: 45px;
}

.logo {
  grid-column: 1;
}

.logo .heading {
  font-weight: 700;
  color: #f5e0c2;
  font-size: 20.4px;
  margin: 0;
}

.nav {
  grid-column: 2;
  display: flex;
  justify-content: flex-end;
  margin-right: 10px;
  flex-wrap: nowrap;
  overflow-x: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
  position: relative;
}

.nav::-webkit-scrollbar {
  display: none;
}

.nav-item {
  color: #f5e0c2;
  font-weight: 500;
  font-size: 15px;
  padding: 0 12px 0 21px;
  height: 36px;
  border-radius: 6px;
  margin-right: 10px;
  position: relative;
  overflow: hidden;
}

.nav-item::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 1.2px;
  background-color: #f5e0c2;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
}

.nav-item:hover::after {
  transform: scaleX(1);
}

.nav-item.active::after {
  transform: scaleX(0);
}

.nav-item:hover,
.nav-item.active {
  color: #fff7e5;
}

.nav-icon.home {
  background-image: url(https://img.icons8.com/?size=100&id=2797&format=png&color=FFFFFF);
}

.nav-icon.custom-order {
  background-image: url(https://img.icons8.com/?size=100&id=59857&format=png&color=FFFFFF);
}

.nav-icon.cart {
  background-image: url(https://img.icons8.com/?size=100&id=LhRbsuC35iCh&format=png&color=FFFFFF);
}

.nav-icon.meals {
  background-image: url(https://img.icons8.com/?size=100&id=qP17ftfJdw0t&format=png&color=FFFFFF);
}

.nav-icon.support {
  background-image: url(https://img.icons8.com/?size=100&id=112508&format=png&color=FFFFFF);
}

.settings-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: transform 0.3s ease;
  background-color: #f5e0c2;
}

.settings-icon {
  width: 26px;
  height: 26px;
  animation: spin 10s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.settings-button:hover .settings-icon {
  animation: spin 3s linear infinite;
}

.settings-dropdown {
  position: absolute;
  top: 48px;
  right: 32px;
  background-color: #ffffff;
  border-radius: 3px;
  display: none;
  flex-direction: column;
  min-width: 230px;
  height: 210px;
  padding: 5px 0;
}

.settings-dropdown.show {
  display: flex;
}

.settings-item {
  display: flex;
  align-items: center;
  padding: 10px 16px;
  text-decoration: none;
  color: #372316;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-size: 10px;
  font-weight: bold;
}

.settings-item:hover {
  background-color: #e9ecefbd;
}

.settings-item-icon {
  width: 19px;
  height: 19px;
  margin-right: 12px;
  fill: currentColor;
}

.popup {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.popup.show {
  display: flex;
}

.popup-content {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 470px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  padding-bottom: 70px;
}

.back-btn, .close-btn {
  position: absolute;
  top: 10px;
  left: 20px;
  background: none;
  border: none;
  color: #6a4125;
  cursor: pointer;
  transition: color 0.3s ease;
}

.back-btn {
  font-size: 30px;
  left: 20px;
  top: 12px;
  color: #000000;
}

.close-btn {
  font-size: 24px;
  left: 15px;
}

.close-btn:hover {
  color: #e57e24;
}

.popup h2 {
  color: #000000;
  font-size: 21px;
  margin-top: 20px;
  margin-bottom: 30px;
  text-align: center;
  padding: 0 30px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #332f2c;
  font-size: 10px;
}

.form-group input {
  width: 100%;
  padding: 8px;
  border: none;
  background-color: rgba(128, 128, 128, 0.267);
  font-size: 12px;
}

.form-group input[readonly] {
  background-color: #f0f0f04d;
  color: #1f1d1dcc;
}

.radio-group {
  display: flex;
  justify-content: flex-start;
  gap: 20px;
}

.radio-group label {
  display: flex;
  align-items: center;
  gap: 5px;
}

.edit-btn, .save-btn, .cancel-btn {
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.edit-btn, .cancel-btn {
  background-color: #ffffff;
  border: 1px solid #345028;
  color: #345028;
}

.edit-btn:hover, .cancel-btn:hover {
  background-color: #345028;
  color: #ffffff;
}

.cancel-btn {
  background-color: #f0f0f0;
  color: #333333;
  border-color: #333333;
}

.cancel-btn:hover {
  background-color: #e0e0e0;
  color: #333333;
}

.save-btn {
  background-color: #345028;
  color: #ffffff;
  border: none;
  padding: 10px 20px;
  transition: transform 0.3s ease;
}

.save-btn:hover {
  background-color: #2a3f20;
  transform: translateY(-2px);
}

.save-btn:active {
  transform: translateY(0);
}

.save-btn.loading, .save-btn.saved {
  cursor: not-allowed;
}

.save-btn.loading {
  background-color: #f0f0f0;
  color: #333333;
}

.save-btn.saved {
  background-color: #4caf50;
}

.loader, .checkmark {
  display: inline-block;
}

.loader {
  border: 2px solid #f3f3f3;
  border-top: 2px solid #345028;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  animation: spin 1s linear infinite;
}

.checkmark {
  color: #ffffff;
  font-size: 18px;
}

#change-email-form, #change-password-form {
  padding-top: 20px;
}

#change-email-form .save-btn, #change-password-form .save-btn {
  position: static;
  width: 100%;
  margin-top: 20px;
}

.bottom-right-btn {
  position: absolute;
  bottom: 20px;
  right: 20px;
}

.custom-order-btn {
  display: block;
  width: 100%;
  padding: 12px;
  background: #6a4125;
  color: white;
  text-decoration: none;
  text-align: center;
  border-radius: 8px;
  font-weight: 500;
  margin-bottom: 8px;
  transition: background-color 0.2s;
}

.custom-order-btn:hover {
  background: #d35400;
}

.mobile-menu-button {
  display: none;
  background: none;
  border: none;
  color: #fff7e5;
  font-size: 15px;
  cursor: pointer;
  padding: 8px 20px;
  font-weight: 600;
  transition: color 0.3s ease;
  position: relative;
  overflow: hidden;
}

.mobile-menu-button::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 1.2px;
  background-color: #fff7e5;
  transform: translateX(-100%);
  transition: transform 0.3s ease;
}

.mobile-menu-button:hover {
  color: #fff7e5;
}

.mobile-menu-button:hover::after {
  transform: translateX(0);
}

.mobile-menu-button:hover img {
  filter: invert(57%) sepia(83%) saturate(1395%) hue-rotate(346deg) brightness(96%) contrast(95%);
}

@media (max-width: 960px) {
  .header {
    padding: 0 10px;
    height: 56px;
    width: 100%;
  }

  .logo .heading, .nav-item {
    font-size: 16px;
  }

  .mobile-menu-button {
    display: block;
    grid-column: 2;
    justify-self: end;
    margin-right: 30px;
  }

  .nav {
    width: 25%;
    display: none;
    position: absolute;
    top: 56px;
    left: 50%;
    right: 0;
    background: white;
    flex-direction: column;
    padding: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    z-index: 1000;
    min-width: 180px;
  }

  .nav.show {
    display: flex;
  }

  .nav-item {
    padding: 6px;
    font-size: 13px;
    margin: 0;
    border-radius: 0;
    justify-content: flex-start;
  }

  .settings-button {
    width: 22px;
    height: 22px;
  }

  .settings-icon {
    width: 22px;
    height: 22px;
  }

  .settings-dropdown {
    right: 10px;
    top: 44px;
    min-width: 160px;
    height: auto;
  }

  .settings-item {
    padding: 8px 12px;
  }

  .settings-item-icon {
    width: 16px;
    height: 16px;
    margin-right: 8px;
  }
}

@media (max-width: 768px) {
  .header {
    padding: 0 10px;
  }

  .logo .heading {
    font-size: 16px;
  }

  .nav-item {
    padding: 0 6px;
    font-size: 9px;
  }
}

@media (max-width: 480px) {
  .popup-content {
    width: 95%;
    padding: 15px;
  }

  .popup h2 {
    font-size: 18px;
    margin-bottom: 20px;
    padding: 0 25px;
  }

  .form-group label, .form-pop-group label {
    font-size: 9px;
  }

  .form-group input, .form-pop-group input {
    font-size: 11px;
  }

  .edit-btn, .cancel-btn, .save-btn {
    font-size: 12px;
    padding: 6px 12px;
  }

  .close-btn {
    font-size: 20px;
    top: 8px;
    left: 12px;
  }

  .bottom-right-btn {
    bottom: 15px;
    right: 15px;
  }
}

@media (max-width: 340px) {
  .mobile-menu-button {
    font-size: 10px;
  }
}

</style>