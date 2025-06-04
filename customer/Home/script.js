 document.getElementById('exploreMealsBtn').addEventListener('click', function() {
            document.getElementById('search-section').scrollIntoView({
                behavior: 'smooth'
            });
        });

        const searchInput = document.getElementById('searchInput');
        const searchBar = document.getElementById('searchBar');
        const categoryGrid = document.getElementById('categoryGrid');
        const categoriesTitle = document.getElementById('categoriesTitle');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchBar.classList.add('loading');
            
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.trim();
                
                if (searchTerm === '') {
                    fetchCategories('');
                    return;
                }
                fetchCategories(searchTerm);
            }, 300); 
        });

        function fetchCategories(searchTerm) {
            fetch(`?ajax_search=1&search=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(categories => {
                    updateCategoriesDisplay(categories, searchTerm);
                    searchBar.classList.remove('loading');
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchBar.classList.remove('loading');
                });
              }
              
        function updateCategoriesDisplay(categories, searchTerm) {
            const container = document.getElementById('categoriesContainer');
            
            if (categories.length === 0) {
                container.innerHTML = '<p class="no-results">No categories found matching your search.</p>';
                categoriesTitle.textContent = 'Search Results';
                return;
            }
            categoriesTitle.textContent = searchTerm ? 'Search Results' : 'Featured Categories';
            
            const newGrid = document.createElement('div');
            newGrid.className = 'category-grid';
            
            setTimeout(() => {
                newGrid.style.opacity = '0';
                newGrid.style.transform = 'translateY(20px)';
                
                categories.forEach(category => {
                    const categoryItem = document.createElement('a');
                    categoryItem.href = `\\NEW-TODAYS-MEAL\\customer\\Show_Caterers\\index.php?cat_id=${category.cat_id}`;
                    
                    const button = document.createElement('button');
                    button.className = 'category-button';
                    button.style.opacity = '0';
                    button.style.transform = 'scale(0.95)';
                    
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'category-image';
                    if (category.category_photo) {
                        imageDiv.style.backgroundImage = `url('${category.category_photo}')`;
                    } else {
                        imageDiv.classList.add('image-default');
                    }
                    
                    const span = document.createElement('span');
                    span.textContent = category.c_name;
                    
                    button.appendChild(imageDiv);
                    button.appendChild(span);
                    categoryItem.appendChild(button);
                    newGrid.appendChild(categoryItem);
                    
                    setTimeout(() => {
                        button.style.opacity = '1';
                        button.style.transform = 'scale(1)';
                    }, 100);
                });
                
                container.innerHTML = '';
                container.appendChild(newGrid);
                
                setTimeout(() => {
                    newGrid.style.opacity = '1';
                    newGrid.style.transform = 'translateY(0)';
                }, 50);
            }, 10);
        }

        if (searchInput.value) {
            fetchCategories(searchInput.value.trim());
        }