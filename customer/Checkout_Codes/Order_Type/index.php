<?php
session_start();
require_once('../../DB_connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /NEW-TODAYS-MEAL/Register&Login/login.php");
    exit();
}

// Initialize order data in session if not exists
if (!isset($_SESSION['order_data'])) {
    $_SESSION['order_data'] = [
        'orderType' => 'weekly',
        'deliveryPreference' => null,
        'deliveryType' => null,
        'deliveryDay' => null
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delivery_preference'])) {
        $_SESSION['order_data']['deliveryPreference'] = $_POST['delivery_preference'];
        
        // Set delivery type based on selection
        if ($_POST['delivery_preference'] === 'all-at-once') {
            $_SESSION['order_data']['deliveryType'] = 'all_at_once';
            $_SESSION['order_data']['orderType'] = 'one_time';
            
            if (isset($_POST['delivery_day'])) {
                $_SESSION['order_data']['deliveryDay'] = $_POST['delivery_day'];
                header("Location: /NEW-TODAYS-MEAL/customer/Checkout_Codes/Payment/all_at_once.php");
                exit();
            }
        } else {
            $_SESSION['order_data']['deliveryType'] = 'daily_delivery';
            $_SESSION['order_data']['orderType'] = 'scheduled';
            $_SESSION['order_data']['deliveryDay'] = null;
            header("Location: ../Meal_Planning/index.php");
            exit();
        }
    }
}

// Function to generate available delivery dates
function getAvailableDeliveryDates($count) {
    $dates = [];
    $date = new DateTime();
    $date->modify('+1 day'); // Start from tomorrow
    
    for ($i = 0; $i < $count; $i++) {
        $dates[] = clone $date;
        $date->modify('+1 day');
    }
    
    return $dates;
}

// Function to format date
function getFormattedDate($date) {
    return $date->format('l, F j, Y');
}

$availableDates = getAvailableDeliveryDates(14); // Two weeks of dates
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Preference - Food Delivery Checkout</title>
    <meta name="description" content="Choose your food delivery order type and preferences for a delicious meal delivered to your door.">
    <link rel="stylesheet" href="../global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
      <?php include '..\..\global\navbar\navbar.php'; ?>

    <div class="container">
        <h1>Checkout</h1>

        <div class="checkout-steps">
            <div class="step">
                <div class="step-circle active">
                    <span>1</span>
                </div>
                <span class="step-name">Delivery</span>
            </div>

            <div class="step">
                <div class="step-circle">
                    <span>2</span>
                </div>
                <span class="step-name">Meal Planning</span>
            </div>

            <div class="step">
                <div class="step-circle">
                    <span>3</span>
                </div>
                <span class="step-name">Review & Pay</span>
            </div>
        </div>

        <div class="main-content">
            <div class="main-column">
                <div class="card">
                    <div class="card-content">
                        <h2 class="card-title">Choose Your Delivery Preference</h2>

                        <form id="delivery-preference-form" method="POST">
                            <div id="delivery-preference-section">
                                <div class="option-card <?= ($_SESSION['order_data']['deliveryPreference'] === 'all-at-once') ? 'selected' : '' ?>" 
                                     id="all-at-once" 
                                     onclick="selectDeliveryPreference('all-at-once')">
                                    <div class="option-header">
                                        <div class="option-icon">
                                            <img src="../icons/all-at-once.svg" alt="All At Once Delivery Icon">
                                        </div>
                                        <div>
                                            <h3 class="option-title">All-at-once Delivery</h3>
                                            <p class="option-description">Receive all your weekly meals in one delivery</p>

                                            <div id="delivery-day-section" class="<?= ($_SESSION['order_data']['deliveryPreference'] !== 'all-at-once') ? 'hidden' : '' ?>" style="margin-top: 1rem;">
                                                <label class="form-label">Select delivery day:</label>
                                                <div class="calendar-container">
                                                    <div id="delivery-day-display" class="form-input" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="event.stopPropagation(); toggleCalendar()">
                                                        <span id="selected-day-text">
                                                            <?= ($_SESSION['order_data']['deliveryDay']) ? getFormattedDate(new DateTime($_SESSION['order_data']['deliveryDay'])) : 'Select a day' ?>
                                                        </span>
                                                        <img src="../icons/calendar-icon.svg" alt="Calendar" width="16" height="16">
                                                    </div>
                                                    <div id="calendar-popup" class="hidden" style="position: absolute; background: white; border: 1px solid #ccc; border-radius: 4px; margin-top: 0.5rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 50;">
                                                        <div id="calendar" style="padding: 1rem;">
                                                            <table class="calendar">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Sun</th>
                                                                        <th>Mon</th>
                                                                        <th>Tue</th>
                                                                        <th>Wed</th>
                                                                        <th>Thu</th>
                                                                        <th>Fri</th>
                                                                        <th>Sat</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <?php
                                                                        $today = new DateTime();
                                                                        foreach ($availableDates as $index => $date) {
                                                                            if ($index > 0 && $index % 7 === 0) {
                                                                                echo '</tr><tr>';
                                                                            }
                                                                            
                                                                            $isSelected = ($_SESSION['order_data']['deliveryDay'] === $date->format('Y-m-d'));
                                                                            $isDisabled = ($date <= $today);
                                                                            
                                                                            echo '<td class="calendar-day ' . ($isSelected ? 'selected' : '') . ($isDisabled ? ' disabled' : '') . '"';
                                                                            echo ' data-date="' . $date->format('Y-m-d') . '"';
                                                                            echo ($isDisabled ? ' disabled' : ' onclick="selectDeliveryDay(this)"') . '>';
                                                                            echo $date->format('j');
                                                                            echo '</td>';
                                                                        }
                                                                        
                                                                        // Fill remaining cells
                                                                        $remainingCells = 7 - (count($availableDates) % 7);
                                                                        if ($remainingCells < 7) {
                                                                            for ($i = 0; $i < $remainingCells; $i++) {
                                                                                echo '<td></td>';
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="delivery-day-input" name="delivery_day" value="<?= $_SESSION['order_data']['deliveryDay'] ?? '' ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-card <?= ($_SESSION['order_data']['deliveryPreference'] === 'daily') ? 'selected' : '' ?>" 
                                     id="daily-delivery" 
                                     onclick="selectDeliveryPreference('daily')">
                                    <div class="option-header">
                                        <div class="option-icon">
                                            <img src="../icons/daily-delivery.svg" alt="Daily Delivery Icon">
                                        </div>
                                        <div>
                                            <h3 class="option-title">Daily Delivery</h3>
                                            <p class="option-description">Get fresh meals delivered each day of the week</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="delivery-preference-input" name="delivery_preference" value="<?= $_SESSION['order_data']['deliveryPreference'] ?? '' ?>">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="button-container">
            <div></div>
            <button type="submit" form="delivery-preference-form" class="button button-primary" id="continue-button" <?= (!$_SESSION['order_data']['deliveryPreference'] || ($_SESSION['order_data']['deliveryPreference'] === 'all-at-once' && !$_SESSION['order_data']['deliveryDay'])) ? 'disabled' : '' ?>>
                Continue
            </button>
        </div>
    </div>

    <script>
        function selectDeliveryPreference(preference) {
            document.getElementById('delivery-preference-input').value = preference;

            // Update UI
            document.getElementById('all-at-once').classList.toggle('selected', preference === 'all-at-once');
            document.getElementById('daily-delivery').classList.toggle('selected', preference === 'daily');

            // Show/hide delivery day section
            const daySection = document.getElementById('delivery-day-section');
            if (preference === 'all-at-once') {
                daySection.classList.remove('hidden');
            } else {
                daySection.classList.add('hidden');
                document.getElementById('delivery-day-input').value = '';
            }

            // Enable/disable continue button
            updateContinueButton();
        }
        
        function toggleCalendar() {
            document.getElementById('calendar-popup').classList.toggle('hidden');
        }
        
        function selectDeliveryDay(element) {
            const dateStr = element.getAttribute('data-date');
            const date = new Date(dateStr);
            
            // Update selected day in UI
            document.querySelectorAll('.calendar-day').forEach(day => {
                day.classList.remove('selected');
            });
            element.classList.add('selected');

            // Format date (e.g., "Monday, January 1, 2023")
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('selected-day-text').textContent = date.toLocaleDateString('en-US', options);
            document.getElementById('delivery-day-input').value = dateStr;

            // Close calendar
            document.getElementById('calendar-popup').classList.add('hidden');

            // Enable continue button if needed
            updateContinueButton();
        }
        
        function updateContinueButton() {
            const preference = document.getElementById('delivery-preference-input').value;
            const deliveryDay = document.getElementById('delivery-day-input').value;
            const continueButton = document.getElementById('continue-button');
            
            if (preference === 'all-at-once' && !deliveryDay) {
                continueButton.disabled = true;
            } else {
                continueButton.disabled = false;
            }
        }
        
        // Close calendar when clicking outside
        document.addEventListener('click', function(event) {
            const calendarPopup = document.getElementById('calendar-popup');
            const deliveryDayDisplay = document.getElementById('delivery-day-display');
            
            if (!calendarPopup.classList.contains('hidden') && 
                !calendarPopup.contains(event.target) && 
                event.target !== deliveryDayDisplay) {
                calendarPopup.classList.add('hidden');
            }
        });
    </script>
</body>
</html>