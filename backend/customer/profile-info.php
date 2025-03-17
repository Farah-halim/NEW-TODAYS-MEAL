<?php
session_start();
require '../../DB_connection.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data including password
$query = "SELECT name, email, phone, address1, address2, gender, role, password FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Set default values
$name = $user['name'] ?? '';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$address1 = $user['address1'] ?? '';
$address2 = $user['address2'] ?? '';
$gender = $user['gender'] ?? 'Male';
$role = $user['role'] ?? 'customer';
$stored_password = $user['password'] ?? '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']) ?: $name;
    $new_email = trim($_POST['email']) ?: $email;
    $new_phone = trim($_POST['phone']) ?: $phone;
    $new_address1 = trim($_POST['address1']) ?: $address1;
    $new_address2 = trim($_POST['address2']) ?: $address2;
    $new_gender = in_array($_POST['gender'], ['Male', 'Female']) ? $_POST['gender'] : $gender;

    // Check if email already exists (excluding the current user)
    if ($new_email !== $email) {
        $emailCheckQuery = "SELECT COUNT(*) AS count FROM users WHERE email = ? AND user_id != ?";
        $stmt = mysqli_prepare($conn, $emailCheckQuery);
        mysqli_stmt_bind_param($stmt, "si", $new_email, $user_id);
        mysqli_stmt_execute($stmt);
        $emailCheckResult = mysqli_stmt_get_result($stmt);
        $emailData = mysqli_fetch_assoc($emailCheckResult);

        if ($emailData['count'] > 0) {
            echo "<script>alert('خطأ: البريد الإلكتروني مستخدم بالفعل.'); window.location.href='profile-info.php';</script>";
            exit();
        }
    }

    // Update user profile in database
    $update_query = "UPDATE users SET name=?, email=?, phone=?, address1=?, address2=?, gender=? WHERE user_id=?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "ssssssi", $new_name, $new_email, $new_phone, $new_address1, $new_address2, $new_gender, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['user_name'] = $new_name;
        echo "<script>alert('تم تحديث الملف الشخصي بنجاح!'); window.location.href='profile-info.php';</script>";
        exit();
    } else {
        echo "<script>alert('خطأ أثناء تحديث الملف الشخصي: " . mysqli_error($conn) . "');</script>";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $stored_password)) {
        echo "<script>alert('خطأ: كلمة المرور القديمة غير صحيحة!');</script>";
    } elseif ($new_password !== $confirm_password) {
        echo "<script>alert('خطأ: كلمة المرور الجديدة غير متطابقة!');</script>";
    } elseif (strlen($new_password) < 6) {
        echo "<script>alert('خطأ: يجب أن تحتوي كلمة المرور الجديدة على 6 أحرف على الأقل!');</script>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_password_query = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_password_query);
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('تم تغيير كلمة المرور بنجاح!'); window.location.href='profile-info.php';</script>";
            exit();
        } else {
            echo "<script>alert('خطأ أثناء تغيير كلمة المرور: " . mysqli_error($conn) . "');</script>";
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../frontend/css/meal.css">
    <script defer src="../../frontend/js/meal.js"></script>
</head>
<body>

<?php include '../nav3.php'; ?>
<?php include 'sidebar-profile.php'; ?>

<main class="container my-4 ms-3">
    <div class="row">
        <div class="col-md-5 e1">
            <h4 class="text-black d-flex align-items-center border-bottom">
                <span class="badge me-2">1</span> Edit Profile <i class="fas fa-edit ms-2"></i>
            </h4>
            <form action="profile-info.php" method="POST">
                
                <div class="mb-3 position-relative">
                    <label class="text-black small fw-bold">Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?= htmlspecialchars($name) ?>" required>
                    <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                </div>

                <div class="mb-3 position-relative">
                    <label class="text-black small fw-bold">E-mail <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?= htmlspecialchars($email) ?>" required>
                    <i class="fas fa-envelope text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                </div>

                <div class="mb-3 position-relative">
                    <label class="text-black small fw-bold">Phone Number <span class="required">*</span></label>
                    <input type="tel" name="phone" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?= htmlspecialchars($phone) ?>" required>
                    <i class="fas fa-phone-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                </div>

                <div class="mb-3 position-relative">
                    <label class="text-black small fw-bold">Address 1 <span class="required">*</span></label>
                    <input type="text" name="address1" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?= htmlspecialchars($address1) ?>" required>
                    <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                </div>

                <div class="mb-3 position-relative">
                    <label class="text-black small fw-bold">Address 2</label>
                    <input type="text" name="address2" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?= htmlspecialchars($address2) ?>">
                    <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                </div>

                <div class="mb-3 position-relative">
                    <label class="text-black small fw-bold">Gender <span class="required">*</span></label>
                    <select name="gender" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;">
                        <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                    <i class="fas fa-venus-mars text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary rounded-4 px-4 py-2 shadow">Update</button>
            </form>
        </div>

        <div class="col-md-5 offset-md-1 p1">
                    <h4 class="text-black d-flex align-items-center border-bottom" style="color: black;"> <span class="badge  me-2">2</span> Change Password</h4>
                    <form>
                        <div class="mb-3">
                            <label class="text-black small fw-bold">Old Password <span class="required">*</span></label>
                            <input type="password" class="form-control rounded-4 shadow-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="text-black small fw-bold">New Password <span class="required">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control rounded-4 shadow-sm" required>
                                <span class="input-group-text toggle-password bg-transparent "><i class="fas fa-eye"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-black small fw-bold">Confirm New Password <span class="required">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control rounded-4 shadow-sm" required>
                                <span class="input-group-text toggle-password bg-transparent"><i class="fas fa-eye-slash"></i></span>
                            </div>
                        </div>
                        <button type="submit" class="btn  w-100 rounded-pill shadow-sm" >
                            <i class="fas fa-save text-custom"></i> <span class="text-custom text-decoration-underline text-left"> Save Changes </span>
                        </button>
                        
                    </form>
                </div>

                <div class="profile-image text-center mt-3">
                    <img src="../../images/pic5.png" alt="Profile Image" class="img-fluid " >
                 </div>
                </div>
                <img src="../../images/pic0.png" alt="Background Image"
                 class="img-fluid position-absolute w-50 m1"
                 style="bottom: 0; z-index: 10; padding-right: 50px; margin-top: -250px; margin-left:540px; transform: translateY(170px);">

            </div>
   

           <!-- <img src="picture0.png" alt="Background Image"
             class="img-fluid position-relative w-50"
             style="bottom: 0; z-index: 20; padding-right: 50px; margin-top: -300px; margin-left:650px">-->
<script>

         document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".dropdown-btn").forEach(button => {
                 button.addEventListener("click", function() {
                    let dropdown = this.nextElementSibling;
                    let isOpen = dropdown.style.display === "block";
                    document.querySelectorAll(".dropdown-content").forEach(content => content.style.display = "none");
                    dropdown.style.display = isOpen ? "none" : "block";
                });
             });
          });

         </script>
            </div>
        </div>
        </div>
    </div>
</main>

<?php include '../footer.php'; ?>

</body>
</html>