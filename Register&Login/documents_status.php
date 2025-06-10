<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../DB_connection.php";
session_start();

$error = '';
$success = '';
$user = null;
$documents = [];
$kitchen_id = null;
$search_email = '';
$show_email_form = true;

// Handle email-based lookup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'lookup_by_email') {
    $search_email = trim($_POST['email'] ?? '');
    
    if (empty($search_email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($search_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Look up cloud kitchen owner by email
        $email_query = "SELECT u.user_id, u.u_name, u.mail, eu.ext_role, cko.user_id AS cloud_kitchen_id, 
                               cko.business_name, cko.is_approved, cko.status, cko.registration_date
                        FROM users u
                        LEFT JOIN external_user eu ON u.user_id = eu.user_id
                        LEFT JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
                        WHERE u.mail = ? AND u.u_role = 'external_user' AND eu.ext_role = 'cloud_kitchen_owner'";
        
        $email_stmt = $conn->prepare($email_query);
        $email_stmt->bind_param("s", $search_email);
        $email_stmt->execute();
        $email_result = $email_stmt->get_result();
        
        if ($email_result->num_rows === 0) {
            $error = "No cloud kitchen registration found for this email address. Please check your email or register first.";
        } else {
            $user = $email_result->fetch_assoc();
            $kitchen_id = $user['cloud_kitchen_id'];
            $show_email_form = false;
            
            // Get all documents for this kitchen with re-upload detection
            $docs_query = "SELECT kd.*, a.u_name as reviewed_by_name,
                           (SELECT COUNT(*) FROM kitchen_documents kd2 
                            WHERE kd2.kitchen_id = kd.kitchen_id 
                            AND kd2.document_type = kd.document_type 
                            AND kd2.upload_date < kd.upload_date) as previous_uploads_count
                           FROM kitchen_documents kd
                           LEFT JOIN admin ad ON kd.reviewed_by = ad.user_id
                           LEFT JOIN users a ON ad.user_id = a.user_id
                           WHERE kd.kitchen_id = ? 
                           ORDER BY kd.document_type, kd.upload_date DESC";
            
            $docs_stmt = $conn->prepare($docs_query);
            $docs_stmt->bind_param("i", $kitchen_id);
            $docs_stmt->execute();
            $docs_result = $docs_stmt->get_result();
            
            // Process documents to get only the latest version of each type
            $latest_documents = [];
            if ($docs_result->num_rows > 0) {
                while ($row = $docs_result->fetch_assoc()) {
                    $doc_type = $row['document_type'];
                    // Only keep the latest document of each type (first one due to ORDER BY upload_date DESC)
                    if (!isset($latest_documents[$doc_type])) {
                        $row['is_reupload'] = $row['previous_uploads_count'] > 0;
                        $latest_documents[$doc_type] = $row;
                    }
                }
                $documents = array_values($latest_documents);
            }
        }
    }
}

// Check if user is logged in (fallback to session-based access)
elseif (isset($_SESSION['user_id'])) {
    $show_email_form = false;

$user_id = $_SESSION['user_id'];

// Check if user is a cloud kitchen owner
$query = "SELECT u.u_role, eu.ext_role, cko.user_id AS cloud_kitchen_id, cko.business_name, cko.is_approved, cko.status
          FROM users u
          LEFT JOIN external_user eu ON u.user_id = eu.user_id
          LEFT JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

    if (!$user || $user['u_role'] != 'external_user' || $user['ext_role'] != 'cloud_kitchen_owner' || !$user['cloud_kitchen_id']) {
        $error = "Access denied. This page is only for cloud kitchen owners. Please make sure you're logged in with a cloud kitchen account.";
    } else {
        $kitchen_id = $user['cloud_kitchen_id'];
        
        // Get all documents for this kitchen with re-upload detection
        $docs_query = "SELECT kd.*, a.u_name as reviewed_by_name,
                       (SELECT COUNT(*) FROM kitchen_documents kd2 
                        WHERE kd2.kitchen_id = kd.kitchen_id 
                        AND kd2.document_type = kd.document_type 
                        AND kd2.upload_date < kd.upload_date) as previous_uploads_count
                       FROM kitchen_documents kd
                       LEFT JOIN admin ad ON kd.reviewed_by = ad.user_id
                       LEFT JOIN users a ON ad.user_id = a.user_id
                       WHERE kd.kitchen_id = ? 
                       ORDER BY kd.document_type, kd.upload_date DESC";
        
        $docs_stmt = $conn->prepare($docs_query);
        $docs_stmt->bind_param("i", $kitchen_id);
        $docs_stmt->execute();
        $docs_result = $docs_stmt->get_result();
        
        // Process documents to get only the latest version of each type
        $latest_documents = [];
        if ($docs_result->num_rows > 0) {
            while ($row = $docs_result->fetch_assoc()) {
                $doc_type = $row['document_type'];
                // Only keep the latest document of each type (first one due to ORDER BY upload_date DESC)
                if (!isset($latest_documents[$doc_type])) {
                    $row['is_reupload'] = $row['previous_uploads_count'] > 0;
                    $latest_documents[$doc_type] = $row;
                }
            }
            $documents = array_values($latest_documents);
        }
    }
}

// Handle new document upload (works for both logged-in and email-verified users)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_document') {
    $upload_email = trim($_POST['upload_email'] ?? '');
    $upload_kitchen_id = intval($_POST['kitchen_id'] ?? 0);
    
    // Verify email matches the kitchen owner for security
    if (!empty($upload_email) && $upload_kitchen_id > 0) {
        $verify_query = "SELECT cko.user_id FROM users u
                        JOIN external_user eu ON u.user_id = eu.user_id
                        JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
                        WHERE u.mail = ? AND cko.user_id = ?";
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bind_param("si", $upload_email, $upload_kitchen_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows > 0) {
            $response = handleDocumentReupload($upload_kitchen_id, $conn);
            if (strpos($response, 'successful') !== false) {
                $success = $response;
                // Refresh the page to show updated documents
                $_POST['action'] = 'lookup_by_email';
                $_POST['email'] = $upload_email;
                $search_email = $upload_email;
                $show_email_form = false;
                
                // Re-fetch user and documents data
                $email_query = "SELECT u.user_id, u.u_name, u.mail, eu.ext_role, cko.user_id AS cloud_kitchen_id, 
                                       cko.business_name, cko.is_approved, cko.status, cko.registration_date
                                FROM users u
                                LEFT JOIN external_user eu ON u.user_id = eu.user_id
                                LEFT JOIN cloud_kitchen_owner cko ON eu.user_id = cko.user_id
                                WHERE u.mail = ? AND u.u_role = 'external_user' AND eu.ext_role = 'cloud_kitchen_owner'";
                
                $email_stmt = $conn->prepare($email_query);
                $email_stmt->bind_param("s", $search_email);
                $email_stmt->execute();
                $email_result = $email_stmt->get_result();
                $user = $email_result->fetch_assoc();
                $kitchen_id = $user['cloud_kitchen_id'];
                
                // Re-fetch documents with re-upload detection
                $docs_query = "SELECT kd.*, a.u_name as reviewed_by_name,
                               (SELECT COUNT(*) FROM kitchen_documents kd2 
                                WHERE kd2.kitchen_id = kd.kitchen_id 
                                AND kd2.document_type = kd.document_type 
                                AND kd2.upload_date < kd.upload_date) as previous_uploads_count
                               FROM kitchen_documents kd
                               LEFT JOIN admin ad ON kd.reviewed_by = ad.user_id
                               LEFT JOIN users a ON ad.user_id = a.user_id
                               WHERE kd.kitchen_id = ? 
                               ORDER BY kd.document_type, kd.upload_date DESC";
                
                $docs_stmt = $conn->prepare($docs_query);
                $docs_stmt->bind_param("i", $kitchen_id);
                $docs_stmt->execute();
                $docs_result = $docs_stmt->get_result();
                
                // Process documents to get only the latest version of each type
                $documents = [];
                $latest_documents = [];
                if ($docs_result->num_rows > 0) {
                    while ($row = $docs_result->fetch_assoc()) {
                        $doc_type = $row['document_type'];
                        // Only keep the latest document of each type (first one due to ORDER BY upload_date DESC)
                        if (!isset($latest_documents[$doc_type])) {
                            $row['is_reupload'] = $row['previous_uploads_count'] > 0;
                            $latest_documents[$doc_type] = $row;
                        }
                    }
                    $documents = array_values($latest_documents);
                }
            } else {
                $error = $response;
            }
        } else {
            $error = "Upload verification failed. Please ensure you're using the correct email address.";
        }
    } else {
        $error = "Invalid upload request. Please try again.";
    }
}

function handleDocumentReupload($kitchen_id, $conn) {
    // Add detailed error logging
    error_log("Upload attempt - Kitchen ID: " . $kitchen_id);
    
    if (!isset($_POST['document_type']) || !isset($_FILES['document_file'])) {
        error_log("Missing data - POST: " . print_r($_POST, true) . " FILES: " . print_r($_FILES, true));
        return "Missing document type or file";
    }
    
    $document_type = $_POST['document_type'];
    $file = $_FILES['document_file'];
    
    error_log("File info: " . print_r($file, true));
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload error code: " . $file['error']);
        switch($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                return "File is too large (exceeds server limit)";
            case UPLOAD_ERR_FORM_SIZE:
                return "File is too large (exceeds form limit)";
            case UPLOAD_ERR_PARTIAL:
                return "File was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing temporary upload directory";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "File upload stopped by extension";
            default:
                return "Unknown upload error: " . $file['error'];
        }
    }
    
    // Validate file type
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        error_log("Invalid file type: " . $file['type']);
        return "Invalid file type: " . $file['type'] . ". Please upload PDF, JPEG, PNG, or GIF files only.";
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        error_log("File too large: " . $file['size'] . " bytes");
        return "File too large: " . round($file['size'] / 1048576, 2) . "MB. Maximum size is 5MB.";
    }
    
    // Create uploads directory structure
    $baseUploadDir = "../admin/c/c/uploads/documents/" . $kitchen_id . "/";
    error_log("Creating directory: " . $baseUploadDir);
    
    if (!file_exists($baseUploadDir)) {
        if (!mkdir($baseUploadDir, 0755, true)) {
            error_log("Failed to create directory: " . $baseUploadDir);
            return "Failed to create upload directory. Please check server permissions.";
        }
    }
    
    // Check if directory is writable
    if (!is_writable($baseUploadDir)) {
        error_log("Directory not writable: " . $baseUploadDir);
        return "Upload directory is not writable. Please check server permissions.";
    }
    
    // Generate safe filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeFileName = strtolower($document_type) . '_' . time() . '.' . $extension;
    $targetPath = $baseUploadDir . $safeFileName;
    $relativePath = "uploads/documents/" . $kitchen_id . "/" . $safeFileName;
    
    error_log("Target path: " . $targetPath);
    error_log("Source temp file: " . $file['tmp_name']);
    
    // Check if temp file exists
    if (!file_exists($file['tmp_name'])) {
        error_log("Temp file does not exist: " . $file['tmp_name']);
        return "Temporary upload file not found. Please try again.";
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $error = error_get_last();
        error_log("Failed to move file. Last error: " . print_r($error, true));
        return "Failed to save document. Error: " . ($error['message'] ?? 'Unknown error');
    }
    
    error_log("File moved successfully to: " . $targetPath);
    
    // Get document name mapping
    $document_names = [
        'national_id' => 'National ID',
        'business_license' => 'Business License',
        'health_certificate' => 'Health Certificate',
        'tax_certificate' => 'Tax Certificate',
        'kitchen_photos' => 'Kitchen Photos'
    ];
    
    $document_name = $document_names[$document_type] ?? 'Document';
    
    // Insert new document record into database
    $sql = "INSERT INTO kitchen_documents (kitchen_id, document_type, document_name, file_path, file_size, file_type, upload_date) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return "Database error: Failed to prepare statement";
    }
    
    $stmt->bind_param("isssss", $kitchen_id, $document_type, $document_name, $relativePath, $file['size'], $file['type']);
    
    if (!$stmt->execute()) {
        error_log("Failed to execute statement: " . $stmt->error);
        return "Failed to save document record: " . $stmt->error;
    }
    
    error_log("Document uploaded successfully for kitchen " . $kitchen_id);
    return "SUCCESS: Document uploaded successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Meal - Document Status</title>
    <link rel="stylesheet" href="global-register.css" />
    <link rel="stylesheet" href="register.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="signup-form">
        <?php if ($error): ?>
            <div class="error-message" style="color: red; margin-bottom: 20px; padding: 15px; background: #fee; border: 1px solid #fcc; border-radius: 8px;">
                <i data-lucide="alert-circle" style="width: 16px; height: 16px; display: inline; margin-right: 8px;"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message" style="color: green; margin-bottom: 20px; padding: 15px; background: #efe; border: 1px solid #cfc; border-radius: 8px;">
                <i data-lucide="check-circle" style="width: 16px; height: 16px; display: inline; margin-right: 8px;"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <nav class="navbar">
            <div class="nav-content">
                <a href="#" class="logo-link">
                    <img src="logo.png" alt="Today's Meal" class="logo">
                </a>
                <div class="nav-links">
                    <a href="#" class="nav-link">Home</a>
                    <a href="#" class="nav-link">About</a>
                    <a href="#" class="nav-link">Contact</a>
                    <a href="login.php" class="nav-link">Login</a>
                </div>
            </div>
        </nav>
        
        <div class="main-content">
            <div class="header">
                <h1>Document Status & Review</h1>
                <?php if ($user && !$error): ?>
                    <p>Track your document review status and view admin feedback for <strong><?php echo htmlspecialchars($user['business_name']); ?></strong></p>
                <?php elseif ($show_email_form): ?>
                    <p>Enter your email address to check your cloud kitchen registration and document review status</p>
                <?php endif; ?>
            </div>
            
            <div class="content-wrapper">
                <div class="image-container">
                    <div class="image"></div>
                </div>
                
                <div class="form-container">
                    <?php if ($show_email_form): ?>
                        <!-- Email Lookup Form -->
                        <div class="email-lookup-section">
                            <h2><i data-lucide="mail"></i> Check Your Document Status</h2>
                            <p class="lookup-description">
                                Enter your registered email address to view your cloud kitchen registration status and document review feedback.
                            </p>
                            
                            <form method="POST" class="email-lookup-form">
                                <input type="hidden" name="action" value="lookup_by_email">
                                
                                <div class="input-group full-width">
                                    <label for="email">
                                        <i data-lucide="mail"></i> Your Email Address <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="email" id="email" name="email" 
                                               placeholder="Enter your registered email address" 
                                               value="<?php echo htmlspecialchars($search_email); ?>" 
                                               required autocomplete="email">
                                    </div>
                                </div>
                                
                                <button type="submit" class="submit-btn">
                                    <i data-lucide="search"></i>
                                    Check Document Status
                                </button>
                            </form>
                            
                            <div class="help-section">
                                <h3><i data-lucide="help-circle"></i> Need Help?</h3>
                                <ul>
                                    <li>Use the same email address you used during registration</li>
                                    <li>If you haven't registered yet, <a href="cloud_kitchen.php">click here to register</a></li>
                                    <li>Having issues? <a href="login.php">Login to your account</a></li>
                                </ul>
                            </div>
                        </div>
                    
                    <?php elseif ($user && !$error): ?>
                        <!-- Registration Status Overview -->
                        <div class="status-overview">
                            <h2>Registration Status</h2>
                            <div class="status-card">
                                <div class="status-header">
                                    <i data-lucide="building" class="status-icon"></i>
                                    <div class="status-info">
                                        <h3><?php echo htmlspecialchars($user['business_name']); ?></h3>
                                        <p>Cloud Kitchen Registration</p>
                                    </div>
                                    <div class="status-badge <?php echo $user['is_approved'] ? 'approved' : 'pending'; ?>">
                                        <?php if ($user['is_approved']): ?>
                                            <i data-lucide="check-circle"></i>
                                            <span>Approved</span>
                                        <?php else: ?>
                                            <i data-lucide="clock"></i>
                                            <span>Pending Review</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (!$user['is_approved']): ?>
                                    <div class="status-message">
                                        <p>Your registration is currently under review. Please check the document status below and address any admin feedback.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="status-message approved">
                                        <p>Congratulations! Your cloud kitchen has been approved and is now active on our platform.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div class="documents-section">
                            <h2>Uploaded Documents</h2>
                            
                            <?php if (empty($documents)): ?>
                                <div class="no-documents">
                                    <i data-lucide="file-x" style="width: 48px; height: 48px; color: #ccc; margin-bottom: 16px;"></i>
                                    <h3>No Documents Found</h3>
                                    <p>No documents have been uploaded yet. Please complete your registration first.</p>
                                    <a href="cloud_kitchen.php" class="btn-primary">Complete Registration</a>
                                </div>
                            <?php else: ?>
                                <div class="documents-grid">
                                    <?php 
                                    $document_types = [
                                        'national_id' => ['National ID', 'credit-card', '#17a2b8'],
                                        'business_license' => ['Business License', 'briefcase', '#28a745'],
                                        'health_certificate' => ['Health Certificate', 'shield-check', '#dc3545'],
                                        'tax_certificate' => ['Tax Certificate', 'receipt', '#6f42c1'],
                                        'kitchen_photos' => ['Kitchen Photos', 'camera', '#fd7e14']
                                    ];
                                    
                                    foreach ($documents as $doc): 
                                        $type_info = $document_types[$doc['document_type']] ?? ['Other Documents', 'file-text', '#6c757d'];
                                        $file_size_mb = round($doc['file_size'] / 1048576, 2);
                                        
                                        // Determine document status
                                        $hasAdminNotes = !empty($doc['admin_notes']);
                                        if ($user['is_approved'] && !$hasAdminNotes) {
                                            $docStatus = 'approved';
                                            $statusLabel = 'Approved';
                                        } elseif ($hasAdminNotes) {
                                            $docStatus = 'needs-attention';
                                            $statusLabel = 'Needs Attention';
                                        } else {
                                            $docStatus = 'pending';
                                            $statusLabel = 'Under Review';
                                        }
                                    ?>
                                        <div class="document-card <?php echo $docStatus; ?>">
                                            <div class="document-header">
                                                <div class="doc-type">
                                                    <i data-lucide="<?php echo $type_info[1]; ?>" style="color: <?php echo $type_info[2]; ?>"></i>
                                                    <span><?php echo $type_info[0]; ?></span>
                                                    <?php if (isset($doc['is_reupload']) && $doc['is_reupload']): ?>
                                                        <span class="reupload-tag">
                                                            <i data-lucide="refresh-cw"></i> Updated
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="doc-status <?php echo $docStatus; ?>">
                                                    <?php if ($docStatus === 'approved'): ?>
                                                        <i data-lucide="check-circle"></i>
                                                    <?php elseif ($docStatus === 'needs-attention'): ?>
                                                        <i data-lucide="alert-triangle"></i>
                                                    <?php else: ?>
                                                        <i data-lucide="clock"></i>
                                                    <?php endif; ?>
                                                    <span><?php echo $statusLabel; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="document-body">
                                                <h4><?php echo htmlspecialchars($doc['document_name']); ?></h4>
                                                <div class="document-meta">
                                                    <p><i data-lucide="file"></i> <?php echo strtoupper(pathinfo($doc['file_path'], PATHINFO_EXTENSION)); ?> • <?php echo $file_size_mb; ?> MB</p>
                                                    <p><i data-lucide="calendar"></i> 
                                                        <?php if (isset($doc['is_reupload']) && $doc['is_reupload']): ?>
                                                            Re-uploaded <?php echo date('M j, Y', strtotime($doc['upload_date'])); ?>
                                                            <span class="upload-count">(Version <?php echo $doc['previous_uploads_count'] + 1; ?>)</span>
                                                        <?php else: ?>
                                                            Uploaded <?php echo date('M j, Y', strtotime($doc['upload_date'])); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                
                                                <?php if ($doc['admin_notes']): ?>
                                                    <div class="admin-feedback">
                                                        <h5><i data-lucide="message-square"></i> Admin Feedback:</h5>
                                                        <p><?php echo htmlspecialchars($doc['admin_notes']); ?></p>
                                                        <?php if ($doc['reviewed_by_name'] && $doc['reviewed_at']): ?>
                                                            <small>
                                                                Reviewed by <?php echo htmlspecialchars($doc['reviewed_by_name']); ?> 
                                                                on <?php echo date('M j, Y', strtotime($doc['reviewed_at'])); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="document-actions">
                                                    <button class="btn-secondary" onclick="viewDocument('<?php echo addslashes($doc['file_path']); ?>', '<?php echo $doc['file_type']; ?>')">
                                                        <i data-lucide="eye"></i> View
                                                    </button>
                                                    
                                                    <?php if ($hasAdminNotes && !$user['is_approved']): ?>
                                                        <button class="btn-primary" onclick="showReuploadModal('<?php echo $doc['document_type']; ?>', '<?php echo addslashes($type_info[0]); ?>', '<?php echo addslashes($user['mail']); ?>', <?php echo $user['cloud_kitchen_id']; ?>)">
                                                            <i data-lucide="upload"></i> Re-upload
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="../cloud_kitchen/dashboard.php" class="btn-primary">
                                    <i data-lucide="arrow-left"></i> Go to Dashboard
                                </a>
                                <a href="login.php" class="btn-secondary">
                                    <i data-lucide="log-out"></i> Logout
                                </a>
                            <?php else: ?>
                                <button onclick="window.location.reload()" class="btn-secondary">
                                    <i data-lucide="mail"></i> Check Another Email
                                </button>
                                <a href="login.php?redirect=documents_status.php" class="btn-primary">
                                    <i data-lucide="log-in"></i> Login to Account
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="access-denied">
                            <i data-lucide="search-x" style="width: 64px; height: 64px; color: #dc3545; margin-bottom: 16px;"></i>
                            <h2>No Registration Found</h2>
                            <p><?php echo htmlspecialchars($error); ?></p>
                            <div class="login-options">
                                <button onclick="window.location.reload()" class="btn-primary">
                                    <i data-lucide="mail"></i> Try Another Email
                                </button>
                                <a href="cloud_kitchen.php" class="btn-secondary">
                                    <i data-lucide="plus-circle"></i> Register Cloud Kitchen
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div id="documentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Document Viewer</h3>
                <button class="modal-close" onclick="closeDocumentModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="documentViewer"></div>
            </div>
        </div>
    </div>

    <!-- Re-upload Modal -->
    <div id="reuploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Re-upload Document</h3>
                <button class="modal-close" onclick="closeReuploadModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="reupload-instructions">
                    <p><i data-lucide="info"></i> Select a new file to replace your current document. Make sure to address any admin feedback before uploading.</p>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="reuploadForm">
                    <input type="hidden" name="action" value="upload_document">
                    <input type="hidden" name="document_type" id="reuploadDocType">
                    <input type="hidden" name="upload_email" id="reuploadEmail">
                    <input type="hidden" name="kitchen_id" id="reuploadKitchenId">
                    
                    <div class="file-upload-section">
                        <div class="input-group">
                            <label for="reupload_file" id="reuploadLabel">
                                <i data-lucide="file-plus"></i> Select New Document
                            </label>
                            <div class="input-wrapper">
                                <input type="file" 
                                       name="document_file" 
                                       id="reupload_file" 
                                       accept=".pdf,.jpg,.jpeg,.png,.gif" 
                                       required
                                       class="file-input">
                                <div class="file-help">
                                    <i data-lucide="info"></i>
                                    Accepted formats: PDF, JPEG, PNG, GIF • Maximum size: 5MB
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-secondary" onclick="closeReuploadModal()">
                            <i data-lucide="x"></i> Cancel
                        </button>
                        <button type="submit" class="btn-primary" id="reuploadSubmitBtn">
                            <i data-lucide="upload"></i> Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .email-lookup-section {
            max-width: 600px;
            margin: 0 auto 2rem auto;
            text-align: center;
        }
        
        .email-lookup-section h2 {
            color: #2d3748;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .lookup-description {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .email-lookup-form {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .help-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: left;
        }
        
        .help-section h3 {
            color: #2d3748;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }
        
        .help-section ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #6c757d;
        }
        
        .help-section li {
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        
        .help-section a {
            color: #e57e24;
            text-decoration: none;
        }
        
        .help-section a:hover {
            text-decoration: underline;
        }
        
        .status-overview {
            margin-bottom: 2rem;
        }
        
        .status-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        
        .status-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .status-icon {
            width: 40px;
            height: 40px;
            color: #e57e24;
        }
        
        .status-info h3 {
            margin: 0;
            color: #2d3748;
            font-size: 1.2rem;
        }
        
        .status-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            margin-left: auto;
        }
        
        .status-badge.approved {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-message {
            padding: 1rem;
            background: #fff3cd;
            border-radius: 8px;
            color: #856404;
        }
        
        .status-message.approved {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .documents-section {
            margin-bottom: 2rem;
        }
        
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .document-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .document-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .document-card.needs-attention {
            border-color: #ffc107;
            background: #fffbf0;
        }
        
        .document-card.approved {
            border-color: #28a745;
            background: #f8fff9;
        }
        
        .document-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .doc-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            flex-wrap: wrap;
        }
        
        .reupload-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.15rem 0.5rem;
            border-radius: 10px;
            margin-left: 0.5rem;
            white-space: nowrap;
            box-shadow: 0 1px 3px rgba(23, 162, 184, 0.3);
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        .reupload-tag i {
            width: 10px;
            height: 10px;
        }
        
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 1px 3px rgba(23, 162, 184, 0.3);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 2px 8px rgba(23, 162, 184, 0.5);
                transform: scale(1.02);
            }
        }
        
        .doc-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .doc-status.approved {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .doc-status.needs-attention {
            background: #fff3cd;
            color: #856404;
        }
        
        .doc-status.pending {
            background: #e2e3e5;
            color: #6c757d;
        }
        
        .document-body {
            padding: 1rem;
        }
        
        .document-body h4 {
            margin: 0 0 0.5rem 0;
            color: #2d3748;
        }
        
        .document-meta {
            margin-bottom: 1rem;
        }
        
        .document-meta p {
            margin: 0.25rem 0;
            color: #6c757d;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .upload-count {
            color: #17a2b8;
            font-weight: 600;
            font-size: 0.8rem;
            margin-left: 0.25rem;
        }
        
        .admin-feedback {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .admin-feedback h5 {
            margin: 0 0 0.5rem 0;
            color: #856404;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .admin-feedback p {
            margin: 0 0 0.5rem 0;
            color: #856404;
        }
        
        .admin-feedback small {
            color: #6c757d;
        }
        
        .document-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-primary, .btn-secondary {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #e57e24;
            color: white;
        }
        
        .btn-primary:hover {
            background: #d16515;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .no-documents, .access-denied {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .login-options {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }
        
        .access-denied h2 {
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .access-denied p {
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: hidden;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #2d3748;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
        }
        
        .modal-body {
            padding: 1.5rem;
            max-height: calc(90vh - 120px);
            overflow-y: auto;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        
        #documentViewer {
            text-align: center;
        }
        
        #documentViewer img {
            max-width: 100%;
            max-height: 600px;
            border-radius: 8px;
        }
        
        #documentViewer iframe {
            width: 100%;
            height: 600px;
            border: none;
            border-radius: 8px;
        }
        
        .upload-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .upload-info p {
            margin: 0;
            color: #856404;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .upload-info i {
            margin-top: 0.1rem;
            flex-shrink: 0;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Modal form styling */
        .input-group {
            margin-bottom: 1.5rem;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
            background: white;
            cursor: pointer;
        }
        
        .input-wrapper input[type="file"]:focus {
            outline: none;
            border-color: #e57e24;
            box-shadow: 0 0 0 3px rgba(229, 126, 36, 0.1);
        }
        
        .input-wrapper input[type="file"]:hover {
            border-color: #cbd5e0;
        }
        
        .file-help {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6c757d;
            line-height: 1.4;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        
        /* Re-upload specific styling */
        .reupload-instructions {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #495057;
        }
        
        .reupload-instructions p {
            margin: 0;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            line-height: 1.5;
        }
        
        .reupload-instructions i {
            margin-top: 0.1rem;
            flex-shrink: 0;
            color: #6c757d;
        }
        
        .file-upload-section {
            background: white;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .file-upload-section:hover {
            border-color: #e57e24;
            background: #fefefe;
        }
        
        .file-upload-section .input-group {
            margin-bottom: 0;
        }
        
        .file-upload-section label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #2d3748;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .file-input {
            display: block !important;
            width: 100% !important;
            max-width: 400px;
            margin: 0 auto;
            padding: 1rem !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 8px !important;
            background: white !important;
            cursor: pointer !important;
            font-size: 0.9rem;
        }
        
        .file-input:hover {
            border-color: #e57e24 !important;
        }
        
        .file-input:focus {
            outline: none !important;
            border-color: #e57e24 !important;
            box-shadow: 0 0 0 3px rgba(229, 126, 36, 0.1) !important;
        }
    </style>

    <script>
        lucide.createIcons();
        
        function viewDocument(filePath, fileType) {
            const modal = document.getElementById('documentModal');
            const viewer = document.getElementById('documentViewer');
            
            viewer.innerHTML = '<div style="padding: 2rem;">Loading document...</div>';
            modal.style.display = 'block';
            
            setTimeout(() => {
                if (fileType.startsWith('image/')) {
                    viewer.innerHTML = `<img src="../admin/c/c/${filePath}" alt="Document" onerror="this.parentElement.innerHTML='<div style=\\'color: red; padding: 2rem;\\'>Failed to load image</div>'">`;
                } else if (fileType === 'application/pdf') {
                    viewer.innerHTML = `<iframe src="../admin/c/c/${filePath}" onerror="this.parentElement.innerHTML='<div style=\\'color: red; padding: 2rem;\\'>Failed to load PDF</div>'"></iframe>`;
                } else {
                    viewer.innerHTML = '<div style="color: #6c757d; padding: 2rem;">Preview not available for this file type</div>';
                }
            }, 100);
        }
        
        function closeDocumentModal() {
            document.getElementById('documentModal').style.display = 'none';
        }
        
        function showReuploadModal(docType, docName, email, kitchenId) {
            document.getElementById('reuploadDocType').value = docType;
            document.getElementById('reuploadEmail').value = email || '';
            document.getElementById('reuploadKitchenId').value = kitchenId || '';
            document.getElementById('reuploadLabel').textContent = `Re-upload ${docName}`;
            document.getElementById('reuploadModal').style.display = 'block';
        }
        
        function closeReuploadModal() {
            document.getElementById('reuploadModal').style.display = 'none';
            // Reset form
            document.getElementById('reuploadForm').reset();
        }
        
        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const reuploadForm = document.getElementById('reuploadForm');
            if (reuploadForm) {
                reuploadForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Prevent default submission first
                    
                    const fileInput = document.getElementById('reupload_file');
                    const submitBtn = document.getElementById('reuploadSubmitBtn');
                    
                    // Check if file is selected
                    if (!fileInput.files[0]) {
                        alert('Please select a file to upload');
                        return false;
                    }
                    
                    const file = fileInput.files[0];
                    
                    // File size validation
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        return false;
                    }
                    
                    // File type validation
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please upload only PDF, JPEG, PNG, or GIF files');
                        return false;
                    }
                    
                    // Show loading message
                    const originalContent = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i data-lucide="loader" style="animation: spin 1s linear infinite;"></i> Uploading...';
                    submitBtn.disabled = true;
                    
                    // Create timeout to reset button if upload takes too long
                    const timeoutId = setTimeout(function() {
                        submitBtn.innerHTML = originalContent;
                        submitBtn.disabled = false;
                        alert('Upload is taking longer than expected. Please try again.');
                    }, 30000); // 30 seconds timeout
                    
                    // Submit the form
                    const formData = new FormData(reuploadForm);
                    
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        clearTimeout(timeoutId);
                        return response.text();
                    })
                    .then(data => {
                        console.log('Server response:', data); // Debug log
                        if (data.includes('SUCCESS:') || data.includes('uploaded successfully')) {
                            alert('Document uploaded successfully!');
                            window.location.reload();
                        } else if (data.includes('Failed') || data.includes('error') || data.includes('Invalid')) {
                            // Extract error message from response
                            const errorMatch = data.match(/(?:Failed|Invalid|error)[^<]*/i);
                            const errorMessage = errorMatch ? errorMatch[0] : 'Upload failed';
                            throw new Error(errorMessage);
                        } else {
                            // If we can't determine success, check if we got a proper HTML response
                            if (data.includes('<!DOCTYPE') || data.includes('<html')) {
                                window.location.reload();
                            } else {
                                throw new Error('Unexpected server response');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        submitBtn.innerHTML = originalContent;
                        submitBtn.disabled = false;
                        alert('Upload failed. Please try again.');
                    });
                });
            }
        });
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const documentModal = document.getElementById('documentModal');
            const reuploadModal = document.getElementById('reuploadModal');
            
            if (event.target === documentModal) {
                closeDocumentModal();
            }
            if (event.target === reuploadModal) {
                closeReuploadModal();
            }
        }
        
        // File upload validation
        document.addEventListener('DOMContentLoaded', function() {
            function validateFile(event) {
                const file = event.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB');
                        event.target.value = '';
                        return false;
                    }
                    
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please upload only PDF, JPEG, or PNG files');
                        event.target.value = '';
                        return false;
                    }
                }
                return true;
            }
            
            // Attach validation to all file inputs
            document.addEventListener('change', function(event) {
                if (event.target.type === 'file') {
                    validateFile(event);
                }
            });
        });
    </script>
</body>
</html> 