<?php
require_once 'db_connect.php';

// Fetch categories, subcategories, and tags with additional statistics
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(cksc.cloud_kitchen_id) as kitchens_count 
                         FROM category c 
                         LEFT JOIN cloud_kitchen_specialist_category cksc ON c.cat_id = cksc.cat_id 
                         GROUP BY c.cat_id 
                         ORDER BY c.c_name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT s.*, c.c_name as parent_name 
                         FROM sub_category s 
                         JOIN category c ON s.parent_cat_id = c.cat_id
                         ORDER BY c.c_name, s.subcat_name");
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT dt.*, COUNT(ct.user_id) as usage_count
                         FROM dietary_tags dt
                         LEFT JOIN caterer_tags ct ON dt.tag_id = ct.tag_id
                         GROUP BY dt.tag_id
                         ORDER BY dt.tag_name");
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate statistics
    $total_categories = count($categories);
    $total_subcategories = count($subcategories);
    $total_tags = count($tags);
    $categories_with_kitchens = count(array_filter($categories, function($cat) { return $cat['kitchens_count'] > 0; }));

} catch(PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<!-- Statistics Overview -->
<div class="stats-overview fade-in">
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-item">
                <i class="fas fa-tags fa-2x text-primary mb-2"></i>
                <span class="stats-number"><?= $total_categories ?></span>
                <div class="stats-label">Total Categories</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-item">
                <i class="fas fa-tag fa-2x text-success mb-2"></i>
                <span class="stats-number"><?= $total_subcategories ?></span>
                <div class="stats-label">Subcategories</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-item">
                <i class="fas fa-leaf fa-2x text-info mb-2"></i>
                <span class="stats-number"><?= $total_tags ?></span>
                <div class="stats-label">Dietary Tags</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-item">
                <i class="fas fa-store fa-2x text-warning mb-2"></i>
                <span class="stats-number"><?= $categories_with_kitchens ?></span>
                <div class="stats-label">Active Categories</div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <!-- Action Buttons -->
        <div class="action-buttons fade-in">
            <button class="btn btn-primary" onclick="toggleForm('categoryForm')">
                <i class="fas fa-plus me-2"></i>Add New Category
            </button>
            <button class="btn btn-success" onclick="toggleForm('tagForm')">
                <i class="fas fa-tag me-2"></i>Add Dietary Tag
            </button>
            <button class="btn btn-info" onclick="toggleForm('subcategoryForm')">
                <i class="fas fa-plus-circle me-2"></i>Add Subcategory
            </button>
        </div>

        <!-- Enhanced Category Form -->
        <div id="categoryForm" class="form-container card mb-4 fade-in" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-folder-plus me-2"></i>Add New Category</h2>
            </div>
            <div class="card-body">
                <form action="save_category.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoryName" class="form-label">
                                <i class="fas fa-tag me-1"></i>Category Name
                            </label>
                            <input type="text" class="form-control" id="categoryName" name="name" required 
                                   placeholder="Enter category name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="categoryPhoto" class="form-label">
                                <i class="fas fa-image me-1"></i>Category Photo
                            </label>
                            <input type="file" class="form-control" id="categoryPhoto" name="category_photo" 
                                   accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Description
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Enter category description"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Category
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm('categoryForm')">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enhanced Subcategory Form -->
        <div id="subcategoryForm" class="form-container card mb-4 fade-in" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-plus-circle me-2"></i>Add New Subcategory</h2>
            </div>
            <div class="card-body">
                <form action="save_subcategory.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subcategoryName" class="form-label">
                                <i class="fas fa-tag me-1"></i>Subcategory Name
                            </label>
                            <input type="text" class="form-control" id="subcategoryName" name="subcat_name" required 
                                   placeholder="Enter subcategory name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="parentCategory" class="form-label">
                                <i class="fas fa-folder me-1"></i>Parent Category
                            </label>
                            <select class="form-select" id="parentCategory" name="parent_cat_id" required>
                                <option value="">Select parent category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?= $category['cat_id'] ?>">
                                        <?= htmlspecialchars($category['c_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save me-2"></i>Save Subcategory
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm('subcategoryForm')">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enhanced Tag Form -->
        <div id="tagForm" class="form-container card mb-4 fade-in" style="display: none;">
            <div class="section-header">
                <h2><i class="fas fa-leaf me-2"></i>Add New Dietary Tag</h2>
            </div>
            <div class="card-body">
                <form action="save_tag.php" method="POST">
                    <div class="mb-3">
                        <label for="tagName" class="form-label">
                            <i class="fas fa-leaf me-1"></i>Tag Name
                        </label>
                        <input type="text" class="form-control" id="tagName" name="tag_name" required 
                               placeholder="Enter dietary tag name (e.g., Vegan, Gluten-Free)">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Save Tag
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleForm('tagForm')">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Categories and Tags List -->
<div class="row mb-4">
    <!-- Categories List -->
    <div class="col-md-8">
        <div class="card fade-in">
            <div class="section-header">
                <h2><i class="fas fa-folder me-2"></i>Food Categories</h2>
            </div>
            <div class="card-body">
                <?php if(empty($categories)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No categories found. Start by adding your first category.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($categories as $category): ?>
                        <div class="category-item">
                            <div class="category-header d-flex justify-content-between align-items-center" 
                                 onclick="showKitchensByCategory(<?= $category['cat_id'] ?>, '<?= htmlspecialchars($category['c_name']) ?>')"
                                 style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-folder text-warning me-3 fs-5"></i>
                                    <div>
                                        <strong class="fs-6"><?= htmlspecialchars($category['c_name']) ?></strong>
                                        <?php if($category['description']): ?>
                                            <div class="text-muted small mt-1">
                                                <?= htmlspecialchars($category['description']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if($category['kitchens_count'] > 0): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-store me-1"></i><?= $category['kitchens_count'] ?> kitchens
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-store me-1"></i>No kitchens
                                        </span>
                                    <?php endif; ?>
                                    <form action="delete_category.php" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.')" 
                                          onclick="event.stopPropagation()">
                                        <input type="hidden" name="category_id" value="<?= $category['cat_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Subcategories -->
                            <div class="subcategories ms-4 mt-3">
                                <?php 
                                $hasSubcategories = false;
                                foreach($subcategories as $sub): 
                                    if($sub['parent_cat_id'] == $category['cat_id']): 
                                        $hasSubcategories = true;
                                ?>
                                        <div class="sub-category">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-tag text-info me-2"></i>
                                                    <span><?= htmlspecialchars($sub['subcat_name']) ?></span>
                                                </div>
                                                <form action="delete_category.php" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this subcategory?')">
                                                    <input type="hidden" name="subcategory_id" value="<?= $sub['subcat_id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                <?php endif; endforeach; ?>
                                <?php if(!$hasSubcategories): ?>
                                    <div class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>No subcategories defined
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tags List -->
    <div class="col-md-4">
        <div class="card fade-in">
            <div class="section-header">
                <h2><i class="fas fa-leaf me-2"></i>Dietary Tags</h2>
            </div>
            <div class="card-body">
                <?php if(empty($tags)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-leaf fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No dietary tags found. Add tags to help customers find suitable meals.</p>
                    </div>
                <?php else: ?>
                    <div class="tags-container">
                        <?php foreach($tags as $tag): ?>
                            <div class="tag-item d-flex justify-content-between align-items-center mb-3" 
                                 onclick="showKitchensByTag(<?= $tag['tag_id'] ?>, '<?= htmlspecialchars($tag['tag_name']) ?>')"
                                 role="button">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-leaf text-success me-2"></i>
                                    <span class="fw-medium"><?= htmlspecialchars($tag['tag_name']) ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info">
                                        <i class="fas fa-utensils me-1"></i><?= $tag['usage_count'] ?>
                                    </span>
                                    <form action="delete_tag.php" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this tag? This will remove it from all associated kitchens.')" 
                                          onclick="event.stopPropagation()">
                                        <input type="hidden" name="tag_id" value="<?= $tag['tag_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleForm(formId) {
    const form = document.getElementById(formId);
    const allForms = ['categoryForm', 'tagForm', 'subcategoryForm'];
    
    // Hide all other forms
    allForms.forEach(id => {
        if (id !== formId) {
            document.getElementById(id).style.display = 'none';
        }
    });
    
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        form.querySelector('input[type="text"]').focus();
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        form.style.display = 'none';
    }
}

async function showKitchensByTag(tagId, tagName) {
    showLoadingSpinner();
    try {
        const response = await fetch(`get_kitchens_by_tag.php?tag_id=${tagId}`);
        const kitchens = await response.json();
        displayKitchensPreview(kitchens, `Kitchens with tag: ${tagName}`, 'tag');
    } catch (error) {
        console.error('Error:', error);
        showToast('Failed to fetch kitchens', 'danger');
    } finally {
        hideLoadingSpinner();
    }
}

async function showKitchensByCategory(catId, catName) {
    showLoadingSpinner();
    try {
        const response = await fetch(`get_kitchens_by_category.php?category_id=${catId}`);
        const kitchens = await response.json();
        displayKitchensPreview(kitchens, `Kitchens in category: ${catName}`, 'category');
    } catch (error) {
        console.error('Error:', error);
        showToast('Failed to fetch kitchens', 'danger');
    } finally {
        hideLoadingSpinner();
    }
}

function displayKitchensPreview(kitchens, title, type) {
    const preview = document.createElement('div');
    preview.className = 'modal-preview';
    preview.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="fas fa-${type === 'tag' ? 'leaf' : 'folder'} me-2"></i>${title}</h4>
            <button onclick="this.closest('.modal-preview').remove()" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times"></i>
            </button>
        </div>
        ${kitchens.length === 0 ? 
          `<div class="text-center py-4">
               <i class="fas fa-store fa-3x text-muted mb-3"></i>
               <p class="text-muted">No kitchens found in this ${type}.</p>
           </div>` :
          `<p class="text-muted mb-3">
               <i class="fas fa-info-circle me-1"></i>${kitchens.length} kitchen(s) found
           </p>
           <div class="kitchen-preview-list" style="max-height: 300px; overflow-y: auto;">
               ${kitchens.map(k => `
                   <div class="kitchen-preview-item mb-3">
                       <div class="d-flex justify-content-between align-items-start">
                           <div>
                               <strong class="d-block">${k.business_name || k.u_name}</strong>
                               <div class="text-muted small mt-1">
                                   <i class="fas fa-envelope me-1"></i>${k.mail || 'No email'}
                               </div>
                           </div>
                           <div class="text-end">
                               <span class="badge ${k.status === 'active' ? 'bg-success' : k.status === 'suspended' ? 'bg-warning' : 'bg-danger'} mb-1">
                                   ${k.status}
                               </span>
                               <div class="text-muted small">
                                   <i class="fas fa-shopping-cart me-1"></i>Orders: ${k.orders_count || 0}
                               </div>
                           </div>
                       </div>
                   </div>
               `).join('')}
           </div>`
        }
        <div class="mt-4 d-flex gap-2">
            <a href="manage_kitchens.php" class="btn btn-primary">
                <i class="fas fa-external-link-alt me-2"></i>View All Kitchens
            </a>
            <button onclick="this.closest('.modal-preview').remove()" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i>Close
            </button>
        </div>`;
    
    // Remove existing previews
    document.querySelectorAll('.modal-preview').forEach(el => el.remove());
    
    // Add backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.style.zIndex = '1049';
    backdrop.onclick = () => {
        backdrop.remove();
        preview.remove();
    };
    
    document.body.appendChild(backdrop);
    document.body.appendChild(preview);
    
    setTimeout(() => preview.classList.add('show'), 10);
}

function showLoadingSpinner() {
    // Add loading spinner logic if needed
}

function hideLoadingSpinner() {
    // Remove loading spinner logic if needed
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Add smooth animations on page load
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.fade-in');
    elements.forEach((el, index) => {
        el.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>