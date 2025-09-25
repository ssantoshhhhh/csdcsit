<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_logged_in']) || !$_SESSION['faculty_logged_in']) {
    header('Location: login.php');
    exit();
}

include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$faculty_id = $_SESSION['faculty_id'] ?? null;
$faculty_name = $_SESSION['faculty_name'] ?? 'Unknown Faculty';
$faculty_sections = $_SESSION['faculty_sections'] ?? '';

// Get assigned sections
$assigned_sections = [];
if (!empty($faculty_sections)) {
    $assigned_sections = explode(',', $faculty_sections);
}

$success = '';
$error = '';

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $section = $_POST['section'] ?? '';
    $date = $_POST['date'] ?? '';
    $session = $_POST['session'] ?? '';
    $attendance = $_POST['attendance'] ?? [];
    
    if (empty($section) || empty($date) || empty($session) || empty($attendance)) {
        $error = 'Please fill all required fields.';
    } elseif (!in_array($section, $assigned_sections)) {
        $error = 'You are not authorized to mark attendance for this section.';
    } else {
        $success_count = 0;
        $error_count = 0;
        
        foreach ($attendance as $student_id => $status) {
            // Insert or update attendance record
            $query = "INSERT INTO student_attendance (student_id, attendance_date, session, status, faculty_id) 
                     VALUES (?, ?, ?, ?, ?) 
                     ON DUPLICATE KEY UPDATE status = ?, faculty_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssssss", 
                $student_id, 
                $date, 
                $session, 
                $status, 
                $faculty_id,
                $status,
                $faculty_id
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        if ($error_count == 0) {
            $success = "Attendance marked successfully for $success_count students.";
        } else {
            $error = "Errors occurred while marking attendance. Success: $success_count, Errors: $error_count";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Mark Attendance - Faculty Dashboard</title>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-user-check"></i> Mark Attendance</h2>
            <p>Faculty: <?php echo htmlspecialchars($faculty_name); ?></p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger" style="border-radius: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="border-radius: 10px;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-clipboard-check"></i> Mark Attendance
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="attendanceForm">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="section" class="form-label">Select Section</label>
                                <select name="section" id="section" class="form-control" required>
                                    <option value="">Choose section...</option>
                                    <?php foreach ($assigned_sections as $section): ?>
                                        <option value="<?php echo htmlspecialchars($section); ?>">
                                            <?php echo htmlspecialchars($section); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" name="date" id="date" class="form-control" 
                                       value="<?php echo date('Y-m-d'); ?>" 
                                       max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="session" class="form-label">Session</label>
                                <select name="session" id="session" class="form-control" required>
                                    <option value="">Select session...</option>
                                    <option value="Forenoon">Forenoon</option>
                                    <option value="Afternoon">Afternoon</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="studentList" class="mb-4">
                            <!-- Student list will be loaded here dynamically -->
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" name="submit_attendance" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="fas fa-save"></i> Save Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const section = document.getElementById('section');
            const date = document.getElementById('date');
            const session = document.getElementById('session');
            const studentList = document.getElementById('studentList');
            const submitBtn = document.getElementById('submitBtn');
            
            // Function to load students
            function loadStudents() {
                const selectedSection = section.value;
                const selectedDate = date.value;
                const selectedSession = session.value;
                
                if (!selectedSection || !selectedDate || !selectedSession) {
                    studentList.innerHTML = '';
                    submitBtn.disabled = true;
                    return;
                }
                
                // Fetch students using AJAX
                fetch(`get_students.php?section=${selectedSection}&date=${selectedDate}&session=${selectedSession}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            studentList.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                            submitBtn.disabled = true;
                            return;
                        }
                        
                        if (data.students.length === 0) {
                            studentList.innerHTML = '<div class="alert alert-info">No students found in this section.</div>';
                            submitBtn.disabled = true;
                            return;
                        }
                        
                        // Create table for students
                        let html = `
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead style="background: var(--light-blue);">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Register No</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.students.forEach((student, index) => {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${student.register_no}</td>
                                    <td>${student.name}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="attendance[${student.student_id}]" 
                                                   id="present_${student.student_id}" value="Present" 
                                                   ${student.status === 'Present' ? 'checked' : ''} required>
                                            <label class="btn btn-outline-success" for="present_${student.student_id}">
                                                Present (1 point)
                                            </label>
                                            
                                            <input type="radio" class="btn-check" name="attendance[${student.student_id}]" 
                                                   id="absent_${student.student_id}" value="Absent"
                                                   ${student.status === 'Absent' ? 'checked' : ''} required>
                                            <label class="btn btn-outline-danger" for="absent_${student.student_id}">
                                                Absent (0 points)
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        html += '</tbody></table></div>';
                        studentList.innerHTML = html;
                        submitBtn.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        studentList.innerHTML = '<div class="alert alert-danger">Error loading students.</div>';
                        submitBtn.disabled = true;
                    });
            }
            
            // Load students when any of the filters change
            section.addEventListener('change', loadStudents);
            date.addEventListener('change', loadStudents);
            session.addEventListener('change', loadStudents);
        });
    </script>
    
    <style>
        .btn-check:checked + .btn-outline-success {
            background-color: var(--bs-success) !important;
            color: white !important;
        }
        
        .btn-check:checked + .btn-outline-danger {
            background-color: var(--bs-danger) !important;
            color: white !important;
        }
        
        .btn-group label {
            min-width: 120px;
        }
        
        @media (max-width: 768px) {
            .btn-group {
                display: flex;
                flex-direction: column;
            }
            
            .btn-group label {
                border-radius: 5px !important;
                margin: 2px 0;
            }
        }
    </style>
</body>
</html>