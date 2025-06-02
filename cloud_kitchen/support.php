<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="front_end/support/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
  <body>
  <?php include 'global/navbar.php'; ?>

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
                  <div class="faq-question">
                    How do I update my kitchen's availability?
                  </div>
                  <div class="faq-answer">Access the Dashboard and click on 'Kitchen Settings' to update your operating hours and delivery radius. Changes take effect immediately.</div>
                </div>
                <div class="faq-item">
                  <div class="faq-question">
                    What happens if I run out of ingredients?
                  </div>
                  <div class="faq-answer">Update your inventory immediately in the Inventory section. This will automatically pause orders for affected menu items until restocked.</div>
                </div>
                <div class="faq-item">
                  <div class="faq-question">
                    How do I handle special dietary requests?
                  </div>
                  <div class="faq-answer">Review special instructions in the order details. If you cannot accommodate, contact support immediately to reassign the order.</div>
                </div>
                <div class="faq-item">
                  <div class="faq-question">
                    What's the process for order fulfillment?
                  </div>
                  <div class="faq-answer">Accept orders within 5 minutes, prepare according to timeline, and mark ready for pickup. Delivery partners will be automatically notified.</div>
                </div>
                <div class="faq-item">
                  <div class="faq-question">
                    How are cooking time delays handled?
                  </div>
                  <div class="faq-answer">If experiencing delays, update the order status with new estimated completion time. This automatically notifies customers and delivery partners.</div>
                </div>
                <div class="faq-item">
                  <div class="faq-question">
                    What quality standards must I maintain?
                  </div>
                  <div class="faq-answer">Follow food safety guidelines, maintain cleanliness ratings above 4.5/5, and ensure consistent portion sizes. Regular inspections will be conducted.</div>
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
        </div>
      </main>
    </div>
    <script src="front_end/support/script.js"></script>  
  </body>
</html>