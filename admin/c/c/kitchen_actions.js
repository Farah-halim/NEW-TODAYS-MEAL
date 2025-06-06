// Kitchen Actions JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeKitchenActions();
});

// Initialize Kitchen Action Event Listeners
function initializeKitchenActions() {
    // Suspend buttons
    document.querySelectorAll('.suspend-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const kitchenId = this.dataset.kitchenId;
            const kitchenName = this.dataset.kitchenName;
            showSuspensionModal(kitchenId, kitchenName);
        });
    });

    // Unsuspend buttons
    document.querySelectorAll('.unsuspend-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const kitchenId = this.dataset.kitchenId;
            unsuspendKitchen(kitchenId);
        });
    });

    // Block buttons
    document.querySelectorAll('.block-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const kitchenId = this.dataset.kitchenId;
            blockKitchen(kitchenId);
        });
    });

    // Unblock buttons
    document.querySelectorAll('.unblock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const kitchenId = this.dataset.kitchenId;
            unblockKitchen(kitchenId);
        });
    });

    // Delete buttons
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const kitchenId = this.dataset.kitchenId;
            const kitchenName = this.dataset.kitchenName;
            deleteKitchen(kitchenId, kitchenName);
        });
    });
}

// Suspension Modal Functions
function showSuspensionModal(kitchenId, kitchenName) {
    const modal = new bootstrap.Modal(document.getElementById('suspensionModal'));
    document.getElementById('suspensionKitchenName').textContent = kitchenName;
    document.getElementById('suspensionKitchenId').value = kitchenId;
    document.getElementById('suspensionReason').value = '';
    modal.show();
}

function confirmSuspension() {
    const kitchenId = document.getElementById('suspensionKitchenId').value;
    const reason = document.getElementById('suspensionReason').value.trim();
    const btn = document.getElementById('confirmSuspensionBtn');

    if (!reason) {
        showToast('Please provide a suspension reason', 'warning');
        return;
    }

    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Suspending...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'suspend');
    formData.append('kitchen_id', kitchenId);
    formData.append('reason', reason);

    fetch('manage_kitchen_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateKitchenStatus(kitchenId, 'suspended');
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('suspensionModal')).hide();
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error occurred', 'danger');
    })
    .finally(() => {
        btn.innerHTML = '<i class="fas fa-pause me-2"></i>Suspend Kitchen';
        btn.disabled = false;
    });
}

// Unsuspend Kitchen
function unsuspendKitchen(kitchenId) {
    if (!confirm('Are you sure you want to unsuspend this kitchen?')) return;

    const formData = new FormData();
    formData.append('action', 'unsuspend');
    formData.append('kitchen_id', kitchenId);

    fetch('manage_kitchen_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateKitchenStatus(kitchenId, 'active');
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error occurred', 'danger');
    });
}

// Block Kitchen
function blockKitchen(kitchenId) {
    if (!confirm('Are you sure you want to block this kitchen?')) return;

    const formData = new FormData();
    formData.append('action', 'block');
    formData.append('kitchen_id', kitchenId);

    fetch('manage_kitchen_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateKitchenStatus(kitchenId, 'blocked');
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error occurred', 'danger');
    });
}

// Unblock Kitchen
function unblockKitchen(kitchenId) {
    if (!confirm('Are you sure you want to unblock this kitchen?')) return;

    const formData = new FormData();
    formData.append('action', 'unblock');
    formData.append('kitchen_id', kitchenId);

    fetch('manage_kitchen_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateKitchenStatus(kitchenId, 'active');
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error occurred', 'danger');
    });
}

// Delete Kitchen Function (Fixed)
function deleteKitchen(kitchenId, kitchenName) {
    const modal = new bootstrap.Modal(document.getElementById('deleteWarningModal'));
    const warningText = document.querySelector('#deleteWarningModal .modal-body p');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Clear previous event listeners
    confirmBtn.replaceWith(confirmBtn.cloneNode(true));
    const newConfirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Set warning message
    warningText.innerHTML = `Are you sure you want to permanently delete <strong>${kitchenName}</strong>?`;
    
    newConfirmBtn.onclick = function() {
        const btn = this;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Deleting...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('kitchen_id', kitchenId);

        fetch('manage_kitchen_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row with animation
                const row = document.querySelector(`tr[data-kitchen-id="${kitchenId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showToast('Network error occurred', 'danger');
        })
        .finally(() => {
            modal.hide();
            btn.innerHTML = '<i class="fas fa-trash me-2"></i>Permanently Delete';
            btn.disabled = false;
        });
    };
    
    modal.show();
}

// Update Kitchen Status in UI
function updateKitchenStatus(kitchenId, newStatus) {
    const row = document.querySelector(`tr[data-kitchen-id="${kitchenId}"]`);
    if (!row) return;

    // Update status badge
    const statusBadge = row.querySelector('.status-badge');
    const statusClass = newStatus === 'active' ? 'success' : 
                      newStatus === 'suspended' ? 'warning' : 'danger';
    statusBadge.className = `badge bg-${statusClass} status-badge`;
    statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

    // Update action buttons
    const actionsCell = row.querySelector('.kitchen-actions');
    let buttonsHtml = '';

    if (newStatus === 'active') {
        buttonsHtml = `
            <button class="btn btn-warning btn-sm suspend-btn" 
                    data-kitchen-id="${kitchenId}"
                    data-kitchen-name="${row.querySelector('strong').textContent}">
                <i class="fas fa-pause"></i> Suspend
            </button>
            <button class="btn btn-danger btn-sm block-btn" 
                    data-kitchen-id="${kitchenId}">
                <i class="fas fa-ban"></i> Block
            </button>
        `;
    } else if (newStatus === 'suspended') {
        buttonsHtml = `
            <button class="btn btn-success btn-sm unsuspend-btn" 
                    data-kitchen-id="${kitchenId}">
                <i class="fas fa-play"></i> Unsuspend
            </button>
            <button class="btn btn-danger btn-sm block-btn" 
                    data-kitchen-id="${kitchenId}">
                <i class="fas fa-ban"></i> Block
            </button>
        `;
    } else if (newStatus === 'blocked') {
        buttonsHtml = `
            <button class="btn btn-secondary btn-sm unblock-btn" 
                    data-kitchen-id="${kitchenId}">
                <i class="fas fa-unlock"></i> Unblock
            </button>
        `;
    }

    // Always add delete button
    buttonsHtml += `
        <button class="btn btn-outline-danger btn-sm delete-btn" 
                data-kitchen-id="${kitchenId}"
                data-kitchen-name="${row.querySelector('strong').textContent}">
            <i class="fas fa-trash"></i> Delete
        </button>
    `;

    actionsCell.innerHTML = buttonsHtml;
    
    // Reinitialize event listeners for the updated buttons
    initializeKitchenActions();
}

// Toast Notification Function (Enhanced)
function showToast(message, type = 'info') {
    // Create container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1100';
        document.body.appendChild(container);
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast show align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Set icon based on type
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'danger' ? 'fa-exclamation-circle' : 
                 type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas ${icon} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after delay
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    }, 5000);
    
    // Add click handler for close button
    toast.querySelector('.btn-close').addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    });
} 