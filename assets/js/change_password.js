document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // Password validation
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    const requirements = {
        length: /.{8,}/,
        uppercase: /[A-Z]/,
        lowercase: /[a-z]/,
        number: /[0-9]/,
        special: /[^A-Za-z0-9]/
    };

    function validatePassword() {
        const password = newPasswordInput.value;
        
        for (let req in requirements) {
            const element = document.getElementById(req);
            if (requirements[req].test(password)) {
                element.classList.add('valid');
            } else {
                element.classList.remove('valid');
            }
        }
    }

    newPasswordInput.addEventListener('input', validatePassword);

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        let isValid = true;
        for (let req in requirements) {
            if (!requirements[req].test(newPassword)) {
                isValid = false;
                break;
            }
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please meet all password requirements');
            return;
        }

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
            return;
        }
    });
});