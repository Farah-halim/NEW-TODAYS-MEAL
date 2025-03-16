document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.querySelector("form");

    loginForm.addEventListener("submit", function (event) {
        event.preventDefault(); 

        const email = document.querySelector("input[type='email']").value;
        const password = document.querySelector("input[type='password']").value;

        if (email === "test@example.com" && password === "123456") {
            alert("Login successful!");
            window.location.href = "index.html";
        } else {
            alert("Invalid email or password. Try again.");
        }
    });
});