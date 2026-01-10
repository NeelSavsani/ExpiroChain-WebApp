document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector("form");

    if (!form) return;

    form.addEventListener("submit", (e) => {

        const email = document.querySelector("input[name='email_id']");
        const password = document.querySelector("input[name='user_pass']");

        if (!email.value.trim() || !password.value.trim()) {
            e.preventDefault();
            alert("Email and password are required");
            return;
        }

    });

});
