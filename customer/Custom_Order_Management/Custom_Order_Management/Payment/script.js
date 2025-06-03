// JavaScript for Checkout page

document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const cashRadio = document.querySelector('input[value="cash"]');
    const cardRadio = document.querySelector('input[value="card"]');
    const cardDetailsSection = document.getElementById('card-details');
    const placeOrderButton = document.getElementById('place-order-btn');
    
    // Event listeners for payment method selection
    cashRadio.addEventListener('change', function() {
        if (this.checked) {
            cardDetailsSection.classList.add('hidden');
        }
    });
    
    cardRadio.addEventListener('change', function() {
        if (this.checked) {
            cardDetailsSection.classList.remove('hidden');
        }
    });
    
    // Format card number input with spaces
    const cardNumberInput = document.getElementById('card-number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            e.target.value = formattedValue.substring(0, 19);
        });
    }
    
    // Format expiry date input
    const expiryInput = document.getElementById('card-expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            
            if (value.length > 2) {
                value = value.substring(0, 2) + ' / ' + value.substring(2, 4);
            }
            
            e.target.value = value.substring(0, 7);
        });
    }
    
    // Limit CVV to 3-4 digits
    const cvvInput = document.getElementById('card-cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/gi, '');
            e.target.value = value.substring(0, 4);
        });
    }
    
    // Handle order submission
    placeOrderButton.addEventListener('click', function() {
        // Get form values
        const firstName = document.getElementById('first-name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        const paymentMethod = document.querySelector('input[name="payment-method"]:checked')?.value;
        
        // Basic validation
        if (!firstName) {
            alert('Please enter your name');
            return;
        }
        
        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address');
            return;
        }
        
        if (!phone) {
            alert('Please enter your phone number');
            return;
        }
        
        if (!address) {
            alert('Please enter your delivery address');
            return;
        }
        
        if (paymentMethod === 'card') {
            const cardName = document.getElementById('card-name').value.trim();
            const cardNumber = document.getElementById('card-number').value.trim();
            const cardExpiry = document.getElementById('card-expiry').value.trim();
            const cardCvv = document.getElementById('card-cvv').value.trim();
            
            if (!cardName) {
                alert('Please enter the cardholder name');
                return;
            }
            
            if (!cardNumber || cardNumber.replace(/\s/g, '').length < 16) {
                alert('Please enter a valid card number');
                return;
            }
            
            if (!cardExpiry || cardExpiry.length < 7) {
                alert('Please enter a valid expiry date');
                return;
            }
            
            if (!cardCvv || cardCvv.length < 3) {
                alert('Please enter a valid security code');
                return;
            }
        }
        
        // Create order data
        const orderData = {
            firstName,
            email,
            phone,
            address,
            paymentMethod,
            orderDate: new Date().toISOString(),
            status: 'placed'
        };
        
        console.log('Order placed:', orderData);
        
        // Show success message
        alert('Thank you for your order! Your delicious meals will be delivered soon.');
        
        // Reset form
        document.getElementById('first-name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('address').value = '';
        
        if (paymentMethod === 'card') {
            document.getElementById('card-name').value = '';
            document.getElementById('card-number').value = '';
            document.getElementById('card-expiry').value = '';
            document.getElementById('card-cvv').value = '';
        }
        
        // Reset payment method to cash
        cashRadio.checked = true;
        cardDetailsSection.classList.add('hidden');
    });
});