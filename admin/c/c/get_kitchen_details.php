<?php
require_once 'connection.php';

$kitchen_id = (int)($_GET['id'] ?? 0);

if ($kitchen_id <= 0) {
    echo '<div class="alert alert-danger">Invalid kitchen ID</div>';
    exit();
}

// Get comprehensive kitchen details
$query = "SELECT cko.*, u.u_name, u.mail, u.phone, u.created_at as user_created,
          eu.address, eu.latitude, eu.longitude, z.name as zone_name,
          cat.c_name as speciality_name,
          GROUP_CONCAT(DISTINCT dt.tag_name SEPARATOR ', ') as dietary_tags,
          GROUP_CONCAT(DISTINCT cats.c_name SEPARATOR ', ') as all_categories
          FROM cloud_kitchen_owner cko
          JOIN users u ON cko.user_id = u.user_id
          JOIN external_user eu ON cko.user_id = eu.user_id
          LEFT JOIN zones z ON eu.zone_id = z.zone_id
          JOIN category cat ON cko.speciality_id = cat.cat_id
          LEFT JOIN caterer_tags ct ON cko.user_id = ct.user_id
          LEFT JOIN dietary_tags dt ON ct.tag_id = dt.tag_id
          LEFT JOIN cloud_kitchen_specialist_category cksc ON cko.user_id = cksc.cloud_kitchen_id
          LEFT JOIN category cats ON cksc.cat_id = cats.cat_id
          WHERE cko.user_id = ?
          GROUP BY cko.user_id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $kitchen_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">Kitchen not found</div>';
    exit();
}

$kitchen = $result->fetch_assoc();

// Get meals count
$mealsQuery = "SELECT COUNT(*) as meal_count FROM meals WHERE cloud_kitchen_id = ?";
$mealsStmt = $conn->prepare($mealsQuery);
$mealsStmt->bind_param("i", $kitchen_id);
$mealsStmt->execute();
$mealsResult = $mealsStmt->get_result();
$mealsData = $mealsResult->fetch_assoc();
$mealCount = $mealsData['meal_count'];

// Get recent orders count
$ordersQuery = "SELECT COUNT(*) as recent_orders FROM orders WHERE cloud_kitchen_id = ? AND order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bind_param("i", $kitchen_id);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();
$ordersData = $ordersResult->fetch_assoc();
$recentOrders = $ordersData['recent_orders'];

// Get documents count
$docsQuery = "SELECT COUNT(*) as doc_count, 
              SUM(CASE WHEN admin_notes IS NOT NULL AND admin_notes != '' THEN 1 ELSE 0 END) as docs_with_notes,
              SUM(CASE WHEN reviewed_by IS NOT NULL THEN 1 ELSE 0 END) as reviewed_docs
              FROM kitchen_documents WHERE kitchen_id = ?";
$docsStmt = $conn->prepare($docsQuery);
$docsStmt->bind_param("i", $kitchen_id);
$docsStmt->execute();
$docsResult = $docsStmt->get_result();
$docsData = $docsResult->fetch_assoc();
?>

<style>
    .detail-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #e57e24;
    }
    .detail-label {
        font-weight: 600;
        color: #6a4125;
        margin-bottom: 0.25rem;
    }
    .detail-value {
        color: #495057;
        margin-bottom: 0.75rem;
    }
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    .status-approved {
        background: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .status-active {
        background: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .status-blocked {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .rating-display {
        color: #ffc107;
        font-size: 1.1rem;
    }
    .documents-summary {
        background: linear-gradient(135deg, #e57e24 0%, #f39c12 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(229, 126, 36, 0.2);
    }
    .doc-stat {
        display: inline-block;
    }
    .doc-stat h3 {
        color: white;
        margin: 0;
        font-weight: bold;
        font-size: 2.5rem;
        line-height: 1;
    }
    .doc-stat .label {
        color: rgba(255,255,255,0.9);
        font-size: 1rem;
        margin-top: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 500;
    }
    .doc-stat .icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.8;
    }
</style>

<!-- Navigation Tabs for Details and Documents -->
<ul class="nav nav-tabs mb-3" id="kitchenDetailsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
            <i class="fas fa-info-circle me-1"></i>Kitchen Details
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
            <i class="fas fa-file-alt me-1"></i>Documents 
            <?php if ($docsData['doc_count'] > 0): ?>
                <span class="badge bg-primary ms-1"><?php echo $docsData['doc_count']; ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<div class="tab-content" id="kitchenDetailsTabContent">
    <!-- Kitchen Details Tab -->
    <div class="tab-pane fade show active" id="details" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="detail-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>Owner Information
                    </h6>
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($kitchen['u_name']); ?></div>
                    
                    <div class="detail-label">Email Address</div>
                    <div class="detail-value">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($kitchen['mail']); ?>
                    </div>
                    
                    <div class="detail-label">Phone Number</div>
                    <div class="detail-value">
                        <i class="fas fa-phone me-2"></i>
                        <?php echo htmlspecialchars($kitchen['phone']); ?>
                    </div>
                    
                    <div class="detail-label">National ID</div>
                    <div class="detail-value">
                        <i class="fas fa-id-card me-2"></i>
                        <?php echo htmlspecialchars($kitchen['c_n_id']); ?>
                    </div>
                </div>

                <div class="detail-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-store me-2"></i>Business Information
                    </h6>
                    <div class="detail-label">Business Name</div>
                    <div class="detail-value">
                        <strong><?php echo htmlspecialchars($kitchen['business_name']); ?></strong>
                    </div>
                    
                    <div class="detail-label">Years of Experience</div>
                    <div class="detail-value">
                        <span class="badge bg-info"><?php echo htmlspecialchars($kitchen['years_of_experience']); ?></span>
                    </div>
                    
                    <div class="detail-label">Started in</div>
                    <div class="detail-value"><?php echo htmlspecialchars($kitchen['start_year']); ?></div>
                    
                    <div class="detail-label">Provide Customized Orders</div>
                    <div class="detail-value">
                        <?php if ($kitchen['customized_orders']): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Yes
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <i class="fas fa-times me-1"></i>No
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="detail-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>Location Details
                    </h6>
                    <div class="detail-label">Address</div>
                    <div class="detail-value"><?php echo htmlspecialchars($kitchen['address']); ?></div>
                    
                    <div class="detail-label">Zone</div>
                    <div class="detail-value">
                        <span class="badge bg-warning text-dark">
                            <?php echo htmlspecialchars($kitchen['zone_name'] ?? 'Not Assigned'); ?>
                        </span>
                    </div>
                    
                    <?php if ($kitchen['latitude'] && $kitchen['longitude']): ?>
                    <div class="detail-label">Coordinates</div>
                    <div class="detail-value">
                        <small class="text-muted">
                            Lat: <?php echo $kitchen['latitude']; ?>, 
                            Lng: <?php echo $kitchen['longitude']; ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="detail-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-utensils me-2"></i>Cuisine Information
                    </h6>
                    <div class="detail-label">Primary Speciality</div>
                    <div class="detail-value">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($kitchen['speciality_name']); ?></span>
                    </div>
                    
                    <?php if ($kitchen['all_categories']): ?>
                    <div class="detail-label">All Categories</div>
                    <div class="detail-value">
                        <?php 
                        $categories = explode(', ', $kitchen['all_categories']);
                        foreach ($categories as $category): 
                        ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($category); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($kitchen['dietary_tags']): ?>
                    <div class="detail-label">Dietary Tags</div>
                    <div class="detail-value">
                        <?php 
                        $tags = explode(', ', $kitchen['dietary_tags']);
                        foreach ($tags as $tag): 
                        ?>
                            <span class="badge bg-success me-1"><?php echo htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="detail-section">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-chart-line me-2"></i>Status
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-label">Approval Status</div>
                            <div class="detail-value">
                                <?php if ($kitchen['is_approved']): ?>
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check me-1"></i>Approved
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-label">Registration Date</div>
                            <div class="detail-value">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo date('F j, Y \a\t g:i A', strtotime($kitchen['registration_date'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Tab -->
    <div class="tab-pane fade" id="documents" role="tabpanel">
        <?php if ($docsData['doc_count'] > 0): ?>
            <div class="documents-summary">
                <div class="doc-stat">
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3><?php echo $docsData['doc_count']; ?></h3>
                    <div class="label">Total Documents</div>
                </div>
            </div>
        <?php endif; ?>

        <div id="documentsContainer">
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading documents...</span>
                </div>
                <p class="mt-2">Loading documents...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?php if (!$kitchen['is_approved']): ?>
        <button type="button" class="btn btn-success" onclick="approveKitchen(<?php echo $kitchen_id; ?>); $('#kitchenModal').modal('hide');">
            <i class="fas fa-check me-1"></i>Approve Kitchen
        </button>
        <button type="button" class="btn btn-danger" onclick="rejectKitchen(<?php echo $kitchen_id; ?>); $('#kitchenModal').modal('hide');">
            <i class="fas fa-times me-1"></i>Reject Registration
        </button>
    <?php else: ?>
        <a href="kitchen_details.php?id=<?php echo $kitchen_id; ?>" class="btn btn-primary" target="_blank">
            <i class="fas fa-external-link-alt me-1"></i>View Full Details
        </a>
    <?php endif; ?>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

<script>
(function() {
    console.log('Kitchen details script loaded'); // Debug log
    
    // Ensure DOM is ready and elements exist
    function initializeDocumentTabs() {
        const documentsTab = document.getElementById('documents-tab');
        const documentsPane = document.getElementById('documents');
        
        if (!documentsTab) {
            console.error('Documents tab not found');
            return;
        }
        
        console.log('Documents tab found, adding event listener'); // Debug log
        
        // Remove any existing event listeners to prevent duplicates
        documentsTab.removeEventListener('click', loadDocumentsHandler);
        
        // Add the event listener
        documentsTab.addEventListener('click', loadDocumentsHandler);
        
        // Auto-load documents if the tab is already active
        if (documentsPane && documentsPane.classList.contains('active')) {
            console.log('Documents tab is active on load, loading documents'); // Debug log
            loadDocuments(<?php echo $kitchen_id; ?>);
        }
    }
    
    // Event handler function
    function loadDocumentsHandler() {
        console.log('Documents tab clicked'); // Debug log
        loadDocuments(<?php echo $kitchen_id; ?>);
    }
    
    function loadDocuments(kitchenId) {
        console.log('loadDocuments called with kitchenId:', kitchenId); // Debug log
        const container = document.getElementById('documentsContainer');
        
        if (!container) {
            console.error('Documents container not found');
            return;
        }
        
        // Show loading state
        container.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading documents...</span>
                </div>
                <p class="mt-2">Loading documents for kitchen ${kitchenId}...</p>
            </div>
        `;
        
        console.log('Making fetch request to: get_kitchen_documents.php?id=' + kitchenId); // Debug log
        
        fetch(`get_kitchen_documents.php?id=${kitchenId}`)
            .then(response => {
                console.log('Fetch response received:', response); // Debug log
                console.log('Response status:', response.status); // Debug log
                console.log('Response ok:', response.ok); // Debug log
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                console.log('Response HTML length:', html.length); // Debug log
                console.log('Response HTML preview:', html.substring(0, 200)); // Debug log
                container.innerHTML = html;
                
                // Execute any scripts in the loaded content
                executeScriptsInElement(container);
                
                console.log('Documents loaded successfully'); // Debug log
            })
            .catch(error => {
                console.error('Error loading documents:', error); // Debug log
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading documents: ${error.message}
                        <br><small>Check browser console for details</small>
                    </div>
                `;
            });
    }
    
    // Function to execute scripts in dynamically loaded content
    function executeScriptsInElement(element) {
        const scripts = element.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            
            // Replace the old script with the new one
            script.parentNode.replaceChild(newScript, script);
        });
        
        console.log(`Executed ${scripts.length} scripts in loaded content`); // Debug log
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDocumentTabs);
    } else {
        // DOM is already ready
        initializeDocumentTabs();
    }
    
    // Also try to initialize after a short delay to ensure modal content is fully rendered
    setTimeout(initializeDocumentTabs, 100);
    
    // Make functions globally available for debugging and cross-modal access
    window.loadDocuments = loadDocuments;
    window.initializeDocumentTabs = initializeDocumentTabs;
    window.executeScriptsInElement = executeScriptsInElement;
    
})();
</script>