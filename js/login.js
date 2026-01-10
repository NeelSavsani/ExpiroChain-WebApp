document.addEventListener("DOMContentLoaded", () => {

    /* ---------- FORM VALIDATION ---------- */
    const form = document.querySelector("form");

    if (form) {
        form.addEventListener("submit", (e) => {
            const email = document.querySelector("input[name='email_id']");
            const password = document.querySelector("input[name='user_pass']");

            if (!email.value.trim() || !password.value.trim()) {
                e.preventDefault();
                alert("Email and password are required");
            }
        });
    }

    /* ---------- PASSWORD EYE TOGGLE ---------- */
    document.querySelectorAll(".toggle-eye").forEach(eye => {
        eye.addEventListener("click", () => {
            const input = eye.previousElementSibling;

            if (input.type === "password") {
                input.type = "text";
                eye.textContent = "🙈";
            } else {
                input.type = "password";
                eye.textContent = "👁";
            }
        });
    });

});
