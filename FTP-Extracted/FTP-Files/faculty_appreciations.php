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

// Get real faculty data from database
$faculty_id = $_SESSION['faculty_id'] ?? null;
if (!$faculty_id) {
    // Session data is missing, redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

$faculty_query = "SELECT faculty_name, class_id, phone_number, email FROM faculties WHERE faculty_id = ?";
$stmt = mysqli_prepare($conn, $faculty_query);
mysqli_stmt_bind_param($stmt, "i", $faculty_id);
mysqli_stmt_execute($stmt);
$faculty_result = mysqli_stmt_get_result($stmt);
$faculty_data = mysqli_fetch_assoc($faculty_result);

if ($faculty_data) {
    $faculty_name = $faculty_data['faculty_name'];
    $faculty_sections = (string)($faculty_data['class_id'] ?? '');
    $faculty_phone = $faculty_data['phone_number'];
    $faculty_email = $faculty_data['email'];
} else {
    // Fallback to session data if database query fails
    $faculty_name = $_SESSION['faculty_name'] ?? 'Unknown Faculty';
    $faculty_sections = $_SESSION['faculty_sections'] ?? '';
    $faculty_phone = $_SESSION['faculty_phone'] ?? '';
    $faculty_email = $_SESSION['faculty_email'] ?? '';
}

// Get assigned sections - handle empty sections properly
$assigned_sections = [];
if (!empty($faculty_sections)) {
    $assigned_sections = explode(',', $faculty_sections);
    // Clean up any empty entries
    $assigned_sections = array_filter($assigned_sections, function($section) {
        return !empty(trim($section));
    });
}

$classes = [
    '28csit_a_attendance' => '2/4 CSIT-A',
    '28csit_b_attendance' => '2/4 CSIT-B',
    '28csd_attendance'    => '2/4 CSD',
    '27csit_attendance'   => '3/4 CSIT',
    '27csd_attendance'    => '3/4 CSD',
    '26csd_attendance'    => '4/4 CSD',
];

$success = '';
$error = '';

// Handle appreciation points submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['award_appreciation'])) {
    $student_id = $_POST['student_id'];
    $event_id = $_POST['event_id'];
    $points = (int)$_POST['points'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    
    if (empty($student_id) || empty($event_id) || $points < 1 || empty($reason)) {
        $error = "Please fill all required fields for appreciation points.";
    } else {
        $insert_query = "INSERT INTO appreciations (student_id, event_id, points, reason, created_by, created_at) 
                       VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "siisi", $student_id, $event_id, $points, $reason, $_SESSION['faculty_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Appreciation points awarded successfully!";
        } else {
            $error = "Error awarding appreciation points: " . mysqli_error($conn);
        }
    }
}

// Fetch events for the dropdown
$events_query = "SELECT event_id, title FROM events ORDER BY event_date DESC";
$events_result = mysqli_query($conn, $events_query);
$events = [];
while ($event = mysqli_fetch_assoc($events_result)) {
    $events[] = $event;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Manage Appreciations - SRKR Engineering College</title>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-award"></i> Manage Appreciations</h2>
            <p>Award and view appreciation points for students</p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger" style="border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" style="border-radius: 10px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <strong>Success:</strong> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="text-end mb-4">
                <a href="faculty_dashboard.php" class="btn btn-primary me-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="faculty_logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Appreciation Points Section -->
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-award"></i> Award Appreciation Points
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="faculty_appreciations.php">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="student_select" class="form-label">Select Student</label>
                                <select class="form-control" id="student_select" name="student_id" required>
                                    <option value="">Choose a student...</option>
                                    <?php
                                    if (!empty($assigned_sections)) {
                                        $class_ids_in = implode(',', array_map('intval', $assigned_sections));
                                        $students_query = "SELECT s.student_id, s.name, c.year, c.branch, c.section 
                                                         FROM students s 
                                                         JOIN classes c ON s.class_id = c.class_id 
                                                         WHERE s.class_id IN ($class_ids_in) 
                                                         ORDER BY c.year, c.branch, c.section, s.name";
                                        $students_result = mysqli_query($conn, $students_query);
                                        while ($student = mysqli_fetch_assoc($students_result)) {
                                            echo '<option value="' . htmlspecialchars($student['student_id']) . '">' 
                                                . htmlspecialchars($student['name']) . ' - ' 
                                                . htmlspecialchars($student['year'] . '/' . $student['branch'] . '-' . $student['section'])
                                                . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="event_select" class="form-label">Select Event</label>
                                <select class="form-control" id="event_select" name="event_id" required>
                                    <option value="">Choose an event...</option>
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?php echo htmlspecialchars($event['event_id']); ?>">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="points" class="form-label">Points</label>
                                <input type="number" class="form-control" id="points" name="points" min="1" max="10" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="appreciation_reason" class="form-label">Reason</label>
                                <input type="text" class="form-control" id="appreciation_reason" name="reason" required>
                            </div>
                        </div>
                        <button type="submit" name="award_appreciation" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Award Points
                        </button>
                    </form>

                    <!-- Recent Appreciation Points History -->
                    <div class="mt-4">
                        <h6 class="mb-3">Recent Appreciation Points Awarded</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Points</th>
                                        <th>Reason</th>
                                        <th>Awarded On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($assigned_sections)) {
                                        $class_ids_in = implode(',', array_map('intval', $assigned_sections));
                                        $recent_points_query = "SELECT a.*, s.name, c.year, c.branch, c.section 
                                                              FROM appreciations a 
                                                              JOIN students s ON a.student_id = s.student_id 
                                                              JOIN classes c ON s.class_id = c.class_id 
                                                              WHERE s.class_id IN ($class_ids_in)
                                                              ORDER BY a.created_at DESC LIMIT 10";
                                        $recent_points_result = mysqli_query($conn, $recent_points_query);
                                        while ($point = mysqli_fetch_assoc($recent_points_result)) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($point['name']) . ' (' 
                                                . htmlspecialchars($point['year'] . '/' . $point['branch'] . '-' . $point['section']) . ')</td>';
                                            echo '<td>' . htmlspecialchars($point['points']) . '</td>';
                                            echo '<td>' . htmlspecialchars($point['reason']) . '</td>';
                                            echo '<td>' . date('d M Y H:i', strtotime($point['created_at'])) . '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <style>
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(7,101,147,0.25);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 20px 15px;
            }
            
            .form-control {
                font-size: 16px;
                padding: 12px 15px;
            }
            
            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
            
            .table-responsive {
                font-size: 14px;
            }
        }
    </style>
</body>
</html>