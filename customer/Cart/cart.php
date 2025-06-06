<?php
session_start();
require_once('../DB_connection.php');

// Ensure user is logged in and has a valid user ID
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Show any redirect messages safely
if (!empty($_SESSION['redirect_reason'])) {
    $msg = htmlspecialchars($_SESSION['redirect_reason'], ENT_QUOTES, 'UTF-8');
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() { 
            showNotification('{$msg}', 'error');
        });
    </script>";
    unset($_SESSION['redirect_reason']);
}

// Initialize variables
$emptyCart = true;
$allCarts = [];
$totalItems = 0;

// Prepare statement to prevent SQL injection on $user_id
$cartsQuery = "
    SELECT c.cart_id, c.cloud_kitchen_id, cko.business_name 
    FROM cart c
    JOIN cloud_kitchen_owner cko ON c.cloud_kitchen_id = cko.user_id
    WHERE c.customer_id = ?
    ORDER BY c.created_at DESC
";

if ($stmt = $conn->prepare($cartsQuery)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cartsResult = $stmt->get_result();

    if ($cartsResult && $cartsResult->num_rows > 0) {
        $emptyCart = false;

        while ($cart = $cartsResult->fetch_assoc()) {
            $cart_id = $cart['cart_id'];
            $cloud_kitchen_name = $cart['business_name'];
            $cloud_kitchen_id = $cart['cloud_kitchen_id'];

            $cartData = [
                'cart_id' => $cart_id,
                'cloud_kitchen_name' => $cloud_kitchen_name,
                'cloud_kitchen_id' => $cloud_kitchen_id,
                'items' => [],
                'subtotal' => 0,
                'totalItems' => 0,
                'hasOutOfStockItems' => false,
            ];

            // Prepare statement to safely fetch cart items
            $itemsQuery = "
                SELECT ci.*, m.name, m.photo, m.status, m.stock_quantity, m.price AS current_price
                FROM cart_items ci
                JOIN meals m ON ci.meal_id = m.meal_id
                WHERE ci.cart_id = ?
            ";
            if ($itemsStmt = $conn->prepare($itemsQuery)) {
                $itemsStmt->bind_param("i", $cart_id);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();

                if ($itemsResult) {
                    $cartItems = $itemsResult->fetch_all(MYSQLI_ASSOC);

                    foreach ($cartItems as $item) {
                        $itemTotal = $item['price'] * $item['quantity'];
                        $cartData['subtotal'] += $itemTotal;
                        $cartData['totalItems'] += $item['quantity'];
                        $totalItems += $item['quantity'];

                        if (strtolower($item['status']) === 'out of stock' || (int)$item['stock_quantity'] <= 0) {
                            $cartData['hasOutOfStockItems'] = true;
                        }
                    }
                    $cartData['total'] = $cartData['subtotal'];
                    $cartData['items'] = $cartItems;
                }
                $itemsStmt->close();
            }

            $allCarts[] = $cartData;
        }
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Today's Meal</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Popup Notification Styles */
        .notification-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            display: flex;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            max-width: 350px;
        }
        
        .notification-popup.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .notification-popup.success {
            background-color: #4CAF50;
        }
        
        .notification-popup.error {
            background-color: #F44336;
        }
        
        .notification-popup.warning {
            background-color: #FF9800;
        }
        
        .notification-icon {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .close-notification {
            margin-left: 15px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../global/navbar/navbar.php'; ?>
    
    <!-- Notification Popup Container -->
    <div id="notificationContainer"></div>

    <div class="container">
        <!-- Cart Header -->
        <div class="cart-header">
            <h1>Your Cart</h1>
            <?php if (!$emptyCart): ?>
            <div class="item-count">
                <i class="fas fa-shopping-cart cart-icon"></i>
                <span id="total-items"><?= $totalItems ?> item<?= $totalItems != 1 ? 's' : '' ?></span>
            </div>
            <?php endif; ?>
        </div>

        <p class="subtitle">Ready to Complete Your Order?</p>

        <!-- Cart Groups Container -->
        <div id="cart-groups" class="cart-groups">
            <?php if ($emptyCart): ?>
                <div class="empty-cart-message">
                    <p>Looks like you haven't added any items yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($allCarts as $cartData): ?>
                <!-- Cloud Kitchen Group -->
                <div class="cart-group">
                    <div class="caterer-header">
                        <h2><?= htmlspecialchars($cartData['cloud_kitchen_name']) ?></h2>
                    </div>
                    
                    <?php if ($cartData['hasOutOfStockItems']): ?>
                    <div class="out-of-stock-warning">
                        <div class="warning-content">
                            <i class="fas fa-exclamation-triangle warning-icon"></i>
                            <span class="warning-text">Some items are currently out of stock. You can still schedule an order for later.</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="cart-items">
                        <?php foreach ($cartData['items'] as $item): 
                            $is_out_of_stock = $item['status'] == 'out of stock' || $item['stock_quantity'] <= 0;
                        ?>
                        <div class="cart-item <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
                            <img src="../../uploads/meals/<?= htmlspecialchars($item['photo'] ?? 'default-meal.jpg') ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                            
                            <div class="item-details">
                                <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
                                <p class="item-price"><?= number_format($item['current_price'], 2) ?> EGP</p>
                                <?php if ($is_out_of_stock): ?>
                                <div class="out-of-stock-label">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Out of stock
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?= $item['cart_item_id'] ?>, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="quantity-display"><?= $item['quantity'] ?></span>
                                <button class="quantity-btn" onclick="updateQuantity(<?= $item['cart_item_id'] ?>, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            
                            <div class="item-total-section">
                                <span class="item-total"><?= number_format($item['price'] * $item['quantity'], 2) ?> EGP</span>
                                <button class="remove-btn" onclick="removeItem(<?= $item['cart_item_id'] ?>)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="group-summary">
                        <div class="group-total-row">
                            <div class="summary-row">
                                <span>Group Total</span>
                                <span class="group-total-amount"><?= number_format($cartData['total'], 2) ?> EGP</span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="\NEW-TODAYS-MEAL\customer\Checkout_Codes\Meal_Planning\index.php" class="cart-actions">
    <input type="hidden" name="cart_id" value="<?= htmlspecialchars($cartData['cart_id'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="customer_id" value="<?= htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="cloud_kitchen_id" value="<?= htmlspecialchars($cartData['cloud_kitchen_id'], ENT_QUOTES, 'UTF-8') ?>">

    <button type="submit" 
            class="checkout-btn btn btn-primary <?= $cartData['hasOutOfStockItems'] ? 'disabled' : '' ?>" 
            <?= $cartData['hasOutOfStockItems'] ? 'disabled' : '' ?>>
        Checkout Now
    </button>

    <a href="/NEW-TODAYS-MEAL/customer/Checkout_Codes/Order_Type/index.php?cart_id=<?= urlencode($cartData['cart_id']) ?>" 
       class="btn btn-outline">
        Schedule Order
    </a>
</form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Show notification popup
    function showNotification(message, type) {
        const container = document.getElementById('notificationContainer');
        const notification = document.createElement('div');
        notification.className = `notification-popup ${type}`;
        
        // Create icon based on type
        let icon;
        switch(type) {
            case 'success':
                icon = '<i class="fas fa-check-circle notification-icon"></i>';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle notification-icon"></i>';
                break;
            case 'warning':
                icon = '<i class="fas fa-exclamation-triangle notification-icon"></i>';
                break;
            default:
                icon = '<i class="fas fa-info-circle notification-icon"></i>';
        }
        
        notification.innerHTML = `
            ${icon}
            ${message}
            <span class="close-notification">&times;</span>
        `;
        
        container.appendChild(notification);
        
        // Show the notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Close button functionality
        notification.querySelector('.close-notification').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    // Checkout button functionality for each cart
    document.querySelectorAll('.checkout-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const cartId = this.getAttribute('data-cart-id');
            const hasOutOfStock = this.classList.contains('disabled');
            
            if (hasOutOfStock) {
                showNotification('Please remove out-of-stock items before proceeding to checkout', 'error');
                return;
            }
            
            // Set cart ID in session and then redirect
            fetch('set_cart_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "/NEW-TODAYS-MEAL/customer/Checkout_Codes/Payment/index.php";
                } else {
                    showNotification('Failed to prepare checkout. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred during checkout', 'error');
            });
        });
    });

    function updateQuantity(cartItemId, change) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_item_id=${cartItemId}&change=${change}&ignore_stock=true`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showNotification(data.message || 'Error updating quantity', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating quantity', 'error');
        });
    }

    function removeItem(cartItemId) {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            fetch('remove_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_item_id=${cartItemId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Item removed from cart', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message || 'Error removing item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while removing item', 'error');
            });
        }
    }
    </script>
              <?php include '..\global\footer\footer.php'; ?>

</body>
</html>