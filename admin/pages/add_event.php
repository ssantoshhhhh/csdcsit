<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if session expired
    exit();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: login.php?session_expired=true");
    exit();
}

$_SESSION['last_activity'] = time(); // Reset session timer

require '../utils/connect.php';
$toastMessage = '';
$toastType = '';
$username = $_SESSION['username'];

// Fetch aid and hid from admins table
$query = "SELECT admin_id, hid FROM admins WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $aid = $row['admin_id'];
    $house_id = $row['hid'];
} else {
    $toastMessage = "Admin not found.";
    $toastType = "error";
    echo "<script>alert('$toastMessage');</script>";
    exit();
}

// Fetch all students belonging to the same house
$query = "SELECT s.student_id, s.student_id as username, s.name, c.year, c.branch FROM students s JOIN classes c ON s.class_id = c.class_id";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['event_name'];
    $description = $_POST['description'];
    $venue = $_POST['venue'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['timings'];
    $participate_points = $_POST['participate_points'];
    $winner_points = $_POST['winner_points'];
    $organiser_points = $_POST['organiser_points'];

    // Get the selected students from the POST data and decode the JSON data
    if (isset($_POST['selected_students'])) {
        $selected_students_json = $_POST['selected_students'];
        $selected_students = json_decode($selected_students_json, true);
    } else {
        $selected_students = [];  // If no students are selected
    }

    // Handle file upload
    $image_path = null;
    if (isset($_FILES['house_photo']) && $_FILES['house_photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "files/events/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create the directory if it doesn't exist
        }

        $fileName = basename($_FILES['house_photo']['name']);
        $targetFilePath = $targetDir . $fileName; // Unique filename
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES['house_photo']['tmp_name'], $targetFilePath)) {
                $image_path = "admin/pages/" . $targetFilePath; // Store the full path for events_overview.php
            } else {
                $toastMessage = "Failed to upload the image.";
                $toastType = "error";
            }
        } else {
            $toastMessage = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            $toastType = "error";
        }
    }
    $accept_registrations = $_POST['accept_registrations'] === 'true' ? 1 : 0;

    if (!$toastMessage) {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Insert data into the events table (without organiser JSON field)
            $query = "INSERT INTO events (admin_id, hid, title, description, venue, event_date, start_time, image_path, participate_points, winner_points, accept_registrations, organiser_points)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iissssssiiis", $aid, $house_id, $title, $description, $venue, $event_date, $start_time, $image_path, $participate_points, $winner_points, $accept_registrations, $organiser_points);

            if (!$stmt->execute()) {
                throw new Exception("Failed to create the event: " . $stmt->error);
            }

            // Get the event_id of the newly created event
            $event_id = $conn->insert_id;

            // Insert organizers into the organizers table
            if (!empty($selected_students)) {
                $insert_sql = "INSERT INTO organizers (student_id, event_id, points) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);

                if (!$insert_stmt) {
                    throw new Exception("Error preparing organizers insert query: " . $conn->error);
                }

                foreach ($selected_students as $username) {
                    // Find the student_id corresponding to this username
                    $find_student_id = null;
                    foreach ($students as $student) {
                        if ($student['username'] === $username) {
                            $find_student_id = $student['student_id'];
                            break;
                        }
                    }

                    if ($find_student_id) {
                        $insert_stmt->bind_param("sii", $find_student_id, $event_id, $organiser_points);
                        if (!$insert_stmt->execute()) {
                            throw new Exception("Error adding organizer: " . $insert_stmt->error);
                        }
                    } else {
                        // Log that we couldn't find this user
                        error_log("Could not find student_id for username: " . $username);
                    }
                }
            }

            // Commit the transaction
            $conn->commit();

            $toastMessage = "Event created successfully!";
            $toastType = "success";
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();
            $toastMessage = $e->getMessage();
            $toastType = "error";
        }
    }
}

// Display toast message for success
if (!empty($toastMessage) && $toastType == "success") {
    echo '
    <div class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">' .
        htmlspecialchars($toastMessage) .
        '</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎉 Event Creation Dashboard</title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Sweetalert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            --secondary-gradient: linear-gradient(135deg, #f4f6f9, #e1e5f0);
        }

        body {
            background: var(--secondary-gradient);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .event-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px auto;
            max-width: calc(100% - 40px);
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px;
        }

        .btn-primary {
            background: green;
            border: none;
            transition: transform 0.3s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .tooltip-info {
            cursor: help;
            color: #2575fc;
        }

        .scrollable-table {
            max-height: 200px;
            /* Adjust the height as needed */
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php include '../utils/sidenavbar.php'; ?>

    <div class="container">
        <div class="event-container">
            <h2 class="text-center mb-4">
                <i class="bi bi-calendar-event text-primary"></i>
                Create Exciting Event 🎨
            </h2>

            <form id="event-upload-form" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Event Name 🏷️
                            <i class="bi bi-info-circle tooltip-info"
                                title="Give your event a catchy and descriptive name!"></i>
                        </label>
                        <input type="text" class="form-control" name="event_name" required
                            placeholder="e.g., Annual Cultural Fest 2024">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Venue 📍
                            <i class="bi bi-info-circle tooltip-info"
                                title="Where will the magic happen?"></i>
                        </label>
                        <input type="text" class="form-control" name="venue" required
                            placeholder="School Auditorium, Main Hall">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Event Date 📅
                            <i class="bi bi-info-circle tooltip-info"
                                title="Select the date of your exciting event"></i>
                        </label>
                        <input type="date" class="form-control" name="event_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Event Time ⏰
                            <i class="bi bi-info-circle tooltip-info"
                                title="Specify the start time of your event"></i>
                        </label>
                        <input type="time" class="form-control" name="timings" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Description 📝
                        <i class="bi bi-info-circle tooltip-info"
                            title="Tell us more about your awesome event!"></i>
                    </label>
                    <textarea class="form-control" name="description" rows="4" required
                        placeholder="Provide details about the event, activities, highlights..."></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Participate Points 🏆
                            <i class="bi bi-info-circle tooltip-info"
                                title="Points awarded for participation"></i>
                        </label>
                        <input type="number" class="form-control" name="participate_points" required min="0">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Organiser Points
                            <i class="bi bi-info-circle tooltip-info"
                                title="Points awarded for oraganisation"></i>
                        </label>
                        <input type="number" class="form-control" name="organiser_points" required min="0">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Winner Points 🥇
                            <i class="bi bi-info-circle tooltip-info"
                                title="Points awarded to winners"></i>
                        </label>
                        <input type="number" class="form-control" name="winner_points" required min="0">
                    </div>


                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Search reg. number or name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select id="yearFilter" class="form-select">
                            <option value="">All Years</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="classFilter" class="form-select">
                            <option value="">All Branches</option>
                            <option value="CSD">CSD</option>
                            <option value="CSIT A">CSIT A</option>
                            <option value="CSIT B">CSIT B</option>
                        </select>
                    </div>


                    <br>

                    <br>
                    <div class="table-responsive" style="margin-top: 10px;">
                        <table class="table table-hover" id="studentsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Reg. Number</th>
                                    <th>Name</th>
                                    <th>Year</th>
                                    <th>Branch</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-hover">
                                <tbody id="tableBody">
                                    <!-- Dynamic rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div>


                <div class="mb-3">
                    <label class="form-label">
                        Event Poster 🖼️
                        <i class="bi bi-info-circle tooltip-info"
                            title="Upload an attractive poster to grab attention!"></i>
                    </label>
                    <div class="file-input-wrapper">
                        <input type="file" id="event-poster" name="house_photo"
                            accept="image/*" required class="form-control">
                        <div class="btn btn-outline-primary w-100">
                            <i class="bi bi-upload"></i> Upload Poster
                        </div>
                    </div>
                    <img id="poster-preview" class="file-preview" style="display:none;">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Accepting Registrations 📝
                        <i class="bi bi-info-circle tooltip-info"
                            title="Enable to accept registrations from students"></i>
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            name="accept_registrations" value="true" checked>
                        <label class="form-check-label">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            name="accept_registrations" value="false">
                        <label class="form-check-label">No</label>
                    </div>
                </div>


                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-plus"></i> Create Event
                    </button>
                </div>
                <input type="hidden" id="selected_students_input" name="selected_students">
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script>
        // Poster preview
        document.getElementById('event-poster').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('poster-preview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Toast notifications
        <?php if (!empty($toastMessage)) { ?>
            Swal.fire({
                icon: '<?php echo ($toastType == "success") ? "success" : "error"; ?>',
                title: '<?php echo $toastMessage; ?>',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        <?php } ?>

        let students = <?php echo json_encode($students); ?>;
        let selectedStudents = [];

        document.addEventListener('DOMContentLoaded', function() {
            renderTable(students);

            document.getElementById('event-upload-form').addEventListener('submit', function(event) {
                // Prevent the default form submission
                event.preventDefault();

                // Set the value of the hidden input field
                document.getElementById('selected_students_input').value = JSON.stringify(selectedStudents);

                // Now submit the form
                this.submit();
            });
        });

        // Function to render student data in table
        // Function to render student data in table
        function renderTable(data) {
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';

            data.forEach((student, index) => {
                const row = `
            <tr>
                <td>${student.username}</td>
                <td>${student.name}</td>
                <td>${student.year}</td>
                <td>${student.branch}</td>
                <td>
                    <input type="checkbox" class="select-student" data-userid="${student.student_id}" data-username="${student.username}">
                </td>
            </tr>
        `;
                tableBody.innerHTML += row;
            });

            attachCheckboxListeners();
        }

        // Handle checkbox selection
        function attachCheckboxListeners() {
            document.querySelectorAll('.select-student').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const userId = this.getAttribute('data-userid');
                    if (this.checked) {
                        selectedStudents.push(userId);
                    } else {
                        selectedStudents = selectedStudents.filter(id => id !== userId);
                    }
                    console.log(selectedStudents);
                });
            });
        }

        // Function to filter table
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const yearTerm = document.getElementById('yearFilter').value;
            const classTerm = document.getElementById('classFilter').value;

            const filteredStudents = students.filter(student => {
                const matchSearch = student.username.toLowerCase().includes(searchTerm) ||
                    student.name.toLowerCase().includes(searchTerm);
                const matchYear = !yearTerm || student.year === yearTerm;
                const matchClass = !classTerm || student.branch === classTerm;


                return matchSearch && matchYear && matchClass;
            });

            renderTable(filteredStudents);
        }

        // Attach event listeners for search and filter inputs
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('yearFilter').addEventListener('change', filterTable);
        document.getElementById('classFilter').addEventListener('change', filterTable);

    </script>
</body>

</html>