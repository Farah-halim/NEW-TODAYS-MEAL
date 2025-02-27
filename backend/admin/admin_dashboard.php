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

// Fetch pending caterers
$sql = "SELECT user_id, name, email 
FROM users 
WHERE role = 'caterer' AND is_approved = 0";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Welcome, Admin!</h2>
    <h3>Pending Caterer Requests:</h3>

    <table border="1">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['name']; ?></td>
                <td><?= $row['email']; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                        <button type="submit" name="approve">Approve</button>
                    </form>
                
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                        <button type="submit" name="decline">Decline</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <br>
</body>
</html>
