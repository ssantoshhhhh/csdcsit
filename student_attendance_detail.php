<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "./connect.php";


// Handle reg_no and table via POST and store in session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reg_no'])) {
    $_SESSION['reg_no'] = $_POST['reg_no'];
    // Optionally, also set table if needed
    if (isset($_POST['table'])) {
        $_SESSION['table'] = $_POST['table'];
    }
    header('Location: student_attendance_detail.php');
    exit();
}
$reg_no = isset($_SESSION['reg_no']) ? $_SESSION['reg_no'] : '';
$table = isset($_SESSION['table']) ? $_SESSION['table'] : '28csit_b_attendance';

if (empty($reg_no)) {
    header("Location: student_attendance.php");
    exit();
}

// Get student attendance details
$query = "
    SELECT 
        attendance_date,
        session,
        status,
        CASE 
            WHEN status = 1 THEN 'Present'
            ELSE 'Absent'
        END as status_text
    FROM $table
    WHERE register_no = ?
    ORDER BY attendance_date DESC, 
             CASE WHEN session = 'Forenoon' THEN 1 ELSE 2 END
";

$stmt = $sconn->prepare($query);
$stmt->bind_param("s", $reg_no);
$stmt->execute();
$result = $stmt->get_result();

// Calculate summary statistics
$summary_query = "
    SELECT 
        COUNT(*) as total_sessions,
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as present_sessions,
        ROUND((SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_percentage,
        SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as attendance_points
    FROM $table 
    WHERE register_no = ?
";

$summary_stmt = $sconn->prepare($summary_query);
$summary_stmt->bind_param("s", $reg_no);
$summary_stmt->execute();
$summary_result = $summary_stmt->get_result();
$summary = $summary_result->fetch_assoc();

// Group attendance by date for better display
$attendance_by_date = [];
while ($row = $result->fetch_assoc()) {
    $date = $row['attendance_date'];
    if (!isset($attendance_by_date[$date])) {
        $attendance_by_date[$date] = [
            'forenoon' => ['status' => 'Not Recorded', 'class' => 'text-muted'],
            'afternoon' => ['status' => 'Not Recorded', 'class' => 'text-muted']
        ];
    }
    
    $session_key = strtolower($row['session']);
    $attendance_by_date[$date][$session_key] = [
        'status' => $row['status_text'],
        'class' => $row['status'] == 1 ? 'text-success' : 'text-danger'
    ];
}
?>

<?php include "./head.php"; ?>
<title>Attendance Details - <?php echo htmlspecialchars($reg_no); ?> - SRKR Engineering College</title>

<style>
    .hero-section {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        color: white;
        padding: 40px 0 30px;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    .student-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        border-radius: 20px;
        padding: 8px 20px;
        margin: 15px 0;
        letter-spacing: 0.5px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 15px;
        margin: 30px 0;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px 15px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(255,255,255,0.2);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-blue), var(--secondary-blue));
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 8px;
        display: block;
    }
    
    .stat-label {
        color: var(--gray-medium);
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .attendance-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 8px;
    }
    
    .attendance-badge.excellent {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .attendance-badge.warning {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
    }
    
    .attendance-badge.low {
        background: linear-gradient(135deg, #dc3545, #e83e8c);
        color: white;
    }
    
    .attendance-table {
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-top: 25px;
    }
    
    .table-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 20px 25px;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.5px;
    }
    
    .attendance-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .attendance-table th {
        background: var(--light-blue);
        padding: 15px 12px;
        text-align: center;
        font-weight: 600;
        color: var(--primary-blue);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .attendance-table td {
        padding: 15px 12px;
        text-align: center;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.9rem;
    }
    
    .attendance-table tr:hover {
        background: linear-gradient(135deg, #f8f9ff, #f0f7ff);
    }
    
    .date-cell {
        font-weight: 600;
        color: var(--gray-dark);
        background: rgba(7,101,147,0.05);
    }
    
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-present {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .status-absent {
        background: linear-gradient(135deg, #dc3545, #e83e8c);
        color: white;
    }
    
    .status-not-recorded {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
    }
    
    .back-btn {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        border: none;
        border-radius: 20px;
        padding: 10px 25px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(7,101,147,0.3);
    }
    
    .back-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(7,101,147,0.4);
        color: white;
        text-decoration: none;
    }
    
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 30px;
    }
    
    .breadcrumb a {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .breadcrumb a:hover {
        color: var(--secondary-blue);
    }
    
    .breadcrumb .active {
        color: var(--gray-medium);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--gray-medium);
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .empty-state h4 {
        margin-bottom: 10px;
        color: var(--gray-dark);
    }
    
    @media (max-width: 768px) {
        .hero-section {
            padding: 30px 0 25px;
        }
        
        .hero-section h1 {
            font-size: 1.8rem !important;
        }
        
        .student-badge {
            font-size: 1rem;
            padding: 10px 20px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin: 25px 0;
        }
        
        .stat-card {
            padding: 15px 12px;
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
        
        .stat-label {
            font-size: 0.8rem;
        }
        
        .attendance-table {
            margin-top: 20px;
        }
        
        .table-header {
            padding: 20px 20px;
            font-size: 1.1rem;
        }
        
        .attendance-table th,
        .attendance-table td {
            padding: 12px 8px;
            font-size: 0.85rem;
        }
        
        .status-badge {
            padding: 6px 12px;
            font-size: 0.75rem;
        }
        
        .back-btn {
            padding: 10px 20px;
            font-size: 0.9rem;
        }
        
        .empty-state {
            padding: 40px 15px;
        }
        
        .empty-state i {
            font-size: 3rem;
        }
    }
    
    @media (max-width: 576px) {
        .hero-section {
            padding: 30px 0 20px;
        }
        
        .hero-section h1 {
            font-size: 1.5rem !important;
        }
        
        .student-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        
        .stat-card {
            padding: 15px 10px;
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
        
        .stat-label {
            font-size: 0.7rem;
        }
        
        .attendance-table th,
        .attendance-table td {
            padding: 8px 6px;
            font-size: 0.75rem;
        }
        
        .status-badge {
            padding: 4px 8px;
            font-size: 0.65rem;
        }
        
        .table-header {
            padding: 15px 15px;
            font-size: 1rem;
        }
        
        .back-btn {
            padding: 8px 16px;
            font-size: 0.8rem;
        }
        
        .empty-state {
            padding: 30px 10px;
        }
        
        .empty-state i {
            font-size: 2.5rem;
        }
    }
    
    @media (max-width: 768px) and (orientation: landscape) {
        .hero-section {
            padding: 20px 0 15px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .main-content {
            padding: 20px 0;
        }
    }
</style>

<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 10px;">
                    <i class="fas fa-user-graduate"></i> Attendance Details
                </h1>
                <div class="student-badge">
                    <i class="fas fa-id-card"></i> Registration No: <?php echo htmlspecialchars($reg_no); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="student_attendance.php">Student Attendance Points</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($reg_no); ?></li>
                </ol>
            </nav>
            
            <!-- Back Button -->
            <div class="mb-4">
                <a href="student_attendance.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Attendance List
                </a>
            </div>
            
            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $summary['attendance_points']; ?></div>
                    <div class="stat-label">Attendance Points</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $summary['attendance_percentage']; ?>%</div>
                    <div class="stat-label">Attendance Percentage</div>
                    <?php
                    $att_perc = floatval($summary['attendance_percentage']);
                    $badge_class = $att_perc >= 75 ? 'excellent' : ($att_perc >= 60 ? 'warning' : 'low');
                    $badge_text = $att_perc >= 75 ? 'Excellent' : ($att_perc >= 60 ? 'Warning' : 'Low');
                    ?>
                    <div class="attendance-badge <?php echo $badge_class; ?>">
                        <?php echo $badge_text; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $summary['present_sessions']; ?></div>
                    <div class="stat-label">Present Sessions</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $summary['total_sessions'] - $summary['present_sessions']; ?></div>
                    <div class="stat-label">Absent Sessions</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value"><?php echo $summary['total_sessions']; ?></div>
                    <div class="stat-label">Total Sessions</div>
                </div>
            </div>
            
            <!-- Detailed Attendance Table -->
            <div class="attendance-table">
                <div class="table-header">
                    <i class="fas fa-calendar-alt"></i> Detailed Attendance Record
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Forenoon Session</th>
                                <th>Afternoon Session</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($attendance_by_date)): ?>
                                <?php foreach ($attendance_by_date as $date => $sessions): ?>
                                    <tr>
                                        <td class="date-cell">
                                            <?php echo date('d M Y, D', strtotime($date)); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $sessions['forenoon']['status'] === 'Present' ? 'status-present' : ($sessions['forenoon']['status'] === 'Absent' ? 'status-absent' : 'status-not-recorded'); ?>">
                                                <?php echo $sessions['forenoon']['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $sessions['afternoon']['status'] === 'Present' ? 'status-present' : ($sessions['afternoon']['status'] === 'Absent' ? 'status-absent' : 'status-not-recorded'); ?>">
                                                <?php echo $sessions['afternoon']['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <h4>No Records Found</h4>
                                            <p>No attendance records found for this student.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
</body>
</html> 