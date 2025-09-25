<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php"; 
include "connect.php";

// Password protection for admin functions
$admin_password = 'b'; // Change this to your desired password
$is_admin = false;

// Check if user is logged in as admin
if (isset($_SESSION['house_admin_logged_in']) && $_SESSION['house_admin_logged_in']) {
    $is_admin = true;
}

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $password = $_POST['admin_password'] ?? '';
    if ($password === $admin_password) {
        $_SESSION['house_admin_logged_in'] = true;
        $is_admin = true;
        $message = "Admin access granted!";
        $message_type = "success";
    } else {
        $message = "Invalid password. Please try again.";
        $message_type = "error";
    }
}

// Handle admin logout
if (isset($_GET['logout'])) {
    unset($_SESSION['house_admin_logged_in']);
    $is_admin = false;
    $message = "Logged out successfully!";
    $message_type = "success";
}

// Handle form submission (only for admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $is_admin) {
    if ($_POST['action'] == 'add') {
        $regd_no = mysqli_real_escape_string($conn, $_POST['regd_no']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $year_section = mysqli_real_escape_string($conn, $_POST['year_section']);
        $house_name = mysqli_real_escape_string($conn, $_POST['house_name']);
        $total_points = (int)$_POST['total_points'];
        
        // Check if student already exists
        $check_sql = "SELECT id FROM house_points WHERE regd_no = '$regd_no'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $sql = "UPDATE house_points SET name = '$name', year_section = '$year_section', 
                    house_name = '$house_name', total_points = $total_points 
                    WHERE regd_no = '$regd_no'";
        } else {
            // Insert new record
            $sql = "INSERT INTO house_points (regd_no, name, year_section, house_name, total_points) 
                    VALUES ('$regd_no', '$name', '$year_section', '$house_name', $total_points)";
        }
        
        if (mysqli_query($conn, $sql)) {
            $message = "Student data saved successfully!";
            $message_type = "success";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "error";
        }
    } elseif ($_POST['action'] == 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM house_points WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $message = "Student deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting student: " . mysqli_error($conn);
            $message_type = "error";
        }
    }
}

// Include database helper
include './db_migration_helper.php';

// Get filter parameters
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
$filter_house = isset($_GET['house']) ? $_GET['house'] : '';

// Get students with house assignments
$students_with_houses = $db_helper->getStudentsWithHouses();

// Filter students based on criteria
$filtered_students = [];
foreach ($students_with_houses as $student) {
    $include = true;
    
    if ($filter_section) {
        $display_section = $student['year'] . '/4 ' . $student['branch'];
        if (!empty($student['section'])) {
            $display_section .= '-' . $student['section'];
        }
        if ($display_section !== $filter_section) {
            $include = false;
        }
    }
    
    if ($filter_house && $student['house_name'] !== $filter_house) {
        $include = false;
    }
    
    if ($include) {
        $filtered_students[] = $student;
    }
}

// Get unique sections and houses for filters
$classes = $db_helper->getAllClasses();
$houses = $db_helper->getHouses();
?>

<body>
    <!-- Top Bar -->
    
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Page Title -->
    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-trophy"></i> Student House Points Management</h2>
            <p>View and manage student house points data</p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Admin Login Section -->
            <?php if (!$is_admin): ?>
                <div class="card mb-4" style="background: var(--light-blue); border: none; border-radius: 15px;">
                    <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                        <h4 class="mb-0"><i class="fas fa-lock"></i> Admin Access Required</h4>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="text-primary mb-3">House Points Management</h5>
                                <p class="text-muted mb-0">To add, edit, or delete student house points data, you need admin access. Please enter the admin password below.</p>
                            </div>
                            <div class="col-md-4">
                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="admin_password" class="form-label">Admin Password</label>
                                        <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                                    </div>
                                    <button type="submit" name="admin_login" class="btn btn-primary w-100">
                                        <i class="fas fa-sign-in-alt"></i> Login as Admin
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Admin Controls -->
                <div class="card mb-4" style="background: var(--success); border: none; border-radius: 15px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-white mb-1"><i class="fas fa-user-shield"></i> Admin Mode Active</h5>
                                <small class="text-white-50">You have full access to manage house points data</small>
                            </div>
                            <a href="?logout=1" class="btn btn-outline-light">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Add Student Form -->
                <div class="card mb-4" style="background: var(--light-blue); border: none; border-radius: 15px;">
                    <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                        <h4 class="mb-0"><i class="fas fa-plus"></i> Add/Update Student House Points</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="add">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="regd_no" class="form-label">Registration Number *</label>
                                    <input type="text" class="form-control" id="regd_no" name="regd_no" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Student Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="year_section" class="form-label">Year & Section *</label>
                                    <select class="form-control" id="year_section" name="year_section" required>
                                        <option value="">Select Year & Section</option>
                                        <option value="2/4 CSIT-A">2/4 CSIT-A</option>
                                        <option value="2/4 CSIT-B">2/4 CSIT-B</option>
                                        <option value="2/4 CSD">2/4 CSD</option>
                                        <option value="3/4 CSIT">3/4 CSIT</option>
                                        <option value="3/4 CSD">3/4 CSD</option>
                                        <option value="4/4 CSD">4/4 CSD</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="house_name" class="form-label">House Name *</label>
                                    <select class="form-control" id="house_name" name="house_name" required>
                                        <option value="">Select House</option>
                                        <option value="Aakash">Aakash</option>
                                        <option value="Jal">Jal</option>
                                        <option value="Vayu">Vayu</option>
                                        <option value="Pruthvi">Pruthvi</option>
                                        <option value="Agni">Agni</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="total_points" class="form-label">Total Points *</label>
                                    <input type="number" class="form-control" id="total_points" name="total_points" min="0" required>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Save Student Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
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
                            <a href="house_points.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Student Data Table -->
            <div class="card mb-5" style="background: var(--white); border: none; border-radius: 15px; box-shadow: 0 4px 16px rgba(7,101,147,0.1);">
                <div class="card-header" style="background: var(--primary-blue); color: white; border-radius: 15px 15px 0 0;">
                    <h4 class="mb-0"><i class="fas fa-list"></i> Student House Points Data</h4>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Regd No</th>
                                        <th>Name</th>
                                        <th>Year & Section</th>
                                        <th>House Name</th>
                                        <th>Total Points</th>
                                        <?php if ($is_admin): ?>
                                            <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['regd_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($row['year_section']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?php echo htmlspecialchars($row['house_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <?php echo $row['total_points']; ?> pts
                                                </span>
                                            </td>
                                            <?php if ($is_admin): ?>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="editStudent('<?php echo $row['regd_no']; ?>', '<?php echo $row['name']; ?>', '<?php echo $row['year_section']; ?>', '<?php echo $row['house_name']; ?>', <?php echo $row['total_points']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="post" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this student?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No student data found</h5>
                            <p class="text-muted">
                                <?php if ($is_admin): ?>
                                    Add some students using the form above.
                                <?php else: ?>
                                    No house points data has been added yet.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <script>
        function editStudent(regdNo, name, yearSection, houseName, totalPoints) {
            document.getElementById('regd_no').value = regdNo;
            document.getElementById('name').value = name;
            document.getElementById('year_section').value = yearSection;
            document.getElementById('house_name').value = houseName;
            document.getElementById('total_points').value = totalPoints;
            
            // Scroll to form
            document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
    
    <style>
        .card {
            margin-bottom: 2rem;
        }
        
        .table th {
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.8rem;
        }
        
        .btn-sm {
            margin: 0 2px;
        }
        
        /* Ensure proper footer positioning */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
            padding: 40px 0;
        }
        
        .footer {
            margin-top: auto;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</body>
</html> 