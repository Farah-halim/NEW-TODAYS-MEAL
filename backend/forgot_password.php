<?php
include("../DB_connection.php"); 
date_default_timezone_set('Africa/Cairo');

$showLoader = false;
$redirectURL = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $showLoader = true;  

    $query = "SELECT user_id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];

        $token = bin2hex(random_bytes(32)); 
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update_query = "UPDATE users SET reset_token = '$token', reset_token_expiry = '$expiry' WHERE user_id = '$user_id'";
        mysqli_query($conn, $update_query);

        $reset_link = "http://localhost/Todays-Meal-1/backend/reset_password.php?token=" . $token;
        $redirectURL = $reset_link;
    } else {
        $redirectURL = "forgot_password.php?error=notfound";
    }}?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        .loader-container {
            display: <?php echo $showLoader ? 'flex' : 'none'; ?>;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .loader {
            width: 80px;
            height: 40px;
            border-radius: 0 0 100px 100px;
            border: 5px solid #538a2d;
            border-top: 0;
            box-sizing: border-box;
            background:
                radial-gradient(farthest-side at top, #0000 calc(100% - 5px), #e7ef9d calc(100% - 4px)), 
                radial-gradient(2px 3px, #5c4037 89%, #0000) 0 0/17px 12px, #ff1643;
            --c: radial-gradient(farthest-side, #000 94%, #0000);
            -webkit-mask:
                linear-gradient(#0000 0 0),
                var(--c) 12px -8px,
                var(--c) 29px -8px,
                var(--c) 45px -6px,
                var(--c) 22px -2px,
                var(--c) 34px 6px, 
                var(--c) 21px 6px,
                linear-gradient(#000 0 0);
            mask:
                linear-gradient(#000 0 0),
                var(--c) 12px -8px,
                var(--c) 29px -8px,
                var(--c) 45px -6px,
                var(--c) 22px -2px,
                var(--c) 34px 6px, 
                var(--c) 21px 6px,
                linear-gradient(#0000 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude, add, add, add, add, add, add;
            -webkit-mask-repeat: no-repeat;
            animation: l8 5s infinite;
        }

        @keyframes l8 {
            0%   {-webkit-mask-size: auto, 0 0, 0 0, 0 0, 0 0, 0 0, 0 0}
            15%  {-webkit-mask-size: auto, 20px 20px, 0 0, 0 0, 0 0, 0 0, 0 0, 0 0}
            30%  {-webkit-mask-size: auto, 20px 20px, 20px 20px, 0 0, 0 0, 0 0, 0 0}
            45%  {-webkit-mask-size: auto, 20px 20px, 20px 20px, 20px 20px, 0 0, 0 0, 0 0}
            60%  {-webkit-mask-size: auto, 20px 20px, 20px 20px, 20px 20px, 20px 20px, 0 0, 0 0}
            75%  {-webkit-mask-size: auto, 20px 20px, 20px 20px, 20px 20px, 20px 20px, 20px 20px, 0 0}
            90%, 100% {-webkit-mask-size: auto, 20px 20px, 20px 20px, 20px 20px, 20px 20px, 20px 20px, 20px 20px}
        }

        .wait-text {
            margin-top: 15px;
            animation: fadeIn 1s infinite alternate;
        }

        @keyframes fadeIn {
            0% { opacity: 0.3; }
            100% { opacity: 1; }
        }
    </style>
    
    <?php if ($showLoader): ?>
    <meta http-equiv="refresh" content="2;url=<?php echo $redirectURL; ?>">
    <?php endif; ?>
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <div class="wait-text">Wait ..</div>
    </div>

    <h2>Forgot Password</h2>
    <?php
if (isset($_GET['error']) && $_GET['error'] == 'notfound') {
    echo "<script> 
        alert('❌ Email not found!');
        window.location.href = 'forgot_password.php';
    </script>";
    exit();
} ?>
    <form method="POST">
        <input type="email" name="email" required placeholder="Enter your email">
        <button type="submit">Verify</button>
    </form>

</body>
</html>