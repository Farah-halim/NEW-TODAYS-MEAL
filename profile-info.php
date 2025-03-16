<?php
session_start();
require 'DB_connection.php'; // Database connection

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "You must log in first."; // Store message in session
    header("Location: backend/login.php");
    exit(); // Stop further execution
}

// Fetch user details from `users` table
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ensure `$user` is always set
$user = $user ?? [];

// ✅ Store the current email
$current_email = $user['email'] ?? '';

// Assign existing values
$first_name = $user['first_name'] ?? '';
$last_name = $user['last_name'] ?? '';
$username = $user['username'] ?? '';
$phone = $user['phone'] ?? '';
$address1 = $user['address1'] ?? '';
$address2 = $user['address2'] ?? '';
$city = $user['city'] ?? '';
$gender = $user['gender'] ?? '';
$birthdate = $user['birthdate'] ?? '';

// Handle form submission (Update Profile)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $submitted_email = trim($_POST['email'] ?? '');
    $username = $_POST['username'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address1 = $_POST['address1'] ?? '';
    $address2 = $_POST['address2'] ?? '';
    $role = $_POST['role'] ?? 'customer';

    // ✅ **Only check for duplicate emails if the user changed their email**
    if ($submitted_email !== $current_email) {  
        // ✅ **Modify query to exclude the logged-in user's own email**
        $emailCheckStmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE email = ? AND user_id != ?");
        $emailCheckStmt->bind_param("si", $submitted_email, $user_id);
        $emailCheckStmt->execute();
        $emailCheckResult = $emailCheckStmt->get_result();
        $emailData = $emailCheckResult->fetch_assoc();

        if ($emailData['count'] > 0) {
            die("Error: This email is already in use by another account.");
        }
        $emailCheckStmt->close();
    } else {
        // ✅ If the email is unchanged, use the existing email
        $submitted_email = $current_email;
    }




  


    // Update `users` table
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=?, phone=?, address1=?, address2=?, role=? WHERE user_id=?");
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $submitted_email, $username, $phone, $address1, $address2, $role, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile-info.php';</script>";
    } else {
        echo "<script>alert('Error updating profile: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="meal.css">
    <script defer src="meal.js"></script>
</head>
<body>
    <!--<nav class="navbar navbar-light bg-white shadow-sm px-4 d-flex justify-content-between align-items-center">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="logo.png" alt="Logo" height="50">
            <span class="ms-3 text-black fs-3 fw-bold" style="color: #3e7ea6;">Today's Meal</span>
        </a>
        <div>
            <i class="fas fa-home text-warning mx-3 fa-3x"></i>
            <i class="fas fa-shopping-cart text-warning mx-3 fa-3x"></i>
            <i class="fas fa-user text-warning mx-3 fa-3x"></i>
        </div>
    </nav>-->

 <?php include 'nav3.php'; ?>
 <?php include 'sidebar-profile.php'; ?>
    

        <main class="container my-4 ms-3  " >
        
            <div class="row ">
                <div class="col-md-5 e1">
                    <h4 class="text-black d-flex align-items-center border-bottom" style="color: black;"> <span class="badge  me-2">1</span> Edit Profile <i class="fas fa-edit ms-2" ></i></h4>
                    <form action="profile-info.php" method="POST">
                        
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">First Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Last Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;"  value="<?php echo htmlspecialchars($user['last_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative" >
                            <label class="text-black small fw-bold">E-mail <span class="required">*</span></label>
                            <input type="email" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['email']); ?>"required>
                            <i class="fas fa-envelope text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Username <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <i class="fas fa-user-circle text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Phone Number <span class="required">*</span></label>
                            <input type="tel" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            <i class="fas fa-phone-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 1 <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['address1'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                            
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 2 </label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['address2'], ENT_QUOTES, 'UTF-8'); ?>" >
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">City </label><span class="required">*</span></label>
                            <select id="city" name="city" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="Cairo" <?php echo ($user['city'] ?? '') === 'Cairo' ? 'selected' : ''; ?>>Cairo</option>
                            <option value="Alexandria"  <?php echo ($user['city'] ?? '') === 'Alexandria' ? 'selected' : ''; ?>>Alexandria</option>
                            <option value="Giza"  <?php echo ($user['city'] ?? '') === 'Giza' ? 'selected' : ''; ?>>Giza</option>
                            </select>
                            <i class="fas fa-city text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                             
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Gender</label><span class="required">*</span></label>
                            <select id="gender" name="gender" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="male"<?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?> >Male</option>
                            <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                            <i class="fas fa-venus-mars text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Birh Date</label><span class="required">*</span></label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>" onchange="calculateAge()" style="padding-left: 40px; " >
                            <i class="fas fa-birthday-cake text-customt position-absolute" style="top: 55%; left: 12px; transform: translateY(-50%);"></i>
                            <p id="ageDisplay">Age: </p>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-4 px-4 py-2 shadow" >Update</button>
                        
                    
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
                  <!--dropdown forms <992px-->
                <div class="profile-dropdown d-block d-md-none">
                   <button class="dropdown-btn">Edit Profile</button>
                   <div class="dropdown-content">
                      <form action="profile-info.php" method="POST">
                        
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">First Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Last Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;"  value="<?php echo htmlspecialchars($user['last_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative" >
                            <label class="text-black small fw-bold">E-mail <span class="required">*</span></label>
                            <input type="email" class="form-control rounded-4 shadow-sm inputs" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['email']); ?>"required>
                            <i class="fas fa-envelope text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Username <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <i class="fas fa-user-circle text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Phone Number <span class="required">*</span></label>
                            <input type="tel" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            <i class="fas fa-phone-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 1 <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['adress1'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                            
                         </div>

                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 2 </label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['address2'] ?? ''); ?>">
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">City </label><span class="required">*</span></label>
                            <select id="city" name="city" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="Cairo" <?php echo ($user['city'] ?? '') === 'Cairo' ? 'selected' : ''; ?>>Cairo</option>
                            <option value="Alexandria"  <?php echo ($user['city'] ?? '') === 'Alexandria' ? 'selected' : ''; ?>>Alexandria</option>
                            <option value="Giza"  <?php echo ($user['city'] ?? '') === 'Giza' ? 'selected' : ''; ?>>Giza</option>
                            </select>
                            <i class="fas fa-city text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                             
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Gender</label><span class="required">*</span></label>
                            <select id="gender" name="gender" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="male"<?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?> >Male</option>
                            <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                            </select>
                            <i class="fas fa-venus-mars text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Birh Date</label><span class="required">*</span></label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>" onchange="calculateAge()" style="padding-left: 40px; " >
                            <i class="fas fa-birthday-cake text-customt position-absolute" style="top: 55%; left: 12px; transform: translateY(-50%);"></i>
                            <p id="ageDisplay">Age: </p>
                        </div>



                         <button class=" btnu btn-primary btn rounded-4 px-4 py-2 shadow " >Update</button>
                        
                    
                        </form>
                  </div>

                  <button class="dropdown-btn">Change Password</button>
                 <div class="dropdown-content">
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
                    <img src="pic5.png" alt="Profile Image" class="img-fluid " >
                 </div>
                </div>
                <img src="pic0.png" alt="Background Image"
                 class="img-fluid position-absolute w-50 m1"
                 style="bottom: 0; z-index: 20; padding-right: 50px; margin-top: -250px; margin-left:550px; transform: translateY(75px);">

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

          function calculateAge() {
            let birthDate = document.getElementById('birthdate').value;
            if (birthDate) {
                let today = new Date();
                let birthDateObj = new Date(birthDate);
                let age = today.getFullYear() - birthDateObj.getFullYear();
                let monthDiff = today.getMonth() - birthDateObj.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDateObj.getDate())) {
                    age--;
                }
                document.getElementById('ageDisplay').innerText = 'Age: ' + age;
            }
        }

         </script>

        </main>
        
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
