// Cloud Kitchen JavaScript - Simplified version for rating filtering only
document.addEventListener('DOMContentLoaded', function() {
    // Rating filtering (if you want to keep this client-side)
    const ratingBtns = document.querySelectorAll('.rating-btn');
    
    if (ratingBtns) {
        ratingBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                ratingBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
            });
        });
    }

    // Reset button functionality
    const resetButtons = document.querySelectorAll('[href*="reset=1"]');
    resetButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.php';
        });
    });
});