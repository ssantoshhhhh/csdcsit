<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require '../utils/connect.php';

$username = $_SESSION['username'];
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$event = [];

// Fetch admin details
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
    die("Admin not found.");
}

// Fetch event details if editing
if ($event_id) {
    $query = "SELECT * FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
    } else {
        die("Event not found.");
    }
}

// Fetch students from the same house
$query = "SELECT s.student_id, s.student_id as username, s.name, c.year, c.branch FROM students s JOIN classes c ON s.class_id = c.class_id";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Get selected organizers if updating
$selected_students = [];
if ($event_id) {
    $query = "SELECT student_id FROM organizers WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selected_students[] = $row['student_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event_id ? 'Edit Event' : 'Create Event' ?> 🎉</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">
    <?php include '../utils/sidenavbar.php'; ?>

    <div class="container mt-4">
        <div class="card shadow-lg p-4">
            <h2 class="text-center text-primary mb-4">
                <i class="bi bi-calendar-plus"></i> <?= $event_id ? 'Edit' : 'Create' ?> Event
            </h2>

            <form id="event-form" action="save_event.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?? '' ?>">

                <!-- 🎯 Basic Info Section -->
                <h5 class="text-secondary">📌 Event Details</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Event Name 🏷️</label>
                        <input type="text" class="form-control" name="event_name" required value="<?= $event['title'] ?? '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Venue 📍</label>
                        <input type="text" class="form-control" name="venue" required value="<?= $event['venue'] ?? '' ?>">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Event Date 📅</label>
                        <input type="date" class="form-control" name="event_date" required value="<?= $event['event_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Time ⏰</label>
                        <input type="time" class="form-control" name="timings" required value="<?= $event['start_time'] ?? '' ?>">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Description 📝</label>
                    <textarea class="form-control" name="description" rows="4" required><?= $event['description'] ?? '' ?></textarea>
                </div>

                <hr>

                <!-- 🏆 Points Section -->
                <h5 class="text-secondary">🎯 Points System</h5>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Participation Points</label>
                        <input type="number" class="form-control" name="participate_points" required min="0" value="<?= $event['participate_points'] ?? 0 ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Organiser Points 🏆</label>
                        <input type="number" class="form-control" name="organiser_points" required min="0" value="<?= $event['organiser_points'] ?? 0 ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Winner Points 🥇</label>
                        <input type="number" class="form-control" name="winner_points" required min="0" value="<?= $event['winner_points'] ?? 0 ?>">
                    </div>
                </div>

                <hr>

                <!-- 👥 Organizers Section -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="form-label">Search by Reg No.</label>
                        <input type="text" id="searchInput" class="form-control" placeholder="Enter Reg No.">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Year</label>
                        <select id="yearFilter" class="form-select">
                            <option value="">All Years</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Branch</label>
                        <select id="branchFilter" class="form-select">
                            <option value="">All Branches</option>
                            <option value="CSD">CSD</option>
                            <option value="CSIT A">CSIT A</option>
                            <option value="CSIT B">CSIT B</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Reg. Number</th>
                                <th>Name</th>
                                <th>Year</th>
                                <th>Branch</th>
                                <th>Select</th>
                            </tr>
                            
                        </thead>
                        </table>
                        <div style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover">
                        <tbody id="studentsTable"></tbody>
                       
                    </table>
                    </div>
                </div>

                <input type="hidden" id="selected_students_input" name="selected_students">

                <hr>

                <!-- 🖼️ Poster Upload Section -->
                <h5 class="text-secondary">🖼️ Upload Poster</h5>
                <input type="file" id="event-poster" name="house_photo" accept="image/*" class="form-control">
                <?php if (!empty($event['image_path'])) : 
                    // Handle both old and new image path formats
                    $display_path = $event['image_path'];
                    if (strpos($display_path, 'admin/pages/') === 0) {
                        // Remove 'admin/pages/' prefix for admin display
                        $display_path = substr($display_path, 12);
                    }
                ?>
                    <img src="<?= $display_path ?>" class="img-thumbnail mt-3" style="max-width:200px;">
                <?php endif; ?>

                <hr>

                <div class="mb-3">
                    <label class="form-label">
                        Accepting Registrations 📝
                        <i class="bi bi-info-circle tooltip-info"
                            title="Enable to accept registrations from students"></i>
                    </label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            name="accept_registrations" value="true"
                            <?= (isset($event['accept_registrations']) && $event['accept_registrations'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label">Yes</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            name="accept_registrations" value="false"
                            <?= (isset($event['accept_registrations']) && $event['accept_registrations'] == 0) ? 'checked' : '' ?>>
                        <label class="form-check-label">No</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-calendar-plus"></i> <?= $event_id ? 'Update Event' : 'Save Event' ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        let students = <?= json_encode($students) ?>;
        let selectedStudents = <?= json_encode($selected_students) ?>;

        // Ensure selectedStudents is always an array
        if (!Array.isArray(selectedStudents)) {
            selectedStudents = [];
        }

        // Function to render all students and check selected organizers
        function renderTable(filteredStudents = students) {
            const tableBody = document.getElementById('studentsTable');
            tableBody.innerHTML = '';

            filteredStudents.forEach(student => {
                // Check if the student is in the selected organizers list
                const isChecked = selectedStudents.includes(student.student_id) ? 'checked' : '';

                const row = `
            <tr>
                <td>${student.username}</td>
                <td>${student.name}</td>
                <td>${student.year}</td>
                <td>${student.branch}</td>
                <td><input type="checkbox" class="select-student" data-userid="${student.student_id}" ${isChecked}></td>
            </tr>
        `;
                tableBody.innerHTML += row;
            });

            attachCheckboxListeners();
        }

        // Function to handle form submission
        document.getElementById('event-form').addEventListener('submit', function(event) {
            let selectedOrganizers = [];

            document.querySelectorAll('.select-student:checked').forEach(checkbox => {
                selectedOrganizers.push(checkbox.getAttribute('data-userid'));
            });

            console.log("Selected Organizers before submitting:", selectedOrganizers);

            // Set the hidden input field with selected organizers as JSON
            document.getElementById('selected_students_input').value = JSON.stringify(selectedOrganizers);
        });

        // Function to attach event listeners to checkboxes
        function attachCheckboxListeners() {
            document.querySelectorAll('.select-student').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    let userId = this.getAttribute('data-userid');
                    if (this.checked) {
                        if (!selectedStudents.includes(userId)) {
                            selectedStudents.push(userId);
                        }
                    } else {
                        selectedStudents = selectedStudents.filter(id => id !== userId);
                    }
                    console.log("Updated Selected Organizers:", selectedStudents);
                });
            });
        }

        // Filter function
        function filterStudents() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const yearFilter = document.getElementById('yearFilter').value;
            const branchFilter = document.getElementById('branchFilter').value;

            const filteredStudents = students.filter(student => {
                const matchRegNo = student.username.toLowerCase().includes(searchTerm);
                const matchYear = !yearFilter || student.year === yearFilter;
                const matchBranch = !branchFilter || student.branch === branchFilter;

                return matchRegNo && matchYear && matchBranch;
            });

            renderTable(filteredStudents);
        }

        // Attach event listeners for filters
        document.getElementById('searchInput').addEventListener('input', filterStudents);
        document.getElementById('yearFilter').addEventListener('change', filterStudents);
        document.getElementById('branchFilter').addEventListener('change', filterStudents);

        // Render the student table when the page loads
        renderTable();
    </script>
</body>

</html>