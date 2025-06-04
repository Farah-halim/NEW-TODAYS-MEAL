document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const priceRange = document.getElementById('priceRange');
    const menuGrid = document.getElementById('menuGrid');
    const noResults = document.getElementById('noResults');
    const categoryBtns = document.querySelectorAll('.category-btn');
    
    let currentCategory = 'all';

    searchInput.addEventListener('input', function() {
        filterMenuItems();
    });

    // Price range filtering
    priceRange.addEventListener('change', function() {
        filterMenuItems();
    });

    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            categoryBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.value;
            filterMenuItems();
        });
    });

    function filterMenuItems() {
        const searchQuery = searchInput.value.toLowerCase().trim();
        const selectedPriceRange = priceRange.value;
        const items = menuGrid.querySelectorAll('.menu-item');
        let visibleCount = 0;

        items.forEach(item => {
            const name = item.querySelector('.item-name').textContent.toLowerCase();
            const description = item.querySelector('.item-description').textContent.toLowerCase();
            const categoryIds = item.dataset.category ? item.dataset.category.split(',') : [];
            const price = parseFloat(item.dataset.price);
            const tags = item.dataset.tags ? item.dataset.tags.toLowerCase() : '';
            
            const matchesSearch = searchQuery === '' || 
                                name.includes(searchQuery) || 
                                description.includes(searchQuery) || 
                                tags.includes(searchQuery);
            
            const matchesCategory = currentCategory === 'all' || 
                                  categoryIds.includes(currentCategory);
            
            let matchesPrice = true;
            if (selectedPriceRange === '0-50') {
                matchesPrice = price <= 50;
            } else if (selectedPriceRange === '50-100') {
                matchesPrice = price > 50 && price <= 100;
            } else if (selectedPriceRange === '100+') {
                matchesPrice = price > 100;
            }

            // Show/hide based on all filters
            if (matchesSearch && matchesCategory && matchesPrice) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        console.log(`Search Query: "${searchQuery}"`);
        console.log(`Visible Items: ${visibleCount}`);
    }
    filterMenuItems();
});

