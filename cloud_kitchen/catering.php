<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../DB_connection.php';

// Get cloud kitchen ID from session or database
if (!isset($_SESSION['cloud_kitchen_id'])) {
    // If not in session, fetch from database
    $stmt = $conn->prepare("SELECT user_id FROM cloud_kitchen_owner WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // User is not a cloud kitchen owner
        header("Location: unauthorized.php");
        exit();
    }
    
    $row = $result->fetch_assoc();
    $_SESSION['cloud_kitchen_id'] = $row['user_id'];
    $stmt->close();
}

$kitchenId = $_SESSION['cloud_kitchen_id'];
$showPopup = false;
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['kitchen_price']) && isset($_POST['order_id'])) {
            // Process acceptance with a chosen price
            $price = floatval($_POST['kitchen_price']);
            $orderId = intval($_POST['order_id']);
            
            // Verify the order belongs to this kitchen
            $verify_stmt = $conn->prepare("SELECT order_id FROM customized_order WHERE order_id = ? AND kitchen_id = ?");
            $verify_stmt->bind_param("ii", $orderId, $kitchenId);
            $verify_stmt->execute();
            
            if ($verify_stmt->get_result()->num_rows === 1) {
                $update_stmt = $conn->prepare("UPDATE customized_order SET status='accepted', chosen_amount=? WHERE order_id=? AND kitchen_id=?");
                $update_stmt->bind_param("dii", $price, $orderId, $kitchenId);
                
                if ($update_stmt->execute()) {
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    throw new Exception("Error updating record: " . $conn->error);
                }
            } else {
                throw new Exception("Order not found or you don't have permission to modify it");
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'reject' && isset($_POST['order_id'])) {
            // Process rejection in the database
            $orderId = intval($_POST['order_id']);
            
            // Verify the order belongs to this kitchen
            $verify_stmt = $conn->prepare("SELECT order_id FROM customized_order WHERE order_id = ? AND kitchen_id = ?");
            $verify_stmt->bind_param("ii", $orderId, $kitchenId);
            $verify_stmt->execute();
            
            if ($verify_stmt->get_result()->num_rows === 1) {
                $update_stmt = $conn->prepare("UPDATE customized_order SET status='rejected', customer_approval='rejected' WHERE order_id=? AND kitchen_id=?");
                $update_stmt->bind_param("ii", $orderId, $kitchenId);
                
                if ($update_stmt->execute()) {
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    throw new Exception("Error updating record: " . $conn->error);
                }
            } else {
                throw new Exception("Order not found or you don't have permission to modify it");
            }
        } elseif (isset($_POST['show_popup']) && isset($_POST['order_id'])) {
            $showPopup = true;
            $orderId = intval($_POST['order_id']);
            
            // Verify the order belongs to this kitchen
            $verify_stmt = $conn->prepare("SELECT order_id FROM customized_order WHERE order_id = ? AND kitchen_id = ?");
            $verify_stmt->bind_param("ii", $orderId, $kitchenId);
            $verify_stmt->execute();
            
            if ($verify_stmt->get_result()->num_rows !== 1) {
                $showPopup = false;
                $orderId = null;
                throw new Exception("Order not found or you don't have permission to view it");
            }
        }
    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => $e->getMessage()
        ];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all orders for this kitchen using prepared statement
$stmt = $conn->prepare("SELECT * FROM customized_order WHERE kitchen_id = ?");
$stmt->bind_param("i", $kitchenId);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// If a specific order is selected, get its details
$selectedOrder = null;
if ($orderId) {
    $stmt = $conn->prepare("SELECT * FROM customized_order WHERE order_id = ? AND kitchen_id = ?");
    $stmt->bind_param("ii", $orderId, $kitchenId);
    $stmt->execute();
    $result = $stmt->get_result();
    $selectedOrder = $result->fetch_assoc();
    $stmt->close();
}

// Check for session notification
$notification = $_SESSION['notification'] ?? null;
unset($_SESSION['notification']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Requests</title>
    <link rel="stylesheet" href="front_end/catering/style.css" />
</head>
<body>
    <?php include 'global/navbar.php'; ?>

    <!-- Notification Container -->
    <?php if ($notification): ?>
    <div class="notification <?= $notification['type'] ?>">
        <?= htmlspecialchars($notification['message']) ?>
    </div>
    <?php endif; ?>

    <div class="filter">
        <button class="filter-button all active" data-filter="all">All Requests</button>
        <button class="filter-button pending" data-filter="pending">Pending</button>
        <button class="filter-button accepted" data-filter="accepted">Accepted</button>
        <button class="filter-button rejected" data-filter="rejected">Rejected</button>
    </div>

    <div class="requests-container">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="request-card" data-status="<?php echo $order['status']; ?>">
                    <!-- Retrieve and display the image from the database -->
                    <div class="request-image" style="background-image: url('<?php 
                        echo !empty($order['img_reference']) 
                            ? '../uploads/custom_orders/' . htmlspecialchars(basename($order['img_reference'])) 
                            : 'https://yourdomain.com/path/to/default/image.png'; 
                    ?>');">
                    </div>
                    <div class="request-content">
                        <div class="request-header">
                            <h2 class="request-name">#ORD<?php echo str_pad($order['order_id'], 4, '0', STR_PAD_LEFT); ?></h2>
                            <span class="request-status <?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                        <p class="request-description"><?php echo htmlspecialchars($order['ord_description']); ?></p>
                        <div class="request-details">
                            <div class="detail-item">
                                <span class="detail-label">Budget :</span>
                                <span class="detail-value budget">$<?php echo $order['budget_min']; ?> - $<?php echo $order['budget_max']; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Servings :</span>
                                <span class="detail-value"><?php echo $order['people_servings']; ?> people</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Needed by :</span>
                                <span class="detail-value"><?php echo $order['preferred_completion_date']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="request-actions">
                        <?php if ($order['status'] === 'pending'): ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="show_popup" value="true">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" class="button button-accept">Accept</button>
                            </form>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <button type="submit" class="button button-reject" onclick="return confirm('Are you sure you want to reject this order?')">Reject</button>
                            </form>
                        <?php else: ?>
                            <div class="request-status-label">
                                <span class="status-label">Status:</span>
                                <span class="status-value"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="no-requests-message" id="noFilterResults"> No Requests Found </div>
        <?php else: ?>
            <div class='no-requests-message'> <h3>No Requests Found </h3> </div>
        <?php endif; ?>
    </div>

    <!-- Price Confirmation Popup -->
    <?php if ($showPopup && $selectedOrder && $selectedOrder['status'] === 'pending'): ?>
    <div class="confirm-budget-overlay" style="display:flex;">
        <div class="confirm-budget-modal">
            <div class="confirm-budget-title">Confirm Price Acceptance</div>
            <div class="confirm-budget-amount">Budget: $<?php echo $selectedOrder['budget_min']; ?> - $<?php echo $selectedOrder['budget_max']; ?></div>
            <form method="POST" action="">
                <input type="hidden" name="order_id" value="<?php echo $selectedOrder['order_id']; ?>">
                <div class="confirm-budget-input">
                    <label>Your Price: $</label>
                    <input type="number" name="kitchen_price" min="<?php echo $selectedOrder['budget_min']; ?>" max="<?php echo $selectedOrder['budget_max']; ?>" step="0.01" required>
                </div>
                <div class="confirm-budget-buttons">
                    <button type="submit" class="confirm-budget-button confirm">Confirm</button>
                    <button type="button" class="confirm-budget-button cancel" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-button');
            const requestCards = document.querySelectorAll('.request-card');
            const noFilterResults = document.getElementById('noFilterResults');
            
            function updateFilterUI() {
                const activeFilter = document.querySelector('.filter-button.active').getAttribute('data-filter');
                let visibleCards = 0;
                
                requestCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    
                    if (activeFilter === 'all' || status === activeFilter) {
                        card.style.display = 'flex';
                        visibleCards++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show/hide no filter results message
                if (visibleCards === 0 && requestCards.length > 0) {
                    noFilterResults.style.display = 'block';
                } else {
                    noFilterResults.style.display = 'none';
                }
            }
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    updateFilterUI();
                });
            });
            updateFilterUI();
            
            // Popup cancel button
            document.querySelector('.cancel')?.addEventListener('click', function() {
                document.querySelector('.confirm-budget-overlay').style.display = 'none';
            });
        });
    </script>
</body>
</html>