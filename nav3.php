<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="meal.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="path/to/font-awesome.css"> <!-- Ensure you have FontAwesome or any relevant icon library -->
    <style>
        .sticky-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1030; /* Ensure it's above other elements */
            display: flex;
            justify-content: space-around;
            align-items: center;
            background-color: #3e7ea6; /* Ensures the background is primary as indicated */
            padding: 10px 0; /* You can adjust this padding as needed */
        }
        .navbar{
            background-color: #3E7EA6;
        }
        
        @media (min-width: 992px) {
            .sticky-footer {
                display: none; /* Hide sticky footer on larger screens */
            }

            .d-lg-flex {
                display: flex !important;
            }
        }
        
        @media (max-width: 991px) {
            .d-lg-flex {
                display: none !important;
            }
            .input-group input {
                font-size: 0.75rem; /* Smaller font size for smaller screens */
                
            }
            .input-group{
                justify-content: center;
            }
            .navbar-brand {
                justify-content: center;
                width: 100%;
            }
            .navbar-brand img {
                
                height: 40px; /* Adjust size for smaller screens */
            }
            .navbar-brand span {
                text-align: center;
                font-size: 1.25rem; /* Adjust size for smaller screens */
            } 
            
                
            
        }
    </style>

</head>
<nav class="navbar navbar-light  shadow-sm px-4">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="logo.png" alt="Logo" height="55" style="margin-right: 10px;">
            <span class="ms-2 text-white fs-5 fw-bold fst-italic">Today's Meal</span>
        </a>
        <div class="input-group me-3 flex-grow-1 my-3 my-lg-0" style="flex: 0 1 100px;">
            <input type="text" class="form-control rounded-pill shadow-sm pe-5" placeholder="Search for Caterers, Categories or Products">
            <span class="input-group-text border-0 bg-transparent position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%);">
                <i class="fas fa-search text-custom"></i>
            </span>
        </div>
        <div class="d-none d-lg-flex align-items-center"> <!-- For Desktop -->
            <i class="fas fa-home text-white mx-3 fa-2x"></i>
            <i class="fas fa-shopping-cart text-white mx-3 fa-2x"></i>
            <i class="fas fa-user text-white mx-3 fa-2x"></i>
        </div>
    </div>
</nav>

<!-- Sticky Footer for Mobile screens -->
<div class="sticky-footer d-lg-none d-flex justify-content-around align-items-center  py-3"> <!--For Mobile Phones-->
    <i class="fas fa-home text-white fa-1.5x"></i>
    <i class="fas fa-shopping-cart text-white fa-1.5x"></i>
    <i class="fas fa-user text-white fa-1.5x"></i>
</div>
