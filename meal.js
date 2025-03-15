document.querySelectorAll('.toggle-password').forEach(item => {
    item.addEventListener('click', function () {
        let input = this.previousElementSibling;
        if (input.type === "password") {
            input.type = "text";
            this.innerHTML = '<i class="fas fa-eye-slash"></i>';
        } else {
            input.type = "password";
            this.innerHTML = '<i class="fas fa-eye"></i>';
        }
    });
});


