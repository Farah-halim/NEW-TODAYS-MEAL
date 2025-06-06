<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'connection.php';

$kitchen_id = (int)($_GET['id'] ?? 0);

// Debug: Log the kitchen ID
error_log("get_kitchen_documents.php called with kitchen_id: " . $kitchen_id);

if ($kitchen_id <= 0) {
    echo '<div class="alert alert-danger">Invalid kitchen ID: ' . htmlspecialchars($_GET['id'] ?? 'null') . '</div>';
    exit();
}

try {
    // Get kitchen approval status
    $kitchenQuery = "SELECT is_approved FROM cloud_kitchen_owner WHERE user_id = ?";
    $kitchenStmt = $conn->prepare($kitchenQuery);
    
    if (!$kitchenStmt) {
        throw new Exception("Kitchen query prepare failed: " . $conn->error);
    }
    
    $kitchenStmt->bind_param("i", $kitchen_id);
    
    if (!$kitchenStmt->execute()) {
        throw new Exception("Kitchen query execute failed: " . $kitchenStmt->error);
    }
    
    $kitchenResult = $kitchenStmt->get_result();
    $kitchenData = $kitchenResult->fetch_assoc();
    $isKitchenApproved = $kitchenData['is_approved'] ?? 0;
    
    // Debug: Log kitchen status
    error_log("Kitchen $kitchen_id approval status: " . ($isKitchenApproved ? 'approved' : 'pending'));

    // Get all documents for this kitchen
    $query = "SELECT kd.*, a.u_name as reviewed_by_name 
              FROM kitchen_documents kd
              LEFT JOIN admin ad ON kd.reviewed_by = ad.user_id
              LEFT JOIN users a ON ad.user_id = a.user_id
              WHERE kd.kitchen_id = ? 
              ORDER BY kd.document_type, kd.upload_date DESC";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Documents query prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $kitchen_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Documents query execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $documents = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $documents[] = $row;
        }
    }
    
    // Debug: Log document count
    error_log("Found " . count($documents) . " documents for kitchen $kitchen_id");
    
} catch (Exception $e) {
    error_log("Error in get_kitchen_documents.php: " . $e->getMessage());
    echo '<div class="alert alert-danger">';
    echo '<i class="fas fa-exclamation-circle me-2"></i>';
    echo '<strong>Database Error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '<br><small>Kitchen ID: ' . $kitchen_id . '</small>';
    echo '</div>';
    exit();
}
?>

<style>
    .document-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    .document-card:hover {
        border-color: #e57e24;
        box-shadow: 0 2px 8px rgba(229, 126, 36, 0.1);
    }
    .document-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
    }
    .document-body {
        padding: 1rem;
    }
    .doc-type-badge {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .file-info {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .document-actions {
        margin-top: 0.75rem;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-approved {
        background: #d1e7dd;
        color: #0f5132;
    }
    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }
    .empty-documents {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    .kitchen-status-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #e57e24;
    }
</style>

<div class="documents-container">
    <!-- Kitchen Status Information -->
    <div class="kitchen-status-info">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-2">
                    <i class="fas fa-info-circle me-2"></i>Kitchen Approval Status
                </h6>
                <p class="mb-0">
                    <?php if ($isKitchenApproved): ?>
                        <span class="badge status-approved me-2">
                            <i class="fas fa-check me-1"></i>Kitchen Approved
                        </span>
                        All documents are considered approved with the kitchen registration.
                    <?php else: ?>
                        <span class="badge status-pending me-2">
                            <i class="fas fa-clock me-1"></i>Kitchen Pending Approval
                        </span>
                        Documents are pending review until kitchen is approved.
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">
                    <i class="fas fa-file-alt me-1"></i>
                    <?php echo count($documents); ?> document(s) uploaded
                </small>
            </div>
        </div>
    </div>

    <?php if (empty($documents)): ?>
        <div class="empty-documents">
            <i class="fas fa-folder-open fa-3x mb-3" style="color: #dee2e6;"></i>
            <h5>No Documents Found</h5>
            <p>This kitchen owner hasn't uploaded any documents yet.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php 
            $document_types = [
                'national_id' => ['National ID', 'fas fa-id-card', '#17a2b8'],
                'business_license' => ['Business License', 'fas fa-certificate', '#28a745'],
                'health_certificate' => ['Health Certificate', 'fas fa-medkit', '#dc3545'],
                'tax_certificate' => ['Tax Certificate', 'fas fa-receipt', '#6f42c1'],
                'kitchen_photos' => ['Kitchen Photos', 'fas fa-camera', '#fd7e14'],
                'other' => ['Other Documents', 'fas fa-file-alt', '#6c757d']
            ];
            
            foreach ($documents as $doc): 
                $type_info = $document_types[$doc['document_type']] ?? $document_types['other'];
                $file_size_mb = round($doc['file_size'] / 1048576, 2);
                $is_image = strpos($doc['file_type'], 'image/') === 0;
                $is_pdf = $doc['file_type'] === 'application/pdf';
                
                // Determine document status based on kitchen approval and admin notes
                $hasAdminNotes = !empty($doc['admin_notes']);
                if ($isKitchenApproved && !$hasAdminNotes) {
                    $docStatus = 'approved';
                    $statusLabel = 'Approved';
                } elseif ($hasAdminNotes) {
                    $docStatus = 'rejected';
                    $statusLabel = 'Issues Noted';
                } else {
                    $docStatus = 'pending';
                    $statusLabel = 'Pending';
                }
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="document-card">
                        <div class="document-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="doc-type-badge badge" style="background-color: <?php echo $type_info[2]; ?>;">
                                    <i class="<?php echo $type_info[1]; ?> me-1"></i>
                                    <?php echo $type_info[0]; ?>
                                </span>
                                <span class="badge status-<?php echo $docStatus; ?>">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </div>
                        </div>
                        <div class="document-body">
                            <h6 class="mb-2"><?php echo htmlspecialchars($doc['document_name']); ?></h6>
                            
                            <div class="file-info mb-2">
                                <div><i class="fas fa-file me-1"></i> <?php echo strtoupper(pathinfo($doc['file_path'], PATHINFO_EXTENSION)); ?> File</div>
                                <div><i class="fas fa-hdd me-1"></i> <?php echo $file_size_mb; ?> MB</div>
                                <div><i class="fas fa-calendar me-1"></i> <?php echo date('M j, Y', strtotime($doc['upload_date'])); ?></div>
                            </div>

                            <?php if ($doc['admin_notes']): ?>
                                <div class="alert alert-sm alert-warning">
                                    <strong><i class="fas fa-exclamation-triangle me-1"></i>Admin Notes:</strong><br>
                                    <?php echo htmlspecialchars($doc['admin_notes']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($doc['reviewed_by'] && $doc['reviewed_at']): ?>
                                <div class="file-info">
                                    <small>
                                        <i class="fas fa-user-check me-1"></i>
                                        Reviewed by <?php echo htmlspecialchars($doc['reviewed_by_name']); ?> 
                                        on <?php echo date('M j, Y', strtotime($doc['reviewed_at'])); ?>
                                    </small>
                                </div>
                            <?php endif; ?>

                            <div class="document-actions">
                                <div class="btn-group w-100">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewDocument(<?php echo $doc['doc_id']; ?>, '<?php echo addslashes($doc['file_path']); ?>', '<?php echo $doc['file_type']; ?>')">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="downloadDocument(<?php echo $doc['doc_id']; ?>, '<?php echo addslashes($doc['file_path']); ?>')">
                                        <i class="fas fa-download me-1"></i>Download
                                    </button>
                                    <?php if (!$isKitchenApproved): ?>
                                        <button class="btn btn-outline-warning btn-sm" onclick="addDocumentNotes(<?php echo $doc['doc_id']; ?>, '<?php echo addslashes($doc['admin_notes'] ?? ''); ?>')" title="Add Notes">
                                            <i class="fas fa-sticky-note"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentViewerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="documentContent">
                    <!-- Document content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Ensure script executes in modal context
(function() {
    console.log('Document functions initialized'); // Debug log
    
    // Make functions globally available
    window.viewDocument = function(docId, filePath, fileType) {
        console.log('viewDocument called:', {docId, filePath, fileType}); // Debug log
        
        const content = document.getElementById('documentContent');
        if (!content) {
            console.error('Document content container not found');
            showToast('Error: Document viewer not available', 'error');
            return;
        }
        
        // Close any existing modals first
        const existingModal = bootstrap.Modal.getInstance(document.getElementById('documentViewerModal'));
        if (existingModal) {
            existingModal.hide();
        }
        
        // Clear previous content
        content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading document...</p></div>';
        
        // Show modal first
        const modal = new bootstrap.Modal(document.getElementById('documentViewerModal'));
        modal.show();
        
        // Load content after modal is shown
        setTimeout(() => {
            if (fileType.startsWith('image/')) {
                content.innerHTML = `
                    <img src="${filePath}" class="img-fluid" style="max-height: 500px;" alt="Document" 
                         onerror="this.parentElement.innerHTML='<div class=\\"alert alert-danger\\">Failed to load image</div>'">
                `;
            } else if (fileType === 'application/pdf') {
                content.innerHTML = `
                    <iframe src="${filePath}" width="100%" height="500px" frameborder="0" 
                            onerror="this.parentElement.innerHTML='<div class=\\"alert alert-danger\\">Failed to load PDF</div>'">
                        <p>Your browser does not support PDFs. <a href="${filePath}" target="_blank">Download the PDF</a></p>
                    </iframe>
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This file type cannot be previewed. Please download to view.
                        <br><br>
                        <a href="${filePath}" download class="btn btn-primary" onclick="downloadDocument(${docId}, '${filePath}')">
                            <i class="fas fa-download me-1"></i>Download File
                        </a>
                    </div>
                `;
            }
        }, 100);
    };

    window.downloadDocument = function(docId, filePath) {
        console.log('downloadDocument called:', {docId, filePath}); // Debug log
        
        try {
            // Create hidden link element
            const link = document.createElement('a');
            link.href = filePath;
            link.download = filePath.split('/').pop();
            link.style.display = 'none';
            
            // Add to body, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showToast('Download started', 'success');
        } catch (error) {
            console.error('Download error:', error);
            showToast('Download failed', 'error');
            
            // Fallback: open in new tab
            window.open(filePath, '_blank');
        }
    };

    window.addDocumentNotes = function(docId, currentNotes) {
        console.log('addDocumentNotes called:', {docId, currentNotes}); // Debug log
        
        // Use a better prompt dialog
        const notes = prompt('Add admin notes for this document:\n(Leave empty to clear notes)', currentNotes || '');
        
        if (notes === null) {
            return; // User cancelled
        }
        
        if (notes === currentNotes) {
            showToast('No changes made', 'info');
            return; // No changes
        }
        
        // Show loading
        showToast('Updating notes...', 'info');
        
        fetch('update_document_notes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                doc_id: docId,
                admin_notes: notes
            })
        })
        .then(response => {
            console.log('Update response status:', response.status); // Debug log
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Update response data:', data); // Debug log
            if (data.success) {
                showToast('Document notes updated successfully', 'success');
                // Refresh the documents by reloading the tab content
                setTimeout(() => {
                    if (typeof loadDocuments === 'function') {
                        loadDocuments(<?php echo $kitchen_id; ?>);
                    } else {
                        location.reload();
                    }
                }, 1000);
            } else {
                showToast('Error: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Update error:', error); // Debug log
            showToast('Error updating document notes: ' + error.message, 'error');
        });
    };

    window.showToast = function(message, type) {
        // Remove existing toasts
        document.querySelectorAll('.custom-toast').forEach(toast => toast.remove());
        
        // Create new toast
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} position-fixed custom-toast`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 99999; min-width: 300px; max-width: 400px;';
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="this.closest('.custom-toast').remove()"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    };
    
    console.log('Document functions ready'); // Debug log
    
})();
</script>
</script> 