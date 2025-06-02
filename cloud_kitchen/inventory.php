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

$cloud_kitchen_id = $_SESSION['cloud_kitchen_id'];
$quantity_updated = false;

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $meal_id = intval($_POST['meal_id']);
    $new_quantity = intval($_POST['new_quantity']);
    
    // Verify the meal belongs to this cloud kitchen
    $verify_stmt = $conn->prepare("SELECT meal_id FROM meals WHERE meal_id = ? AND cloud_kitchen_id = ?");
    $verify_stmt->bind_param("ii", $meal_id, $cloud_kitchen_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows === 1) {
        // Determine the status based on the new quantity
        $status = ($new_quantity === 0) ? 'out of stock' : (($new_quantity <= 10) ? 'low stock' : 'available');

        // Using prepared statements to prevent SQL injection
        $stmt = $conn->prepare("UPDATE meals SET stock_quantity = ?, status = ? WHERE meal_id = ? AND cloud_kitchen_id = ?");
        $stmt->bind_param("isii", $new_quantity, $status, $meal_id, $cloud_kitchen_id);
        $stmt->execute();
        $stmt->close();

        header("Location: " . $_SERVER['PHP_SELF'] . "?update=success");
        exit();
    }
    $verify_stmt->close();
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Prepare the SQL query for fetching meals
$query = "SELECT * FROM meals WHERE cloud_kitchen_id = ?";
$params = [$cloud_kitchen_id];
$types = 'i'; // cloud_kitchen_id is integer

if ($search) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss'; // adding two strings for the LIKE clauses
}

// Map the filter values to database values
$status_mapping = [
    'All Statuses' => '',
    'Available' => 'available',
    'Low Stock' => 'low stock',
    'Out of Stock' => 'out of stock'
];

if ($status_filter && $status_filter !== "All Statuses") {
    $status_value = $status_mapping[$status_filter];
    $query .= " AND status = ?";
    $params[] = $status_value;
    $types .= 's'; // adding one string for status
}

// Prepare the SQL statement
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$meals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="front_end/inventory/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'global/navbar.php'; ?>
    <main class="container">
        <div class="white-container">
            <form method="GET" action="">
                <div class="top-controls">
                    <input type="text" name="search" placeholder="Search by meal name..." class="search-input" value="<?php echo htmlspecialchars($search); ?>" />
                    <select name="status" class="status-select" onchange="this.form.submit()">
                        <option value="All Statuses" <?php echo $status_filter === 'All Statuses' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Available" <?php echo $status_filter === 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Low Stock" <?php echo $status_filter === 'Low Stock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="Out of Stock" <?php echo $status_filter === 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                    <button type="button" class="export-btn btn-export" onclick="exportToExcel()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" x2="12" y1="15" y2="3"></line>
                        </svg> Export 
                    </button>
                    <button type="submit" style="display: none;">Submit</button>
                </div>
            </form>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-amber-800 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider rounded-tl-lg w-16">#</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Meal</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-sm font-medium uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-sm font-medium uppercase tracking-wider rounded-tr-lg">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($meals)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-sm text-gray-500">No meals found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($meals as $index => $meal): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php if (!empty($meal['photo']) && file_exists($meal['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($meal['photo']); ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" style="width: 40px; height: 40px; border-radius: 4px;" onerror="this.style.display='none'">
                                        <?php endif; ?>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($meal['name']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo '$' . number_format($meal['price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center"><?php echo $meal['stock_quantity']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="status-display">
                                        <span class="status-dot <?php echo str_replace(' ', '_', $meal['status']); ?>"></span>
                                        <span class="status-text"><?php echo ucwords($meal['status']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button class="update-btn" onclick="showQuantityModal(<?php echo $meal['meal_id']; ?>, <?php echo $meal['stock_quantity']; ?>)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                        </svg>
                                        Update
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Modal to update quantity -->
            <div class="modal" id="quantityModal">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h3>Update Stock Quantity</h3>
                            <button type="button" class="close-qnty-btn" onclick="closeModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="meal_id" id="modalMealId">
                            <div class="quantity-control">
                                <button type="button" class="qty-btn" onclick="adjustQuantity(-1)">-</button>
                                <input type="number" name="new_quantity" id="newQuantity" min="0" value="0" required>
                                <button type="button" class="qty-btn" onclick="adjustQuantity(1)">+</button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                            <button type="submit" name="update_quantity" class="btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
            <script>
                // Function to export to Excel
                function exportToExcel() {
                    if (confirm(`Are you sure you want to download the complete meals inventory?\nThis will export all <?php echo count($meals); ?> meals in Excel format.`)) {
                        const table = document.querySelector('.min-w-full');
                        const workbook = XLSX.utils.table_to_book(table);
                        XLSX.writeFile(workbook, 'meals_inventory.xlsx');
                    }
                }

                // Show quantity modal
                function showQuantityModal(mealId, currentQuantity) {
                    document.getElementById("modalMealId").value = mealId;
                    document.getElementById("newQuantity").value = currentQuantity;
                    document.getElementById("quantityModal").style.display = "flex";
                }

                // Adjust quantity up or down
                function adjustQuantity(change) {
                    const input = document.getElementById("newQuantity");
                    input.value = Math.max(0, parseInt(input.value) + change);
                }

                // Close modal function
                function closeModal() {
                    document.getElementById("quantityModal").style.display = "none";
                }

                // Show alert after successful update
                <?php if (isset($_GET['update']) && $_GET['update'] == 'success'): ?>
                    window.onload = function() {
                        alert('The quantity has been updated successfully!');
                    };
                <?php endif; ?>
            </script>
        </div>
    </main>
</body>
</html>