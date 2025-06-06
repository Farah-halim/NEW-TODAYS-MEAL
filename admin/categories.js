document.addEventListener('DOMContentLoaded', function() {
    // Handle category add form submission
    const addCategoryForm = document.getElementById('addCategoryForm');
    if (addCategoryForm) {
        addCategoryForm.addEventListener('submit', function(event) {
            // Form validation will be handled by browser's built-in validation
        });
    }
    
    // Handle category delete buttons
    const deleteButtons = document.querySelectorAll('.delete-category-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName;
            
            // Confirm deletion
            if (confirm(`Are you sure you want to delete the category "${categoryName}"? This action cannot be undone.`)) {
                // The form will be submitted to the server
            } else {
                event.preventDefault();
            }
        });
    });
    
    // Initialize the parent category select dropdown
    initializeParentCategoryDropdown();
    
    // Set initial state of parent category dropdown based on radio selection
    const rootRadio = document.getElementById('rootCategory');
    const subRadio = document.getElementById('subCategory');
    
    if (rootRadio && subRadio) {
        rootRadio.addEventListener('change', function() {
            toggleParentCategoryVisibility(false);
        });
        
        subRadio.addEventListener('change', function() {
            toggleParentCategoryVisibility(true);
        });
    }
});

function initializeParentCategoryDropdown() {
    const parentSelect = document.getElementById('parentCategory');
    if (!parentSelect) return;
    
    // Fetch categories and populate dropdown
    fetch('/api/categories')
        .then(response => response.json())
        .then(categories => {
            // Clear existing options
            parentSelect.innerHTML = '';
            
            // Add categories
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                
                // Create indentation for subcategories to show hierarchy
                let prefix = '';
                if (category.parent_id !== null) {
                    prefix = '-- ';
                }
                
                option.textContent = prefix + category.name;
                parentSelect.appendChild(option);
            });
            
            // Only show top-level categories first
            const filteredOptions = Array.from(parentSelect.options).filter(option => {
                const cat = categories.find(c => c.id === parseInt(option.value));
                return cat && cat.parent_id === null;
            });
            
            if (filteredOptions.length === 0) {
                const placeholder = document.createElement('option');
                placeholder.value = "";
                placeholder.textContent = "No parent categories available";
                placeholder.disabled = true;
                placeholder.selected = true;
                parentSelect.appendChild(placeholder);
            }
        })
        .catch(error => console.error('Error fetching categories:', error));
}

// Function to toggle the parent category dropdown visibility
function toggleParentCategoryVisibility(show) {
    const parentCategoryDiv = document.getElementById('parentCategoryDiv');
    if (parentCategoryDiv) {
        parentCategoryDiv.style.display = show ? 'block' : 'none';
        
        // If hiding, set parent_id value to empty
        if (!show) {
            const parentSelect = document.getElementById('parentCategory');
            if (parentSelect) {
                parentSelect.value = '';
            }
        }
    }
}

// Function to toggle the visibility of the add category form
function toggleAddCategoryForm() {
    const form = document.getElementById('addCategoryFormContainer');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        document.getElementById('toggleFormBtn').textContent = 'Hide Form';
    } else {
        form.style.display = 'none';
        document.getElementById('toggleFormBtn').textContent = 'Add New Category';
    }
}
