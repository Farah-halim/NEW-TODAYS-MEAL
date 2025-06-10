/**
 * Today's Meal - Orders Management System
 * JavaScript functionality for the orders management page
 */

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document ready, initializing orders.js');
    
    // Highlight active sidebar link
    const sidebarLinks = document.querySelectorAll('.nav-link');
    sidebarLinks.forEach(link => {
        if (link.textContent.trim() === 'Orders') {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Enable search functionality
    const searchInput = document.getElementById('orderSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterOrdersTable(this.value);
        });
        
        // Also handle search button click
        const searchButton = searchInput.nextElementSibling;
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                filterOrdersTable(searchInput.value);
            });
        }
    }

    // Initialize order type tabs functionality
    initializeOrderTypeTabs();
    filterTabTablesByType();
    window.filterOrdersByType('all');

    // Setup filter form handling
    const filterForm = document.getElementById('filterForm');
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    const resetFilterBtn = document.getElementById('resetFilterBtn');
    
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            applyFilters();
        });
    }
    
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            resetFilters();
        });
    }



    // Setup delivery assignment functionality
    const saveDeliveryAssignmentBtn = document.getElementById('saveDeliveryAssignmentBtn');
    if (saveDeliveryAssignmentBtn) {
        saveDeliveryAssignmentBtn.addEventListener('click', function() {
            saveDeliveryAssignment();
        });
    }

    // Enable printing order details
    const printOrderBtn = document.getElementById('printOrderBtn');
    if (printOrderBtn) {
        printOrderBtn.addEventListener('click', function() {
            printOrderDetails();
        });
    }
});

/**
 * Filter the orders table based on search input
 */
function filterOrdersTable(query) {
    console.log('Filtering orders table with query:', query);
    query = query.toLowerCase();
    
    // Determine which tab is active
    const activeTabPane = document.querySelector('.tab-pane.active');
    if (!activeTabPane) return;
    
    // Get rows from the active tab
    const rows = activeTabPane.querySelectorAll('tr.order-row');
    console.log('Total rows found for searching in active tab:', rows.length);
    
    let visibleCount = 0;
    
    // Filter the rows in the active tab
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    console.log('Visible rows after filtering:', visibleCount);
    
    // Show empty state if no results in the active tab
    if (visibleCount === 0) {
        // Get the type from the tab ID
        const tabId = activeTabPane.id;
        const type = tabId.replace('-orders', '');
        updateEmptyState(true, type, activeTabPane);
    } else {
        updateEmptyState(false, '', activeTabPane);
    }
}

/**
 * Update empty states for all tabs based on search results
 */
function updateEmptyStatesForAllTabs(query) {
    const tabIds = ['all-orders', 'normal-orders', 'customized-orders', 'scheduled-orders'];
    
    tabIds.forEach(tabId => {
        const tabPane = document.getElementById(tabId);
        if (!tabPane) return;
        
        const rows = tabPane.querySelectorAll('tr.order-row');
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        
        // Remove any existing empty state rows
        const existingEmptyRow = tabPane.querySelector('.empty-state-row');
        if (existingEmptyRow) {
            existingEmptyRow.remove();
        }
        
        if (visibleRows.length === 0) {
            const tbody = tabPane.querySelector('tbody');
            if (tbody) {
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-state-row';
                
                let message = 'No matching orders found';
                if (query) {
                    message = `No orders matching "${query}" found`;
                } else {
                    const tabType = tabId.replace('-orders', '');
                    message = tabType === 'all' ? 'No orders found' : `No ${tabType} orders found`;
                }
                
                emptyRow.innerHTML = `
                    <td colspan="9" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                            <h5>${message}</h5>
                            <p class="text-muted">Try adjusting your search criteria</p>
                        </div>
                    </td>
                `;
                tbody.appendChild(emptyRow);
            }
        }
    });
}

/**
 * Apply filters to the orders table
 */
function applyFilters() {
    const formData = new FormData(document.getElementById('filterForm'));
    const filters = {
        startDate: formData.get('start_date'),
        endDate: formData.get('end_date'),
        status: formData.get('status'),
        orderType: formData.get('order_type'),
        minPrice: formData.get('min_price'),
        maxPrice: formData.get('max_price')
    };
    
    // AJAX call to get filtered orders
    fetch('get_filtered_orders.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        updateOrdersTable(data);
        const filterModal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
        filterModal.hide();
    })
    .catch(error => {
        console.error('Error applying filters:', error);
        alert('Error applying filters. Please try again.');
    });
}

/**
 * Reset all filters
 */
function resetFilters() {
    document.getElementById('filterForm').reset();
    refreshOrders();
}

/**
 * Update the orders table with new data
 */
function updateOrdersTable(orders) {
    const tbody = document.getElementById('ordersTableBody');
    tbody.innerHTML = '';
    
    if (orders.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="empty-state">
                        <i class="fas fa-filter fa-3x mb-3 text-muted"></i>
                        <h5>No orders match your filters</h5>
                        <p class="text-muted">Try adjusting your filter criteria</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    orders.forEach(order => {
        const row = document.createElement('tr');
        row.className = 'order-row';
        row.setAttribute('data-order-id', order.order_id);
        
        // Format the date
        const orderDate = new Date(order.order_date);
        const formattedDate = orderDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
        
        row.innerHTML = `
            <td>#${order.order_id}</td>
            <td>${formattedDate}</td>
            <td>${order.customer_name || 'Unknown'}</td>
            <td>${order.kitchen_name || 'Unknown'}</td>
            <td>
                <span class="order-type-badge">
                    ${order.ord_type.charAt(0).toUpperCase() + order.ord_type.slice(1)}
                </span>
            </td>
            <td>$${parseFloat(order.total_price).toFixed(2)}</td>
            <td>
                <span class="status-pill status-${order.order_status}">
                    ${getStatusDisplayText(order.order_status)}
                </span>
            </td>
            <td>
                ${getDeliveryStatusBadge(order.delivery_status)}
            </td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-accent" onclick="viewOrderDetails(${order.order_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="updateOrderStatus(${order.order_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="assignDelivery(${order.order_id})">
                        <i class="fas fa-truck"></i>
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

/**
 * Get the HTML for delivery status badge
 */
function getDeliveryStatusBadge(status) {
    if (!status) {
        return '<span class="badge bg-secondary">Not Assigned</span>';
    }
    
    const bgClass = status === 'delivered' ? 'success' : 'warning';
    return `<span class="badge bg-${bgClass}">
                ${status.charAt(0).toUpperCase() + status.slice(1)}
            </span>`;
}

/**
 * Refresh the orders list
 */
function refreshOrders() {
    fetch('get_orders.php')
    .then(response => response.json())
    .then(data => {
        updateOrdersTable(data);
    })
    .catch(error => {
        console.error('Error refreshing orders:', error);
        alert('Error refreshing orders. Please try again.');
    });
}

/**
 * View order details
 */
function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    // Set the modal title with the order ID
    document.getElementById('orderDetailsModalLabel').textContent = `Order Details - #${orderId}`;
    
    // Clear previous content and show loading spinner
    const contentContainer = document.getElementById('orderDetailsContent');
    contentContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading order details...</p>
        </div>
    `;
    
    // Fetch order details
    fetch(`get_order_details.php?id=${orderId}`)
    .then(response => response.json())
    .then(data => {
        // Once data is loaded, update the modal content
        renderOrderDetails(contentContainer, data);
    })
    .catch(error => {
        console.error('Error loading order details:', error);
        contentContainer.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                Failed to load order details. Please try again.
            </div>
        `;
    });
}

/**
 * Render order details in the modal
 */
function renderOrderDetails(container, orderData) {
    // Set defaults for missing data
    const order = {
        ...orderData,
        customer_name: orderData.customer_name || 'Unknown Customer',
        kitchen_name: orderData.kitchen_name || 'Unknown Kitchen'
    };
    
    // Format the date
    const orderDate = new Date(order.order_date);
    const formattedDate = orderDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Build the HTML
    let html = `
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted">Order Information</h6>
                <p><strong>Date:</strong> ${formattedDate}</p>
                <p><strong>Status:</strong> 
                    <span class="status-pill status-${order.order_status}">
                        ${getStatusDisplayText(order.order_status)}
                    </span>
                </p>
                <p><strong>Type:</strong> ${order.ord_type.charAt(0).toUpperCase() + order.ord_type.slice(1)}</p>
                <p><strong>Delivery Zone:</strong> ${order.delivery_zone}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Parties Involved</h6>
                <p><strong>Customer:</strong> ${order.customer_name}</p>
                <p><strong>Cloud Kitchen:</strong> ${order.kitchen_name}</p>
                <p><strong>Delivery Person:</strong> ${order.delivery_man_name || 'Not assigned yet'}</p>
            </div>
        </div>
        
        <hr>
        
        <h6 class="mb-3">Order Items</h6>
    `;
    
    // Add order items table
    if (order.items && order.items.length > 0) {
        html += `
            <div class="table-responsive mb-4">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        // Add each item
        order.items.forEach(item => {
            const itemTotal = parseFloat(item.price) * parseInt(item.quantity);
            html += `
                <tr>
                    <td>${item.name}</td>
                    <td>${item.quantity}</td>
                    <td class="text-end">$${parseFloat(item.price).toFixed(2)}</td>
                    <td class="text-end">$${itemTotal.toFixed(2)}</td>
                </tr>
            `;
        });
        
        // Add total row
        html += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th class="text-end">$${parseFloat(order.total_price).toFixed(2)}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
    } else {
        html += `
            <div class="alert alert-info" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                No item details available for this order.
            </div>
        `;
    }
    
    // Add delivery information if available
    if (order.delivery_details) {
        const deliveryDate = new Date(order.delivery_details.delivery_date_and_time);
        const formattedDeliveryDate = deliveryDate.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <hr>
            <h6 class="mb-3">Delivery Information</h6>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Location:</strong> ${order.delivery_details.d_location}</p>
                    <p><strong>Payment Method:</strong> ${order.delivery_details.p_method.toUpperCase()}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Scheduled For:</strong> ${formattedDeliveryDate}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-${order.delivery_details.d_status === 'delivered' ? 'success' : 'warning'}">
                            ${order.delivery_details.d_status === 'delivered' ? 'Delivered' : 'Not Delivered'}
                        </span>
                    </p>
                </div>
            </div>
        `;
    }
    
    // If this is a customized order, show additional information
    if (order.ord_type === 'customized' && order.customized_details) {
        html += `
            <hr>
            <h6 class="mb-3">Customization Details</h6>
            <div class="alert alert-light">
                <p><strong>Budget Range:</strong> $${parseFloat(order.customized_details.budget_min).toFixed(2)} - 
                    $${parseFloat(order.customized_details.budget_max).toFixed(2)}</p>
                <p><strong>People to Serve:</strong> ${order.customized_details.people_servings}</p>
                <p><strong>Preferred Completion Date:</strong> 
                    ${new Date(order.customized_details.preferred_completion_date).toLocaleDateString()}</p>
                <p><strong>Description:</strong> ${order.customized_details.ord_description}</p>
            </div>
        `;
    }
    
    // Add order timeline/activity if available
    if (order.timeline && order.timeline.length > 0) {
        html += `
            <hr>
            <h6 class="mb-3">Order Timeline</h6>
            <div class="timeline">
        `;
        
        order.timeline.forEach(event => {
            const eventDate = new Date(event.timestamp);
            const formattedEventDate = eventDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            html += `
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between">
                            <span>${event.description}</span>
                            <small class="text-muted">${formattedEventDate}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `</div>`;
    }
    
    // Populate the container
    container.innerHTML = html;
}



/**
 * Assign delivery personnel to an order
 */
function assignDelivery(orderId) {
    // Reset the form
    document.getElementById('assignDeliveryForm').reset();
    
    // Set the order ID
    document.getElementById('deliveryOrderId').value = orderId;
    
    // Set default delivery date as tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(12, 0, 0, 0);
    document.getElementById('deliveryDate').value = tomorrow.toISOString().slice(0, 16);
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('assignDeliveryModal'));
    modal.show();
}

/**
 * Save delivery assignment
 */
function saveDeliveryAssignment() {
    const form = document.getElementById('assignDeliveryForm');
    const formData = new FormData(form);
    
    fetch('assign_delivery.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignDeliveryModal'));
            modal.hide();
            
            // Show success message
            alert('Delivery assigned successfully!');
            
            // Refresh orders list
            refreshOrders();
        } else {
            alert('Error assigning delivery: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error assigning delivery:', error);
        alert('Error assigning delivery. Please try again.');
    });
}

/**
 * Export orders data to CSV
 */
function exportOrdersData() {
    window.location.href = 'export_orders.php';
}

/**
 * Print order details
 */
function printOrderDetails() {
    const content = document.getElementById('orderDetailsContent').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Order Details</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .status-pill { 
                    padding: 5px 10px; 
                    border-radius: 20px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    display: inline-block;
                }
                .status-pending { background-color: #ffd700; color: #6a4125; }
                .status-in_progress { background-color: #3d6f5d; color: white; }
                .status-delivered { background-color: #4CAF50; color: white; }
                .status-cancelled { background-color: #f44336; color: white; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                .text-end { text-align: right; }
                .badge { 
                    padding: 5px 8px; 
                    border-radius: 4px;
                    font-size: 0.75rem;
                    color: white;
                    display: inline-block;
                }
                .bg-success { background-color: #4CAF50; }
                .bg-warning { background-color: #ffc107; color: #212529; }
                .bg-secondary { background-color: #6c757d; }
                h6 { margin-top: 20px; margin-bottom: 10px; }
                hr { margin: 20px 0; border: 0; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <h2>Order Details</h2>
            ${content}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    
    // Print after a short delay to ensure content is loaded
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}

/**
 * Initialize the order type tabs functionality
 */
function initializeOrderTypeTabs() {
    console.log('Initializing order type tabs');
    
    // First ensure all tab panes have the proper structure
    setupTabPaneStructure();
    
    // Then populate them with filtered data
    filterTabTablesByType();
    
    // Make sure tabs are properly initialized with Bootstrap
    var tabElements = document.querySelectorAll('#ordersTabs button[data-bs-toggle="tab"]');
    tabElements.forEach(function(tabEl) {
        // Make sure Bootstrap Tab is initialized
        try {
            new bootstrap.Tab(tabEl);
        } catch (e) {
            console.error('Error initializing tab:', e);
        }
    });
}

/**
 * Setup tab pane structure
 */
function setupTabPaneStructure() {
    // Get the reference table from all-orders tab
    const originalTable = document.querySelector('#all-orders .table-responsive');
    if (!originalTable) {
        console.error('Original table not found in all-orders tab');
        return;
    }
    
    // Make sure each tab pane has a table structure
    const tabIds = ['normal-orders', 'customized-orders', 'scheduled-orders'];
    tabIds.forEach(tabId => {
        const tabPane = document.getElementById(tabId);
        if (!tabPane) {
            console.error(`Tab pane ${tabId} not found`);
            return;
        }
        
        // Check if the tab pane already has a table
        if (!tabPane.querySelector('.table-responsive')) {
            // Create a row and column structure
            const row = document.createElement('div');
            row.className = 'row';
            
            const col = document.createElement('div');
            col.className = 'col-md-12';
            
            // Clone the table structure
            const tableResponsive = originalTable.cloneNode(true);
            
            // Clear out the rows from the tbody
            const tbody = tableResponsive.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = '';
            }
            
            // Assemble the structure
            col.appendChild(tableResponsive);
            row.appendChild(col);
            tabPane.appendChild(row);
            
            console.log(`Added table structure to ${tabId}`);
        }
    });
}

/**
 * Filter orders by type (normal, customized, scheduled)
 */
window.filterOrdersByType = function(type) {
    console.log('Filtering orders by type:', type);
    
    // First, get all order rows to reset display
    const allOrderRows = document.querySelectorAll('tr.order-row');
    
    // Check if we have any orders at all
    if (allOrderRows.length === 0) {
        // If we have no orders, make sure the empty state is visible
        const emptyStateRow = document.getElementById('emptyStateRow');
        if (emptyStateRow) {
            emptyStateRow.style.display = '';
        }
        return;
    }
    
    // If we have orders, we should hide the empty state message
    const emptyStateRow = document.getElementById('emptyStateRow');
    if (emptyStateRow) {
        emptyStateRow.style.display = 'none';
    }
    
    // Reset all rows visibility first
    allOrderRows.forEach(row => {
        row.style.display = 'none';
    });
    
    // Determine which rows to show based on type
    let orderRows;
    if (type === 'all') {
        // For 'all' tab, show all rows in the all-orders tab
        orderRows = document.querySelectorAll('#all-orders tr.order-row');
        orderRows.forEach(row => {
            row.style.display = '';
        });
    } else {
        // For specific tabs, only show rows matching that order type
        const tabId = `#${type}-orders`;
        const tabPane = document.querySelector(tabId);
        
        if (tabPane) {
            orderRows = tabPane.querySelectorAll('tr.order-row');
            orderRows.forEach(row => {
                const orderTypeBadge = row.querySelector('.order-type-badge');
                const orderType = orderTypeBadge ? orderTypeBadge.textContent.trim().toLowerCase() : '';
                if (orderType === type) {
                    row.style.display = '';
                }
            });
        }
    }
    
    // Show empty state if no visible rows in the active tab
    const activeTabId = type === 'all' ? 'all-orders' : `${type}-orders`;
    const activeTab = document.getElementById(activeTabId);
    if (activeTab) {
        const visibleRows = activeTab.querySelectorAll('tr.order-row[style="display: ;"], tr.order-row:not([style*="display: none"])');
        if (visibleRows.length === 0) {
            updateEmptyState(true, type, activeTab);
        } else {
            updateEmptyState(false, type, activeTab);
        }
    }
}

/**
 * Filter the tables in each tab to show only relevant order types
 */
function filterTabTablesByType() {
    // For each order type, populate its tab with matching orders
    const orderTypes = ['normal', 'customized', 'scheduled'];
    
    orderTypes.forEach(type => {
        const tabId = `${type}-orders`;
        const tabPane = document.getElementById(tabId);
        if (!tabPane) return;
        
        const tableBody = tabPane.querySelector('tbody');
        if (!tableBody) return;
        
        // Clear existing rows
        tableBody.innerHTML = '';
        
        // Get all original rows that match this type
        const allRows = document.querySelectorAll('#all-orders tr.order-row');
        let matchingRows = [];
        
        allRows.forEach(row => {
            const orderType = row.querySelector('.order-type-badge')?.textContent.trim().toLowerCase();
            if (orderType === type) {
                const clonedRow = row.cloneNode(true);
                tableBody.appendChild(clonedRow);
                matchingRows.push(clonedRow);
            }
        });
        
        // Show empty state if no matching rows
        if (matchingRows.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.className = 'empty-state-row';
            emptyRow.innerHTML = `
                <td colspan="9" class="text-center py-4">
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                        <h5>No ${type} orders found</h5>
                        <p class="text-muted">Orders will appear here once they are placed</p>
                    </div>
                </td>
            `;
            tableBody.appendChild(emptyRow);
        }
    });
}

/**
 * Update empty state message based on filter results
 */
function updateEmptyState(noResults, type, container) {
    // Container is the tab pane or another element where we want to show the empty state
    container = container || document.getElementById('all-orders');
    if (!container) return;
    
    // Remove any existing empty state rows
    const existingEmptyRow = container.querySelector('.empty-state-row');
    if (existingEmptyRow) {
        existingEmptyRow.remove();
    }
    
    if (noResults) {
        const tbody = container.querySelector('tbody');
        if (!tbody) return;
        
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-state-row';
        
        let message = 'No orders found';
        if (type !== 'all') {
            message = `No ${type} orders found`;
        }
        
        emptyRow.innerHTML = `
            <td colspan="9" class="text-center py-4">
                <div class="empty-state">
                    <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                    <h5>${message}</h5>
                    <p class="text-muted">Orders will appear here once they are placed</p>
                </div>
            </td>
        `;
        tbody.appendChild(emptyRow);
    }
}

/**
 * Populate the order type tabs with their respective content
 */
function populateOrderTypeTabs() {
    console.log('Populating order type tabs');
    
    // Get the original table from all-orders tab
    const originalTable = document.querySelector('#all-orders .table-responsive');
    console.log('Original table exists:', !!originalTable);
    
    if (originalTable) {
        const tabIds = ['normal-orders', 'customized-orders', 'scheduled-orders'];
        
        // Clone the table structure for each tab
        tabIds.forEach(tabId => {
            const tabPane = document.getElementById(tabId);
            if (!tabPane) return;
            
            // Create table for this tab
            const clonedTable = originalTable.cloneNode(true);
            
            // Clear the existing content and add the table
            tabPane.innerHTML = '';
            tabPane.appendChild(clonedTable);
            console.log(`Added table to ${tabId}`);
        });
        
        // Filter each table based on order type
        filterTabTablesByType();
    } else {
        console.error('Original table not found in all-orders tab');
    }
}

// orders.js - Additional JavaScript functionality for Orders Management

// Function to fetch order details via AJAX
function fetchOrderDetails(orderId) {
    // In a real implementation, this would be an AJAX call to the server
    // For now, we'll simulate a response
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                order_id: orderId,
                customer_name: "Sample Customer",
                order_date: "2023-06-15",
                order_items: [
                    { name: "Item 1", quantity: 2, price: 10.99 },
                    { name: "Item 2", quantity: 1, price: 15.99 }
                ],
                total: 37.97,
                status: "pending",
                address: "123 Sample Street, City"
            });
        }, 500);
    });
}

// Function to populate order details modal
async function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const content = document.getElementById('orderDetailsContent');
    
    // Show loading spinner
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading order details...</p>
        </div>
    `;
    
    // Show the modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    try {
        // Fetch order details
        const orderDetails = await fetchOrderDetails(orderId);
        
        // Populate modal with order details
        content.innerHTML = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Order #${orderDetails.order_id}</h5>
                    <p class="mb-1"><strong>Date:</strong> ${new Date(orderDetails.order_date).toLocaleDateString()}</p>
                    <p class="mb-1"><strong>Customer:</strong> ${orderDetails.customer_name}</p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">${orderDetails.status}</span></p>
                </div>
                <div class="col-md-6">
                    <h5>Delivery Information</h5>
                    <p class="mb-1"><strong>Address:</strong> ${orderDetails.address}</p>
                </div>
            </div>
            
            <h5>Order Items</h5>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${orderDetails.order_items.map(item => `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.quantity}</td>
                            <td>$${item.price.toFixed(2)}</td>
                            <td>$${(item.quantity * item.price).toFixed(2)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th>$${orderDetails.total.toFixed(2)}</th>
                    </tr>
                </tfoot>
            </table>
            
            <h5>Order Timeline</h5>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <p class="mb-0"><strong>${new Date(orderDetails.order_date).toLocaleString()}</strong> - Order placed</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <p class="mb-0"><strong>${new Date(orderDetails.order_date).toLocaleString()}</strong> - Order confirmed</p>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        content.innerHTML = `
            <div class="alert alert-danger">
                Error loading order details. Please try again.
            </div>
        `;
    }
}

// Print order functionality
document.addEventListener('DOMContentLoaded', function() {
    const printOrderBtn = document.getElementById('printOrderBtn');
    if (printOrderBtn) {
        printOrderBtn.addEventListener('click', function() {
            const content = document.getElementById('orderDetailsContent').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Order</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 20px; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <div class="row mb-4">
                            <div class="col-12 text-center">
                                <h2>Today's Meal - Order Details</h2>
                                <hr>
                            </div>
                        </div>
                        ${content}
                        <div class="row mt-5 no-print">
                            <div class="col-12 text-center">
                                <button class="btn btn-primary" onclick="window.print()">Print</button>
                                <button class="btn btn-secondary" onclick="window.close()">Close</button>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        });
    }
});

// Update the status display for new enum values
function getStatusDisplayText(status) {
    const statusMap = {
        'pending': 'Pending',
        'preparing': 'Preparing', 
        'ready_for_pickup': 'Ready for Pickup',
        'in_transit': 'In Transit',
        'delivered': 'Delivered',
        'cancelled': 'Cancelled'
    };
    return statusMap[status] || status;
} 