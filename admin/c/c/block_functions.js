// Block Kitchen
function blockKitchen(kitchenId) {
    // Get kitchen name for the modal
    const row = document.querySelector(`tr[data-kitchen-id="${kitchenId}"]`);
    const kitchenName = row ? row.querySelector('strong').textContent : 'this kitchen';
    
    const modal = new bootstrap.Modal(document.getElementById('blockConfirmModal'));
    document.getElementById('blockKitchenName').textContent = kitchenName;
    document.getElementById('blockKitchenId').value = kitchenId;
    modal.show();
}

// Confirm Block
function confirmBlock() {
    const kitchenId = document.getElementById('blockKitchenId').value;
    const btn = document.getElementById('confirmBlockBtn');

    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Blocking...';
    btn.disabled = true;

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
            bootstrap.Modal.getInstance(document.getElementById('blockConfirmModal')).hide();
        } else {
            showToast(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error occurred', 'danger');
    })
    .finally(() => {
        btn.innerHTML = '<i class="fas fa-ban me-2"></i>Block Kitchen';
        btn.disabled = false;
    });
} 