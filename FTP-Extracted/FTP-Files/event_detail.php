<?php
session_start();
include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$event_id = $_GET['event_id'] ?? null;
$success_message = '';
$error_message = '';

if (!$event_id) {
    header('Location: events_overview.php');
    exit();
}

// Handle event registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    // Check if student is logged in
    if (!isset($_SESSION['student_logged_in']) || !$_SESSION['student_logged_in']) {
        $error_message = 'You must be logged in as a student to register for events.';
    } else {
        $student_id = $_SESSION['student_id'];
        
        // First get event data to check if registration is still allowed
        $event_check_query = "SELECT event_date, start_time FROM events WHERE event_id = ?";
        $event_check_stmt = mysqli_prepare($conn, $event_check_query);
        mysqli_stmt_bind_param($event_check_stmt, "i", $event_id);
        mysqli_stmt_execute($event_check_stmt);
        $event_check_result = mysqli_stmt_get_result($event_check_stmt);
        $event_info = mysqli_fetch_assoc($event_check_result);
        
        if (!$event_info) {
            $error_message = 'Event not found.';
        } else {
            $event_datetime = $event_info['event_date'] . ' ' . $event_info['start_time'];
            $current_datetime = date('Y-m-d H:i:s');
            
            // Check if event has already started or passed
            if (strtotime($event_datetime) <= strtotime($current_datetime)) {
                $error_message = 'Registration is closed. This event has already started or ended.';
            } else {
                // Check if student is already registered in registrations table
                $check_query = "SELECT registered_id FROM registrations WHERE student_id = ? AND event_id = ?";
                $check_stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($check_stmt, "si", $student_id, $event_id);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);
                
                if (mysqli_num_rows($check_result) > 0) {
                    $error_message = 'You are already registered for this event.';
                } else {
                    // Register the student for the event in registrations table
                    $register_query = "INSERT INTO registrations (student_id, event_id, status, registered_at) VALUES (?, ?, 'confirmed', NOW())";
                    $register_stmt = mysqli_prepare($conn, $register_query);
                    mysqli_stmt_bind_param($register_stmt, "si", $student_id, $event_id);
                    
                    if (mysqli_stmt_execute($register_stmt)) {
                        $success_message = 'Successfully registered for the event! You will receive participation points after attending.';
                    } else {
                        $error_message = 'Error registering for the event. Please try again.';
                    }
                }
            }
        }
    }
}

$event_data = null;
$feedback_data = [];
$organizers_data = [];
$participants_data = [];
$winners_data = [];

// Fetch main event details
$event_query = "SELECT * FROM events WHERE event_id = ?";
$stmt = mysqli_prepare($conn, $event_query);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$event_result = mysqli_stmt_get_result($stmt);
$event_data = mysqli_fetch_assoc($event_result);

if (!$event_data) {
    echo "<div class=\"alert alert-danger\">Event not found.</div>";
    include "./footer.php";
    exit();
}

// Fetch event feedback
$feedback_query = "SELECT ef.*, s.name as student_name FROM event_feedback ef JOIN students s ON ef.student_id = s.student_id WHERE ef.event_id = ? ORDER BY ef.created_at DESC";
$stmt = mysqli_prepare($conn, $feedback_query);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$feedback_result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($feedback_result)) {
    $feedback_data[] = $row;
}

// Fetch organizers
$organizers_query = "SELECT o.*, s.name as student_name FROM organizers o JOIN students s ON o.student_id = s.student_id WHERE o.event_id = ? ORDER BY s.name ASC";
$stmt = mysqli_prepare($conn, $organizers_query);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$organizers_result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($organizers_result)) {
    $organizers_data[] = $row;
}

// Fetch participants
$participants_query = "SELECT p.*, s.name as student_name FROM participants p JOIN students s ON p.student_id = s.student_id WHERE p.event_id = ? ORDER BY s.name ASC";
$stmt = mysqli_prepare($conn, $participants_query);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$participants_result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($participants_result)) {
    $participants_data[] = $row;
}

// Fetch winners
$winners_query = "SELECT w.*, s.name as student_name FROM winners w JOIN students s ON w.student_id = s.student_id WHERE w.event_id = ? ORDER BY w.position ASC";
$stmt = mysqli_prepare($conn, $winners_query);
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$winners_result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($winners_result)) {
    $winners_data[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title><?php echo htmlspecialchars($event_data['title']); ?> Details - SRKR Engineering College</title>
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #333;
        }

        .page-header {
            background: #f8f9fa;
            padding: 3rem 0 2rem 0;
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0;
            background: #f8f9fa;
            color: #333;
        }

        .page-subtitle {
            font-size: 1rem;
            color: #666;
            font-weight: 400;
        }

        .back-nav {
            margin-bottom: 2rem;
        }

        .back-btn {
            background: #333;
            border: none;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .back-btn:hover {
            background: #555;
            color: #fff;
        }

        .main-content {
            padding: 0 0 3rem 0;
        }

        .detail-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .detail-card-header {
            background: #e9ecef;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            border-bottom: none;
        }

        .detail-card-body {
            padding: 1.5rem;
        }

        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #666;
            font-size: 1rem;
            line-height: 1.5;
        }

        .table-container {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .table {
            margin-bottom: 0;
            font-size: 14px;
        }

        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            font-weight: 600;
            color: #333;
            padding: 1rem 0.75rem;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 0.75rem;
            border-bottom: 1px solid #f1f3f4;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .winner-row {
            background: #fff8e1 !important;
            border-left: 4px solid #ffc107;
        }

        .winner-row:hover {
            background: #fff3c4 !important;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-participated {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-registered {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .position-badge {
            background: #333;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            min-width: 30px;
            text-align: center;
            display: inline-block;
        }

        .points-badge {
            background: #4caf50;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #666;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }

        .empty-state h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            font-weight: 600;
        }

        .section-title i {
            color: #666;
        }



        .status-attended {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
        }

        /* Registration Action Area */
        .registration-info {
            flex: 1;
        }

        .registration-action {
            flex-shrink: 0;
        }

        .registration-info .fas {
            font-size: 1.1rem;
        }

        .border-top {
            border-color: #e9ecef !important;
        }

        /* Tab Styles */
        .event-tabs {
            border: none;
            margin-bottom: 0;
        }

        .event-tabs .nav-link {
            border: none;
            border-radius: 0;
            color: #666;
            font-weight: 500;
            padding: 1rem 1.5rem;
            background: transparent;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .event-tabs .nav-link:hover {
            background: #f8f9fa;
            color: #333;
            border-bottom-color: #dee2e6;
        }

        .event-tabs .nav-link.active {
            background: #fff;
            color: #333;
            border-bottom-color: #333;
            font-weight: 600;
        }

        .tab-content {
            background: #fff;
            border-radius: 0 0 8px 8px;
            min-height: 300px;
        }

        .tab-pane {
            padding: 1.5rem;
        }

        .tab-header {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
        }

        .tab-count {
            background: #f8f9fa;
            color: #fff;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        @media (max-width: 768px) {
            .event-tabs .nav-link {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            .tab-pane {
                padding: 1rem;
            }
            .page-title {
                font-size: 2rem;
            }
            
            .event-info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .detail-card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-header">
        <div class="container">
            <h1 class="page-title"><?php echo htmlspecialchars($event_data['title']); ?></h1>
            <p class="page-subtitle">Event Details & Participation Information</p>
        </div>
    </div>
    
    <div class="main-content">
        <div class="container">
            <div class="back-nav">
                <a href="events_overview.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Events
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Event Main Details -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-info-circle"></i> Event Information
                </div>
                <div class="detail-card-body">
                    <div class="event-info-grid">
                        <div class="info-item">
                            <div class="info-label">Description</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($event_data['description'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Event Date</div>
                            <div class="info-value"><?php echo date('l, F j, Y', strtotime($event_data['event_date'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Time</div>
                            <div class="info-value"><?php echo date('g:i A', strtotime($event_data['start_time'])); ?> - <?php echo date('g:i A', strtotime($event_data['end_time'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Venue</div>
                            <div class="info-value"><?php echo htmlspecialchars($event_data['venue']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Created</div>
                            <div class="info-value"><?php echo date('M j, Y \a\t g:i A', strtotime($event_data['created_at'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Registered Participants</div>
                            <div class="info-value">
                                <?php 
                                $count_query = "SELECT COUNT(*) as total FROM registrations WHERE event_id = ? AND status != 'cancelled'";
                                $count_stmt = mysqli_prepare($conn, $count_query);
                                mysqli_stmt_bind_param($count_stmt, "i", $event_id);
                                mysqli_stmt_execute($count_stmt);
                                $count_result = mysqli_stmt_get_result($count_stmt);
                                $registration_count = mysqli_fetch_assoc($count_result)['total'];
                                echo $registration_count . " student" . ($registration_count != 1 ? "s" : "");
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Registration Action Area -->
                    <div class="mt-4 pt-4 border-top">
                        <?php
                        $is_student_logged_in = isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'];
                        $is_registered = false;
                        $registration_status = '';
                        $is_registration_open = true;
                        
                        // Check if registration is still open (event hasn't started yet)
                        $event_datetime = $event_data['event_date'] . ' ' . $event_data['start_time'];
                        $current_datetime = date('Y-m-d H:i:s');
                        if (strtotime($event_datetime) <= strtotime($current_datetime)) {
                            $is_registration_open = false;
                        }
                        
                        if ($is_student_logged_in) {
                            $student_id = $_SESSION['student_id'];
                            $check_query = "SELECT status FROM registrations WHERE student_id = ? AND event_id = ?";
                            $check_stmt = mysqli_prepare($conn, $check_query);
                            mysqli_stmt_bind_param($check_stmt, "si", $student_id, $event_id);
                            mysqli_stmt_execute($check_stmt);
                            $check_result = mysqli_stmt_get_result($check_stmt);
                            
                            if ($row = mysqli_fetch_assoc($check_result)) {
                                $is_registered = true;
                                $registration_status = $row['status'];
                            }
                        }
                        ?>
                        
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="registration-info">
                                <?php if (!$is_student_logged_in): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle text-muted me-2"></i>
                                        <span class="text-muted">Login as a student to register for this event</span>
                                    </div>
                                <?php elseif ($is_registered): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="text-success fw-semibold">You're registered!</span>
                                        <span class="badge bg-<?php echo $registration_status === 'confirmed' ? 'success' : ($registration_status === 'pending' ? 'warning' : 'secondary'); ?> ms-2">
                                            <?php echo ucfirst($registration_status); ?>
                                        </span>
                                    </div>
                                <?php elseif (!$is_registration_open): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-danger me-2"></i>
                                        <span class="text-danger">Registration closed - Event has started</span>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-plus text-primary me-2"></i>
                                        <span class="text-primary fw-semibold">Ready to join this event?</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="registration-action">
                                <?php if (!$is_student_logged_in && $is_registration_open): ?>
                                    <a href="login.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Login to Register
                                    </a>
                                <?php elseif (!$is_registered && $is_registration_open): ?>
                                    <form method="POST" id="registrationForm" style="display: inline;">
                                        <button type="button" class="btn btn-success" onclick="showRegistrationDialog()">
                                            <i class="fas fa-user-plus"></i> Register Now
                                        </button>
                                        <input type="hidden" name="register_event" value="1">
                                    </form>

<script>
function showRegistrationDialog() {
    const dialog = document.createElement('div');
    dialog.className = 'modal fade';
    dialog.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus text-success me-2"></i>
                        Event Registration
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to register for this event?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You will receive participation points after attending the event.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" onclick="submitRegistration()">
                        <i class="fas fa-check me-1"></i> Yes, Register
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(dialog);
    const modal = new bootstrap.Modal(dialog);
    modal.show();
    
    dialog.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(dialog);
    });
}

function submitRegistration() {
    const form = document.querySelector('form[method="POST"]');
    form.submit();
}
</script>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Only show Event People section after the event has ended
            $event_datetime = $event_data['event_date'] . ' ' . $event_data['end_time'];
            $current_datetime = date('Y-m-d H:i:s');
            $event_has_ended = strtotime($event_datetime) < strtotime($current_datetime);
            ?>
            
            <?php if ($event_has_ended): ?>
            <!-- Event People - Tabbed Interface -->
            <div class="detail-card">
                <div class="tab-header">
                    <h5 class="mb-0" style="color: #333; font-weight: 600;">
                        <i class="fas fa-users"></i> Event People
                    </h5>
                </div>
                
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs event-tabs" id="eventPeopleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab" aria-controls="participants" aria-selected="true">
                            <i class="fas fa-users"></i> Participants
                            <span class="tab-count"><?php echo count($participants_data); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="organizers-tab" data-bs-toggle="tab" data-bs-target="#organizers" type="button" role="tab" aria-controls="organizers" aria-selected="false">
                            <i class="fas fa-users-cog"></i> Organizers
                            <span class="tab-count"><?php echo count($organizers_data); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="winners-tab" data-bs-toggle="tab" data-bs-target="#winners" type="button" role="tab" aria-controls="winners" aria-selected="false">
                            <i class="fas fa-trophy"></i> Winners
                            <span class="tab-count"><?php echo count($winners_data); ?></span>
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="eventPeopleTabContent">
                    <!-- Participants Tab -->
                    <div class="tab-pane fade show active" id="participants" role="tabpanel" aria-labelledby="participants-tab">
                        <?php if (!empty($participants_data)): ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Student ID</th>
                                            <th>Status</th>
                                            <th>Points</th>
                                            <th>Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($participants_data as $participant): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($participant['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($participant['student_id']); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo strtolower($participant['participation_status']); ?>">
                                                        <?php echo htmlspecialchars($participant['participation_status']); ?>
                                                    </span>
                                                </td>
                                                <td><span class="points-badge"><?php echo htmlspecialchars($participant['points']); ?></span></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($participant['registered_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <h4>No Participants</h4>
                                <p>No participants have recorded for this event yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Organizers Tab -->
                    <div class="tab-pane fade" id="organizers" role="tabpanel" aria-labelledby="organizers-tab">
                        <?php if (!empty($organizers_data)): ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Student ID</th>
                                            <th>Role</th>
                                            <th>Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($organizers_data as $org): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($org['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($org['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($org['role']); ?></td>
                                                <td><span class="points-badge"><?php echo htmlspecialchars($org['points']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-users-slash"></i>
                                <h4>No Organizers</h4>
                                <p>No organizers have been recorded for this event.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Winners Tab -->
                    <div class="tab-pane fade" id="winners" role="tabpanel" aria-labelledby="winners-tab">
                        <?php if (!empty($winners_data)): ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Position</th>
                                            <th>Student Name</th>
                                            <th>Student ID</th>
                                            <th>Points Awarded</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($winners_data as $winner): ?>
                                            <tr class="winner-row">
                                                <td><span class="position-badge"><?php echo htmlspecialchars($winner['position']); ?></span></td>
                                                <td><?php echo htmlspecialchars($winner['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($winner['student_id']); ?></td>
                                                <td><span class="points-badge"><?php echo htmlspecialchars($winner['points']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-trophy"></i>
                                <h4>No Winners Yet</h4>
                                <p>Winners have not been announced for this event.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Event People section will be available after the event ends -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-clock"></i> Event People
                </div>
                <div class="detail-card-body">
                    <div class="text-center p-4">
                        <i class="fas fa-hourglass-half" style="font-size: 3rem; color: #6c757d; margin-bottom: 1rem;"></i>
                        <h5 class="mb-3">Event In Progress</h5>
                        <p class="text-muted mb-4">
                            Participant details, organizers, and winners will be displayed after the event concludes.
                        </p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Event ends on <?php echo date('l, F j, Y \a\t g:i A', strtotime($event_data['event_date'] . ' ' . $event_data['end_time'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <script>
        // Enhanced tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth transitions to tab content
            const tabButtons = document.querySelectorAll('#eventPeopleTabs button[data-bs-toggle="tab"]');
            
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function(e) {
                    // Add a subtle animation when tab content is shown
                    const targetPane = document.querySelector(e.target.getAttribute('data-bs-target'));
                    if (targetPane) {
                        targetPane.style.opacity = '0';
                        targetPane.style.transform = 'translateY(10px)';
                        
                        setTimeout(() => {
                            targetPane.style.transition = 'all 0.3s ease';
                            targetPane.style.opacity = '1';
                            targetPane.style.transform = 'translateY(0)';
                        }, 50);
                    }
                });
            });
            
            // Highlight the tab with the most content
            const participantCount = <?php echo count($participants_data); ?>;
            const organizerCount = <?php echo count($organizers_data); ?>;
            const winnerCount = <?php echo count($winners_data); ?>;
            
            // If there are winners, make that tab more prominent
            if (winnerCount > 0) {
                const winnersTab = document.getElementById('winners-tab');
                if (winnersTab) {
                    winnersTab.innerHTML += ' <i class="fas fa-star text-warning ms-1" title="Results Available"></i>';
                }
            }
            
            // Add tooltips to tab counts
            const tabCounts = document.querySelectorAll('.tab-count');
            tabCounts.forEach(count => {
                const number = parseInt(count.textContent);
                if (number === 0) {
                    count.style.background = '#6c757d';
                } else if (number > 10) {
                    count.style.background = '#28a745';
                }
            });
        });
    </script>
</body>
</html>