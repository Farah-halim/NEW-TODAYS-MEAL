<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Today's Meal</title>

  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-xHOa8B2XN1XaV4gKkdEvhT+3vzhyHvKgI0wJqKvOtVjT2Ry2Uos5QxITV0M/8sw8K6MfxnvJj2AfMHV2O6l3EQ=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />

  <style>
    html, body {
      height: 100%;
      margin: 0;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .page-wrapper {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      padding: 20px;
    }

    .footer {
      background-color: #6a4125;
      color: #f5f5f5;
      padding: 40px 20px;
      margin-top: 250px;
    }

    .footer-container {
      max-width: 1200px;
      margin: auto;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 40px;
    }

    .footer-col {
      flex: 1 1 200px;
    }

    .footer h3 {
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 10px;
      text-transform: uppercase;
      color: #ffffff;
    }

    .footer ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .footer ul li {
      margin-bottom: 6px;
    }

    .footer a {
      text-decoration: none;
      color: #e0e0e0;
      font-size: 13px;
    }

    .footer a:hover {
      color: #ffffff;
      text-decoration: underline;
    }

    .social-icons {
      margin: 10px 0;
    }

    .social-icons a {
      display: inline-block;
      margin-right: 10px;
      font-size: 16px;
      color: #f5f5f5;
      transition: color 0.3s ease;
    }

    .social-icons a:hover {
      color: #ffffff;
    }

    .newsletter {
      font-size: 13px;
      margin: 10px 0;
      color: #e8e8e8;
    }

    .subscribe-button {
      background-color: #f5e0c2;
      color: #000;
      padding: 8px 14px;
      border: none;
      font-size: 13px;
      cursor: pointer;
      border-radius: 4px;
    }

    .subscribe-button i {
      margin-right: 6px;
    }

    .footer-bottom {
      text-align: center;
      font-size: 12px;
      margin-top: 20px;
      color: #d4d4d4;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 10px;
    }

    @media (max-width: 768px) {
      .footer-container {
        flex-direction: column;
        align-items: flex-start;
        gap: 30px;
      }
    }
  </style>
</head>
<body>
  <div class="page-wrapper">
    <div class="main-content">
    </div>

    <footer class="footer">
      <div class="footer-container">
        <!-- About -->
        <div class="footer-col">
          <h3>About Today's Meal</h3>
          <ul>
            <li><a href="#">Our Kitchen</a></li>
            <li><a href="#">Fresh Ingredients</a></li>
            <li><a href="#">Delivery Areas</a></li>
            <li><a href="#">Contact Us</a></li>
          </ul>
        </div>

        <!-- Support -->
        <div class="footer-col">
          <h3>Support</h3>
          <ul>
            <li><a href="NEW-TODAYS-MEAL/customer/support/support.php">FAQs</a></li>
            <li><a href="#">Order Tracking</a></li>
            <li><a href="#">Cancel or Change Order</a></li>
            <li><a href="#">Allergen Info</a></li>
          </ul>
        </div>

        <!-- Social / Newsletter -->
        <div class="footer-col">
          <h3>Connect with Us</h3>
          <div class="social-icons">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-x-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
          </div>
          <p class="newsletter">Get exclusive updates and offers!<br>Subscribe to our newsletter.</p>
          <button class="subscribe-button">
            <i class="fas fa-envelope"></i> Subscribe
          </button>
        </div>
      </div>

      <div class="footer-bottom">
        &copy; 2025 Today's Meal Cloud Kitchen. All rights reserved.
      </div>
    </footer>
  </div>
</body>
</html>
