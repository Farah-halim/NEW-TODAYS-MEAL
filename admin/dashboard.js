document.addEventListener('DOMContentLoaded', function() {
    // Initialize all charts
    initializeCharts();
});

function initializeCharts() {
    // Fetch analytics data and create charts
    fetch('/api/analytics')
        .then(response => response.json())
        .then(data => {
            createKitchenStatusChart(data);
            createCategoryDistributionChart(data);
            createOrdersChart(data);
        })
        .catch(error => console.error('Error fetching analytics data:', error));
}

function createKitchenStatusChart(data) {
    const ctx = document.getElementById('kitchenStatusChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [data.active_kitchens, data.inactive_kitchens],
                backgroundColor: [
                    '#3d6f5d',  // accent color for active
                    '#dc3545'   // red for inactive
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Kitchen Status Distribution',
                    color: '#6a4125',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });
}

function createCategoryDistributionChart(data) {
    const ctx = document.getElementById('categoryDistributionChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Root Categories', 'Sub Categories'],
            datasets: [{
                data: [data.root_categories, data.sub_categories],
                backgroundColor: [
                    '#e57e24',  // primary color for root
                    '#dab98b'   // secondary color for sub
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Category Distribution',
                    color: '#6a4125',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });
}

function createOrdersChart(data) {
    // Get top 5 categories by orders
    const categories = Object.keys(data.orders_by_category);
    const orderValues = Object.values(data.orders_by_category);
    
    // Sort categories by order count and get top 5
    const indices = Array.from(Array(categories.length).keys())
        .sort((a, b) => orderValues[b] - orderValues[a])
        .slice(0, 5);
    
    const topCategories = indices.map(i => categories[i]);
    const topOrderValues = indices.map(i => orderValues[i]);
    
    const ctx = document.getElementById('ordersChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: topCategories,
            datasets: [{
                label: 'Orders Today',
                data: topOrderValues,
                backgroundColor: '#3d6f5d', // accent color
                borderColor: '#3d6f5d',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Top Categories by Orders Today',
                    color: '#6a4125',
                    font: {
                        size: 16
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Orders'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Category'
                    }
                }
            }
        }
    });
}
