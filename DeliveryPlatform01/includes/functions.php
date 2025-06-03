<?php
/**
 * Common functions for the application
 */
require_once 'db_connect.php';

/**
 * Get delivery statistics for the current user
 * 
 * @return array Statistics data
 */
function get_delivery_stats() {
    global $db;
    
    // Initialize stats array
    $stats = [
        'total_deliveries' => 0,
        'deliveries_today' => 0,
        'status_counts' => [
            'pending' => 0,
            'in-progress' => 0,
            'completed' => 0, 
            'cancelled' => 0,
            'delayed' => 0
        ],
        'completion_rate' => 0
    ];
    
    // Get today's date for filtering
    $today = date('Y-m-d');
    
    // Get all deliveries for current user
    $sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN DATE(scheduled_time) = :today THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in-progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'delayed' THEN 1 ELSE 0 END) as delayed
        FROM deliveries
        WHERE delivery_person_id = :user_id
    ";
    
    $result = db_query_single($sql, [
        ':today' => $today,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    if ($result) {
        $stats['total_deliveries'] = $result['total'];
        $stats['deliveries_today'] = $result['today'];
        $stats['status_counts'] = [
            'pending' => $result['pending'],
            'in-progress' => $result['in_progress'],
            'completed' => $result['completed'],
            'cancelled' => $result['cancelled'],
            'delayed' => $result['delayed']
        ];
        
        // Calculate completion rate
        $completed = $result['completed'];
        $total = $completed + $result['cancelled'];
        $stats['completion_rate'] = $total > 0 ? round(($completed / $total) * 100) : 0;
    }
    
    return $stats;
}

/**
 * Get deliveries based on status
 * 
 * @param string|null $status Status filter or null for all
 * @return array List of deliveries
 */
function get_deliveries($status = null) {
    $sql = "
        SELECT d.*, 
               c.name AS customer_name, c.address AS customer_address,
               c.latitude AS customer_latitude, c.longitude AS customer_longitude,
               p.name AS provider_name, p.address AS provider_address,
               p.latitude AS provider_latitude, p.longitude AS provider_longitude
        FROM deliveries d
        JOIN customers c ON d.customer_id = c.id
        JOIN food_providers p ON d.provider_id = p.id
        WHERE d.delivery_person_id = :user_id
    ";
    
    $params = [':user_id' => $_SESSION['user_id']];
    
    if ($status !== null) {
        $sql .= " AND d.status = :status";
        $params[':status'] = $status;
    }
    
    $sql .= " ORDER BY d.scheduled_time DESC";
    
    return db_query($sql, $params);
}

/**
 * Get delivery details by ID
 * 
 * @param int $delivery_id Delivery ID
 * @return array|false Delivery details or false if not found
 */
function get_delivery($delivery_id) {
    $sql = "
        SELECT * FROM deliveries
        WHERE id = :delivery_id
        LIMIT 1
    ";
    
    return db_query_single($sql, [':delivery_id' => $delivery_id]);
}

/**
 * Get detailed delivery information
 * 
 * @param int $delivery_id Delivery ID
 * @return array|false Delivery details with customer and provider info, or false if not found
 */
function get_delivery_with_details($delivery_id) {
    $sql = "
        SELECT d.*, 
               c.name AS customer_name, c.address AS customer_address, c.phone AS customer_phone,
               c.latitude AS customer_latitude, c.longitude AS customer_longitude,
               p.name AS provider_name, p.address AS provider_address, p.phone AS provider_phone,
               p.latitude AS provider_latitude, p.longitude AS provider_longitude
        FROM deliveries d
        JOIN customers c ON d.customer_id = c.id
        JOIN food_providers p ON d.provider_id = p.id
        WHERE d.id = :delivery_id
        LIMIT 1
    ";
    
    return db_query_single($sql, [':delivery_id' => $delivery_id]);
}

/**
 * Get delivery status history
 * 
 * @param int $delivery_id Delivery ID
 * @return array Status history
 */
function get_delivery_status_history($delivery_id) {
    $sql = "
        SELECT * FROM delivery_status_history
        WHERE delivery_id = :delivery_id
        ORDER BY timestamp DESC
    ";
    
    return db_query($sql, [':delivery_id' => $delivery_id]);
}

/**
 * Update delivery status
 * 
 * @param int $delivery_id Delivery ID
 * @param string $status New status
 * @return bool True if update successful
 */
function update_delivery_status($delivery_id, $status) {
    global $db;
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Get current timestamp
        $now = date('Y-m-d H:i:s');
        
        // Update status-specific timestamp field
        $timestampField = null;
        switch ($status) {
            case 'in-progress':
                $timestampField = 'pickup_time';
                break;
            case 'completed':
                $timestampField = 'completion_time';
                break;
            case 'cancelled':
                $timestampField = 'cancelled_time';
                break;
            case 'delayed':
                $timestampField = 'delayed_time';
                break;
        }
        
        // Update delivery status and timestamp
        $sql = "UPDATE deliveries SET status = :status, last_status_update = :now";
        $params = [
            ':delivery_id' => $delivery_id,
            ':status' => $status,
            ':now' => $now
        ];
        
        if ($timestampField) {
            $sql .= ", $timestampField = :timestamp";
            $params[':timestamp'] = $now;
        }
        
        $sql .= " WHERE id = :delivery_id";
        
        // Execute update
        $result = db_execute($sql, $params);
        
        if (!$result) {
            throw new Exception('Failed to update delivery status');
        }
        
        // Add status history record
        $historySql = "
            INSERT INTO delivery_status_history (delivery_id, status, timestamp) 
            VALUES (:delivery_id, :status, :timestamp)
        ";
        
        $historyResult = db_execute($historySql, [
            ':delivery_id' => $delivery_id,
            ':status' => $status,
            ':timestamp' => $now
        ]);
        
        if (!$historyResult) {
            throw new Exception('Failed to add status history record');
        }
        
        // Commit transaction
        $db->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        error_log('Error updating delivery status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Search deliveries
 * 
 * @param string $query Search query
 * @return array Matching deliveries
 */
function search_deliveries($query) {
    $searchTerm = "%$query%";
    
    $sql = "
        SELECT d.*, 
               c.name AS customer_name, c.address AS customer_address,
               p.name AS provider_name
        FROM deliveries d
        JOIN customers c ON d.customer_id = c.id
        JOIN food_providers p ON d.provider_id = p.id
        WHERE d.delivery_person_id = :user_id
        AND (
            d.order_id LIKE :search
            OR c.name LIKE :search
            OR c.address LIKE :search
            OR p.name LIKE :search
            OR d.notes LIKE :search
        )
        ORDER BY d.scheduled_time DESC
    ";
    
    return db_query($sql, [
        ':user_id' => $_SESSION['user_id'],
        ':search' => $searchTerm
    ]);
}

/**
 * Update user profile information
 * 
 * @param int $user_id User ID
 * @param string $name Full name
 * @param string $email Email address
 * @param string $phone Phone number
 * @param string|null $new_password New password (optional)
 * @return bool True if update successful
 */
function update_user_profile($user_id, $name, $email, $phone, $new_password = null) {
    // Basic update query for name, email, and phone
    $sql = "UPDATE users SET name = :name, email = :email, phone = :phone";
    $params = [
        ':user_id' => $user_id,
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone
    ];
    
    // Add password update if new password provided
    if ($new_password !== null) {
        $password_hash = hash_password($new_password);
        $sql .= ", password = :password";
        $params[':password'] = $password_hash;
    }
    
    // Complete the query
    $sql .= " WHERE id = :user_id";
    
    // Execute update
    return db_execute($sql, $params) !== false;
}

/**
 * Format datetime string for display
 * 
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function format_datetime($datetime) {
    if (empty($datetime)) {
        return 'N/A';
    }
    
    $timestamp = strtotime($datetime);
    return date('M d, Y g:i A', $timestamp);
}

/**
 * Get Bootstrap status class based on status
 * 
 * @param string $status Status value
 * @return string Bootstrap class
 */
function get_status_class($status) {
    switch ($status) {
        case 'pending':
            return 'secondary';
        case 'in-progress':
            return 'primary';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        case 'delayed':
            return 'warning';
        default:
            return 'info';
    }
}
?>