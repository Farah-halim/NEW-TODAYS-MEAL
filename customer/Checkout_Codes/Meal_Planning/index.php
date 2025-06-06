<?php
session_start();
require_once('../../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$cartItems = [];
$orderPackages = [];
$cart_id = 0;

// Fetch latest cart_id for logged-in user and save securely in session
$cartQuery = "SELECT c.cart_id 
              FROM cart c 
              WHERE c.customer_id = ? 
              ORDER BY c.created_at DESC 
              LIMIT 1";
$stmt = $conn->prepare($cartQuery);
if (!$stmt) {
    // Handle statement preparation error gracefully
    $_SESSION['redirect_reason'] = "Database error. Please try again later.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartResult = $stmt->get_result();
if ($cartResult && $cartResult->num_rows > 0) {
    $cart = $cartResult->fetch_assoc();

    $cart_id = (int)$cart['cart_id'];

    // Store cart_id securely in session after verifying ownership
    $_SESSION['cart_id'] = $cart_id;
} else {
    // No active cart found for user, clear cart_id session if set
    unset($_SESSION['cart_id']);
    $_SESSION['redirect_reason'] = "Your cart is empty or expired.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}
$stmt->close();

// Ensure cart_id is set and valid in session, else redirect
if (!isset($_SESSION['cart_id']) || !is_numeric($_SESSION['cart_id'])) {
    $_SESSION['redirect_reason'] = "Cart session expired. Please add items to your cart.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}

$cart_id = (int)$_SESSION['cart_id'];

// Verify the cart belongs to the logged-in user (security)
$stmt = $conn->prepare("SELECT 1 FROM cart WHERE cart_id = ? AND customer_id = ?");
if (!$stmt) {
    $_SESSION['redirect_reason'] = "Unexpected error. Please try again.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    unset($_SESSION['cart_id']);
    $_SESSION['redirect_reason'] = "Cart session expired or invalid. Please add items to your cart.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}
$stmt->close();

// Load cart items by cart_id from session (always use session cart_id)
$itemsQuery = "SELECT ci.cart_item_id, ci.meal_id, ci.quantity, ci.price,
                      m.name, m.photo AS image
               FROM cart_items ci
               JOIN meals m ON ci.meal_id = m.meal_id
               WHERE ci.cart_id = ?";
$stmt = $conn->prepare($itemsQuery);
if (!$stmt) {
    $_SESSION['redirect_reason'] = "Database error retrieving cart items.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$itemsResult = $stmt->get_result();

while ($row = $itemsResult->fetch_assoc()) {
    $cartItems[$row['meal_id']] = $row;
}
$stmt->close();

if (empty($cartItems)) {
    unset($_SESSION['cart_id']); // Clear cart_id if no items found
    $_SESSION['redirect_reason'] = "Your cart is empty.";
    header("Location: /NEW-TODAYS-MEAL/customer/cart/cart.php");
    exit();
}

// Enforce order_data session check for deliveryPreference and orderType
if (
    !isset($_SESSION['order_data']) ||
    ($_SESSION['order_data']['deliveryPreference'] ?? '') !== 'daily' ||
    ($_SESSION['order_data']['orderType'] ?? '') !== 'scheduled'
) {
    header("Location: ../Order_Type/index.php");
    exit();
}

// Initialize scheduledMeals in session if unset
if (!isset($_SESSION['order_data']['scheduledMeals']) || !is_array($_SESSION['order_data']['scheduledMeals'])) {
    $_SESSION['order_data']['scheduledMeals'] = [];
}

// POST handling for adding and removing scheduled meals with all validations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_meal'])) {
        $mealId = filter_var($_POST['meal_id'], FILTER_VALIDATE_INT);
        $cartItemId = filter_var($_POST['cart_item_id'], FILTER_VALIDATE_INT);
        $day = trim($_POST['day']);

        // Validate required inputs
        if ($mealId === false || $cartItemId === false || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $_SESSION['error_message'] = "Invalid request data.";
            header("Location: .");
            exit();
        }

        if (!isset($cartItems[$mealId])) {
            $_SESSION['error_message'] = "Meal not found in your cart.";
            header("Location: .");
            exit();
        }

        // Count how many times this meal and cart item has been scheduled
        $scheduledCount = 0;
        foreach ($_SESSION['order_data']['scheduledMeals'] as $sm) {
            if ($sm['meal_id'] === $mealId && $sm['cart_item_id'] === $cartItemId) {
                $scheduledCount++;
            }
        }

        if ($scheduledCount < $cartItems[$mealId]['quantity']) {
            $_SESSION['order_data']['scheduledMeals'][] = [
                'id' => uniqid('', true),
                'meal_id' => $mealId,
                'cart_item_id' => $cartItemId,
                'day' => $day
            ];
        } else {
            $_SESSION['error_message'] = "You cannot schedule this meal more than " . $cartItems[$mealId]['quantity'] . " times.";
        }
        header("Location: .");
        exit();
    }

    if (isset($_POST['remove_group_meal'])) {
        $mealIdToRemove = filter_var($_POST['meal_id'], FILTER_VALIDATE_INT);
        $dayToRemove = trim($_POST['delivery_day'] ?? '');
        
        if ($mealIdToRemove !== false && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayToRemove)) {
            // Filter out all matching scheduled meals for meal_id and day
            $_SESSION['order_data']['scheduledMeals'] = array_filter($_SESSION['order_data']['scheduledMeals'], function($sm) use ($mealIdToRemove, $dayToRemove) {
                return !($sm['meal_id'] === $mealIdToRemove && $sm['day'] === $dayToRemove);
            });
            // Reindex array keys
            $_SESSION['order_data']['scheduledMeals'] = array_values($_SESSION['order_data']['scheduledMeals']);
        }
        header("Location: .");
        exit();
    }
}

// Group scheduled meals by meal_id and day, summing count
$scheduledSummary = [];
if (!empty($_SESSION['order_data']['scheduledMeals'])) {
    foreach ($_SESSION['order_data']['scheduledMeals'] as $sm) {
        $key = $sm['meal_id'] . '_' . $sm['day'];
        if (!isset($scheduledSummary[$key])) {
            $scheduledSummary[$key] = [
                'meal_id' => $sm['meal_id'],
                'day' => $sm['day'],
                'count' => 0
            ];
        }
        $scheduledSummary[$key]['count']++;
    }
}

// For rendering week schedule grouped by day
$weeklyScheduleGrouped = [];
foreach ($scheduledSummary as $item) {
    $mealId = $item['meal_id'];
    $day = $item['day'];
    $count = $item['count'];
    if (!isset($weeklyScheduleGrouped[$day])) {
        $weeklyScheduleGrouped[$day] = [];
    }
    $weeklyScheduleGrouped[$day][] = [
        'meal_id' => $mealId,
        'count' => $count,
        'meal_name' => isset($cartItems[$mealId]) ? $cartItems[$mealId]['name'] : 'Unknown Meal',
        'meal_image' => isset($cartItems[$mealId]) ? $cartItems[$mealId]['image'] : 'default-meal.jpg',
        'meal_price' => isset($cartItems[$mealId]) ? $cartItems[$mealId]['price'] : 0.0
    ];
}

// Calculate totals and group scheduled meals by delivery day
$deliveryFee = 15.00;
$total = 0;
$orderPackages = [];

if (!empty($_SESSION['order_data']['scheduledMeals'])) {
    foreach ($_SESSION['order_data']['scheduledMeals'] as $scheduledMeal) {
        $mealId = $scheduledMeal['meal_id'];
        $deliveryDay = $scheduledMeal['day'];

        if (isset($cartItems[$mealId])) {
            $meal = $cartItems[$mealId];
            $mealName = $meal['name'];
            $mealImage = $meal['image'];
            $mealPrice = $meal['price'];

            if (!isset($orderPackages[$deliveryDay])) {
                $orderPackages[$deliveryDay] = [
                    'date' => $deliveryDay,
                    'meals' => [],
                    'day_total' => 0
                ];
            }

            // Group meals by name within a day to combine quantities
            $mealKey = $mealName;

            if (!isset($orderPackages[$deliveryDay]['meals'][$mealKey])) {
                $orderPackages[$deliveryDay]['meals'][$mealKey] = [
                    'meal_name' => $mealName,
                    'meal_image' => $mealImage,
                    'price' => $mealPrice,
                    'quantity' => 1
                ];
            } else {
                $orderPackages[$deliveryDay]['meals'][$mealKey]['quantity']++;
            }

            $orderPackages[$deliveryDay]['day_total'] += $mealPrice;
            $total += $mealPrice;
        }
    }
    // Reindex meals arrays to avoid gaps
    foreach ($orderPackages as &$package) {
        $package['meals'] = array_values($package['meals']);
    }
    unset($package);
}

$grandTotal = $total + $deliveryFee;

// *** New: Check if all meals scheduled in full quantity ***
$allMealsScheduled = true;
foreach ($cartItems as $meal) {
    $mealId = $meal['meal_id'];
    $requiredQty = $meal['quantity'];
    $scheduledQty = 0;
    if (!empty($_SESSION['order_data']['scheduledMeals'])) {
        foreach ($_SESSION['order_data']['scheduledMeals'] as $sm) {
            if ($sm['meal_id'] === $mealId) {
                $scheduledQty++;
            }
        }
    }
    if ($scheduledQty < $requiredQty) {
        $allMealsScheduled = false;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Meal Planning - Food Delivery Checkout</title>
<meta name="description" content="Plan your meals for different days of the week with our easy-to-use meal planner.">
<link rel="stylesheet" href="../global.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
.error-message {
    border: 1px solid var(--destructive);
    background: #ffebe8;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: var(--radius);
    color: var(--destructive);
}
.success-message {
    border: 1px solid #4BB543;
    background: #DFF2BF;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: var(--radius);
    color: #4BB543;
}

/* Order Summary Styling */
.order-summary-card {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    padding: 24px;
    border: 1px solid var(--border);
}

.package-card {
    background: hsl(34deg 100% 85.17% / 34%);
    border-radius: var(--radius);
    padding: 16px;
    margin-bottom: 20px;
    border-left: 4px solid var(--primary);
}

.package-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.package-date {
    font-weight: 600;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 8px;
}

.package-meal-count {
    background: rgba(142, 64, 22, 0.1);
    color: var(--primary);
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
}

.meal-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.meal-item:last-child {
    border-bottom: none;
}

.meal-image {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 12px;
    border: 1px solid var(--border);
}

.meal-details {
    flex: 1;
}

.meal-name {
    font-weight: 500;
}

.price-summary {
    margin-top: 20px;
    border-top: 1px solid var(--border);
    padding-top: 16px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.price-value {
    font-weight: 500;
}

.total-row {
    border-top: 1px dashed var(--border);
    padding-top: 12px;
    margin-top: 12px;
    font-size: 1.1rem;
}

.total-label {
    font-weight: 600;
}

.total-value {
    font-weight: 700;
    font-size: 1.2rem;
}

/* Form Styling */
.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .main-content {
        flex-direction: column;
    }
    .sidebar {
        margin-top: 20px;
    }
}

.meal-quantity-badge {
    display: inline-block;
    background-color: #f0f0f0;
    color: #333;
    border-radius: 12px;
    padding: 2px 8px;
    font-size: 0.8em;
    margin-left: 8px;
    font-weight: bold;
}

/* Disabled date picker */
.date-picker-trigger.disabled {
    pointer-events: none;
    opacity: 0.5;
    cursor: default;
}

.button-container a.button-primary,
.button-container button.button-primary {
    cursor: pointer;
}

.button-primary[disabled],
.button-primary.disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>
</head>
<body>
<?php include '../../global/navbar/navbar.php'; ?>
<div class="container">
    <h1>Checkout</h1>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="checkout-steps">
        <div class="step completed">
            <div class="step-circle"><img src="../icons/check.svg" alt="Completed" width="16" height="16" /></div>
            <span class="step-name">Order Type</span>
        </div>
        <div class="step completed">
            <div class="step-circle"><img src="../icons/check.svg" alt="Completed" width="16" height="16" /></div>
            <span class="step-name">Meal Planning</span>
        </div>
        <div class="step active">
            <div class="step-circle active"><span>3</span></div>
            <span class="step-name">Review & Pay</span>
        </div>
    </div>

    <div class="main-content">
        <div class="main-column">
            <div class="card">
                <div class="card-content">
                    <div class="two-column-grid">
                        <div>
                            <h2 class="card-title">Available Items</h2>
                            <div id="meal-list" class="meal-list">
                                <?php foreach ($cartItems as $mealId => $meal): ?>
                                    <?php if ($meal['quantity'] > 0): ?>
                                        <div class="meal-item" data-meal-id="<?= htmlspecialchars($mealId) ?>">
                                            <div class="meal-item-header">
                                                <div class="meal-image" style="margin-right: 10px;">
                                                    <?php
                                                    $baseDir = '../../../uploads/meals/';
                                                    $imagePath = $baseDir . htmlspecialchars($meal['image'] ?? 'default-meal.jpg');
                                                    ?>
                                                    <img src="<?= $imagePath ?>" 
                                                         onerror="this.onerror=null;this.src='<?= $baseDir ?>default-meal.jpg';" 
                                                         alt="<?= htmlspecialchars($meal['name']) ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                </div>
                                                <div class="meal-details">
                                                    <h3><?= htmlspecialchars($meal['name']) ?></h3>
                                                    <p class="meal-quantity">Quantity: <?= (int)$meal['quantity'] ?></p>
                                                    <div class="meal-price"><?= number_format($meal['price'], 2) ?> EGP</div>
                                                </div>
                                            </div>
                                            <div class="meal-item-footer">
                                                <?php
                                                $scheduledCount = 0;
                                                foreach ($_SESSION['order_data']['scheduledMeals'] as $scheduledMeal) {
                                                    if ($scheduledMeal['meal_id'] === $mealId && $scheduledMeal['cart_item_id'] === $meal['cart_item_id']) {
                                                        $scheduledCount++;
                                                    }
                                                }
                                                $fullyScheduled = $scheduledCount >= $meal['quantity'];
                                                ?>
                                                <div class="date-picker-trigger <?= $fullyScheduled ? 'disabled' : '' ?>" 
                                                     data-meal-id="<?= htmlspecialchars($mealId) ?>" 
                                                     data-cart-item-id="<?= htmlspecialchars($meal['cart_item_id']) ?>"
                                                     <?= $fullyScheduled ? 'style="pointer-events:none; opacity:0.5;"' : '' ?>>
                                                    Select a delivery day
                                                </div>
                                                <div class="calendar-popup hidden" id="calendar-<?= htmlspecialchars($mealId) ?>"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <h2 class="card-title">Weekly Schedule</h2>
                            <div class="disclaimer">
                                <i class="fas fa-info-circle disclaimer-icon" aria-hidden="true"></i>
                                <span class="disclaimer-text">You can schedule orders for up to <strong>2 weeks</strong> only.</span>
                            </div>
                            <div id="schedule-container" class="schedule-container">
                                <div id="day-schedules">
                                    <?php if(empty($weeklyScheduleGrouped)): ?>
                                        <p class="empty-schedule">No meals scheduled yet. Start by adding meals from the available items.</p>
                                    <?php else: ?>
                                        <?php 
                                        ksort($weeklyScheduleGrouped);
                                        foreach ($weeklyScheduleGrouped as $day => $mealsOnDay): 
                                            $dayTotal = 0;
                                            foreach ($mealsOnDay as $meal) {
                                                $dayTotal += $meal['meal_price'] * $meal['count'];
                                            }
                                        ?>
                                            <div class="day-schedule">
                                                <div class="day-title">
                                                    <strong><?= date('l, F j, Y', strtotime($day)) ?></strong>
                                                </div>
                                                <div class="day-meals">
                                                    <?php foreach ($mealsOnDay as $meal): ?>
                                                        <div class="day-meal-item">
                                                            <div class="day-meal-details">
                                                                <div class="day-meal-image">
                                                                    <img src="../../../uploads/meals/<?= htmlspecialchars($meal['meal_image']) ?>" 
                                                                         alt="<?= htmlspecialchars($meal['meal_name']) ?>">
                                                                </div>
                                                                <div class="day-meal-info">
                                                                    <h4>
                                                                        <?= ($meal['count'] > 1 ? $meal['count'] . 'x ' : '') ?>
                                                                        <?= htmlspecialchars($meal['meal_name']) ?>
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                            <div class="day-meal-actions">
                                                                <span class="day-meal-price"><?= number_format($meal['meal_price'] * $meal['count'], 2) ?> EGP</span>
                                                                <!-- Remove form removes all scheduled meals for this meal_id and day -->
                                                                <form method="POST" style="display:inline;">
                                                                    <input type="hidden" name="meal_id" value="<?= htmlspecialchars($meal['meal_id']) ?>">
                                                                    <input type="hidden" name="delivery_day" value="<?= htmlspecialchars($day) ?>">
                                                                    <button type="submit" name="remove_group_meal" class="remove-button" title="Remove All Quantity">
                                                                        <i class="fas fa-trash-alt"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div><strong>Day Total: <?= number_format($dayTotal, 2) ?> EGP</strong></div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar">
            <div class="order-summary-card">
                <h2 class="order-summary-title">Order Summary</h2>
                
                <?php if (!empty($orderPackages)): ?>
                    <?php $packageIndex = 1; ?>
                    <?php foreach ($orderPackages as $day => $package): ?>
                        <div class="package-card">
                            <div class="package-header">
                                <div class="package-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= date('l, M j, Y', strtotime($day)) ?>
                                </div>
                                <span class="package-meal-count">
                                    <?= array_sum(array_column($package['meals'], 'quantity')) ?> meal<?= array_sum(array_column($package['meals'], 'quantity')) > 1 ? 's' : '' ?>
                                </span>
                            </div>
                            
                            <div class="package-meals">
                                <?php foreach ($package['meals'] as $meal): ?>
                                    <div class="meal-item">
                                        <img src="../../../uploads/meals/<?= htmlspecialchars($meal['meal_image']) ?>" alt="<?= htmlspecialchars($meal['meal_name']) ?>" class="meal-image">
                                        <div class="meal-details">
                                            <div class="meal-name">
                                                <?= $meal['quantity'] > 1 ? $meal['quantity'] . 'x ' : '' ?>
                                                <?= htmlspecialchars($meal['meal_name']) ?>
                                            </div>
                                        </div>
                                        <div class="meal-price">
                                            <?= number_format($meal['price'] * $meal['quantity'], 2) ?> EGP
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="price-row" style="margin-top: 10px;">
                                <span class="price-label">
                                    <i class="fas fa-tag"></i> Package Total:
                                </span>
                                <span class="price-value">
                                    <?= number_format($package['day_total'], 2) ?> EGP
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No scheduled meals found.</p>
                <?php endif; ?>
                
                <div class="price-summary">
                    <div class="price-row">
                        <span class="price-label">
                            <i class="fas fa-shopping-basket"></i> Subtotal:
                        </span>
                        <span class="price-value">
                            <?= number_format($total, 2) ?> EGP
                        </span>
                    </div>
                    
                    <div class="price-row">
                        <span class="price-label">
                            <i class="fas fa-truck"></i> Delivery Fee:
                        </span>
                        <span class="price-value">
                            <?= number_format($deliveryFee, 2) ?> EGP
                        </span>
                    </div>
                    
                    <div class="price-row total-row">
                        <span class="total-label">
                            <i class="fas fa-wallet"></i> Total:
                        </span>
                        <span class="total-value">
                            <?= number_format($grandTotal, 2) ?> EGP
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="button-container">
        <a href="../Order_Type/index.php?back=1" class="button button-back-outline" style="text-decoration: none;">Back</a>
        <?php if (!empty($_SESSION['order_data']['scheduledMeals']) && $allMealsScheduled): ?>
            <a href="../Payment/daily.php" class="button button-primary" style="text-decoration: none;">Continue to Payment</a>
        <?php else: ?>
            <button class="button button-primary" disabled title="<?= empty($_SESSION['order_data']['scheduledMeals']) ? 'Please schedule meals to continue.' : 'Please schedule all meals fully to continue.'; ?>">Continue to Payment</button>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggers = document.querySelectorAll('.date-picker-trigger:not(.disabled)');
    triggers.forEach(trigger => {
        const mealId = trigger.getAttribute('data-meal-id');
        const cartItemId = trigger.getAttribute('data-cart-item-id');
        const popup = document.getElementById(`calendar-${mealId}`);

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.calendar-popup').forEach(p => {
                if (p !== popup) p.classList.add('hidden');
            });
            popup.classList.toggle('hidden');

            if (!popup.innerHTML) {
                const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const dates = getNextDays(14);

                let html = "<table class='calendar'><thead><tr>";
                weekdays.forEach(day => html += `<th>${day}</th>`);
                html += "</tr></thead><tbody><tr>";

                dates.forEach((date, i) => {
                    if (i > 0 && i % 7 === 0) html += "</tr><tr>";
                    const isoDate = date.toISOString().split('T')[0];
                    html += `<td class="calendar-day" data-day="${isoDate}">${date.getDate()}</td>`;
                });

                html += "</tr></tbody></table>";
                popup.innerHTML = html;

                popup.querySelectorAll('.calendar-day').forEach(day => {
                    day.addEventListener('click', function() {
                        const isoDate = this.getAttribute('data-day');
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '';
                        form.innerHTML = `
                            <input type="hidden" name="meal_id" value="${mealId}">
                            <input type="hidden" name="cart_item_id" value="${cartItemId}">
                            <input type="hidden" name="day" value="${isoDate}">
                            <input type="hidden" name="add_meal" value="1">`;
                        document.body.appendChild(form);
                        form.submit();
                    });
                });
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.date-picker-trigger') && !e.target.closest('.calendar-popup')) {
            document.querySelectorAll('.calendar-popup').forEach(popup => {
                popup.classList.add('hidden');
            });
        }
    });

    function getNextDays(count) {
        const dates = [];
        let date = new Date();
        date.setDate(date.getDate() + 1); // start from tomorrow
        while(dates.length < count){
            dates.push(new Date(date));
            date.setDate(date.getDate() + 1);
        }
        return dates;
    }
});
</script>
</body>
</html>