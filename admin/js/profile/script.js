 // Get elements
 const editProfileBtn = document.getElementById("editProfileBtn");
 const preferencesContainer = document.getElementById("preferencesContainer");
 const editProfileFormContainer = document.getElementById("editProfileFormContainer");
 const cancelEditIcon = document.getElementById("cancelEditIcon");
 const submitFormButton = document.getElementById("submitForm");
 const confirmationDialog = document.getElementById("confirmationDialog");
 const closeButton = confirmationDialog.querySelector(".close-button");
 const confirmButton = document.getElementById("confirmSubmit");
 const profileImage = document.querySelector(".profile-image");
 const editImageIcon = document.querySelector(".edit-image-icon");
 const newPictureInput = document.getElementById("newPicture");
 const profileNameElement = document.querySelector(".profile-name");
 const profileEmailElement = document.querySelector(".profile-email");
 const logoutButton = document.getElementById("logoutButton");
 const supportButton = document.getElementById("supportButton");
 const supportFormElement = document.getElementById("supportForm");
 const supportSubmitButton = document.getElementById("supportSubmit"); // Get support submit button
 const pinCodeButton = document.getElementById("pinCodeButton");
 const pinCodeFormElement = document.getElementById("pinCodeForm"); // Get the form element
 const pinCodeSubmitButton = document.getElementById("pinCodeSubmit");

 // Password Elements
 const oldPasswordInput = document.getElementById("oldPassword");
 const newPasswordInput = document.getElementById("newPassword");
 const confirmNewPasswordInput = document.getElementById("confirmNewPassword");

 //Error messages
 const newPasswordError = document.getElementById("newPasswordError");
 const confirmNewPasswordError = document.getElementById("confirmNewPasswordError");

 // Strength
 const passwordStrengthText = document.getElementById("passwordStrengthText");
 const passwordStrengthBar = document.getElementById("passwordStrengthBar");

 // CheckBox
 const showPasswordCheckbox = document.getElementById("showPassword");

 // Function to show the preferences container
 function showPreferences() {
     preferencesContainer.classList.remove("hidden");
     editProfileFormContainer.classList.add("hidden");
     logoutButton.classList.remove("hidden");
     supportButton.classList.remove("hidden");
 }

 // Function to show the edit profile form and hide the preferences
 function showEditProfileForm() {
     preferencesContainer.classList.add("hidden");
     editProfileFormContainer.classList.remove("hidden");
     logoutButton.classList.add("hidden");
     supportButton.classList.add("hidden");
 }

 // Function to open the confirmation dialog
 function openConfirmationDialog() {
     confirmationDialog.style.display = "block";
 }

 // Function to close the confirmation dialog
 function closeConfirmationDialog() {
     confirmationDialog.style.display = "none";
 }

 function showToast(toastId) {
     const toastElement = document.getElementById(toastId);
     const toast = new bootstrap.Toast(toastElement);
     toast.show();
 }

 // Event listeners
 editProfileBtn.addEventListener("click", showEditProfileForm);

 cancelEditIcon.addEventListener("click", showPreferences);

 newPictureInput.addEventListener("change", function (event) {
     const file = event.target.files[0];

     if (!file) {
         profileImage.style.backgroundImage = ""; // Clear the image
         profileImage.style.backgroundColor = "#d4edda"; // Reset placeholder color
         return; // No file selected
     }

     if (file.size > 2 * 1024 * 1024) { // Limit to 2MB
         alert("Image size exceeds 2MB. Please select a smaller image.");
         newPictureInput.value = ""; // Clear the input
         profileImage.style.backgroundImage = ""; // Clear the image
         profileImage.style.backgroundColor = "#d4edda"; // Reset placeholder color
         return;
     }

     if (!file.type.startsWith("image/")) {
         alert("Please select a valid image file.");
         newPictureInput.value = ""; // Clear the input
         profileImage.style.backgroundImage = ""; // Clear the image
         profileImage.style.backgroundColor = "#d4edda"; // Reset placeholder color
         return;
     }

     const reader = new FileReader();

     reader.addEventListener("load", function () {
         profileImage.style.backgroundImage = `url(${reader.result})`;
         profileImage.style.backgroundColor = "transparent";
     });

     reader.readAsDataURL(file);
 });

 editImageIcon.onclick = function () {
     newPictureInput.click();
 };

 submitFormButton.addEventListener("click", function (event) {
     event.preventDefault(); // Prevent form from submitting immediately

     const username = document.getElementById("editUsername").value.trim();
     const email = document.getElementById("email").value.trim();
     const branch = document.getElementById("branch").value.trim();
     // Basic validation examples
     if (username === "") {
         alert("Username cannot be empty.");
         return;
     }

     if (!isValidEmail(email)) {
         alert("Please enter a valid email address.");
         return;
     }

     // Example email validation function (basic)
     function isValidEmail(email) {
         const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
         return emailRegex.test(email);
     }

     openConfirmationDialog();
 });

 closeButton.addEventListener("click", closeConfirmationDialog);

 confirmButton.addEventListener("click", function (event) {
     event.preventDefault();

     const formData = new FormData(document.getElementById("editProfileForm"));

     fetch('/update-profile', {
         method: 'POST',
         body: formData,
     })
         .then(response => {
             if (!response.ok) {
                 // Log the error for debugging
                 console.error(`Server error: ${response.status} ${response.statusText}`);

                 // Try to parse the JSON error message from the server (if it sends one)
                 return response.json().then(errorData => {
                     // Throw a custom error with the server's message
                     throw new Error(`Server error: ${response.status} ${errorData.message || 'Unknown error'}`);
                 });
             }
             return response.json();
         })
         .then(data => {
             console.log('Success:', data);
             showToast('successToast'); // Show success toast
             closeConfirmationDialog();
             showPreferences();
             profileNameElement.textContent = formData.get("username");
             profileEmailElement.textContent = formData.get("email");
         })
         .catch(error => {
             console.error('Error:', error);
             showToast('errorToast'); // Show error toast
             closeConfirmationDialog();
         });
 });

 logoutButton.addEventListener("click", function () {
     showToast('logoutToast');
     // Replace this with your actual logout logic
     alert("Logged out!");
 });
 if (supportSubmitButton) {
     supportSubmitButton.addEventListener("click", function (event) {
         event.preventDefault(); // Prevent form from submitting immediately

         const formData = new FormData(document.getElementById("supportFormSubmit"));

         // Simulate sending support message (replace with your actual logic)
         showToast('supportToast');

         // Optionally clear the message field after submission
         document.getElementById('message').value = '';
     });
 }

 // Pin code form submission
 if (pinCodeSubmitButton) {
     pinCodeSubmitButton.addEventListener("click", function (event) {
         event.preventDefault();

         const oldPassword = oldPasswordInput.value;
         const newPassword = newPasswordInput.value;
         const confirmNewPassword = confirmNewPasswordInput.value;

         // Validate new password
         if (checkPasswordStrength(newPassword) < 4) {
             newPasswordError.textContent = "Password must be at least 8 characters long and contain a mix of uppercase, lowercase, numbers, and symbols.";
             return;
         } else {
             newPasswordError.textContent = "";
         }

         // Validate confirm new password
         if (newPassword !== confirmNewPassword) {
             confirmNewPasswordError.textContent = "Passwords do not match.";
             return;
         } else {
             confirmNewPasswordError.textContent = "";
         }

         // Simulate API call
         setTimeout(function () {
             showToast('pinCodeChangeToast'); //Show success toast
             new bootstrap.Collapse(pinCodeFormElement).hide(); // Use supportFormElement here
             logoutButton.classList.remove("hidden");
             supportButton.classList.remove("hidden");

         }, 500);
     });
 }
 supportButton.addEventListener("click", function () {
     logoutButton.classList.add("hidden");
     //setTimeout(() => {  logoutButton.classList.add("hidden"); }, 100);
 });

 supportFormElement.addEventListener('show.bs.collapse', function () {
     logoutButton.classList.add('hidden');
 });

 supportFormElement.addEventListener('hidden.bs.collapse', function () {
     logoutButton.classList.remove('hidden');
 });

 // Pin Code functionality
 pinCodeButton.addEventListener("click", function () {
     logoutButton.classList.add("hidden");
     supportButton.classList.add("hidden");
 });

 pinCodeFormElement.addEventListener('show.bs.collapse', function () {
     logoutButton.classList.add('hidden');
     supportButton.classList.add("hidden");

 });

 pinCodeFormElement.addEventListener('hidden.bs.collapse', function () {
     logoutButton.classList.remove('hidden');
     supportButton.classList.remove("hidden");

 });

 showPasswordCheckbox.addEventListener("change", togglePasswordVisibility);


 function togglePasswordVisibility() {
     const show = showPasswordCheckbox.checked;
     oldPasswordInput.type = show ? "text" : "password";
     newPasswordInput.type = show ? "text" : "password";
     confirmNewPasswordInput.type = show ? "text" : "password";
 }

 // Password strength check
 newPasswordInput.addEventListener("input", function () {
     const password = newPasswordInput.value;
     const strength = checkPasswordStrength(password);
     updatePasswordStrengthIndicator(strength);
 });

 function checkPasswordStrength(password) {
     let strength = 0;
     if (password.length >= 8) {
         strength++;
     }
     if (password.match(/[a-z]+/)) {
         strength++;
     }
     if (password.match(/[A-Z]+/)) {
         strength++;
     }
     if (password.match(/[0-9]+/)) {
         strength++;
     }
     if (password.match(/[^a-zA-Z0-9]+/)) {
         strength++;
     }
     return strength;
 }

 function updatePasswordStrengthIndicator(strength) {
     let strengthText = "";
     let strengthClass = "";
     let strengthWidth = "0%";

     switch (strength) {
         case 0:
         case 1:
             strengthText = "Weak";
             strengthClass = "weak";
             strengthWidth = "20%";
             break;
         case 2:
         case 3:
             strengthText = "Medium";
             strengthClass = "medium";
             strengthWidth = "60%";
             break;
         case 4:
         case 5:
             strengthText = "Strong";
             strengthClass = "strong";
             strengthWidth = "100%";
             break;
     }

     passwordStrengthText.textContent = strengthText;
     passwordStrengthBar.style.width = strengthWidth;
     passwordStrengthBar.className = "strength-bar " + strengthClass;
 }