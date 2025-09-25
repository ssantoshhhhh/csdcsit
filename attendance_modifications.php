<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['hod_logged_in']) || !$_SESSION['hod_logged_in']) {
    header('Location: login.php');
    exit();
}
include './connect.php';

$sections = [
    '28csit_a_attendance' => '2/4 CSIT-A',
    '28csit_b_attendance' => '2/4 CSIT-B',
    '28csd_attendance'    => '2/4 CSD',
    '27csit_attendance'   => '3/4 CSIT',
    '27csd_attendance'    => '3/4 CSD',
    '26csd_attendance'    => '4/4 CSD',
];

// Get filters
$filter_section = $_GET['section'] ?? '';
$filter_faculty = $_GET['faculty'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($filter_section) {
    $where_conditions[] = "table_name = ?";
    $params[] = $filter_section;
}

if ($filter_faculty) {
    $where_conditions[] = "faculty_name LIKE ?";
    $params[] = "%$filter_faculty%";
}

if ($filter_date_from) {
    $where_conditions[] = "attendance_date >= ?";
    $params[] = $filter_date_from;
}

if ($filter_date_to) {
    $where_conditions[] = "attendance_date <= ?";
    $params[] = $filter_date_to;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get modifications with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$count_query = "SELECT COUNT(*) as total FROM attendance_modifications $where_clause";
$count_stmt = $sconn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

$query = "SELECT * FROM attendance_modifications $where_clause ORDER BY modified_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $sconn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$modifications_result = $stmt->get_result();
$modifications = [];
while ($row = $modifications_result->fetch_assoc()) {
    $modifications[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Attendance Modifications - SRKR Engineering College</title>
</head>
<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-history"></i> Attendance Modifications</h2>
            <p>Track all attendance changes and modifications</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="hod_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-filter"></i> Filter Modifications
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="GET" class="row">
                        <div class="col-md-3 mb-3">
                            <label for="section" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                <i class="fas fa-graduation-cap"></i> Section
                            </label>
                            <select name="section" id="section" class="form-control" style="border-radius: 10px;">
                                <option value="">All Sections</option>
                                <?php foreach ($sections as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $filter_section == $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="faculty" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                <i class="fas fa-user-tie"></i> Faculty
                            </label>
                            <input type="text" name="faculty" id="faculty" class="form-control" value="<?php echo htmlspecialchars($filter_faculty); ?>" placeholder="Search faculty..." style="border-radius: 10px;">
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="date_from" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                <i class="fas fa-calendar"></i> From Date
                            </label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($filter_date_from); ?>" style="border-radius: 10px;">
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <label for="date_to" class="form-label" style="color: var(--primary-blue); font-weight: 500;">
                                <i class="fas fa-calendar"></i> To Date
                            </label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($filter_date_to); ?>" style="border-radius: 10px;">
                        </div>
                        
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2" style="border-radius: 10px;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="attendance_modifications.php" class="btn btn-outline-secondary" style="border-radius: 10px;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Results Summary -->
            <div class="alert alert-info" style="border-radius: 10px;">
                <i class="fas fa-info-circle"></i> 
                Showing <?php echo count($modifications); ?> of <?php echo $total_records; ?> modifications
                <?php if (!empty($where_conditions)): ?>
                    (filtered results)
                <?php endif; ?>
            </div>
            
            <!-- Modifications Table -->
            <div class="card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                <div class="card-header" style="background: var(--light-blue); border-bottom: 1px solid #e3e6f0; border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="fas fa-list"></i> Attendance Modifications
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($modifications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead style="background: var(--light-blue);">
                                    <tr>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Section</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Date</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Session</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Faculty</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Reason</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Changes Made</th>
                                        <th style="color: var(--primary-blue); font-weight: 600;">Modified At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modifications as $mod): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary" style="border-radius: 8px;">
                                                    <?php echo $sections[$mod['table_name']] ?? $mod['table_name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($mod['attendance_date'])); ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $mod['session'] == 'Forenoon' ? 'bg-warning' : 'bg-info'; ?>" style="border-radius: 8px;">
                                                    <?php echo htmlspecialchars($mod['session']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($mod['faculty_name']); ?>
                                            </td>
                                            <td>
                                                <span class="text-muted" style="font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($mod['modification_reason']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($mod['changes_made'])): ?>
                                                    <span class="badge bg-info" style="border-radius: 8px; font-size: 0.7rem;">
                                                        <i class="fas fa-exchange-alt"></i> Changes
                                                    </span>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.8rem;">
                                                        <?php echo htmlspecialchars($mod['changes_made']); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted" style="font-size: 0.8rem;">
                                                        <i class="fas fa-info-circle"></i> No changes tracked
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> <?php echo date('d M Y H:i', strtotime($mod['modified_at'])); ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Modifications pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" style="border-radius: 8px;">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" style="border-radius: 8px;">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" style="border-radius: 8px;">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: var(--gray-medium); margin-bottom: 20px;"></i>
                            <h5 style="color: var(--gray-medium);">No modifications found</h5>
                            <p class="text-muted">No attendance modifications match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <style>
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(7,101,147,0.15) !important;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(7,101,147,0.25);
        }
    </style>
</body>
</html> 