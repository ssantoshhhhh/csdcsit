// Profile picture preview
document.getElementById('profile-upload')?.addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('profile-preview');

    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Initialize date picker
flatpickr("#dob", {
    dateFormat: "Y-m-d",
    maxDate: "today",
    yearRange: [1990, new Date().getFullYear() - 15]
});

// Handle toast notifications
if (toastMessage) {
    Swal.fire({
        icon: toastType === "success" ? "success" : "error",
        title: toastMessage,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Form submission validation
document.getElementById('member-registration-form')?.addEventListener('submit', function(event) {
    event.preventDefault();

    Swal.fire({
        title: 'Confirm Registration',
        text: 'Are you sure you want to register this member?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Register',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            this.submit();
        }
    });
});

$(document).ready(function () {
    // Initialize Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };

    // Check for toast message from PHP
    if (toastMessage !== '') {
        if (toastType === 'success') {
            toastr.success(toastMessage, 'Success!');
        } else if (toastType === 'error') {
            toastr.error(toastMessage, 'Error!');
        }
    }

    $("#registerButton").click(function () {
        toastr.info("Processing registration...", "Info");
    });
});