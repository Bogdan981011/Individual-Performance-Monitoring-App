document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.querySelector('input[name="date_match"]');
    const submitBtn = document.querySelector('button[type="submit"]');

    const showError = (input, message) => {
        input.style.border = '2px solid red';

        let error = input.parentElement.querySelector('.error-message');
        if (!error) {
            error = document.createElement('div');
            error.classList.add('error-message');
            error.style.fontSize = '13px';
            error.style.fontWeight = 'bold';
            error.style.marginTop = '6px';
            error.style.padding = '6px';
            error.style.borderRadius = '4px';
            input.parentElement.appendChild(error);
        }

        error.textContent = message;
        error.style.color = 'red';
        error.style.border = '1px solid red';
        error.style.backgroundColor = '#ffe5e5';
    };

    const showSuccess = (input, message) => {
        input.style.border = '2px solid green';

        let error = input.parentElement.querySelector('.error-message');
        if (!error) {
            error = document.createElement('div');
            error.classList.add('error-message');
            error.style.fontSize = '13px';
            error.style.fontWeight = 'bold';
            error.style.marginTop = '6px';
            error.style.padding = '6px';
            error.style.borderRadius = '4px';
            input.parentElement.appendChild(error);
        }

        error.textContent = message;
        error.style.color = 'green';
        error.style.border = '1px solid green';
        error.style.backgroundColor = '#e5ffe5';
    };

    const clearError = (input) => {
        const error = input.parentElement.querySelector('.error-message');
        if (error) error.textContent = '';
        input.style.border = '';
    };

    const validateDate = (input) => {
        const value = input.value.trim();
        clearError(input);

        // ✅ Format check
        if (!value.match(/^\d{4}-\d{2}-\d{2}$/)) {
            showError(input, "❌ Format invalide (attendu : AAAA-MM-JJ)");
            submitBtn.disabled = true;
            return false;
        }

        // ✅ Get today's date in YYYY-MM-DD
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const todayStr = `${yyyy}-${mm}-${dd}`;

        // ✅ Compare strings (safe)
        if (value > todayStr) {
            showError(input, "❌ La date ne peut pas être dans le futur.");
            submitBtn.disabled = true;
            return false;
        }

        // ✅ All good
        showSuccess(input, "✅ Date valide.");
        submitBtn.disabled = false;
        return true;
    };

    if (dateInput) {
        ['input', 'blur'].forEach(evt => {
            dateInput.addEventListener(evt, () => {
                validateDate(dateInput);
            });
        });

        // Initial check when page loads
        if (!validateDate(dateInput)) {
            submitBtn.disabled = true;
        }
    }
});
