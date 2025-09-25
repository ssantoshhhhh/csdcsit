<?php
session_start();
if (!isset($_SESSION['hod_logged_in']) || !$_SESSION['hod_logged_in']) {
    header('Location: login.php');
    exit();
}
include './connect.php';
include './db_migration_helper.php';

$section_key = $_GET['section'] ?? '';
if (empty($section_key)) {
    header('Location: hod_dashboard.php');
    exit();
}

// Parse section key
list($year, $branch, $section) = array_pad(explode('_', $section_key), 3, '');

// Get section data with students, skills, and attendance points
$query = "
    SELECT 
        s.student_id,
        s.name,
        s.email,
        sp.skills,
        (
            SELECT COUNT(*)
            FROM student_attendance sa 
            WHERE sa.student_id = s.student_id 
            AND sa.status = 'Present'
        ) as attendance_points
    FROM classes c
    LEFT JOIN students s ON s.class_id = c.class_id
    LEFT JOIN student_profile sp ON s.student_id = sp.student_id
    WHERE c.year = ? AND c.branch = ? AND c.section = ?
    ORDER BY s.name
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iss", $year, $branch, $section);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$students = [];
$all_skills = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Extract skills and add to all_skills array
    $skills = !empty($row['skills']) ? array_map('trim', explode(',', $row['skills'])) : [];
    foreach ($skills as $skill) {
        if (!empty($skill) && !in_array($skill, $all_skills)) {
            $all_skills[] = $skill;
        }
    }

    // Format registration number
    $reg_no = str_replace('@srkrec.edu.in', '', $row['email']);

    $students[] = [
        'student_id' => $row['student_id'],
        'name' => $row['name'],
        'reg_no' => $reg_no,
        'skills' => implode(', ', $skills),
        'attendance_points' => (int)$row['attendance_points']
    ];
}

// Sort skills alphabetically
sort($all_skills);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title><?php echo "$year/4 $branch-$section"; ?> - SRKR Engineering College</title>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0061f2 0%, #6900f2 100%);
            --secondary-gradient: linear-gradient(135deg, #0061f2 0%, #6900f2 100%);
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: #f8f9fc;
        }
        
        .page-header {
            background: var(--primary-gradient);
            padding: 2rem 0;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .controls-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            border: none;
            transition: var(--transition);
        }
        
        .controls-card:hover {
            transform: translateY(-5px);
        }
        
        .controls-header {
            background: var(--secondary-gradient);
            color: white;
            padding: 1.5rem;
            border-radius: 1rem 1rem 0 0;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .search-box input {
            padding: 1rem 1rem 1rem 3rem;
            border-radius: 2rem;
            border: 2px solid #e3e6f0;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .search-box input:focus {
            border-color: #6900f2;
            box-shadow: 0 0 0 0.25rem rgba(105, 0, 242, 0.25);
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6900f2;
            font-size: 1.2rem;
        }
        
        .skill-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 1rem;
        }
        
        .skill-filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            border: 2px solid #e3e6f0;
            background: white;
            color: #6900f2;
            font-size: 0.875rem;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .skill-filter-btn:hover {
            background: #f8f9fc;
            border-color: #6900f2;
        }
        
        .skill-filter-btn.active {
            background: #6900f2;
            color: white;
            border-color: #6900f2;
        }
        
        .students-card {
            background: white;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            border: none;
        }
        
        .students-table {
            margin: 0;
        }
        
        .students-table th {
            background: #f8f9fc;
            color: #5a5c69;
            font-weight: 600;
            border: none;
            padding: 1rem;
        }
        
        .students-table td {
            padding: 1rem;
            border-color: #f8f9fc;
            vertical-align: middle;
        }
        
        .students-table tbody tr {
            transition: var(--transition);
        }
        
        .students-table tbody tr:hover {
            background: #f8f9fc;
        }
        
        .skill-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 1rem;
            background: #e3e6f0;
            color: #5a5c69;
            font-size: 0.875rem;
            margin: 0.25rem;
            transition: var(--transition);
        }
        
        .skill-badge:hover {
            background: #6900f2;
            color: white;
        }
        
        .points-badge {
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            background: var(--primary-gradient);
            color: white;
            font-weight: 600;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            background: white;
            color: #6900f2;
            text-decoration: none;
            transition: var(--transition);
            margin-bottom: 1rem;
        }
        
        .back-btn:hover {
            transform: translateX(-5px);
            box-shadow: var(--card-shadow);
        }
        
        .sort-btn {
            background: none;
            border: none;
            color: #6900f2;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: 0.5rem;
            transition: var(--transition);
        }
        
        .sort-btn:hover {
            transform: scale(1.2);
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .controls-header {
                padding: 1rem;
            }
            
            .search-box input {
                padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            }
            
            .skill-filter-btn {
                padding: 0.35rem 0.75rem;
            }
        }
    </style>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="page-header">
        <div class="container">
            <a href="hod_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1>
                <i class="fas fa-users"></i> 
                <?php echo "$year/4 $branch" . ($section ? "-$section" : ""); ?> Students
            </h1>
        </div>
    </div>
    
    <div class="container">
        <!-- Search and Filter Controls -->
        <div class="controls-card">
            <div class="controls-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter"></i> Search and Filter Options
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="studentSearch" class="form-control" 
                                   placeholder="Search by name or registration number...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="skill-filters">
                            <?php foreach ($all_skills as $skill): ?>
                                <button class="skill-filter-btn" data-skill="<?php echo htmlspecialchars($skill); ?>">
                                    <?php echo htmlspecialchars($skill); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Students Table -->
        <div class="students-card">
            <div class="table-responsive">
                <table class="table students-table">
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Name</th>
                            <th>
                                Attendance Points
                                <button class="sort-btn" data-sort="points">
                                    <i class="fas fa-sort"></i>
                                </button>
                            </th>
                            <th>Skills</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr class="student-row" 
                                data-name="<?php echo strtolower(htmlspecialchars($student['name'])); ?>"
                                data-regno="<?php echo strtolower(htmlspecialchars($student['reg_no'])); ?>"
                                data-skills="<?php echo strtolower(htmlspecialchars($student['skills'])); ?>">
                                <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td data-points="<?php echo $student['attendance_points']; ?>">
                                    <span class="points-badge">
                                        <?php echo $student['attendance_points']; ?> pts
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $skills = array_filter(explode(',', $student['skills']));
                                    foreach ($skills as $skill) {
                                        echo '<span class="skill-badge">' . htmlspecialchars(trim($skill)) . '</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Skill filter handling
        const skillButtons = document.querySelectorAll('.skill-filter-btn');
        skillButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.toggle('active');
                filterStudents();
            });
        });
        
        // Search handling
        const searchInput = document.getElementById('studentSearch');
        searchInput.addEventListener('input', filterStudents);
        
        // Combined filter function
        function filterStudents() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeSkills = Array.from(document.querySelectorAll('.skill-filter-btn.active'))
                .map(btn => btn.getAttribute('data-skill').toLowerCase());
            
            const rows = document.querySelectorAll('.student-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const regno = row.getAttribute('data-regno');
                const skills = row.getAttribute('data-skills').split(',').map(s => s.trim().toLowerCase());
                
                // Search filter
                const matchesSearch = searchTerm === '' || 
                    name.includes(searchTerm) || 
                    regno.includes(searchTerm);
                
                // Skills filter
                const matchesSkills = activeSkills.length === 0 || 
                    activeSkills.every(skill => skills.includes(skill));
                
                // Show/hide row based on combined filters
                row.style.display = (matchesSearch && matchesSkills) ? '' : 'none';
            });
        }
        
        // Sorting functionality
        document.querySelector('.sort-btn').addEventListener('click', function() {
            const tbody = document.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                const aPoints = parseInt(a.querySelector('td[data-points]').getAttribute('data-points'));
                const bPoints = parseInt(b.querySelector('td[data-points]').getAttribute('data-points'));
                return this.classList.contains('asc') ? aPoints - bPoints : bPoints - aPoints;
            });
            
            this.classList.toggle('asc');
            const icon = this.querySelector('i');
            icon.className = this.classList.contains('asc') ? 'fas fa-sort-up' : 'fas fa-sort-down';
            
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        });
    });
    </script>
</body>
</html>