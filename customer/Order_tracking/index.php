<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Track Connect</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
     <header class="header">
        <div class="logo">
          <h1 class="heading"> 𝓣𝓸𝓭𝓪𝔂'𝓼 𝓜𝓮𝓪𝓵 </h1>
        </div>
        <nav class="nav">
          <a href="../2-Home Codes/index.html" class="nav-item">
            <span class="nav-icon home"></span>
            <span class="nav-text">Home</span>
          </a>
          <a href="../7- Custom Order Requested/index.html" class="nav-item active">
            <span class="nav-icon custom-order"></span>
            <span class="nav-text">Customized Order</span>
          </a>
          <a href="../9-Cart/index.html" class="nav-item">
            <span class="nav-icon cart"></span>
            <span class="nav-text">Cart</span>
          </a>
          <a href="../Meal Management/index.html" class="nav-item">
            <span class="nav-icon meals"></span>
            <span class="nav-text">Order</span>
          </a>
          <a href="../support/support.html" class="nav-item">
            <span class="nav-icon support"></span>
            <span class="nav-text">Support</span>
          </a>
        </nav>
        <button class="settings-button" id="settings-btn">
          <img src="../Custom_Order_Management/icons8-setting-32.png" alt="Settings" class="settings-icon">
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
      
    <div class="container">
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="tab-btn active" onclick="filterOrders('all')">All Orders</button>
            <button class="tab-btn" onclick="filterOrders('preparing')">Preparing</button>
            <button class="tab-btn" onclick="filterOrders('on-the-way')">On the way</button>
            <button class="tab-btn" onclick="filterOrders('delivered')">Delivered</button>
        </div>

<!-- Orders List -->
<div class="orders-list">

  <!-- Order 1 - Preparing -->
  <div class="order-card" data-status="preparing">
    <div class="order-header">
      <div class="restaurant-icon">
        <img alt="El-Tahrir Restaurant" src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=48&amp;h=48&amp;fit=crop&amp;crop=center" />
      </div>
      <div class="order-info">
        <h3>El-Tahrir Restaurant <span class="order-time"> • Today • 7:54 AM</span></h3>
        <p class="order-id">Order ID: 112748123</p>
        <div class="items-list">
          <div class="item-preview">
            <span class="item-name">1x Koshary</span>
            <span class="item-price">EGP 65</span>
          </div>
          <div class="item-preview">
            <span class="item-name">2x Molokhia with Rice</span>
            <span class="item-price">EGP 85</span>
          </div>
          <div class="item-preview">
            <span class="item-name">1x Mixed Grill Platter</span>
            <span class="item-price">EGP 195</span>
          </div>
        </div>
      </div>
      <div class="status-wrap">
        <div class="status-badge preparing">Preparing</div>
        <button class="items-toggle" onclick="toggleItemsDropdown(event)">3 items <i class="fas fa-chevron-down"></i></button>
      </div>
    </div>
    <div class="order-actions">
      <button class="btn-secondary" 
              data-customer-address="45 El Gomhoria St, Dokki" 
              data-customer-name="Mariam Yousef" 
              data-delivery-fee="EGP 20" 
              data-delivery-initials="AH" 
              data-delivery-name="Ahmed Hassan" 
              data-items='[{"name":"1x Koshary","price":"EGP 65"},{"name":"2x Molokhia with Rice","price":"EGP 85"},{"name":"1x Mixed Grill Platter","price":"EGP 195"}]' 
              data-status="preparing" 
              data-subtotal="EGP 335" 
              data-total="EGP 355" 
              onclick="showOrderDetails(this)">
        View Details
      </button>
    </div>
  </div>

  <!-- Order 2 - On the way -->
  <div class="order-card" data-status="on-the-way">
    <div class="order-header">
      <div class="restaurant-icon">
        <img alt="Alexandria Kitchen" src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=48&amp;h=48&amp;fit=crop&amp;crop=center" />
      </div>
      <div class="order-info">
        <h3>Alexandria Kitchen <span class="order-time"> • Today • 7:54 AM</span></h3>
        <p class="order-id">Order ID: 112748124</p>
        <div class="items-list">
          <div class="item-preview">
            <span class="item-name">1x Mahshi Wara Enab</span>
            <span class="item-price">EGP 75</span>
          </div>
          <div class="item-preview">
            <span class="item-name">2x Om Ali</span>
            <span class="item-price">EGP 55</span>
          </div>
          <div class="item-preview">
            <span class="item-name">1x Koshary Special</span>
            <span class="item-price">EGP 85</span>
          </div>
        </div>
      </div>
      <div class="status-wrap">
        <div class="status-badge on-the-way">On the way</div>
        <button class="items-toggle" onclick="toggleItemsDropdown(event)">3 items <i class="fas fa-chevron-down"></i></button>
      </div>
    </div>
    <div class="order-actions">
      <button class="btn-secondary" 
              data-customer-address="12 Tahrir Square, Downtown" 
              data-customer-name="Omar El-Sharif" 
              data-delivery-fee="EGP 25" 
              data-delivery-initials="AH" 
              data-delivery-name="Ahmed Hassan" 
              data-items='[{"name":"1x Mahshi Wara Enab","price":"EGP 75"},{"name":"2x Om Ali","price":"EGP 55"},{"name":"1x Koshary Special","price":"EGP 85"}]' 
              data-status="on-the-way" 
              data-subtotal="EGP 215" 
              data-total="EGP 240" 
              onclick="showOrderDetails(this)">
        View Details
      </button>
    </div>
  </div>

  <!-- Order 3 - Delivered -->
  <div class="order-card" data-status="delivered">
    <div class="order-header">
      <div class="restaurant-icon">
        <img alt="Cairo Cuisine" src="https://images.unsplash.com/photo-1559339352-11d035aa65de?w=48&amp;h=48&amp;fit=crop&amp;crop=center" />
      </div>
      <div class="order-info">
        <h3>Cairo Cuisine <span class="order-time"> • Today • 7:54 AM</span></h3>
        <p class="order-id">Order ID: 112748126</p>
        <div class="items-list">
          <div class="item-preview">
            <span class="item-name">1x Mahshi Wara Enab</span>
            <span class="item-price">EGP 75</span>
          </div>
          <div class="item-preview">
            <span class="item-name">2x Om Ali</span>
            <span class="item-price">EGP 55</span>
          </div>
          <div class="item-preview">
            <span class="item-name">1x Koshary Special</span>
            <span class="item-price">EGP 85</span>
          </div>
        </div>
      </div>
      <div class="status-wrap">
        <div class="status-badge delivered">Delivered</div>
        <button class="items-toggle" onclick="toggleItemsDropdown(event)">3 items <i class="fas fa-chevron-down"></i></button>
      </div>
    </div>
    <div class="order-actions delivered-actions">
      <button class="btn-secondary small" 
              data-customer-address="12 Tahrir Square, Downtown" 
              data-customer-name="Omar El-Sharif" 
              data-delivery-fee="EGP 25" 
              data-items='[{"name":"1x Mahshi Wara Enab","price":"EGP 75"},{"name":"2x Om Ali","price":"EGP 55"},{"name":"1x Koshary Special","price":"EGP 85"}]' 
              data-restaurant="Cairo Cuisine" 
              data-status="delivered" 
              data-subtotal="EGP 215" 
              data-total="EGP 240" 
              onclick="showOrderDetails(this)">
        View Details
      </button>
      <button class="btn-primary" 
              data-restaurant="Cairo Cuisine" 
              onclick="showRatingModal(this)">
        Rate Order
      </button>
    </div>
  </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2>Order Details</h2>
                <button class="close" onclick="closeOrderModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="customer-info">
                    <h3>Customer Information</h3>
                    <div class="info-item">
                        <span class="icon"><i class="fas fa-user"></i></span>
                        <span id="customerName">Mariam Yousef</span>
                    </div>
                    <div class="info-item">
                        <span class="icon"><i class="fas fa-map-marker-alt"></i></span>
                        <span id="customerAddress">45 El Gomhoria St, Dokki, Giza</span>
                    </div>
                </div>

                <div class="delivery-person" id="deliveryPersonSection">
                    <h3>Delivery Person</h3>
                    <div class="delivery-info">
                        <div class="delivery-avatar">AH</div>
                        <span class="delivery-name">Ahmed Hassan</span>
                        <button class="call-btn"><i class="fas fa-phone"></i> Call</button>
                    </div>
                </div>

                <div class="order-items">
                    <h3>Order Items</h3>
                    <div id="itemsList">
                        <div class="item">
                            <span class="item-name">1x Koshary</span>
                            <span class="item-price">EGP 65</span>
                        </div>
                        <div class="item">
                            <span class="item-name">2x Molokhia with Rice</span>
                            <span class="item-price">EGP 85</span>
                        </div>
                        <div class="item">
                            <span class="item-name">1x Mixed Grill Platter</span>
                            <span class="item-price">EGP 195</span>
                        </div>
                    </div>
                </div>

                <div class="payment-summary">
                    <h3>Payment Summary</h3>
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span id="subtotal">EGP 335</span>
                    </div>
                    <div class="summary-item">
                        <span>Delivery Fee</span>
                        <span id="deliveryFee">EGP 20</span>
                    </div>
                    <div class="summary-item total">
                        <span>Total</span>
                        <span id="total">EGP 355</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <!-- Rating Modal -->
    <div class="modal" id="ratingModal" onclick="closeModal(event)">
      <div class="modal-content rating-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
          <button class="close-btn" onclick="closeRatingModal()">×</button>
        </div>
        <div class="modal-body">
          <h2>Rate Your Order</h2>
          <p>How was your experience with <span id="restaurantName">Cairo Cuisine</span>?</p>
          <div class="star-rating">
            <span class="star" data-rating="1" onclick="setRating(1)">☆</span>
            <span class="star" data-rating="2" onclick="setRating(2)">☆</span>
            <span class="star" data-rating="3" onclick="setRating(3)">☆</span>
            <span class="star" data-rating="4" onclick="setRating(4)">☆</span>
            <span class="star" data-rating="5" onclick="setRating(5)">☆</span>
          </div>
          <div class="rating-actions">
            <button class="btn-secondary" onclick="closeRatingModal()">Cancel</button>
            <button class="btn-primary" onclick="submitRating()">Submit Rating</button>
          </div>
        </div>
      </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
