<?php
session_start();
include './connect.php';
include './db_migration_helper.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$db_helper = new DatabaseMigrationHelper($conn);

// Get all classes/sections
$classes = $db_helper->getAllClasses();

// Get section details with student counts
$sections_data = [];
foreach ($classes as $class_id => $display_name) {
    // Get student count for this class
    $student_count_query = "SELECT COUNT(*) as student_count FROM students WHERE class_id = ?";
    $stmt = mysqli_prepare($conn, $student_count_query);
    mysqli_stmt_bind_param($stmt, "i", $class_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student_count = mysqli_fetch_assoc($result)['student_count'];

    // Get class details
    $class_query = "SELECT * FROM classes WHERE class_id = ?";
    $stmt = mysqli_prepare($conn, $class_query);
    mysqli_stmt_bind_param($stmt, "i", $class_id);
    mysqli_stmt_execute($stmt);
    $class_result = mysqli_stmt_get_result($stmt);
    $class_data = mysqli_fetch_assoc($class_result);

    $sections_data[] = [
        'class_id' => $class_id,
        'display_name' => $display_name,
        'student_count' => $student_count,
        'year' => $class_data['year'],
        'branch' => $class_data['branch'],
        'section' => $class_data['section'],
        'semester' => $class_data['semester'],
        'academic_year' => $class_data['academic_year']
    ];
}

// Sort sections by year, then branch, then section
usort($sections_data, function($a, $b) {
    if ($a['year'] != $b['year']) {
        return $a['year'] - $b['year'];
    }
    if ($a['branch'] != $b['branch']) {
        return strcmp($a['branch'], $b['branch']);
    }
    return strcmp($a['section'], $b['section']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Sections House Points - SRKR Engineering College</title>
</head>
<body>
    <?php include "nav.php"; ?>

    <div class="page-title">
        <div class="container">
            <h2><i class="fas fa-trophy"></i> Sections House Points</h2>
            <p>View house points for all sections</p>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-graduation-cap" style="font-size: 2.5rem; color: var(--primary-blue); margin-bottom: 15px;"></i>
                            <h3 class="text-primary"><?php echo count($sections_data); ?></h3>
                            <p class="text-muted mb-0">Total Sections</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-user-graduate" style="font-size: 2.5rem; color: var(--success); margin-bottom: 15px;"></i>
                            <h3 class="text-success"><?php echo array_sum(array_column($sections_data, 'student_count')); ?></h3>
                            <p class="text-muted mb-0">Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-code-branch" style="font-size: 2.5rem; color: var(--info); margin-bottom: 15px;"></i>
                            <h3 class="text-info"><?php echo count(array_unique(array_column($sections_data, 'branch'))); ?></h3>
                            <p class="text-muted mb-0">Branches</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px;">
                        <div class="card-body p-4">
                            <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: var(--warning); margin-bottom: 15px;"></i>
                            <h3 class="text-warning"><?php echo count(array_unique(array_column($sections_data, 'academic_year'))); ?></h3>
                            <p class="text-muted mb-0">Academic Years</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-12">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search sections by branch, year, or section name..." style="border-radius: 25px; padding: 10px 20px; border: 2px solid var(--primary-blue); font-size: 1rem;">
                </div>
            </div>

            <!-- Sections Grid -->
            <div class="row" id="sectionsGrid">
                <?php foreach ($sections_data as $section): ?>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 mb-4 section-item" data-branch="<?php echo htmlspecialchars(strtolower($section['branch'])); ?>" data-year="<?php echo htmlspecialchars($section['year']); ?>" data-section="<?php echo htmlspecialchars(strtolower($section['section'])); ?>">
                        <div class="card h-100 section-card" style="border: none; box-shadow: 0 4px 16px rgba(7,101,147,0.1); border-radius: 15px; transition: all 0.3s ease; cursor: pointer;"
                             onclick="window.location.href='section_house_points_detail.php?class_id=<?php echo $section['class_id']; ?>'">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="text-center mb-3">
                                    <div class="section-icon mb-3">
                                        <i class="fas fa-trophy" style="font-size: 3rem; color: var(--primary-blue);"></i>
                                    </div>
                                    <h5 class="card-title" style="color: var(--primary-blue); font-weight: 600; margin-bottom: 10px;">
                                        <?php echo htmlspecialchars($section['display_name']); ?>
                                    </h5>
                                </div>

                                <div class="section-details mb-3">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="detail-item">
                                                <i class="fas fa-user-graduate text-success"></i>
                                                <div class="detail-value"><?php echo $section['student_count']; ?></div>
                                                <div class="detail-label">Students</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="detail-item">
                                                <i class="fas fa-calendar text-info"></i>
                                                <div class="detail-value"><?php echo $section['semester']; ?></div>
                                                <div class="detail-label">Semester</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-info mt-auto">
                                    <div class="info-row">
                                        <i class="fas fa-code-branch"></i>
                                        <span><strong>Branch:</strong> <?php echo htmlspecialchars(strtoupper($section['branch'])); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><strong>Academic Year:</strong> <?php echo htmlspecialchars($section['academic_year']); ?></span>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <span class="badge bg-primary" style="border-radius: 20px; padding: 8px 16px;">
                                        <i class="fas fa-eye"></i> View House Points
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($sections_data)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users" style="font-size: 4rem; color: var(--gray-medium); margin-bottom: 20px;"></i>
                    <h4 class="text-muted">No sections found</h4>
                    <p class="text-muted">Please contact the administrator to set up sections.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <style>
        .section-card {
            transition: all 0.3s ease;
        }

        .section-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 32px rgba(7,101,147,0.2) !important;
        }

        .detail-item {
            padding: 10px;
        }

        .detail-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-blue);
            margin: 5px 0;
        }

        .detail-label {
            font-size: 0.85rem;
            color: var(--gray-medium);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .info-row i {
            width: 20px;
            margin-right: 10px;
            color: var(--primary-blue);
        }

        .section-icon {
            flex-shrink: 0;
        }

        .card-title {
            flex-shrink: 0;
        }

        .section-details {
            flex-grow: 1;
        }

        .section-info {
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .section-card {
                margin-bottom: 20px;
            }

            .card-body {
                padding: 20px 15px;
            }

            .section-icon i {
                font-size: 2.5rem !important;
            }

            .card-title {
                font-size: 1.1rem !important;
                margin-bottom: 15px !important;
            }

            .detail-value {
                font-size: 1.3rem;
            }

            .info-row {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 15px 10px;
            }

            .section-icon i {
                font-size: 2rem !important;
            }

            .card-title {
                font-size: 1rem !important;
            }

            .detail-value {
                font-size: 1.2rem;
            }
        }
    </style>
</body>
</html>
