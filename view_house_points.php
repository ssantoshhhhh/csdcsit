<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php"; 
include "connect.php";

// Get filter parameters
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_house = isset($_GET['house']) ? $_GET['house'] : '';

// Build query
$where_conditions = [];
if ($filter_section) {
    $where_conditions[] = "year_section = '" . mysqli_real_escape_string($conn, $filter_section) . "'";
}
if ($filter_house) {
    $where_conditions[] = "house_name = '" . mysqli_real_escape_string($conn, $filter_house) . "'";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// If legacy house_points table exists, use it; otherwise show empty dataset and friendly message
$hp_exists = mysqli_query($conn, "SHOW TABLES LIKE 'house_points'");
if ($hp_exists && mysqli_num_rows($hp_exists) > 0) {
    // Get data grouped by section
    $sql = "SELECT * FROM house_points $where_clause ORDER BY year_section, house_name, total_points DESC";
    $result = mysqli_query($conn, $sql);

    // Get unique sections and houses for filters
    $sections_sql = "SELECT DISTINCT year_section FROM house_points ORDER BY year_section";
    $sections_result = mysqli_query($conn, $sections_sql);

    $houses_sql = "SELECT DISTINCT house_name FROM house_points ORDER BY house_name";
    $houses_result = mysqli_query($conn, $houses_sql);

    // Get summary statistics
    $stats_sql = "SELECT 
        COUNT(*) as total_students,
        SUM(total_points) as total_points,
        AVG(total_points) as avg_points,
        MAX(total_points) as max_points,
        MIN(total_points) as min_points
    FROM house_points $where_clause";
    $stats_result = mysqli_query($conn, $stats_sql);
} else {
    $result = false;
    $sections_result = false;
    $houses_result = false;
    $stats_result = false;
    $no_house_points_table = true;
}
$stats = mysqli_fetch_assoc($stats_result);

// Group data by section
$sections_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $section = $row['year_section'];
    if (!isset($sections_data[$section])) {
        $sections_data[$section] = [];
    }
    $sections_data[$section][] = $row;
}
?>

<body>
    <!-- Top Bar -->
<?php if (!empty($no_house_points_table)): ?>
<div class="container mt-3">
	<div class="alert alert-info" style="border-radius: 10px;">
		<i class="fas fa-info-circle"></i> House points data is not available in the new schema yet. This page will show legacy data only if the old <code>house_points</code> table exists.
	</div>
</div>
<?php endif; ?>
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-chart-bar"></i> House Points Overview</h2>
            <p>View student house points data categorized by section</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card text-center" style="background: var(--primary-blue); color: white; border: none; border-radius: 15px;">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h4><?php echo $stats['total_students']; ?></h4>
                            <small>Total Students</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card text-center" style="background: var(--success); color: white; border: none; border-radius: 15px;">
                        <div class="card-body">
                            <i class="fas fa-star fa-2x mb-2"></i>
                            <h4><?php echo number_format($stats['total_points']); ?></h4>
                            <small>Total Points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card text-center" style="background: var(--warning); color: white; border: none; border-radius: 15px;">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <h4><?php echo number_format($stats['avg_points'], 1); ?></h4>
                            <small>Avg Points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card text-center" style="background: var(--info); color: white; border: none; border-radius: 15px;">
                        <div class="card-body">
                            <i class="fas fa-trophy fa-2x mb-2"></i>
                            <h4><?php echo $stats['max_points']; ?></h4>
                            <small>Highest Points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card text-center" style="background: var(--danger); color: white; border: none; border-radius: 15px;">
                        <div class="card-body">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <h4><?php echo $stats['min_points']; ?></h4>
                            <small>Lowest Points</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 mb-3">
                    <div class="card text-center" style="background: var(--secondary); color: white; border: none; border-radius: 15px;">
                        <div class="card-body">
                            <i class="fas fa-home fa-2x mb-2"></i>
                            <h4><?php echo mysqli_num_rows($houses_result); ?></h4>
                            <small>Houses</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                <div class="card-body">
                    <form method="get" action="" class="row">
                        <div class="col-md-4 mb-3">
                            <label for="section_filter" class="form-label">Filter by Section</label>
                            <select class="form-control" id="section_filter" name="section">
                                <option value="">All Sections</option>
                                <?php while ($section = mysqli_fetch_assoc($sections_result)): ?>
                                    <option value="<?php echo htmlspecialchars($section['year_section']); ?>" 
                                            <?php echo $filter_section == $section['year_section'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($section['year_section']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="house_filter" class="form-label">Filter by House</label>
                            <select class="form-control" id="house_filter" name="house">
                                <option value="">All Houses</option>
                                <?php while ($house = mysqli_fetch_assoc($houses_result)): ?>
                                    <option value="<?php echo htmlspecialchars($house['house_name']); ?>" 
                                            <?php echo $filter_house == $house['house_name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($house['house_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="view_house_points.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Section-wise Data -->
            <?php if (!empty($sections_data)): ?>
                <?php foreach ($sections_data as $section_name => $students): ?>
                    <div class="card mb-4" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                        <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                            <h4 class="mb-0">
                                <i class="fas fa-users"></i> 
                                <?php echo htmlspecialchars($section_name); ?>
                                <span class="badge bg-light text-dark ms-2"><?php echo count($students); ?> students</span>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Rank</th>
                                            <th>Regd No</th>
                                            <th>Name</th>
                                            <th>House Name</th>
                                            <th>Total Points</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($students as $student): 
                                            // Calculate performance indicator
                                            $points = $student['total_points'];
                                            $performance_class = '';
                                            $performance_text = '';
                                            
                                            if ($points >= 300) {
                                                $performance_class = 'bg-success';
                                                $performance_text = 'Excellent';
                                            } elseif ($points >= 200) {
                                                $performance_class = 'bg-info';
                                                $performance_text = 'Good';
                                            } elseif ($points >= 100) {
                                                $performance_class = 'bg-warning';
                                                $performance_text = 'Average';
                                            } else {
                                                $performance_class = 'bg-danger';
                                                $performance_text = 'Needs Improvement';
                                            }
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php if ($rank <= 3): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-medal"></i> <?php echo $rank; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo $rank; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($student['regd_no']); ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?php echo htmlspecialchars($student['house_name']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $student['total_points']; ?> pts
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $performance_class; ?>">
                                                        <?php echo $performance_text; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php 
                                            $rank++;
                                        endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No student data found</h5>
                        <p class="text-muted">No house points data available for the selected filters.</p>
                        <a href="house_points.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Student Data
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <style>
        .card {
            margin-bottom: 1.5rem;
        }
        
        .table th {
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .col-md-2 {
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .badge {
                font-size: 0.7rem;
            }
        }
    </style>
</body>
</html> 