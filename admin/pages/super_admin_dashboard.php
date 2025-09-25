<?php
session_start();

// Check if super admin is logged in
if (!isset($_SESSION['superadmin_logged_in']) || $_SESSION['superadmin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include "../utils/connect.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-container {
            padding: 2rem 0;
        }
        .dashboard-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .header-card {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .export-card {
            border-left: 5px solid #28a745;
        }
        .btn-export {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
        .btn-logout {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid;
        }
        .stat-card.students { border-left-color: #007bff; }
        .stat-card.events { border-left-color: #28a745; }
        .stat-card.houses { border-left-color: #ffc107; }
        .stat-card.points { border-left-color: #dc3545; }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <!-- Header -->
    <div class="dashboard-card header-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-1"><i class="fas fa-crown"></i> Super Admin Dashboard</h1>
                <p class="mb-0">Welcome, <?php echo $_SESSION['superadmin_username']; ?>!</p>
            </div>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <?php
        // Get statistics
        $students_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count'];
        $events_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM events"))['count'];
        $houses_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM houses"))['count'];
        $total_points = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
            (SELECT COALESCE(SUM(points), 0) FROM appreciations) +
            (SELECT COALESCE(SUM(points), 0) FROM participants) +
            (SELECT COALESCE(SUM(points), 0) FROM organizers) +
            (SELECT COALESCE(SUM(points), 0) FROM winners) -
            (SELECT COALESCE(SUM(points), 0) FROM penalties) as total"))['total'];
        ?>
        
        <div class="stat-card students">
            <div class="stat-number text-primary"><?php echo $students_count; ?></div>
            <div class="stat-label">Total Students</div>
        </div>
        
        <div class="stat-card events">
            <div class="stat-number text-success"><?php echo $events_count; ?></div>
            <div class="stat-label">Total Events</div>
        </div>
        
        <div class="stat-card houses">
            <div class="stat-number text-warning"><?php echo $houses_count; ?></div>
            <div class="stat-label">Total Houses</div>
        </div>
        
        <div class="stat-card points">
            <div class="stat-number text-danger"><?php echo $total_points; ?></div>
            <div class="stat-label">Total Points Awarded</div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="dashboard-card export-card">
        <h3 class="mb-4"><i class="fas fa-download"></i> Export House Points Data</h3>
        <p class="text-muted mb-4">Download comprehensive reports of all student house points history including appreciations, participations, organizer points, winners, and penalties.</p>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <a href="export_points_advanced.php?format=excel" class="btn btn-export w-100">
                    <i class="fas fa-file-excel"></i> Export Detailed Report (Excel)
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="export_points_advanced.php?format=csv" class="btn btn-export w-100">
                    <i class="fas fa-file-csv"></i> Export Detailed Report (CSV)
                </a>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6 mb-3">
                <a href="export_points_advanced.php?format=excel&type=summary" class="btn btn-export w-100" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                    <i class="fas fa-chart-pie"></i> Export Student Summary (Excel)
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="export_points_advanced.php?format=csv&type=summary" class="btn btn-export w-100" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                    <i class="fas fa-chart-bar"></i> Export Student Summary (CSV)
                </a>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6 mb-3">
                <a href="export_house_summary.php?format=excel" class="btn btn-export w-100" style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);">
                    <i class="fas fa-home"></i> Export House Summary (Excel)
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="export_house_summary.php?format=csv" class="btn btn-export w-100" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                    <i class="fas fa-home"></i> Export House Summary (CSV)
                </a>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6 mb-3">
                <a href="export_house_comparison.php?format=excel" class="btn btn-export w-100" style="background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);">
                    <i class="fas fa-balance-scale"></i> House Comparison (Excel)
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="export_house_comparison.php?format=csv" class="btn btn-export w-100" style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);">
                    <i class="fas fa-balance-scale"></i> House Comparison (CSV)
                </a>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6 mb-3">
                <a href="export_houses_with_sections.php?format=excel" class="btn btn-export w-100" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);">
                    <i class="fas fa-layer-group"></i> Houses with Sections (Excel)
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="export_houses_with_sections.php?format=csv" class="btn btn-export w-100" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                    <i class="fas fa-layer-group"></i> Houses with Sections (CSV)
                </a>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6 mb-3">
                <a href="export_year_wise_sections.php?format=excel" class="btn btn-export w-100" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-graduation-cap"></i> Year-wise Sections (Excel)
                </a>
            </div>
            <div class="col-md-6 mb-3">
                <a href="export_year_wise_sections.php?format=csv" class="btn btn-export w-100" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                    <i class="fas fa-graduation-cap"></i> Year-wise Sections (CSV)
                </a>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12 mb-3">
                <a href="test_all_students.php" class="btn btn-export w-100" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                    <i class="fas fa-bug"></i> Test Export - All Students (Including 0 Points)
                </a>
                <small class="text-muted d-block mt-1">This test export shows all students including those with 0 points, with section counts for verification</small>
            </div>
        </div>
    </div>

    <!-- House-wise Export Section -->
    <div class="dashboard-card" style="border-left: 5px solid #17a2b8;">
        <h3 class="mb-4"><i class="fas fa-home"></i> Export Data by House</h3>
        <p class="text-muted mb-4">Download reports for individual houses or export all houses with separate data sections.</p>
        
        <!-- House Selection Dropdown -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label for="houseSelect" class="form-label">Select House:</label>
                <select id="houseSelect" class="form-select">
                    <option value="all">All Houses</option>
                    <?php
                    $houses_query = "SELECT hid, name FROM houses ORDER BY name";
                    $houses_result = mysqli_query($conn, $houses_query);
                    while ($house = mysqli_fetch_assoc($houses_result)) {
                        echo "<option value='{$house['hid']}'>{$house['name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <!-- Export Buttons -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <button onclick="exportHouseData('excel', 'detailed')" class="btn btn-export w-100" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-file-excel"></i> Detailed (Excel)
                </button>
                <small class="text-muted d-block mt-1" id="filename-preview-1">e.g., PRUDHVI_House_Points_Detailed_Report_2024-01-15.xls</small>
            </div>
            <div class="col-md-3 mb-3">
                <button onclick="exportHouseData('csv', 'detailed')" class="btn btn-export w-100" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                    <i class="fas fa-file-csv"></i> Detailed (CSV)
                </button>
                <small class="text-muted d-block mt-1" id="filename-preview-2">e.g., PRUDHVI_House_Points_Detailed_Report_2024-01-15.csv</small>
            </div>
            <div class="col-md-3 mb-3">
                <button onclick="exportHouseData('excel', 'summary')" class="btn btn-export w-100" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                    <i class="fas fa-chart-pie"></i> Summary (Excel)
                </button>
                <small class="text-muted d-block mt-1" id="filename-preview-3">e.g., PRUDHVI_House_Points_Summary_Report_2024-01-15.xls</small>
            </div>
            <div class="col-md-3 mb-3">
                <button onclick="exportHouseData('csv', 'summary')" class="btn btn-export w-100" style="background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);">
                    <i class="fas fa-chart-bar"></i> Summary (CSV)
                </button>
                <small class="text-muted d-block mt-1" id="filename-preview-4">e.g., PRUDHVI_House_Points_Summary_Report_2024-01-15.csv</small>
            </div>
        </div>
        
        <!-- Quick Export All Houses Separately -->
        <hr class="my-4">
        <h5 class="mb-3"><i class="fas fa-layer-group"></i> Export All Houses Separately</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <button onclick="exportAllHousesSeparately('excel')" class="btn btn-export w-100" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                    <i class="fas fa-file-archive"></i> All Houses Detailed (Excel ZIP)
                </button>
                <small class="text-muted d-block mt-1">Contains: PRUDHVI_House_Detailed_Report.xls, VAYU_House_Detailed_Report.xls, etc.</small>
            </div>
            <div class="col-md-6 mb-3">
                <button onclick="exportAllHousesSeparately('csv')" class="btn btn-export w-100" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                    <i class="fas fa-file-archive"></i> All Houses Detailed (CSV ZIP)
                </button>
                <small class="text-muted d-block mt-1">Contains: PRUDHVI_House_Detailed_Report.csv, VAYU_House_Detailed_Report.csv, etc.</small>
            </div>
        </div>
    </div>

    <!-- House-wise Summary -->
    <div class="dashboard-card">
        <h3 class="mb-4"><i class="fas fa-home"></i> House-wise Points Summary</h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>House</th>
                        <th>Total Students</th>
                        <th>Total Points</th>
                        <th>Average Points per Student</th>
                        <th>Rank</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $house_query = "
                        SELECT 
                            h.name as house_name,
                            COUNT(DISTINCT s.student_id) as student_count,
                            COALESCE(
                                (SELECT SUM(a.points) FROM appreciations a JOIN students s2 ON a.student_id = s2.student_id WHERE s2.hid = h.hid) +
                                (SELECT SUM(p.points) FROM participants p JOIN students s3 ON p.student_id = s3.student_id WHERE s3.hid = h.hid AND p.points > 0) +
                                (SELECT SUM(o.points) FROM organizers o JOIN students s4 ON o.student_id = s4.student_id WHERE s4.hid = h.hid AND o.points > 0) +
                                (SELECT SUM(w.points) FROM winners w JOIN students s5 ON w.student_id = s5.student_id WHERE s5.hid = h.hid) -
                                (SELECT SUM(pen.points) FROM penalties pen JOIN students s6 ON pen.student_id = s6.student_id WHERE s6.hid = h.hid)
                            , 0) as total_points
                        FROM houses h
                        LEFT JOIN students s ON h.hid = s.hid
                        GROUP BY h.hid, h.name
                        ORDER BY total_points DESC
                    ";
                    
                    $house_result = mysqli_query($conn, $house_query);
                    $rank = 1;
                    while ($house_row = mysqli_fetch_assoc($house_result)) {
                        $avg_points = $house_row['student_count'] > 0 ? round($house_row['total_points'] / $house_row['student_count'], 2) : 0;
                        $rank_badge = '';
                        switch($rank) {
                            case 1: $rank_badge = '<span class="badge bg-warning text-dark">🥇 1st</span>'; break;
                            case 2: $rank_badge = '<span class="badge bg-secondary">🥈 2nd</span>'; break;
                            case 3: $rank_badge = '<span class="badge bg-warning">🥉 3rd</span>'; break;
                            default: $rank_badge = '<span class="badge bg-light text-dark">' . $rank . 'th</span>'; break;
                        }
                        
                        echo "<tr>
                            <td><strong>{$house_row['house_name']}</strong></td>
                            <td>{$house_row['student_count']}</td>
                            <td><strong class='text-success'>{$house_row['total_points']}</strong></td>
                            <td>{$avg_points}</td>
                            <td>{$rank_badge}</td>
                        </tr>";
                        $rank++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Quick House Export Buttons -->
        <div class="mt-3">
            <h5 class="mb-3"><i class="fas fa-download"></i> Quick House Exports</h5>
            <div class="row">
                <?php
                // Reset the result pointer to show house buttons
                mysqli_data_seek($house_result, 0);
                while ($house_row = mysqli_fetch_assoc($house_result)) {
                    $house_name = $house_row['house_name'];
                    $house_id = $house_row['hid'] ?? '';
                    echo "<div class='col-md-3 mb-2'>
                        <div class='dropdown'>
                            <button class='btn btn-sm btn-outline-primary dropdown-toggle w-100' type='button' data-bs-toggle='dropdown'>
                                {$house_name}
                            </button>
                            <ul class='dropdown-menu'>
                                <li><a class='dropdown-item' href='export_house_wise.php?format=excel&type=detailed&house_id={$house_id}'>
                                    <i class='fas fa-file-excel'></i> Detailed Excel
                                </a></li>
                                <li><a class='dropdown-item' href='export_house_wise.php?format=csv&type=detailed&house_id={$house_id}'>
                                    <i class='fas fa-file-csv'></i> Detailed CSV
                                </a></li>
                                <li><hr class='dropdown-divider'></li>
                                <li><a class='dropdown-item' href='export_house_wise.php?format=excel&type=summary&house_id={$house_id}'>
                                    <i class='fas fa-chart-pie'></i> Summary Excel
                                </a></li>
                                <li><a class='dropdown-item' href='export_house_wise.php?format=csv&type=summary&house_id={$house_id}'>
                                    <i class='fas fa-chart-bar'></i> Summary CSV
                                </a></li>
                            </ul>
                        </div>
                    </div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Section-wise Analysis -->
    <div class="dashboard-card">
        <h3 class="mb-4"><i class="fas fa-users"></i> Section-wise Performance Analysis</h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-info">
                    <tr>
                        <th>Academic Year</th>
                        <th>Year</th>
                        <th>Branch</th>
                        <th>Section</th>
                        <th>Total Students</th>
                        <th>Active Students</th>
                        <th>Activity Rate</th>
                        <th>Total Points</th>
                        <th>Avg Points/Student</th>
                        <th>Top Performer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $section_query = "
                        SELECT 
                            c.class_id,
                            c.academic_year,
                            c.year,
                            c.branch,
                            c.section,
                            COUNT(DISTINCT s.student_id) as total_students,
                            COUNT(DISTINCT active_s.student_id) as active_students,
                            COALESCE(
                                (SELECT SUM(a.points) FROM appreciations a JOIN students s2 ON a.student_id = s2.student_id WHERE s2.class_id = c.class_id) +
                                (SELECT SUM(p.points) FROM participants p JOIN students s3 ON p.student_id = s3.student_id WHERE s3.class_id = c.class_id) +
                                (SELECT SUM(o.points) FROM organizers o JOIN students s4 ON o.student_id = s4.student_id WHERE s4.class_id = c.class_id) +
                                (SELECT SUM(w.points) FROM winners w JOIN students s5 ON w.student_id = s5.student_id WHERE s5.class_id = c.class_id) -
                                (SELECT SUM(pen.points) FROM penalties pen JOIN students s6 ON pen.student_id = s6.student_id WHERE s6.class_id = c.class_id)
                            , 0) as total_points,
                            top_student.student_name as top_performer,
                            top_student.top_points
                        FROM classes c
                        LEFT JOIN students s ON c.class_id = s.class_id
                        LEFT JOIN (
                            SELECT DISTINCT s7.student_id, s7.class_id
                            FROM students s7
                            WHERE s7.student_id IN (
                                SELECT student_id FROM appreciations
                                UNION
                                SELECT student_id FROM participants
                                UNION
                                SELECT student_id FROM organizers
                                UNION
                                SELECT student_id FROM winners
                                UNION
                                SELECT student_id FROM penalties
                            )
                        ) active_s ON c.class_id = active_s.class_id
                        LEFT JOIN (
                            SELECT 
                                s8.class_id,
                                s8.name as student_name,
                                (COALESCE(app_p.points, 0) + COALESCE(part_p.points, 0) + 
                                 COALESCE(org_p.points, 0) + COALESCE(win_p.points, 0) - COALESCE(pen_p.points, 0)) as top_points,
                                ROW_NUMBER() OVER (PARTITION BY s8.class_id ORDER BY 
                                    (COALESCE(app_p.points, 0) + COALESCE(part_p.points, 0) + 
                                     COALESCE(org_p.points, 0) + COALESCE(win_p.points, 0) - COALESCE(pen_p.points, 0)) DESC) as rn
                            FROM students s8
                            LEFT JOIN (SELECT student_id, SUM(points) as points FROM appreciations GROUP BY student_id) app_p ON s8.student_id = app_p.student_id
                            LEFT JOIN (SELECT student_id, SUM(points) as points FROM participants GROUP BY student_id) part_p ON s8.student_id = part_p.student_id
                            LEFT JOIN (SELECT student_id, SUM(points) as points FROM organizers GROUP BY student_id) org_p ON s8.student_id = org_p.student_id
                            LEFT JOIN (SELECT student_id, SUM(points) as points FROM winners GROUP BY student_id) win_p ON s8.student_id = win_p.student_id
                            LEFT JOIN (SELECT student_id, SUM(points) as points FROM penalties GROUP BY student_id) pen_p ON s8.student_id = pen_p.student_id
                        ) top_student ON c.class_id = top_student.class_id AND top_student.rn = 1
                        GROUP BY c.class_id, c.academic_year, c.year, c.branch, c.section
                        ORDER BY c.academic_year DESC, c.year DESC, c.branch, c.section
                    ";
                    
                    $section_result = mysqli_query($conn, $section_query);
                    while ($section_row = mysqli_fetch_assoc($section_result)) {
                        $avg_points = $section_row['total_students'] > 0 ? round($section_row['total_points'] / $section_row['total_students'], 2) : 0;
                        $activity_rate = $section_row['total_students'] > 0 ? round(($section_row['active_students'] / $section_row['total_students']) * 100, 1) : 0;
                        
                        // Color coding based on activity rate
                        $activity_badge = '';
                        if ($activity_rate >= 80) {
                            $activity_badge = '<span class="badge bg-success">' . $activity_rate . '%</span>';
                        } elseif ($activity_rate >= 60) {
                            $activity_badge = '<span class="badge bg-warning">' . $activity_rate . '%</span>';
                        } elseif ($activity_rate >= 40) {
                            $activity_badge = '<span class="badge bg-info">' . $activity_rate . '%</span>';
                        } else {
                            $activity_badge = '<span class="badge bg-danger">' . $activity_rate . '%</span>';
                        }
                        
                        // Performance indicator
                        $performance_indicator = '';
                        if ($avg_points >= 30) {
                            $performance_indicator = '<i class="fas fa-arrow-up text-success"></i>';
                        } elseif ($avg_points >= 15) {
                            $performance_indicator = '<i class="fas fa-minus text-warning"></i>';
                        } else {
                            $performance_indicator = '<i class="fas fa-arrow-down text-danger"></i>';
                        }
                        
                        echo "<tr>
                            <td><strong>{$section_row['academic_year']}</strong></td>
                            <td><span class='badge bg-primary'>Year {$section_row['year']}</span></td>
                            <td><span class='badge bg-info'>{$section_row['branch']}</span></td>
                            <td><span class='badge bg-secondary'>Section {$section_row['section']}</span></td>
                            <td><strong>{$section_row['total_students']}</strong></td>
                            <td>{$section_row['active_students']}</td>
                            <td>{$activity_badge}</td>
                            <td><strong class='text-success'>{$section_row['total_points']}</strong> {$performance_indicator}</td>
                            <td>{$avg_points}</td>
                            <td>" . ($section_row['top_performer'] ? "<small><strong>{$section_row['top_performer']}</strong><br>({$section_row['top_points']} pts)</small>" : "N/A") . "</td>
                            <td>
                                <div class='btn-group btn-group-sm'>
                                    <a href='export_section_wise.php?format=excel&class_id={$section_row['class_id']}' class='btn btn-outline-success btn-sm' title='Export Excel'>
                                        <i class='fas fa-file-excel'></i>
                                    </a>
                                    <a href='export_section_wise.php?format=csv&class_id={$section_row['class_id']}' class='btn btn-outline-primary btn-sm' title='Export CSV'>
                                        <i class='fas fa-file-csv'></i>
                                    </a>
                                </div>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section Performance Summary -->
    <div class="dashboard-card">
        <h3 class="mb-4"><i class="fas fa-chart-bar"></i> Section Performance Summary</h3>
        <div class="row">
            <?php
            // Get branch-wise summary
            // Get branch-wise summary with simpler approach
            $branches = ['CSD', 'CSIT']; // Known branches
            $branch_data = [];
            
            foreach ($branches as $branch) {
                // Get basic stats
                $basic_query = "
                    SELECT 
                        COUNT(DISTINCT c.class_id) as total_sections,
                        COUNT(DISTINCT s.student_id) as total_students
                    FROM classes c
                    LEFT JOIN students s ON c.class_id = s.class_id
                    WHERE c.branch = '$branch'
                ";
                $basic_result = mysqli_query($conn, $basic_query);
                $basic_stats = mysqli_fetch_assoc($basic_result);
                
                // Get total points for this branch
                $points_query = "
                    SELECT 
                        (
                            COALESCE((SELECT SUM(a.points) FROM appreciations a JOIN students s2 ON a.student_id = s2.student_id JOIN classes c2 ON s2.class_id = c2.class_id WHERE c2.branch = '$branch'), 0) +
                            COALESCE((SELECT SUM(p.points) FROM participants p JOIN students s3 ON p.student_id = s3.student_id JOIN classes c3 ON s3.class_id = c3.class_id WHERE c3.branch = '$branch' AND p.points > 0), 0) +
                            COALESCE((SELECT SUM(o.points) FROM organizers o JOIN students s4 ON o.student_id = s4.student_id JOIN classes c4 ON s4.class_id = c4.class_id WHERE c4.branch = '$branch' AND o.points > 0), 0) +
                            COALESCE((SELECT SUM(w.points) FROM winners w JOIN students s5 ON w.student_id = s5.student_id JOIN classes c5 ON s5.class_id = c5.class_id WHERE c5.branch = '$branch'), 0) -
                            COALESCE((SELECT SUM(pen.points) FROM penalties pen JOIN students s6 ON pen.student_id = s6.student_id JOIN classes c6 ON s6.class_id = c6.class_id WHERE c6.branch = '$branch'), 0)
                        ) as total_points
                ";
                $points_result = mysqli_query($conn, $points_query);
                $points_stats = mysqli_fetch_assoc($points_result);
                
                $avg_points = $basic_stats['total_students'] > 0 ? round($points_stats['total_points'] / $basic_stats['total_students'], 2) : 0;
                
                $branch_data[] = [
                    'branch' => $branch,
                    'total_sections' => $basic_stats['total_sections'],
                    'total_students' => $basic_stats['total_students'],
                    'avg_points_per_student' => $avg_points
                ];
            }
            
            // Sort by avg_points_per_student descending
            usort($branch_data, function($a, $b) {
                return $b['avg_points_per_student'] <=> $a['avg_points_per_student'];
            });
            
            foreach ($branch_data as $branch_row) {
            

                $avg_points = round($branch_row['avg_points_per_student'], 2);
                $card_color = $branch_row['branch'] == 'CSD' ? 'border-primary' : 'border-info';
                
                echo "<div class='col-md-6 mb-3'>
                    <div class='card {$card_color}'>
                        <div class='card-header bg-light'>
                            <h5 class='card-title mb-0'><i class='fas fa-graduation-cap'></i> {$branch_row['branch']} Branch</h5>
                        </div>
                        <div class='card-body'>
                            <div class='row text-center'>
                                <div class='col-4'>
                                    <h4 class='text-primary'>{$branch_row['total_sections']}</h4>
                                    <small class='text-muted'>Sections</small>
                                </div>
                                <div class='col-4'>
                                    <h4 class='text-info'>{$branch_row['total_students']}</h4>
                                    <small class='text-muted'>Students</small>
                                </div>
                                <div class='col-4'>
                                    <h4 class='text-success'>{$avg_points}</h4>
                                    <small class='text-muted'>Avg Points</small>
                                </div>
                            </div>
                            <div class='mt-3'>
                                <a href='export_branch_wise.php?format=excel&branch={$branch_row['branch']}' class='btn btn-sm btn-outline-success me-2'>
                                    <i class='fas fa-file-excel'></i> Export Excel
                                </a>
                                <a href='export_branch_wise.php?format=csv&branch={$branch_row['branch']}' class='btn btn-sm btn-outline-primary'>
                                    <i class='fas fa-file-csv'></i> Export CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>";
            }
            ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="dashboard-card">
        <h3 class="mb-4"><i class="fas fa-clock"></i> Recent Points Activity</h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Type</th>
                        <th>Points</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_query = "
                        (SELECT 'Appreciation' as type, a.created_at, s.name, s.student_id, a.points, a.reason 
                         FROM appreciations a 
                         JOIN students s ON a.student_id = s.student_id 
                         ORDER BY a.created_at DESC LIMIT 5)
                        UNION ALL
                        (SELECT 'Winner' as type, w.announced_at as created_at, s.name, s.student_id, w.points, 
                         CONCAT('Position ', w.position, ' in event') as reason 
                         FROM winners w 
                         JOIN students s ON w.student_id = s.student_id 
                         ORDER BY w.announced_at DESC LIMIT 5)
                        ORDER BY created_at DESC LIMIT 10
                    ";
                    
                    $recent_result = mysqli_query($conn, $recent_query);
                    while ($row = mysqli_fetch_assoc($recent_result)) {
                        $badge_class = $row['type'] == 'Appreciation' ? 'bg-success' : 'bg-warning';
                        echo "<tr>
                            <td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>
                            <td>{$row['name']} <small class='text-muted'>({$row['student_id']})</small></td>
                            <td><span class='badge {$badge_class}'>{$row['type']}</span></td>
                            <td><strong>+{$row['points']}</strong></td>
                            <td>{$row['reason']}</td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function exportHouseData(format, type) {
    const houseSelect = document.getElementById('houseSelect');
    const houseId = houseSelect.value;
    const url = `export_house_wise.php?format=${format}&type=${type}&house_id=${houseId}`;
    window.open(url, '_blank');
}

function exportAllHousesSeparately(format) {
    const url = `export_all_houses_separate.php?format=${format}`;
    window.open(url, '_blank');
}

// Update filename previews based on house selection
document.getElementById('houseSelect').addEventListener('change', function() {
    const selectedHouse = this.options[this.selectedIndex].text;
    const currentDate = new Date().toISOString().split('T')[0];
    
    // Update filename previews
    const previews = [
        { id: 'filename-preview-1', type: 'Detailed', format: 'xls' },
        { id: 'filename-preview-2', type: 'Detailed', format: 'csv' },
        { id: 'filename-preview-3', type: 'Summary', format: 'xls' },
        { id: 'filename-preview-4', type: 'Summary', format: 'csv' }
    ];
    
    previews.forEach(preview => {
        const element = document.getElementById(preview.id);
        if (element) {
            let houseName = selectedHouse === 'All Houses' ? 'All_Houses' : selectedHouse.replace(/\s+/g, '_');
            element.textContent = `e.g., ${houseName}_House_Points_${preview.type}_Report_${currentDate}.${preview.format}`;
        }
    });
});
</script>

</body>
</html>