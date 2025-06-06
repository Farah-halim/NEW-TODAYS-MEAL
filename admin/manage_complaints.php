
<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Complaints</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff7e5; }
        .navbar { background-color: #e57e24; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Manage Complaints</span>
            <a href="admin_dashboard.php" class="btn btn-outline-light">Back to Dashboard</a>
        </div>
    </nav>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Complaints List</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Kitchen</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT c.*, cu.name as customer_name, k.name as kitchen_name 
                                              FROM complaints c 
                                              LEFT JOIN customer cu ON c.customer_id = cu.id 
                                              LEFT JOIN cloud_kitchen_owner k ON c.kitchen_id = k.id");
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['customer_name']}</td>
                                <td>{$row['kitchen_name']}</td>
                                <td>{$row['subject']}</td>
                                <td>{$row['status']}</td>
                                <td>{$row['created_at']}</td>
                                <td>
                                    <a href='?action=view&id={$row['id']}' class='btn btn-sm btn-info'>View</a>
                                    <a href='?action=resolve&id={$row['id']}' class='btn btn-sm btn-success'>Resolve</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
