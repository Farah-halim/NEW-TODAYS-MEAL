
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".account-type button");
    const catererFields = document.getElementById("caterer-fields");
    const deliveryFields = document.getElementById("delivery-fields");
    const form = document.querySelector("form");

    // Function to handle required attributes
    function updateRequiredFields(type) {
        // Remove required from all special fields first
        const catererInputs = catererFields.querySelectorAll('input, select');
        const deliveryInputs = deliveryFields.querySelectorAll('input');
        
        catererInputs.forEach(input => input.required = false);
        deliveryInputs.forEach(input => input.required = false);

        // Add required based on type
        if (type === "caterer") {
            catererInputs.forEach(input => input.required = true);
        } else if (type === "delivery") {
            deliveryInputs.forEach(input => input.required = true);
        }
    }

    // Handle account type selection
    buttons.forEach(button => {
        button.addEventListener("click", function(e) {
            e.preventDefault(); // Prevent form submission
            buttons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");

            const type = this.dataset.type;
            if (type === "caterer") {
                catererFields.style.display = "block";
                deliveryFields.style.display = "none";
            } else if (type === "delivery") {
                deliveryFields.style.display = "block";
                catererFields.style.display = "none";
            } else {
                catererFields.style.display = "none";
                deliveryFields.style.display = "none";
            }
            updateRequiredFields(type);
        });
    });

    // Form submission
    form.addEventListener("submit", function(e) {
        const termsCheckbox = document.getElementById("terms");
        if (!termsCheckbox.checked) {
            e.preventDefault();
            alert("Please agree to the terms and conditions");
            return;
        }

        // Clear caterer and delivery fields when submitting as customer
        const activeType = document.querySelector('.account-type button.active').dataset.type;
        if (activeType === 'customer') {
            document.querySelectorAll('#caterer-fields input, #caterer-fields select, #delivery-fields input').forEach(input => {
                input.value = '';
            });
        }
    });

    // Set initial state
    updateRequiredFields("customer");
});
