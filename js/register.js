/* ================= FILE UPLOAD HANDLING ================= */

document.querySelectorAll(".upload-box input[type='file']").forEach(input => {
    input.addEventListener("change", function () {
        const file = this.files[0];
        const box = this.closest(".upload-box");

        if (!file) return;

        // Allow only images
        if (!file.type.startsWith("image/")) {
            alert("Only image files (PNG, JPG, JPEG) are allowed.");
            this.value = "";
            return;
        }

        // Mark as uploaded
        box.classList.remove("error");
        box.classList.add("uploaded");

        const icon = box.querySelector(".upload-icon");
        if (icon) icon.textContent = "✔";
    });
});


/* ================= PASSWORD EYE TOGGLE ================= */

document.querySelectorAll(".toggle-eye").forEach(eye => {
    eye.addEventListener("click", () => {
        const input = eye.previousElementSibling;
        input.type = input.type === "password" ? "text" : "password";
        eye.textContent = input.type === "password" ? "👁" : "🙈";
    });
});


/* ================= FORM VALIDATION & SUBMIT ================= */

const form = document.querySelector("form");
const submitBtn = document.querySelector(".register-btn");

submitBtn.addEventListener("click", function (e) {
    e.preventDefault(); // stop default first

    let isValid = true;

    /* Validate input fields */
    document.querySelectorAll(".field").forEach(field => {
        const input = field.querySelector("input, select, textarea");
        if (!input) return;

        field.classList.remove("error");

        // Dropdown validation
        if (input.tagName === "SELECT" && input.selectedIndex === 0) {
            field.classList.add("error");
            isValid = false;
            return;
        }

        // Empty check
        if (input.value.trim() === "") {
            field.classList.add("error");
            isValid = false;
        }
    });

    /* Validate file uploads */
    document.querySelectorAll(".upload-box").forEach(box => {
        const fileInput = box.querySelector("input[type='file']");
        box.classList.remove("error");

        if (!fileInput || fileInput.files.length === 0) {
            box.classList.add("error");
            isValid = false;
        }
    });

    /* Password match check */
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");

    if (password && confirmPassword && password.value !== confirmPassword.value) {
        password.closest(".field").classList.add("error");
        confirmPassword.closest(".field").classList.add("error");
        alert("Passwords do not match");
        isValid = false;
    }

    /* FINAL SUBMIT */
    if (isValid) {
        form.submit(); // ✅ THIS allows PHP to run
    }
});
