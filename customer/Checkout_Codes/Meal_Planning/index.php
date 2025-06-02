<?php
session_start();
require_once('../../DB_connection.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

// Initialize cart items array
$cartItems = [];
$orderPackages = []; 
$cart_id = 0;

// Process back action
if (isset($_GET['back']) && $_GET['back'] == '1') {
    unset($_SESSION['order_data']['scheduledMeals']);
    header("Location: index.php");
    exit();
}

// Fetch user's active cart
$user_id = $_SESSION['user_id'];
$cartQuery = "SELECT c.cart_id 
              FROM cart c WHERE c.customer_id = ? 
              ORDER BY c.created_at DESC LIMIT 1";
$stmt = $conn->prepare($cartQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartResult = $stmt->get_result();

if ($cartResult && $cartResult->num_rows > 0) {
    $cart = $cartResult->fetch_assoc();
    $cart_id = $cart['cart_id'];
    $_SESSION['cart_id'] = $cart_id; // Store cart_id in session
}
$stmt->close();

// Get cart items
if ($cart_id > 0) {
    $itemsQuery = "SELECT ci.cart_item_id, ci.meal_id, ci.quantity, ci.price,
                          m.name, m.photo AS image
                   FROM cart_items ci
                   JOIN meals m ON ci.meal_id = m.meal_id
                   WHERE ci.cart_id = ?";
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    
    while ($row = $itemsResult->fetch_assoc()) {
        $cartItems[$row['meal_id']] = $row;
    }
    $stmt->close();
}

// Validate order data
if (!isset($_SESSION['order_data']) || 
    $_SESSION['order_data']['deliveryPreference'] !== 'daily' || 
    $_SESSION['order_data']['orderType'] !== 'scheduled') {
    header("Location: ../Order_Type/index.php");
    exit();
}

// Initialize scheduled meals if not set
if (!isset($_SESSION['order_data']['scheduledMeals'])) {
    $_SESSION['order_data']['scheduledMeals'] = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adding meals to schedule
    if (isset($_POST['add_meal'])) {
        $mealId = (int)$_POST['meal_id'];
        $day = trim($_POST['day']);
        $cartItemId = (int)$_POST['cart_item_id'];

        // Validate date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $_SESSION['error_message'] = "Invalid date format selected.";
            header("Location: .");
            exit();
        }

        // Check meal validity
        if (isset($cartItems[$mealId]) && $cartItems[$mealId]['quantity'] > 0) {
            $scheduledCount = 0;
            foreach ($_SESSION['order_data']['scheduledMeals'] as $scheduledMeal) {
                if ($scheduledMeal['meal_id'] === $mealId) {
                    $scheduledCount++;
                }
            }

            if ($scheduledCount < $cartItems[$mealId]['quantity']) {
                $_SESSION['order_data']['scheduledMeals'][] = [
                    'id' => uniqid(),
                    'meal_id' => $mealId,
                    'cart_item_id' => $cartItemId,
                    'day' => $day  // Now in YYYY-MM-DD format
                ];
            } else {
                $_SESSION['error_message'] = "You cannot schedule this meal more than " . $cartItems[$mealId]['quantity'] . " times.";
            }
        }
        header("Location: .");
        exit();
    }

    // Removing meals from schedule
    if (isset($_POST['remove_meal'])) {
        $scheduledMealId = $_POST['meal_id'];
        foreach ($_SESSION['order_data']['scheduledMeals'] as $key => $meal) {
            if ($meal['id'] === $scheduledMealId) {
                unset($_SESSION['order_data']['scheduledMeals'][$key]);
                break;
            }
        }
        header("Location: .");
        exit();
    }
}

// Preparing order summary data
$deliveryFee = 15.00; // Fixed delivery fee
$total = 0;

if (!empty($_SESSION['order_data']['scheduledMeals'])) {
    foreach ($_SESSION['order_data']['scheduledMeals'] as $scheduledMeal) {
        $mealId = $scheduledMeal['meal_id'];
        $deliveryDay = $scheduledMeal['day'];
        if (isset($cartItems[$mealId])) {
            $meal = $cartItems[$mealId];
            $packagePrice = $meal['price'];

            if (!isset($orderPackages[$deliveryDay])) {
                $orderPackages[$deliveryDay] = [
                    'date' => $deliveryDay,
                    'meals' => [],
                    'day_total' => 0
                ];
            }

            $orderPackages[$deliveryDay]['meals'][] = [
                'meal_name' => $meal['name'],
                'meal_image' => $meal['image'],
                'price' => $packagePrice
            ];

            $orderPackages[$deliveryDay]['day_total'] += $packagePrice;
            $total += $packagePrice;
        }
    }
}
$grandTotal = $total + $deliveryFee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planning - Food Delivery Checkout</title>
    <meta name="description" content="Plan your meals for different days of the week with our easy-to-use meal planner.">
    <link rel="stylesheet" href="../global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include '../../global/navbar/navbar.php'; ?>
    <div class="container">
        <h1>Checkout</h1>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="checkout-steps">
            <div class="step completed">
                <div class="step-circle">
                    <img src="../icons/check.svg" alt="Completed" width="16" height="16">
                </div>
                <span class="step-name">Order Type</span>
            </div>
            <div class="step completed">
                <div class="step-circle">
                    <img src="../icons/check.svg" alt="Completed" width="16" height="16">
                </div>
                <span class="step-name">Meal Planning</span>
            </div>
            <div class="step active">
                <div class="step-circle active">
                    <span>3</span>
                </div>
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
                                            <div class="meal-item" data-meal-id="<?= $mealId ?>">
                                                <div class="meal-item-header">
                                                    <div class="meal-image" style="margin-right: 10px;">
                                                        <?php
                                                        $baseDir = '../../../uploads/meals/';
                                                        $storedFilename = htmlspecialchars($meal['image'] ?? '');
                                                        $imagePath = $baseDir . $storedFilename;
                                                        ?>
                                                        <img src="<?= $imagePath ?>" 
                                                             onerror="this.onerror=null;this.src='<?= $baseDir ?>default-meal.jpg';" 
                                                             alt="<?= htmlspecialchars($meal['name']) ?>" 
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    </div>
                                                    <div class="meal-details">
                                                        <h3><?= htmlspecialchars($meal['name']) ?></h3>
                                                        <p class="meal-quantity">Quantity: <?= $meal['quantity'] ?></p>
                                                        <div class="meal-price"><?= number_format($meal['price'], 2) ?> EGP</div>
                                                    </div>
                                                </div>
                                                <div class="meal-item-footer">
                                                    <?php 
                                                    $scheduledCount = 0;
                                                    foreach ($_SESSION['order_data']['scheduledMeals'] as $scheduledMeal) {
                                                        if ($scheduledMeal['meal_id'] === $mealId) {
                                                            $scheduledCount++;
                                                        }
                                                    }
                                                    $allMealsScheduled = $scheduledCount >= $meal['quantity'];
                                                    ?>
                                                    <div class="date-picker-trigger <?= $allMealsScheduled ? 'disabled' : '' ?>" 
                                                         data-meal-id="<?= $mealId ?>" 
                                                         data-cart-item-id="<?= $meal['cart_item_id'] ?>"
                                                         <?= $allMealsScheduled ? 'style="pointer-events:none; opacity:0.5;"' : '' ?>>
                                                        Select a delivery day
                                                    </div>
                                                    <div class="calendar-popup hidden" id="calendar-<?= $mealId ?>"></div>
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
                                    <?php if (empty($_SESSION['order_data']['scheduledMeals'])): ?>
                                        <p class="empty-schedule">No meals scheduled yet. Start by adding meals from the available items.</p>
                                    <?php else: ?>
                                        <div id="day-schedules">
                                            <?php 
                                            $groupedMeals = [];
                                            foreach ($_SESSION['order_data']['scheduledMeals'] as $meal) {
                                                if (isset($meal['day']) && isset($meal['meal_id'])) {
                                                    $groupedMeals[$meal['day']][] = $meal;
                                                }
                                            }
                                            
                                            // Sort by date
                                            ksort($groupedMeals);
                                            
                                            foreach ($groupedMeals as $day => $meals): ?>
                                                <div class="day-schedule">
                                                    <div class="day-title">
                                                        <strong><?= date('l, F j, Y', strtotime($day)) ?></strong>
                                                    </div>
                                                    <div class="day-meals">
                                                        <?php foreach ($meals as $scheduledMeal): 
                                                            if (isset($scheduledMeal['meal_id']) && isset($cartItems[$scheduledMeal['meal_id']])) {
                                                                $cartMeal = $cartItems[$scheduledMeal['meal_id']]; ?>
                                                                <div class="day-meal-item">
                                                                    <div class="day-meal-details">
                                                                        <div class="day-meal-image">
                                                                            <img src="../../../uploads/meals/<?= htmlspecialchars($cartMeal['image'] ?? 'default-meal.jpg') ?>" 
                                                                                 alt="<?= htmlspecialchars($cartMeal['name']) ?>">
                                                                        </div>
                                                                        <div class="day-meal-info">
                                                                            <h4><?= htmlspecialchars($cartMeal['name']) ?></h4>
                                                                        </div>
                                                                    </div>
                                                                    <div class="day-meal-actions">
                                                                        <span class="day-meal-price"><?= number_format($cartMeal['price'], 2) ?> EGP</span>
                                                                        <form method="POST" style="display:inline;">
                                                                            <input type="hidden" name="meal_id" value="<?= $scheduledMeal['id'] ?>">
                                                                            <button type="submit" name="remove_meal" class="remove-button">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="card order-summary">
                    <div class="card-content">
                        <h2 class="order-summary-title">Order Summary</h2>
                        <?php if (!empty($orderPackages)): ?>
                            <?php foreach ($orderPackages as $deliveryDay => $package): ?>
                                <div class="order-package">
                                    <div class="package-header">
                                        <span class="delivery-date"><?= date('l, F j, Y', strtotime($package['date'])) ?> (<?= count($package['meals']) ?> meal<?= count($package['meals']) > 1 ? 's' : '' ?>)</span>
                                    </div>
                                    <div class="meals-info">
                                        <?php foreach ($package['meals'] as $meal): ?>
                                            <div class="meal-details" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                <div class="meal-info" style="flex-grow: 1; display: flex; align-items: center;">
                                                    <div class="meal-image" style="margin-right: 10px;">
                                                        <img src="../../../uploads/meals/<?= htmlspecialchars($meal['meal_image'] ?? 'default-meal.jpg') ?>" 
                                                             alt="<?= htmlspecialchars($meal['meal_name']) ?>" style="width: 50px; height: 50px;">
                                                    </div>
                                                    <div class="meal-name"><?= htmlspecialchars($meal['meal_name']) ?></div>
                                                </div>
                                                <div class="meal-price" style="margin-left: auto;">
                                                    <?= number_format($meal['price'], 2) ?> EGP
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="delivery-fee">Delivery Fee: <?= number_format($deliveryFee, 2) ?> EGP</div>
                                    <div class="day-total">Day Total: <?= number_format($package['day_total'], 2) ?> EGP</div>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                            <div class="order-summary-totals">
                                <p>Subtotal: <?= number_format($total, 2) ?> EGP</p>
                                <p>Delivery Fee: <?= number_format($deliveryFee, 2) ?> EGP</p>
                                <p><strong>Grand Total: <?= number_format($grandTotal, 2) ?> EGP</strong></p>
                            </div>
                        <?php else: ?>
                            <p>No meals scheduled yet</p>
                            <button class="button button-primary" disabled>Continue</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="button-container">
            <a href="../Order_Type/index.php?back=1" class="button button-back-outline">Back</a>
            <?php if (!empty($_SESSION['order_data']['scheduledMeals'])): ?>
                <?php 
                // Verify cart has items before allowing to proceed
                $checkItems = "SELECT COUNT(*) as item_count FROM cart_items WHERE cart_id = ?";
                $stmt = $conn->prepare($checkItems);
                $stmt->bind_param("i", $_SESSION['cart_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                
                if ($row['item_count'] > 0): ?>
                    <a href="../Payment/daily.php" class="button button-primary">Continue to Payment</a>
                <?php else: ?>
                    <button class="button button-primary" disabled>Continue to Payment</button>
                    <p class="error">Your cart is empty. Please add items.</p>
                <?php endif; ?>
            <?php else: ?>
                <button class="button button-primary" disabled>Continue to Payment</button>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const triggers = document.querySelectorAll('.date-picker-trigger');
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
                        const isoDate = date.toISOString().split('T')[0]; // YYYY-MM-DD format
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
            date.setDate(date.getDate() + 1); // Start from tomorrow
            
            // Skip today and get next 'count' days
            while (dates.length < count) {
                // Skip weekends if needed (optional)
                // if (date.getDay() !== 0 && date.getDay() !== 6) {
                //     dates.push(new Date(date));
                // }
                dates.push(new Date(date));
                date.setDate(date.getDate() + 1);
            }
            return dates;
        }
    });
    </script>
</body>
</html>