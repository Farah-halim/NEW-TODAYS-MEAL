document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".account-type button");
    const catererFields = document.getElementById("caterer-fields");
    const deliveryFields = document.getElementById("delivery-fields");

    buttons.forEach(button => {
        button.addEventListener("click", function () {
            buttons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");

            if (this.dataset.type === "caterer") {
                catererFields.style.display = "block";
                deliveryFields.style.display = "none";
            } else if (this.dataset.type === "delivery") {
                deliveryFields.style.display = "block";
                catererFields.style.display = "none";
            } else {
                catererFields.style.display = "none";
                deliveryFields.style.display = "none";
            }
        });
    });
});