<?php
session_start();
include("../../DB_connection.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_POST['approve'])) {
    $user_id = $_POST['user_id'];
    $sql = "UPDATE users SET is_approved = 1 WHERE user_id = $user_id";
    mysqli_query($conn, $sql);
}

if (isset($_POST['decline'])) {
    $user_id = $_POST['user_id'];
    $sql = "DELETE FROM users WHERE user_id = $user_id";
    mysqli_query($conn, $sql);
}

$sql = "SELECT user_id, name, email, created_at, role 
        FROM users 
        WHERE (role = 'caterer' OR role = 'delivery') 
        AND is_approved = 0";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pending Approvals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../frontend/css/admin.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-shield-lock me-2"></i>
                Admin Dashboard
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Pending Requests</h2>
                    <div class="badge bg-primary rounded-pill">
                        <?php echo mysqli_num_rows($result); ?> Pending
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                                <th> Role </th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td>#<?= str_pad($row['user_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                    <td><?= htmlspecialchars($row['role']); ?></td> 
                                    <td><span class="badge bg-warning">Pending</span></td> 
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                                                <button type="submit" name="approve" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                                                <button type="submit" name="decline" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-x-lg"></i> Decline
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
