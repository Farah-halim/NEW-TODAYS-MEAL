// Global variables
let currentRating = 0;

// Filter orders functionality
function filterOrders(status) {
    // Update active tab
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Show/hide order cards
    const orderCards = document.querySelectorAll('.order-card');
    orderCards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        if (status === 'all' || cardStatus === status) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

// Show order details modal
function showOrderDetails(button) {
    // Get data from button attributes
    const customerName = button.getAttribute('data-customer-name');
    const customerAddress = button.getAttribute('data-customer-address');
    const deliveryName = button.getAttribute('data-delivery-name');
    const deliveryInitials = button.getAttribute('data-delivery-initials');
    const itemsData = JSON.parse(button.getAttribute('data-items'));
    const subtotal = button.getAttribute('data-subtotal');
    const deliveryFee = button.getAttribute('data-delivery-fee');
    const total = button.getAttribute('data-total');
    const status = button.getAttribute('data-status');
    
    // Update customer information
    document.getElementById('customerName').textContent = customerName;
    document.getElementById('customerAddress').textContent = customerAddress;
    
    // Update delivery person (hide if delivered)
    const deliverySection = document.getElementById('deliveryPersonSection');
    if (status === 'delivered') {
        deliverySection.style.display = 'none';
    } else {
        deliverySection.style.display = 'block';
        document.querySelector('.delivery-avatar').textContent = deliveryInitials;
        document.querySelector('.delivery-name').textContent = deliveryName;
    }
    
    // Update order items
    const itemsList = document.getElementById('itemsList');
    itemsList.innerHTML = '';
    itemsData.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'item';
        itemElement.innerHTML = `
            <span class="item-name">${item.name}</span>
            <span class="item-price">${item.price}</span>
        `;
        itemsList.appendChild(itemElement);
    });
    
    // Update payment summary
    document.getElementById('subtotal').textContent = subtotal;
    document.getElementById('deliveryFee').textContent = deliveryFee;
    document.getElementById('total').textContent = total;
    
    // Show modal
    document.getElementById('orderModal').classList.add('show');
}

// Show rating modal
function showRatingModal(button) {
    const restaurantName = button.getAttribute('data-restaurant');
    
    document.getElementById('restaurantName').textContent = restaurantName;
    
    // Reset rating
    currentRating = 0;
    updateStarDisplay();
    
    // Clear comments
    
    
    // Show modal
    document.getElementById('ratingModal').classList.add('show');
}

// Close modals
function closeOrderModal() {
    document.getElementById('orderModal').classList.remove('show');
}

function closeRatingModal() {
    document.getElementById('ratingModal').classList.remove('show');
}

function closeModal(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}

// Rating functionality
function setRating(rating) {
    currentRating = rating;
    updateStarDisplay();
}

function updateStarDisplay() {
    const stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < currentRating) {
            star.classList.add('active');
            star.textContent = '★';
        } else {
            star.classList.remove('active');
            star.textContent = '☆';
        }
    });
}

// Submit rating
function submitRating() {    
    if (currentRating === 0) {
        alert('Please select a rating before submitting.');
        return;
    }
       
    alert('Thank you for your rating!');
    closeRatingModal();
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
});

// Items dropdown functionality
function toggleItemsDropdown(event) {
    event.stopPropagation();
    
    const button = event.currentTarget;
    
    const card = button.closest('.order-card');
    const dropdown = card.querySelector('.items-list');
    

    // Toggle this dropdown only
    dropdown.classList.toggle('show');
    button.classList.toggle('active');
}



// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.items-dropdown')) {
        document.querySelectorAll('.items-list.show').forEach(list => {
            list.classList.remove('show');
            list.previousElementSibling.classList.remove('active');
        });
    }
});

// Keyboard event handling
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeOrderModal();
        closeRatingModal();
        // Close all dropdowns
        document.querySelectorAll('.items-list.show').forEach(list => {
            list.classList.remove('show');
            list.previousElementSibling.classList.remove('active');
        });
    }
});
