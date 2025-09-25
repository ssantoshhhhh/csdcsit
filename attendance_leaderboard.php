<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['hod_logged_in']) || !$_SESSION['hod_logged_in']) {
    header('Location: login.php');
    exit();
}
include './connect.php';
include './db_migration_helper.php';

$class_id = $_GET['class_id'] ?? null;

$students_data = [];
$section_name = '';
$all_classes = $db_helper->getAllClasses();

if ($class_id) {
    if (isset($all_classes[$class_id])) {
        $section_name = $all_classes[$class_id];
        
        $query = "
            SELECT s.student_id, s.name, s.email, COUNT(sa.status) as attendance_points
            FROM students s
            JOIN student_attendance sa ON s.student_id = sa.student_id
            WHERE s.class_id = ? AND sa.status = 'Present'
            GROUP BY s.student_id ORDER BY attendance_points DESC
        ";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $class_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $students_data[] = $row;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Attendance Leaderboard - SRKR Engineering College</title>
</head>
<body>
    <?php include "nav.php"; ?>

    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-trophy"></i> Attendance Leaderboard</h2>
            <p>View student rankings based on attendance points</p>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <?php if (!$class_id): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Select a Section</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($all_classes as $id => $name): ?>
                            <a href="?class_id=<?php echo $id; ?>" class="list-group-item list-group-item-action">
                                <?php echo $name; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Leaderboard for <?php echo $section_name; ?></h5>
                        <a href="attendance_leaderboard.php" class="btn btn-sm btn-primary">Back to Sections</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student Name</th>
                                        <th>Registration No.</th>
                                        <th>Attendance Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($students_data)): ?>
                                        <?php foreach ($students_data as $index => $student): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo $student['name']; ?></td>
                                            <td><?php echo str_replace('@srkrec.edu.in', '', $student['email']); ?></td>
                                            <td><?php echo $student['attendance_points']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No attendance data found for this section.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
</html>