<?php
require_once __DIR__ . '/../../DB_connection.php';
$user_id = $_SESSION['user_id'] ?? null; // Assuming you store user_id in session

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
  <body>
      <?php include '..\global\navbar\navbar.php'; ?>

    <div class="container">
      <main class="main-content">
        <div class="support-header">
          <h1 class="support-title">How can we help you today?</h1>
        </div>
        <section id="faq" class="support-section">
          <div class="support-card faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-list">

             

              <div class="faq-item">
                <div class="faq-question">Can I schedule an order for later?</div>
                <div class="faq-answer">Yes! You can schedule daily or weekly deliveries in advance. Just select your preferred date and time during checkout. You can manage or cancel scheduled orders from your profile.</div>
              </div>

              <div class="faq-item">
  <div class="faq-question">How do I set up a weekly meal plan?</div>
  <div class="faq-answer">
    Simply browse and add meals to your cart as usual. Once you're ready, go to your cart and choose <strong>"Schedule Order"</strong> instead of "Check Out Now". From there, you can set preferred delivery dates and times for each meal. This lets you build your weekly plan directly from the cart without needing a separate section.
  </div>
</div>


              <div class="faq-item">
                <div class="faq-question">Do you offer catering for special events?</div>
                <div class="faq-answer">Yes, we support catering for parties, corporate events, and gatherings. Visit the “Catering” section to submit your event details, menu preferences, and guest count. Our chefs will get in touch with you directly.</div>
              </div>


              <div class="faq-item">
                <div class="faq-question">Do you offer vegetarian, vegan, or gluten-free options?</div>
                <div class="faq-answer">Yes! You can filter meals based on dietary needs or customize your meal to match your preferences. All options are clearly labeled by each food provider.</div>
              </div>

              <div class="faq-item">
                <div class="faq-question">How do I track my order?</div>
                <div class="faq-answer">After placing an order, you'll receive a tracking link via SMS or email. You can also view real-time updates from your profile under “My Orders.”</div>
              </div>

        
              <div class="faq-item">
  <div class="faq-question">What's the difference between "Check Out Now" and "Schedule Order"?</div>
  <div class="faq-answer">
    "Check Out Now" means your meal will be prepared and delivered as soon as possible. "Schedule Order" allows you to pick a specific date and time for your delivery—perfect for meal planning ahead or busy days.
  </div>
</div>


              <div class="faq-item">
                <div class="faq-question">Who prepares the food?</div>
                <div class="faq-answer">All meals are prepared by certified cloud kitchens and vetted home chefs. Each provider follows hygiene and safety guidelines to ensure quality and freshness.</div>
              </div>

              <div class="faq-item">
                <div class="faq-question">How do I contact customer support?</div>
                <div class="faq-answer">Use the Help section in the app or contact us through the channels listed below. We’re available 24/7.</div>
              </div>

            </div>

            <div class="support-card quick-contact">
              <h3>24/7 Support Channels</h3>
              <div class="contact-info">
                <div class="contact-item">
                  <span class="label">
                    <i class="fas fa-phone"></i> Emergency Line
                  </span>
                  <a href="tel:+15551234567" class="contact-link">+1 (555) 123-4567</a>
                </div>
                <div class="contact-item">
                  <span class="label">
                    <i class="fas fa-envelope"></i> Support Email
                  </span>
                  <a href="mailto:Todays.Meal.support@cloudkitchen.com" class="contact-link">Today's Meal support@gmail.com</a>
                </div>
                <div class="contact-item">
                  <span class="label">
                    <i class="fas fa-stopwatch"></i> Response Time
                  </span>
                  <span>Under 30 minutes</span>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
    </div>
    <script src="script.js"></script>  
              <?php include '..\global\footer\footer.php'; ?>
  </body>
</html>
