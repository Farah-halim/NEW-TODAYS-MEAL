// Category Management Functions
function toggleAddCategoryForm() {
    const container = document.getElementById('addCategoryFormContainer');
    const btn = document.getElementById('toggleFormBtn');

    if (container.style.display === 'none') {
        container.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-times me-2"></i>Cancel';
        container.querySelector('#categoryName').focus();
    } else {
        container.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-plus me-2"></i>Add New Category';
        // Reset form
        const form = container.querySelector('form');
        if (form) form.reset();
    }
}

function toggleParentCategory(show) {
    const parentCategoryDiv = document.getElementById('parentCategoryDiv');
    if (parentCategoryDiv) {
        parentCategoryDiv.style.display = show ? 'block' : 'none';
    }
}

function viewKitchensInCategory(categoryId, categoryName) {
    // Update active button
    document.querySelectorAll('.view-kitchens-btn').forEach(btn => {
        if (btn.getAttribute('data-category-id') == categoryId) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Update title
    const titleElement = document.getElementById('kitchensTitle');
    if (titleElement) {
        titleElement.textContent = `Kitchens in ${categoryName}`;
    }

    // Simulate loading kitchens (in real app, this would fetch from backend)
    const kitchensList = document.getElementById('kitchensList');
    if (kitchensList) {
        kitchensList.innerHTML = `
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="kitchen-card">
                    <div class="card-header">
                        <span>Sample Kitchen</span>
                        <span class="badge status-badge status-active">Active</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Sample description for ${categoryName}</p>
                        <div class="kitchen-stats">
                            <div class="stat-item">
                                <div class="stat-value">31</div>
                                <div class="stat-label">Orders Today</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">215</div>
                                <div class="stat-label">Weekly Orders</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">4.4</div>
                                <div class="stat-label">Rating</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }
}

// Initialize event listeners when document loads
async function showKitchensByTag(tagId, tagName) {
    try {
        const response = await fetch(`get_kitchens_by_tag.php?tag_id=${tagId}`);
        const kitchens = await response.json();
        
        const preview = document.createElement('div');
        preview.className = 'modal-preview';
        preview.innerHTML = `
            <h4 class="mb-3">${tagName} Kitchens</h4>
            ${kitchens.length === 0 ? 
              '<p class="text-muted">No kitchens found with this tag.</p>' :
              `<p class="text-muted mb-3">${kitchens.length} kitchen(s) found</p>
               <div class="kitchen-preview-list">
                   ${kitchens.map(k => `
                       <div class="kitchen-preview-item mb-2">
                           <strong>${k.business_name}</strong>
                           <div class="text-muted small">Category: ${k.category_name}</div>
                       </div>
                   `).join('')}
               </div>`
            }
            <div class="mt-3">
                <a href="manage_kitchens.php?tag=${tagId}" class="btn btn-sm btn-success">
                    View Full Details
                </a>
                <button onclick="this.parentElement.parentElement.remove()" 
                        class="btn btn-sm btn-secondary ms-2">Close</button>
            </div>`;
        
        // Remove any existing preview
        document.querySelectorAll('.modal-preview').forEach(el => el.remove());
        document.body.appendChild(preview);
        
        // Animate in
        setTimeout(() => preview.classList.add('show'), 10);
    } catch (error) {
        console.error('Error fetching kitchens:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Set up delete confirmation
    document.querySelectorAll('.delete-category-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Initialize form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nameInput = form.querySelector('#categoryName');
            if (!nameInput.value.trim()) {
                e.preventDefault();
                alert('Category name is required');
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('.sidebar');

    navbarToggler.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
});