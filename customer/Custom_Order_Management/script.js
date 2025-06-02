// Order management functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeFilters();
    initializeOrderDetails();
});

// Initialize order details visibility
function initializeOrderDetails() {
    // Set all order details to be hidden by default
    const orderDetails = document.querySelectorAll('.order-details');
    orderDetails.forEach(detail => {
        detail.classList.add('hidden');
    });
    
    // Set all toggle icons to the collapsed state (rotated)
    const toggleIcons = document.querySelectorAll('.toggle-icon');
    toggleIcons.forEach(icon => {
        icon.classList.add('rotated');
    });
    
    // Set all toggle text to "Show Details"
    const toggleTexts = document.querySelectorAll('.toggle-text');
    toggleTexts.forEach(text => {
        text.textContent = 'Show Details';
    });
}

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', handleSearch);
}

function handleSearch(event) {
    const searchTerm = event.target.value.toLowerCase().trim();
    const orderCards = document.querySelectorAll('.order-card');
    
    orderCards.forEach(card => {
        const searchText = card.getAttribute('data-search').toLowerCase();
        const statusMessage = card.querySelector('.status-message')?.textContent.toLowerCase() || '';
        const kitchenName = card.querySelector('.kitchen-name')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.description-text')?.textContent.toLowerCase() || '';
        
        const matchesSearch = searchText.includes(searchTerm) || 
                            statusMessage.includes(searchTerm) ||
                            kitchenName.includes(searchTerm) || 
                            description.includes(searchTerm);
        
        const matchesFilter = checkFilterMatch(card);
        
        if (matchesSearch && matchesFilter) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });

        checkAndShowNoOrdersMessage();
}

// Filter functionality
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', handleFilter);
    });
}

function handleFilter(event) {
    const filterValue = event.target.getAttribute('data-filter');
    
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filter orders
    const orderCards = document.querySelectorAll('.order-card');
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    orderCards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        const searchText = card.getAttribute('data-search').toLowerCase();
        
        // Update this condition to properly match 'approved' status
        const matchesFilter = filterValue === 'all' || 
                            (filterValue === 'approved' && cardStatus === 'approved') ||
                            (filterValue !== 'approved' && cardStatus === filterValue);
        
        const matchesSearch = searchText.includes(searchTerm);
        
        if (matchesFilter && matchesSearch) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });

        checkAndShowNoOrdersMessage();
}

function checkFilterMatch(card) {
    const activeFilter = document.querySelector('.filter-btn.active');
    const filterValue = activeFilter.getAttribute('data-filter');
    const cardStatus = card.getAttribute('data-status');
    
    return filterValue === 'all' || cardStatus === filterValue;
}

// Toggle order details
function toggleOrderDetails(orderId) {
    const detailsElement = document.getElementById(`orderDetails${orderId}`);
    const toggleText = document.getElementById(`toggleText${orderId}`);
    const toggleIcon = document.getElementById(`toggleIcon${orderId}`);
    
    if (detailsElement.classList.contains('hidden')) {
        detailsElement.classList.remove('hidden');
        toggleText.textContent = 'Hide Details';
        toggleIcon.classList.remove('rotated');
    } else {
        detailsElement.classList.add('hidden');
        toggleText.textContent = 'Show Details';
        toggleIcon.classList.add('rotated');
    }
}

// Price management functions
function acceptPrice(orderIndex, dbOrderId) {
    // Find the order card by its index (orderIndex is 1-based)
    const orderCards = document.querySelectorAll('.order-card');
    if (orderIndex < 1 || orderIndex > orderCards.length) return;
    
    const orderCard = orderCards[orderIndex - 1];
    
    // Update order status
    orderCard.setAttribute('data-status', 'price_accepted');
    
    // Update badge
    const badge = orderCard.querySelector('.badge');
    badge.className = 'badge badge-price-accepted';
    badge.textContent = 'Price accepted';
    
    // Update status message
    const statusMessage = orderCard.querySelector('.status-message');
    statusMessage.textContent = 'Price accepted - Ready for checkout';
    
    // Replace price quote section with invoice
    const priceQuoteSection = orderCard.querySelector('.price-quote-section');
    if (priceQuoteSection) {
        const priceAmount = priceQuoteSection.querySelector('.price-amount').textContent;
        const amount = parseFloat(priceAmount.replace('EGP ', '').replace(',', ''));
        const tax = amount * 0.07;
        const total = amount + tax + 50;
        
        priceQuoteSection.innerHTML = `
            <div class="section-header">
                <img src="https://img.icons8.com/?size=100&id=106571&format=png&color=000000" alt="Receipt" class="section-icon">
                <h4>Order Invoice</h4>
            </div>
            
            <div class="invoice-details">
                <div class="invoice-row">
                    <span>Custom Order Price:</span>
                    <span class="invoice-amount">EGP ${amount.toFixed(2)}</span>
                </div>
                <div class="invoice-row">
                    <span>Tax (7%):</span>
                    <span class="invoice-amount">EGP ${tax.toFixed(2)}</span>
                </div>
                <div class="invoice-row">
                    <span>Delivery Fee:</span>
                    <span class="invoice-amount">EGP 50.00</span>
                </div>
                <hr class="invoice-divider">
                <div class="invoice-row invoice-total">
                    <span>Total Amount:</span>
                    <span class="invoice-amount">EGP ${total.toFixed(2)}</span>
                </div>
            </div>
            
            <a href="../Custom_Order_Management/Payment/index.html?order_id=${dbOrderId}" class="btn btn-checkout">
                <img src="https://img.icons8.com/?size=100&id=86620&format=png&color=FFFFFF" alt="Credit Card">
                Proceed to Checkout
            </a>
        `;
        priceQuoteSection.className = 'invoice-section';
    }
    
    // Send AJAX request to update the database
    updateOrderStatus(dbOrderId, 'price_accepted');
    
    showNotification('Price accepted successfully! You can now proceed to checkout.', 'success');
}

function rejectPrice(orderIndex, dbOrderId) {
    // Find the order card by its index (orderIndex is 1-based)
    const orderCards = document.querySelectorAll('.order-card');
    if (orderIndex < 1 || orderIndex > orderCards.length) return;
    
    const orderCard = orderCards[orderIndex - 1];
    
    // Send AJAX request to update the database
    updateOrderStatus(dbOrderId, 'rejected')
        .then(() => {
            // Remove the order card from the UI after successful deletion
            orderCard.remove();
            
            // Check if no orders left
            if (document.querySelectorAll('.order-card').length === 0) {
                showNoOrdersMessage();
            }
            
            showNotification('Order rejected and removed successfully.', 'success');
        })
        .catch(error => {
            console.error('Error rejecting order:', error);
            showNotification('Failed to reject order. Please try again.', 'error');
        });
}

function showNoOrdersMessage() {
    const ordersList = document.getElementById('ordersList');
    ordersList.innerHTML = `
        <div class="no-orders">
            <img src="https://img.icons8.com/?size=100&id=13030&format=png&color=957B6A" alt="No orders" class="no-orders-icon">
            <h3>No Custom Orders Found</h3>
            <p>You haven't placed any custom orders yet.</p>
        </div>
    `;
}

// Modify updateOrderStatus to return a Promise
function updateOrderStatus(orderId, status) {
    // Create a FormData object to send the data
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', status);
    
    // Return the fetch promise
    return fetch('update_order_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Failed to update order status');
        }
        return data;
    });
}

function deleteRejectedOrder(orderIndex, dbOrderId) {
    // Find the order card by its index (orderIndex is 1-based)
    const orderCards = document.querySelectorAll('.order-card');
    if (orderIndex < 1 || orderIndex > orderCards.length) return;
    
    const orderCard = orderCards[orderIndex - 1];
    
    // Send AJAX request to update the database
    updateOrderStatus(dbOrderId, 'delete_rejected')  // Changed from 'kitchen_rejected' to 'delete_rejected'
        .then(() => {
            // Remove the order card from the UI after successful deletion
            orderCard.remove();
            
            // Check if no orders left
            if (document.querySelectorAll('.order-card').length === 0) {
                showNoOrdersMessage();
            }
            
            showNotification('Order deleted successfully.', 'success');
        })
        .catch(error => {
            console.error('Error deleting order:', error);
            showNotification('Failed to delete order. Please try again.', 'error');
        });
}

function proceedToCheckout(orderId) {
    window.location.href = `../Custom_Order_Management/Payment/index.html?order_id=${orderId}`;
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    `;
    
    // Add notification styles if not already present
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                max-width: 400px;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-out;
            }
            
            .notification-success {
                background-color: #D1FAE5;
                border: 1px solid #BBF7D0;
                color: #065F46;
            }
            
            .notification-error {
                background-color: #FEE2E2;
                border: 1px solid #FECACA;
                color: #991B1B;
            }
            
            .notification-info {
                background-color: #DBEAFE;
                border: 1px solid #BFDBFE;
                color: #1E40AF;
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 16px;
                gap: 12px;
            }
            
            .notification-message {
                flex: 1;
                font-size: 14px;
                font-weight: 500;
            }
            
            .notification-close {
                background: none;
                border: none;
                cursor: pointer;
                padding: 2px;
                border-radius: 4px;
                color: inherit;
                opacity: 0.7;
                transition: opacity 0.2s;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
            
            .notification-close svg {
                width: 16px;
                height: 16px;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function checkAndShowNoOrdersMessage() {
    const orderCards = document.querySelectorAll('.order-card:not(.hidden)');
    const ordersList = document.getElementById('ordersList');
    
    if (orderCards.length === 0) {
        // Check if the no-orders message already exists
        if (!document.querySelector('.no-orders-filtered')) {
            ordersList.innerHTML += `
                <div class="no-orders no-orders-filtered">
                    <img src="https://img.icons8.com/?size=100&id=13030&format=png&color=957B6A" alt="No orders" class="no-orders-icon">
                    <h3>No Orders Found</h3>
                    <p>There are no orders matching your current filter.</p>
                </div>
            `;
        }
    } else {
        // Remove the message if it exists and orders are visible
        const noOrdersMessage = document.querySelector('.no-orders-filtered');
        if (noOrdersMessage) {
            noOrdersMessage.remove();
        }
    }
}