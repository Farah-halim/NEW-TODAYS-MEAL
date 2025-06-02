            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('notificationContainer');
                const notifications = container.querySelectorAll('.notification');
                
                notifications.forEach(notification => {
                    const duration = parseInt(notification.dataset.duration) || 5000;
                    const progressBar = notification.querySelector('.progress');
                    
                    // Show notification
                    setTimeout(() => {
                        notification.classList.add('show');
                        
                        // Animate progress bar
                        progressBar.style.transition = `width ${duration}ms linear`;
                        progressBar.style.width = '0%';
                        
                        setTimeout(() => {
                            progressBar.style.width = '100%';
                        }, 10);
                    }, 100);
                    
                    // Auto-hide after duration
                    const timer = setTimeout(() => {
                        notification.classList.remove('show');
                        notification.classList.add('hide');
                        
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    }, duration);

                });

                // Image preview functionality for add modal
                const newMealImageInput = document.getElementById('newMealImage');
                if (newMealImageInput) {
                    newMealImageInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                document.getElementById('newMealImagePreview').src = event.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                // Image preview functionality for edit modal
                const editMealImageInput = document.getElementById('editMealImage');
                if (editMealImageInput) {
                    editMealImageInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                document.getElementById('editMealImagePreview').src = event.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                // Category search functionality
                const categorySearch = document.getElementById('categorySearch');
                if (categorySearch) {
                    categorySearch.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const categoryGroups = document.querySelectorAll('.category-group');
                        
                        categoryGroups.forEach(group => {
                            const categoryName = group.dataset.category;
                            const subcategoryItems = group.querySelectorAll('.subcategory-item');
                            let hasVisibleItems = false;
                            
                            // Check if category name matches
                            if (categoryName.includes(searchTerm)) {
                                group.style.display = 'block';
                                hasVisibleItems = true;
                            } else {
                                // Check subcategories
                                subcategoryItems.forEach(item => {
                                    const subcategoryName = item.dataset.subcategory;
                                    if (subcategoryName.includes(searchTerm)) {
                                        item.style.display = 'block';
                                        hasVisibleItems = true;
                                    } else {
                                        item.style.display = 'none';
                                    }
                                });
                            }
                            
                            // Show/hide entire category group based on matches
                            group.style.display = hasVisibleItems ? 'block' : 'none';
                        });
                    });
                }

                // Edit category search functionality
                const editCategorySearch = document.getElementById('editCategorySearch');
                if (editCategorySearch) {
                    editCategorySearch.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const categoryGroups = document.querySelectorAll('#editMealModal .category-group');
                        
                        categoryGroups.forEach(group => {
                            const categoryName = group.dataset.category;
                            const subcategoryItems = group.querySelectorAll('.subcategory-item');
                            let hasVisibleItems = false;
                            
                            // Check if category name matches
                            if (categoryName.includes(searchTerm)) {
                                group.style.display = 'block';
                                hasVisibleItems = true;
                            } else {
                                // Check subcategories
                                subcategoryItems.forEach(item => {
                                    const subcategoryName = item.dataset.subcategory;
                                    if (subcategoryName.includes(searchTerm)) {
                                        item.style.display = 'block';
                                        hasVisibleItems = true;
                                    } else {
                                        item.style.display = 'none';
                                    }
                                });
                            }
                            
                            // Show/hide entire category group based on matches
                            group.style.display = hasVisibleItems ? 'block' : 'none';
                        });
                    });
                }
            });