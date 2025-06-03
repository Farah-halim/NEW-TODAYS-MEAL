/**
 * Delivery management functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Setup status update buttons
    setupStatusUpdateButtons();
    
    // Setup search functionality
    setupSearchFunctionality();
});

/**
 * Setup status update buttons
 */
function setupStatusUpdateButtons() {
    const updateButtons = document.querySelectorAll('.btn-update-status');
    
    if (updateButtons.length > 0) {
        updateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const deliveryId = this.dataset.deliveryId;
                const status = this.dataset.status;
                const currentStatus = this.dataset.currentStatus;
                
                // Set values for confirmation modal
                document.getElementById('confirm-delivery-id').value = deliveryId;
                document.getElementById('confirm-status').value = status;
                
                // Update modal content based on status
                const modalTitle = document.querySelector('#statusConfirmModal .modal-title');
                const modalBody = document.querySelector('#statusConfirmModal .modal-body p');
                
                let statusText;
                switch (status) {
                    case 'in-progress':
                        statusText = 'picked up';
                        break;
                    case 'completed':
                        statusText = 'delivered';
                        break;
                    case 'cancelled':
                        statusText = 'cancelled';
                        break;
                    case 'delayed':
                        statusText = 'delayed';
                        break;
                    default:
                        statusText = status;
                }
                
                modalTitle.textContent = `Mark as ${capitalizeFirstLetter(statusText)}`;
                modalBody.textContent = `Are you sure you want to mark this delivery as ${statusText}? This action cannot be undone.`;
                
                // Show confirmation modal
                const modal = new bootstrap.Modal(document.getElementById('statusConfirmModal'));
                modal.show();
            });
        });
        
        // Setup confirmation button
        setupStatusUpdateConfirmation();
    }
}

/**
 * Setup status update confirmation
 */
function setupStatusUpdateConfirmation() {
    const confirmButton = document.getElementById('confirmStatusUpdate');
    
    if (confirmButton) {
        confirmButton.addEventListener('click', function() {
            const deliveryId = document.getElementById('confirm-delivery-id').value;
            const status = document.getElementById('confirm-status').value;
            
            // Create form data
            const formData = new FormData();
            formData.append('delivery_id', deliveryId);
            formData.append('status', status);
            
            // Send update request
            fetch('api/update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('statusConfirmModal'));
                    modal.hide();
                    
                    // Show success notification
                    showNotification('Status Updated', data.message, 'success');
                    
                    // Reload page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification('Error', data.message || 'Failed to update status', 'danger');
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                showNotification('Error', 'Failed to update status. Please try again.', 'danger');
            });
        });
    }
}

/**
 * Show a notification toast
 * 
 * @param {string} title The notification title
 * @param {string} message The notification message
 * @param {string} type The notification type (success, danger, warning, info)
 */
function showNotification(title, message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
    toast.show();
    
    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

/**
 * Setup search functionality
 */
function setupSearchFunctionality() {
    const searchForm = document.getElementById('deliverySearchForm');
    const searchInput = document.getElementById('searchQuery');
    const searchResults = document.getElementById('searchResults');
    
    if (searchForm && searchInput && searchResults) {
        // Add event listener to form submission
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const query = searchInput.value.trim();
            
            if (query.length > 0) {
                performSearch(query);
            }
        });
        
        // Add event listener to input for real-time search (optional)
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length > 2) {
                performSearch(query);
            } else if (query.length === 0) {
                // Clear results if search is empty
                searchResults.innerHTML = '';
                // Hide search results container
                document.getElementById('searchResultsContainer').style.display = 'none';
                // Show default deliveries
                document.getElementById('defaultDeliveriesContainer').style.display = 'block';
            }
        });
    }
}

/**
 * Perform search and display results
 * 
 * @param {string} query Search query
 */
function performSearch(query) {
    const searchResults = document.getElementById('searchResults');
    const resultsContainer = document.getElementById('searchResultsContainer');
    const defaultContainer = document.getElementById('defaultDeliveriesContainer');
    
    // Show loading indicator
    searchResults.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin me-2"></i> Searching...</div>';
    
    // Show search results container, hide default
    if (resultsContainer && defaultContainer) {
        resultsContainer.style.display = 'block';
        defaultContainer.style.display = 'none';
    }
    
    // Send search request
    fetch(`api/search_deliveries.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            // Clear search results
            searchResults.innerHTML = '';
            
            if (data.length === 0) {
                // No results found
                searchResults.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No deliveries found matching "${query}".
                    </div>
                `;
                return;
            }
            
            // Display search results
            const resultCards = data.map(delivery => {
                const statusClass = getStatusClass(delivery.status);
                return `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Order #${delivery.order_id}</h5>
                                <span class="badge bg-${statusClass}">${capitalizeFirstLetter(delivery.status)}</span>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-2">Customer: ${delivery.customer_name}</h6>
                                <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>${delivery.customer_address}</p>
                                <p class="mb-2"><i class="fas fa-store me-2"></i>${delivery.provider_name}</p>
                            </div>
                            <div class="card-footer">
                                <a href="delivery-details.php?id=${delivery.id}" class="btn btn-primary w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Add to search results container
            searchResults.innerHTML = `
                <div class="row">
                    ${resultCards}
                </div>
            `;
        })
        .catch(error => {
            console.error('Error searching deliveries:', error);
            searchResults.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    An error occurred while searching. Please try again.
                </div>
            `;
        });
}

/**
 * Get Bootstrap status class based on delivery status
 * 
 * @param {string} status Delivery status
 * @returns {string} Bootstrap class
 */
function getStatusClass(status) {
    switch (status) {
        case 'pending':
            return 'secondary';
        case 'in-progress':
            return 'primary';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'delayed':
            return 'warning';
        default:
            return 'info';
    }
}

/**
 * Capitalize the first letter of a string
 * 
 * @param {string} str Input string
 * @returns {string} Formatted string
 */
function capitalizeFirstLetter(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}