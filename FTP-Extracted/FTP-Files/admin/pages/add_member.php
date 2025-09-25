<?php
session_start();
include "../utils/connect.php";
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require '../utils/connect.php';
$toastMessage = '';
$toastType = '';
$username = $_SESSION['username'];

// Fetch house name from admin table
$query = "SELECT hid FROM admins WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hid = $row['hid'];
} else {
    $toastMessage = "Admin not found.";
    $toastType = "error";
}

$sql = "SELECT name FROM houses WHERE hid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hid);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $house_name = $row['name']; // Use $house_name instead of $name
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = $_POST['full_name']; // Use a different variable for the user's name
    $registration_number = $_POST['registration_number'];
    $email = $_POST['email'];
    $branch = $_POST['branch'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $dob = $_POST['dob'];
    $description = $_POST['description'];

    // Generate a default password
    $default_password = $registration_number;
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

    // Initialize points to 0
    $points = 0;

    try {
        if (!isset($hid) || empty($hid)) {
            die("Error: House ID (hid) is missing or invalid.");
        }

        // Get class_id based on year, branch, and section
        $class_query = "SELECT class_id FROM classes WHERE year = ? AND branch = ? AND section = ? LIMIT 1";
        $class_stmt = $conn->prepare($class_query);
        $class_stmt->bind_param("sss", $year, $branch, $section);
        $class_stmt->execute();
        $class_result = $class_stmt->get_result();
        $class_row = $class_result->fetch_assoc();
        $class_id = $class_row ? $class_row['class_id'] : 1; // Default to class_id 1 if not found

        // Prepare the SQL statement
        $sql = "INSERT INTO students (name, student_id, email, hid, class_id, password, dob)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssisss", $full_name, $registration_number, $email, $hid, $class_id, $hashed_password, $dob);

        if ($stmt->execute()) {
            $toastMessage = "Registration Successful!";
            $toastType = "success";
        } else {
            $toastMessage = "Registration Failed!";
            $toastType = "error";
        }

        $stmt->close();
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $toastMessage = "Registration number or email already exists!";
            $toastType = "error";
        } else {
            $toastMessage = "An unexpected error occurred. Please try again.";
            $toastType = "error";
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏠 House Member Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../css/add_member/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

</head>

<body>
    <?php include '../utils/sidenavbar.php'; ?>

    <div class="container">
        <div class="member-container">
            <h2 class="text-center mb-4">
                <i class="bi bi-person-plus text-primary"></i>
                New House Member Registration 🏠
            </h2>

            <form id="member-registration-form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>"
                enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Full Name 👤
                            <i class="bi bi-info-circle tooltip-info"
                                title="Enter the student's full legal name"></i>
                        </label>
                        <input type="text" class="form-control" name="full_name" required placeholder="John Doe">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Registration Number 🔢
                            <i class="bi bi-info-circle tooltip-info"
                                title="Unique student registration number"></i>
                        </label>
                        <input type="text" class="form-control" name="registration_number" required
                            placeholder="XXXX-XXXX-XXXX">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Branch 🎓
                            <i class="bi bi-info-circle tooltip-info" title="Select student's academic branch"></i>
                        </label>
                        <select class="form-select" name="branch" required>
                            <option value="">Select Branch</option>
                            <option value="CSD">CSD</option>
                            <option value="CSIT">CSIT</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Year 📅
                            <i class="bi bi-info-circle tooltip-info" title="Current academic year of the student"></i>
                        </label>
                        <select class="form-select" name="year" required>
                            <option value="">Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Section 📚
                            <i class="bi bi-info-circle tooltip-info" title="Student's section"></i>
                        </label>
                        <select class="form-select" name="section" required>
                            <option value="">Select Section</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Date of Birth 🎂
                            <i class="bi bi-info-circle tooltip-info" title="Student's date of birth"></i>
                        </label>
                        <input type="text" class="form-control" id="dob" name="dob" placeholder="Select Date" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Email Address 📧
                            <i class="bi bi-info-circle tooltip-info" title="Official student email address"></i>
                        </label>
                        <input type="email" class="form-control" name="email" required
                            placeholder="student@example.com">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            House Name 🏠
                            <i class="bi bi-info-circle tooltip-info" title="House assigned to the student"></i>
                        </label>
                        <input type="text" class="form-control" name="house_name"
                            value="<?php echo htmlspecialchars($house_name); ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Additional Description 📝
                        <i class="bi bi-info-circle tooltip-info"
                            title="Any additional notes or achievements"></i>
                    </label>
                    <textarea class="form-control" name="description" rows="3"
                        placeholder="Notable achievements, interests, or special skills"></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg" id="registerButton">
                        <i class="bi bi-person-plus-fill"></i> Register Member
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-container"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Pass PHP variables to JavaScript
        const toastMessage = "<?php echo $toastMessage; ?>";
        const toastType = "<?php echo $toastType; ?>";
    </script>
    <script src="../js/add_member/script.js"></script>

</body>

</html>