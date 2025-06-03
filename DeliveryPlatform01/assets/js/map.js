/**
 * Map functionality for delivery tracking
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if map containers exist before initializing
    if (document.getElementById('deliveries-map')) {
        initializeDeliveryMap();
    }
    
    if (document.getElementById('route-map')) {
        initializeRouteMap();
    }
});

/**
 * Initialize the delivery overview map
 */
function initializeDeliveryMap() {
    const mapContainer = document.getElementById('deliveries-map');
    
    // Create map centered on average location
    const map = L.map(mapContainer).setView([30.0444, 31.2357], 12); // Cairo coordinates
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Fetch delivery data
    fetch('api/get_deliveries.php')
        .then(response => response.json())
        .then(deliveries => {
            if (deliveries.length === 0) {
                return;
            }
            
            const bounds = L.latLngBounds();
            
            // Add markers for each delivery
            deliveries.forEach(delivery => {
                // Skip if missing coordinates
                if (!delivery.customer_latitude || !delivery.customer_longitude) {
                    return;
                }
                
                // Create customer marker
                const customerLatLng = [parseFloat(delivery.customer_latitude), parseFloat(delivery.customer_longitude)];
                const statusClass = getStatusClass(delivery.status);
                const iconClass = delivery.status === 'completed' ? 'fa-check' : 'fa-utensils';
                
                const marker = L.marker(customerLatLng, {
                    icon: createCustomIcon(iconClass, `bg-${statusClass}`)
                }).addTo(map);
                
                // Create popup content
                const popupContent = `
                    <div class="popup-content">
                        <h6 class="mb-1">Order #${delivery.order_id}</h6>
                        <p class="mb-1"><strong>${delivery.customer_name}</strong></p>
                        <p class="mb-1 small">${delivery.customer_address}</p>
                        <p class="mb-2 small">
                            <span class="badge bg-${statusClass}">${capitalizeFirstLetter(delivery.status)}</span>
                        </p>
                        <a href="delivery-details.php?id=${delivery.id}" class="btn btn-sm btn-primary w-100">View Details</a>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                bounds.extend(customerLatLng);
            });
            
            // Fit map to bounds with padding
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        })
        .catch(error => {
            console.error('Error fetching deliveries:', error);
        });
}

/**
 * Initialize the route map for a specific delivery
 */
function initializeRouteMap() {
    const mapContainer = document.getElementById('route-map');
    
    // Get coordinates from data attributes
    const providerLat = parseFloat(mapContainer.dataset.providerLat);
    const providerLng = parseFloat(mapContainer.dataset.providerLng);
    const customerLat = parseFloat(mapContainer.dataset.customerLat);
    const customerLng = parseFloat(mapContainer.dataset.customerLng);
    const status = mapContainer.dataset.status;
    
    // Create map
    const map = L.map(mapContainer);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Create bounds
    const bounds = L.latLngBounds(
        [providerLat, providerLng],
        [customerLat, customerLng]
    );
    
    // Add provider marker
    const providerMarker = L.marker([providerLat, providerLng], {
        icon: createCustomIcon('fa-store', 'bg-info')
    }).addTo(map);
    providerMarker.bindPopup('<strong>Pickup Location</strong>');
    
    // Add customer marker
    const customerMarker = L.marker([customerLat, customerLng], {
        icon: createCustomIcon('fa-map-marker-alt', 'bg-danger')
    }).addTo(map);
    customerMarker.bindPopup('<strong>Delivery Location</strong>');
    
    // Draw route line
    const routeLine = L.polyline(
        [
            [providerLat, providerLng],
            [customerLat, customerLng]
        ],
        {
            color: getRouteColor(status),
            weight: 4,
            opacity: 0.7,
            dashArray: status === 'delayed' ? '5, 10' : null
        }
    ).addTo(map);
    
    // Fit map to bounds
    map.fitBounds(bounds, { padding: [30, 30] });
}

/**
 * Create a custom icon for map markers
 * 
 * @param {string} iconClass Font Awesome icon class
 * @param {string} bgClass Background color class
 * @returns {L.DivIcon} Leaflet DivIcon
 */
function createCustomIcon(iconClass, bgClass = 'bg-primary') {
    return L.divIcon({
        className: 'custom-marker-container',
        html: `<div class="custom-marker ${bgClass}"><i class="fas ${iconClass}"></i></div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });
}

/**
 * Get route color based on delivery status
 * 
 * @param {string} status Delivery status
 * @returns {string} Color value
 */
function getRouteColor(status) {
    switch (status) {
        case 'pending':
            return '#6c757d'; // secondary
        case 'in-progress':
            return '#0d6efd'; // primary
        case 'completed':
            return '#198754'; // success
        case 'cancelled':
            return '#dc3545'; // danger
        case 'delayed':
            return '#ffc107'; // warning
        default:
            return '#0dcaf0'; // info
    }
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