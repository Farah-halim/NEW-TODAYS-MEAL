document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[name="search"]');
    let typingTimer;
    const doneTypingInterval = 500; 

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                searchInput.form.submit();
            }, doneTypingInterval);
        });
    }
});
        document.addEventListener('DOMContentLoaded', function() {
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
    const resetButtons = document.querySelectorAll('[href*="reset=1"]');
    resetButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.php';
        });
    });
});