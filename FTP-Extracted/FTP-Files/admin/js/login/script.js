(function () {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Toastr for form validation errors
                toastr.error('Please fill in all required fields.', 'Validation Error');
            }

            form.classList.add('was-validated');
        }, false);
    });
})();

$(document).ready(function() {
    // Listen for successful login after the page loads
    if (typeof login_success !== 'undefined' && login_success) {
        toastr.success('Login successful!', 'Success');
    }
});