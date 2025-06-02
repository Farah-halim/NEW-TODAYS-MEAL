<?php
session_start();
require_once("..\DB_connection.php");

if (!isset($_SESSION['user_id'])) {

    header("Location: \NEW-TODAYS-MEAL\Register&Login\login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Initialize search term
$search_term = '';

// Fetch categories based on search
if (isset($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $category_query = "SELECT * FROM category 
                      WHERE c_name LIKE '%$search_term%'";
} else {
    $category_query = "SELECT * FROM category";
}

$category_result = mysqli_query($conn, $category_query);
$categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Meal - Home Food Delivery</title>
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
</head>
<body>

  <!-- Navbar -->
  <?php include '..\global\navbar\navbar.php'; ?>

    <div class="home-page">
      <main>
        <section id="top" class="hero">
          <div class="hero-content">
            <h1>Order Homemade Food</h1>
            <p>Customize your meals, create weekly plans, and enjoy delicious home-cooked food delivered to your doorstep.</p>
            <button class="cta-button" id="exploreMealsBtn">Explore Meals</button>
          </div>
          <div class="hero-image animated"></div>
        </section>

            <section id="search-section" class="search-section">
                <form method="GET">
                    <div class="search-bar">
                        <img src="https://img.icons8.com/material-outlined/24/734338/search--v1.png" alt="search"/>
                        <input type="text" name="search" placeholder="Search for meal categories..." 
                               value="<?php echo htmlspecialchars($search_term) ?>"/>
                    </div>
                </form>
            </section>

            <!-- Featured Categories -->
            <section class="featured-categories">
                <h2>Featured Categories</h2>
                <?php if (empty($categories)): ?>
                    <p class="no-results">No categories found matching your search.</p>
                <?php else: ?>
                    <div class="category-grid">
                        <?php foreach ($categories as $category): ?>
                            <a href="\NEW-TODAYS-MEAL\customer\Show_Caterers\index.php?cat_id=<?php echo $category['cat_id'] ?>">
                                <button class="category-button">
                                    <?php if ($category['category_photo']): ?>
                                        <div class="category-image" style="background-image: url('<?php echo htmlspecialchars($category['category_photo']) ?>')"></div>
                                    <?php else: ?>
                                        <div class="category-image image-default"></div>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($category['c_name']) ?></span>
                                </button>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        <section id="weekly-meal-plans" class="meal-plans">
          <h2>Weekly Meal Plans</h2>
          <p>Plan your week ahead with customized meal plans. Select your favorite dishes and schedule deliveries to fit your routine.</p>
          <div class="plan-cards">
            <div class="plan-card">
              <div class="icon-circle">
                <img src="https://c.animaapp.com/m8ipwas4EnbDUS/img/frame.svg" alt="Schedule icon" />
              </div>
              <h3>Schedule Your Week</h3>
              <p>Choose specific days and times for your meal deliveries throughout the week.</p>
              <ul>
                <li>Select delivery days</li>
                <li>Choose meal times</li>
                <li>Adjust weekly as needed</li>
              </ul>
              <button>Get Started</button>
            </div>
            <div class="plan-card popular">
              <div class="popular-tag">Most Popular</div>
              <div class="icon-circle">
                <img src="https://c.animaapp.com/m8ipwas4EnbDUS/img/frame.svg" alt="Customize icon" />
              </div>
              <h3>Customize Your Plan</h3>
              <p>Personalize your meals based on dietary preferences and nutritional goals.</p>
              <ul>
                <li>Choose from 50+ dishes</li>
                <li>Specify dietary preferences</li>
                <li>Adjust portion sizes</li>
                <li>Mix and match meals</li>
              </ul>
              <button>Select This Plan</button>
            </div>
            <div class="plan-card">
              <div class="icon-circle">
                <img src="https://c.animaapp.com/m8ipwas4EnbDUS/img/frame.svg" alt="Flexible icon" />
              </div>
              <h3>Flexible Subscription</h3>
              <p>Subscribe and save with our flexible meal subscription options.</p>
              <ul>
                <li>Pause anytime</li>
                <li>Change meals weekly</li>
                <li>Skip delivery dates</li>
              </ul>
              <button>Learn More</button>
            </div>
          </div>
        </section>

        <section id="special-event-catering" class="catering">
          <div class="catering-image"></div>
          <div class="catering-content">
            <h2>Birthday Party Catering</h2>
            <p>Make your birthday celebration special! We offer custom birthday catering packages including personalized cakes made exactly as you envision.</p>
            <div class="catering-features">
              <div class="feature">
                <img src="https://img.icons8.com/ios-filled/50/a35831/birthday-cake.png" alt="Custom cake" />
                <h4>Custom Cakes</h4>
                <p>Send us a photo, we'll create it</p>
              </div>
              <div class="feature">
                <img src="https://img.icons8.com/ios-filled/50/a35831/party-baloons.png" alt="Party packages" />
                <h4>Party Packages</h4>
                <p>Complete birthday solutions</p>
              </div>
              <div class="feature">
                <img src="https://img.icons8.com/ios-filled/50/a35831/food-and-wine.png" alt="Menu options" />
                <h4>Party Menu</h4>
                <p>Kid-friendly options available</p>
              </div>
            </div>
            <button>Plan Your Party</button>
          </div>
        </section>
      </main>
    </div>
    <script>
      // Smooth scroll to search section when Explore Meals is clicked
      document.getElementById('exploreMealsBtn').addEventListener('click', function() {
        document.getElementById('search-section').scrollIntoView({
          behavior: 'smooth'
        });
      });

      window.addEventListener('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search')) {
            // Clear the URL without reloading
            window.history.replaceState({}, document.title, window.location.pathname);
            // Clear the search input field
            document.querySelector('input[name="search"]').value = '';
        }
      });

      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
          });
        });
      });

      // Tag navigation
      const tagsContainer = document.querySelector('.tags');
      const leftButton = document.querySelector('.tags-container .nav-button:first-child');
      const rightButton = document.querySelector('.tags-container .nav-button:last-child');
      
      leftButton.addEventListener('click', () => {
        tagsContainer.scrollBy({
          left: -200,
          behavior: 'smooth'
        });
      });
      
      rightButton.addEventListener('click', () => {
        tagsContainer.scrollBy({
          left: 200,
          behavior: 'smooth'
        });
      });
    </script>
          <?php include '..\global\footer\footer.php'; ?>

  </body>
</html>