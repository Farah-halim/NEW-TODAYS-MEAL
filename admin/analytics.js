document.addEventListener('DOMContentLoaded', function() {
    // This script can be used for additional analytics functionality
    // Currently, the main dashboard.js handles all the chart initialization
    
    // Add a refresh button functionality if needed
    const refreshBtn = document.getElementById('refreshAnalyticsBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Clear existing charts
            document.querySelectorAll('canvas').forEach(canvas => {
                canvas.remove();
            });
            
            // Create new canvas elements
            const chartContainers = document.querySelectorAll('.chart-container');
            chartContainers.forEach(container => {
                const id = container.id.replace('Container', '');
                const canvas = document.createElement('canvas');
                canvas.id = id;
                container.appendChild(canvas);
            });
            
            // Reinitialize charts
            initializeCharts();
        });
    }
});

// Function to format numbers with commas for thousands
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Function to update the analytics data without refreshing the page
function updateAnalytics() {
    fetch('/api/analytics')
        .then(response => response.json())
        .then(data => {
            // Update statistics cards
            document.getElementById('totalKitchens').textContent = formatNumber(data.total_kitchens);
            document.getElementById('activeKitchens').textContent = formatNumber(data.active_kitchens);
            document.getElementById('totalCategories').textContent = formatNumber(data.total_categories);
            document.getElementById('totalOrdersToday').textContent = formatNumber(data.total_orders_today);
            document.getElementById('avgRating').textContent = data.avg_rating.toFixed(1);
        })
        .catch(error => console.error('Error updating analytics:', error));
}
