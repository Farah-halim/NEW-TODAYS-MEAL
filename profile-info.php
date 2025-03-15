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
                    <form action="update-profile.php" method="POST">
                        
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">First Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Last Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;"  value="<?php echo htmlspecialchars($user['first_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative" >
                            <label class="text-black small fw-bold">E-mail <span class="required">*</span></label>
                            <input type="email" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>"required>
                            <i class="fas fa-envelope text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Username <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            <i class="fas fa-user-circle text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Phone Number <span class="required">*</span></label>
                            <input type="tel" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            <i class="fas fa-phone-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 1 <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                            
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 2 </label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;">
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">City </label><span class="required">*</span></label>
                            <select id="city" name="city" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="Cairo">Cairo</option>
                            <option value="Alexandria">Alexandria</option>
                            <option value="Giza">Giza</option>
                            </select>
                            <i class="fas fa-city text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                             
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Gender</label><span class="required">*</span></label>
                            <select id="gender" name="gender" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            </select>
                            <i class="fas fa-venus-mars text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Birh Date</label><span class="required">*</span></label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control" onchange="calculateAge()" style="padding-left: 40px; " >
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
                      <form action="update-profile.php" method="POST">
                        
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">First Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Last Name <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;"  value="<?php echo htmlspecialchars($user['first_name']); ?>"  required>
                            <i class="fas fa-user text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative" >
                            <label class="text-black small fw-bold">E-mail <span class="required">*</span></label>
                            <input type="email" class="form-control rounded-4 shadow-sm inputs" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>"required>
                            <i class="fas fa-envelope text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Username <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            <i class="fas fa-user-circle text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Phone Number <span class="required">*</span></label>
                            <input type="tel" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            <i class="fas fa-phone-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 1 <span class="required">*</span></label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;" value="<?php echo htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                            
                         </div>

                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Address 2 </label>
                            <input type="text" class="form-control rounded-4 shadow-sm" style="padding-left: 40px;">
                            <i class="fas fa-map-marker-alt text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                         </div>
                         
                         <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">City </label><span class="required">*</span></label>
                            <select id="city" name="city" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="Cairo">Cairo</option>
                            <option value="Alexandria">Alexandria</option>
                            <option value="Giza">Giza</option>
                            </select>
                            <i class="fas fa-city text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>
                             
                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Gender</label><span class="required">*</span></label>
                            <select id="gender" name="gender" class="form-control rounded-4 shadow-sm" style="padding-left: 40px; ">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            </select>
                            <i class="fas fa-venus-mars text-customt position-absolute" style="top: 65%; left: 12px; transform: translateY(-50%);"></i>
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="text-black small fw-bold">Birh Date</label><span class="required">*</span></label>
                            <input type="date" id="birthdate" name="birthdate" class="form-control" onchange="calculateAge()" style="padding-left: 40px; " >
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
                 style="bottom: 0; z-index: 20; padding-right: 50px; margin-top: -250px; margin-left:550px; transform: translateY(50px);">

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
