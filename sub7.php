<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        if (typeof jQuery === "undefined") {
            document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"><\/script>');
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="meal.css">
    <style>
        body {
            
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }
        .subscription-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
           
        }
      
       

        .subscription-container:hover {
            transform: translateY(-10px);
        }

        

        .btn-warning {
            background-color: #42abed;
            border: none;
            color: white;
            padding: 10px 20px;
            width: 100%;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
        }
        .payment-container {
            display: none;
            margin-top: 20px;
            padding: 15px;
            border: 2px solid #3e7ea6;
            border-radius: 8px;
            background: #ffffff;
            z-index: 20;
            position: relative;
        }
        .credit-card {
            background: #3e7ea6;
            padding: 15px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        .background-image {
            z-index: 10;
            max-width: 100%;
            height: auto;
            position: relative;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        .form-control {
            padding-left: 40px;
            border-radius: 5px;
        }
        .input-group-text {
            cursor: pointer;
        }
        .section-title {
            font-size: 1.5rem;
            color: #333;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .btn-primary {
           
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
    </style>
</head>
<body>
    <?php include 'nav3.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar-profile.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2 class="section-title">Free Delivery Subscription</h2>
                </div>
                <div class="subscription-container">
                    <p>Subscribe now and enjoy free deliveries on all your orders.</p>
                    <button class="btn btn-primary w-50 text-a rounded shadow mb-3" id="subscribeBtn">Subscribe Now</button>
                    <div class="payment-container" id="paymentContainer">
                        <div class="credit-card">
                            <h4>Enter Your Payment Details</h4>
                        </div>
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Card Number</label>
                                <input type="text" class="form-control" placeholder="1234 5678 9101 1121" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="form-control" placeholder="123" required>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100 rounded-pill shadow-sm">Confirm Payment</button>
                        </form>
                    </div>
                    <img src="deliver.png" alt="Background Image" class="img-fluid background-image" style="z-index: 10; margin-top: 20px;">
                </div>
            </main>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        $(document).ready(function() {
            $('#subscribeBtn').click(function() {
                $('#paymentContainer').slideToggle();
            });
            $('#darkModeToggle').click(function() {
                $('body').toggleClass('dark-mode');
            });
        });
    </script>
</body>
</html>