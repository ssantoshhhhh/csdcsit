<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
include "./head.php"; 
include "connect.php";

// Define the house names with enhanced color schemes
$houses = [
    'Aakash' => [
        'name' => 'Aakash', 
        'color' => '#4A90E2', 
        'gradient' => 'linear-gradient(135deg, #4A90E2 0%, #357ABD 100%)',
        'light_color' => '#E3F2FD',
        'icon' => 'fas fa-cloud',
        'description' => 'Sky House - Reaching for the stars',
        'img' => 'img/house1.png'
    
    ],
    'Jal' => [
        'name' => 'Jal', 
        'color' => '#2196F3', 
        'gradient' => 'linear-gradient(135deg, #2196F3 0%, #1976D2 100%)',
        'light_color' => '#E1F5FE',
        'icon' => 'fas fa-water',
        'description' => 'Water House - Flowing with wisdom',
        'img' => 'img/house2.png'
    
    ],
    'Vayu' => [
        'name' => 'Vayu', 
        'color' => '#4CAF50', 
        'gradient' => 'linear-gradient(135deg, #4CAF50 0%, #388E3C 100%)',
        'light_color' => '#E8F5E8',
        'icon' => 'fas fa-wind',
        'description' => 'Wind House - Swift and free',
        'img' => 'img/house3.png'
    ],

    'PRUDHVI' => [
        'name' => 'PRUDHVI', 
        'color' => '#8D6E63', 
        'gradient' => 'linear-gradient(135deg, #8D6E63 0%, #6D4C41 100%)',
        'light_color' => '#EFEBE9',
        'icon' => 'fas fa-mountain',
        'description' => 'Earth House - Strong and steady',
        'img' => 'img/house4.png'    
    ],
    'Agni' => [
        'name' => 'Agni', 
        'color' => '#F44336', 
        'gradient' => 'linear-gradient(135deg, #F44336 0%, #D32F2F 100%)',
        'light_color' => '#FFEBEE',
        'icon' => 'fas fa-fire',
        'description' => 'Fire House - Burning with passion',
        'img' => 'img/house5.png'
   
    ]
];



// Get house statistics
$house_stats = [];
foreach ($houses as $house_key => $house_info) {
    $house_name = mysqli_real_escape_string($conn, $house_info['name']);
    $stats = ['student_count' => 0, 'total_points' => 0];

    // Find house ID (hid)
    $hid = null;
    $house_sql = "SELECT hid FROM houses WHERE name = '$house_name'";
    $house_result = mysqli_query($conn, $house_sql);
    if ($house_result && mysqli_num_rows($house_result) > 0) {
        $house_row = mysqli_fetch_assoc($house_result);
        $hid = $house_row['hid'];
    }

    if ($hid) {
        // Get student count
        $student_count_sql = "SELECT COUNT(*) as student_count FROM students WHERE hid = $hid";
        $student_count_result = mysqli_query($conn, $student_count_sql);
        if ($student_count_result) {
            $count_data = mysqli_fetch_assoc($student_count_result);
            $stats['student_count'] = $count_data['student_count'];
        }

        // Calculate total points from various sources
        $total_points = 0;

        // Points from participants
        $participants_sql = "SELECT SUM(p.points) as points FROM participants p JOIN students s ON p.student_id = s.student_id WHERE s.hid = $hid";
        $participants_result = mysqli_query($conn, $participants_sql);
        if ($participants_result) {
            $points_data = mysqli_fetch_assoc($participants_result);
            $total_points += (int)$points_data['points'];
        }

        // Points from winners
        $winners_sql = "SELECT SUM(w.points) as points FROM winners w JOIN students s ON w.student_id = s.student_id WHERE s.hid = $hid";
        $winners_result = mysqli_query($conn, $winners_sql);
        if ($winners_result) {
            $points_data = mysqli_fetch_assoc($winners_result);
            $total_points += (int)$points_data['points'];
        }

        // Points from organizers
        $organizers_sql = "SELECT SUM(o.points) as points FROM organizers o JOIN students s ON o.student_id = s.student_id WHERE s.hid = $hid";
        $organizers_result = mysqli_query($conn, $organizers_sql);
        if ($organizers_result) {
            $points_data = mysqli_fetch_assoc($organizers_result);
            $total_points += (int)$points_data['points'];
        }

        // Points from appreciations
        $appreciations_sql = "SELECT SUM(a.points) as points FROM appreciations a JOIN students s ON a.student_id = s.student_id WHERE s.hid = $hid";
        $appreciations_result = mysqli_query($conn, $appreciations_sql);
        if ($appreciations_result) {
            $points_data = mysqli_fetch_assoc($appreciations_result);
            $total_points += (int)$points_data['points'];
        }

        // Subtract penalties
        $penalties_sql = "SELECT SUM(p.points) as points FROM penalties p JOIN students s ON p.student_id = s.student_id WHERE s.hid = $hid";
        $penalties_result = mysqli_query($conn, $penalties_sql);
        if ($penalties_result && mysqli_num_rows($penalties_result) > 0) {
            $points_data = mysqli_fetch_assoc($penalties_result);
            $total_points -= (int)($points_data['points'] ?? 0);
        }

        $stats['total_points'] = $total_points;
    }

    $house_stats[$house_key] = $stats;
}
?>

<style>
/* Modern Stats Cards Styles */
.stats-container {
    padding: 0 15px;
    margin-bottom: 3rem;
}

.stats-grid {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    min-width: 200px;
    flex: 1;
    max-width: 240px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    border-color: #e0e0e0;
}

.stat-icon-container {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon-container i {
    font-size: 20px;
}

.stat-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: #9e9e9e;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.1;
}

/* House Cards */
.house-card {
    transition: all 0.3s ease !important;
}

.house-card:hover {
    transform: translateY(-8px) !important;
    box-shadow: 0 12px 32px rgba(0,0,0,0.15) !important;
}

.house-card:hover .house-overlay {
    opacity: 0.95 !important;
}

.house-link:hover .house-card {
    border-color: var(--primary-color) !important;
}

/* Modern Button Styling */
.btn-outline-primary {
    background: white !important;
    border: 1px solid #e9ecef !important;
    color: #6c757d !important;
    padding: 12px 24px !important;
    border-radius: 12px !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    font-size: 0.9rem !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06) !important;
    position: relative !important;
    overflow: hidden !important;
}

.btn-outline-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.5s ease;
}

.btn-outline-primary:hover::before {
    left: 100%;
}

.btn-outline-primary:hover {
    color: #667eea !important;
    border-color: #667eea !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .house-card {
        height: 260px !important;
    }
    
    .stats-grid {
        flex-direction: column;
        gap: 15px;
    }
    
    .stat-card {
        min-width: unset;
        max-width: unset;
        padding: 20px;
        gap: 14px;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-icon-container {
        width: 40px;
        height: 40px;
    }
    
    .stat-icon-container i {
        font-size: 18px;
    }
}

@media (max-width: 576px) {
    .house-card {
        height: 240px !important;
    }
    
    .house-card h4 {
        font-size: 1.3rem !important;
    }
    
    .house-card h6 {
        font-size: 0.9rem !important;
    }
    
    .stats-container {
        padding: 0 10px;
    }
    
    .stat-card {
        padding: 16px;
        gap: 12px;
    }
    
    .stat-value {
        font-size: 1.3rem;
    }
    
    .stat-icon-container {
        width: 36px;
        height: 36px;
    }
    
    .stat-icon-container i {
        font-size: 16px;
    }
}

/* Contributors Section */
.contributors-container {
    padding: 0 15px;
}

.contributors-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
}

.contributors-header {
    padding: 24px 28px;
    border-bottom: 1px solid #f0f0f0;
    background: #fafbfc;
}

.contributors-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.contributors-title i {
    color: #667eea;
    font-size: 1rem;
}

.contributors-content {
    padding: 28px;
}

/* Filter styling updates */
.form-label {
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    color: #6c757d !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 8px !important;
}

.form-select {
    border: 1px solid #e9ecef !important;
    border-radius: 12px !important;
    font-size: 0.9rem !important;
    background: #fafbfc !important;
    transition: all 0.3s ease !important;
    padding: 10px 16px !important;
}

.form-select:focus {
    border-color: #667eea !important;
    background: white !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

/* Table styling updates */
.table-responsive {
    border-radius: 12px !important;
    border: 1px solid #f0f0f0 !important;
    background: white !important;
}

.table th {
    background: #fafbfc !important;
    border: none !important;
    font-weight: 600 !important;
    color: #495057 !important;
    font-size: 0.85rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 16px 12px !important;
}

.table td {
    border: none !important;
    padding: 14px 12px !important;
    vertical-align: middle !important;
}

.btn-outline-primary.show-more-btn {
    background: #f8f9fa !important;
    border: 1px solid #e9ecef !important;
    color: #667eea !important;
    padding: 10px 20px !important;
    border-radius: 20px !important;
    font-size: 0.85rem !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.btn-outline-primary.show-more-btn:hover {
    background: #667eea !important;
    color: white !important;
    border-color: #667eea !important;
    transform: translateY(-1px) !important;
}
</style>

<body style="background: #f8f9fa; min-height: 100vh; font-family: 'Poppins', sans-serif; color: #333;">
    <!-- Main Header -->
    <?php include "nav.php"; ?>
    
    <!-- Main Content -->
    <div class="main-content" style="padding: 2rem 0 3rem 0;">
        <div class="container ">

            <!-- Houses Grid -->
            <div class="row p-2 justify-content-center" >
                <?php 
                $rank_counter = 1;
                foreach ($houses as $house_key => $house_info): 
                ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-3" style='width:260px'>
                        <a href="house_detail.php?house=<?php echo urlencode($house_key); ?>" 
                           class="text-decoration-none house-link">
                            <div class="house-card" 
                                 data-house="<?php echo $house_key; ?>"
                                 style="background: white; 
                                        border: 1px solid #e9ecef; 
                                        border-radius: 16px; 
                                        transition: all 0.3s ease;
                                        position: relative;
                                        overflow: hidden;
                                        height: 280px;
                                        cursor: pointer;
                                        ">
                                
                                <!-- Rank Badge -->
                                <div style="position: absolute; top: 12px; right: 12px; z-index: 3;">
                                    <span style="background: <?php echo $house_info['color']; ?>; 
                                                color: white; 
                                                padding: 4px 8px; 
                                                border-radius: 12px; 
                                                font-size: 0.75rem; 
                                                font-weight: 600;">
                                        #<?php echo $rank_counter; ?>
                                    </span>
                                </div>
                                
                                <!-- House Header -->
                                <div style="padding: 20px 20px 0 20px; text-align: left;">
                                    <!-- House Name -->
                                    <div style="margin-bottom: 8px;">
                                        <h4 style="color: <?php echo $house_info['color']; ?>; 
                                                   font-weight: 700; 
                                                   font-size: 1.5rem;
                                                   margin: 0;
                                                   line-height: 1.2;">
                                            <?php echo $house_info['name']; ?>
                                        </h4>
                                        <h6 style="color: <?php echo $house_info['color']; ?>; 
                                                   font-weight: 500; 
                                                   font-size: 1rem;
                                                   margin: 0;
                                                   opacity: 0.8;">
                                            House
                                        </h6>
                                    </div>
                                    
                                    <!-- House Description -->
                                    <p style="color: #6c757d; 
                                              font-size: 0.85rem;
                                              margin: 0 0 16px 0;
                                              line-height: 1.4;">
                                        <?php echo $house_info['description']; ?>
                                    </p>
                                </div>
                                
                                <!-- Points Section -->
                                <div style="padding: 0 20px; margin-bottom: 16px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <span style="color: #6c757d; font-size: 0.85rem; font-weight: 500;">Points</span>
                                        <span style="color: #212529; font-size: 1.25rem; font-weight: 700;">
                                            <?php echo number_format($house_stats[$house_key]['total_points']); ?>
                                        </span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div style="width: 100%; 
                                               height: 6px; 
                                               background: #e9ecef; 
                                               border-radius: 3px; 
                                               margin-top: 8px; 
                                               overflow: hidden;">
                                        <?php 
                                        // Calculate progress percentage (assuming max points for visualization)
                                        $max_points = max(array_column($house_stats, 'total_points'));
                                        $progress = $max_points > 0 ? ($house_stats[$house_key]['total_points'] / $max_points) * 100 : 0;
                                        ?>
                                        <div style="width: <?php echo $progress; ?>%; 
                                                   height: 100%; 
                                                   background: <?php echo $house_info['color']; ?>; 
                                                   border-radius: 3px;
                                                   transition: width 0.3s ease;"></div>
                                    </div>
                                </div>
                                
                                <!-- Bottom Section -->
                                <div style="position: absolute; 
                                           bottom: 0; 
                                           left: 0; 
                                           right: 0; 
                                           padding: 16px 20px; 
                                           background: <?php echo $house_info['light_color']; ?>; 
                                           border-top: 1px solid <?php echo $house_info['color']; ?>20;">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <i class="fas fa-users" style="color: <?php echo $house_info['color']; ?>; font-size: 0.9rem;"></i>
                                            <span style="color: #6c757d; font-size: 0.85rem; font-weight: 500;">
                                                <?php echo $house_stats[$house_key]['student_count']; ?> students
                                            </span>
                                        </div>
                                        <i class="fas fa-arrow-right" style="color: <?php echo $house_info['color']; ?>; font-size: 0.9rem;"></i>
                                    </div>
                                </div>
                                
                                <!-- Hover Effect -->
                                <div class="house-overlay" style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    right: 0;
                                    bottom: 0;
                                    background: <?php echo $house_info['gradient']; ?>;
                                    opacity: 0;
                                    transition: all 0.3s ease;
                                    border-radius: 15px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;">
                                    <div style="color: white; text-align: center;">
                                        <i class="fas fa-eye" style="font-size: 2rem; margin-bottom: 8px;"></i>
                                        <div style="font-size: 1rem; font-weight: 600;">View Details</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                <?php 
                    $rank_counter++;
                endforeach; 
                ?>
            </div>
<div class="text-center mb-5">
                <a href='events_overview.php' class="btn btn-outline-primary">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Explore Events
                </a>
            </div>
            <!-- Key Statistics Section -->
            <div class="stats-container mb-5">
                <?php
                // Calculate overall statistics
                $total_events = 0;
                $total_points = 0;
                $active_students = 0;
                $total_houses = count($houses);
                
                // Get total events
                $events_query = "SELECT COUNT(*) as total FROM events";
                $events_result = mysqli_query($conn, $events_query);
                if ($events_result) {
                    $events_data = mysqli_fetch_assoc($events_result);
                    $total_events = $events_data['total'];
                }
                
                // Get total points from all sources
                $points_sources = [
                    "SELECT SUM(points) as total FROM participants WHERE points > 0",
                    "SELECT SUM(points) as total FROM winners WHERE points > 0", 
                    "SELECT SUM(points) as total FROM organizers WHERE points > 0",
                    "SELECT SUM(points) as total FROM appreciations WHERE points > 0"
                ];
                
                foreach ($points_sources as $query) {
                    $result = mysqli_query($conn, $query);
                    if ($result) {
                        $data = mysqli_fetch_assoc($result);
                        $total_points += (int)$data['total'];
                    }
                }
                
                // Get active students count
                $students_query = "SELECT COUNT(*) as total FROM students WHERE is_alumni = 0";
                $students_result = mysqli_query($conn, $students_query);
                if ($students_result) {
                    $students_data = mysqli_fetch_assoc($students_result);
                    $active_students = $students_data['total'];
                }
                
                // Define stats with clean design
                $stats = [
                    [
                        'title' => 'Total Houses',
                        'value' => $total_houses,
                        'icon' => 'fas fa-home',
                        'icon_bg' => '#e3f2fd',
                        'icon_color' => '#1976d2'
                    ],
                    [
                        'title' => 'Total Events', 
                        'value' => $total_events,
                        'icon' => 'fas fa-calendar-alt',
                        'icon_bg' => '#e8f5e9',
                        'icon_color' => '#388e3c'
                    ],
                    [
                        'title' => 'Total Points',
                        'value' => number_format($total_points),
                        'icon' => 'fas fa-star',
                        'icon_bg' => '#fff3e0',
                        'icon_color' => '#f57c00'
                    ],
                    [
                        'title' => 'Active Students',
                        'value' => number_format($active_students),
                        'icon' => 'fas fa-users',
                        'icon_bg' => '#fce4ec',
                        'icon_color' => '#c2185b'
                    ]
                ];
                ?>
                
                <div class="stats-grid">
                    <?php foreach ($stats as $stat): ?>
                        <div class="stat-card">
                            <div class="stat-icon-container" style="background-color: <?php echo $stat['icon_bg']; ?>;">
                                <i class="<?php echo $stat['icon']; ?>" style="color: <?php echo $stat['icon_color']; ?>;"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-label"><?php echo $stat['title']; ?></div>
                                <div class="stat-value"><?php echo $stat['value']; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Contributors Section -->
            <div class="contributors-container mb-5">
                <div class="contributors-card">
                    <div class="contributors-header">
                        <h4 class="contributors-title">
                            <i class="fas fa-trophy"></i>
                            Top Contributors
                        </h4>
                    </div>
                    
                    <div class="contributors-content">
                            
                            <!-- Filters -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label" style="font-weight: 500; color: #6c757d;">Branch</label>
                                    <select class="form-select" style="width:150px; margin-left:10px;" id="branchFilter" onchange="filterContributors()">
                                        <option value="">All Branches</option>
                                        <option value="CSD">CSD</option>
                                        <option value="CSIT">CSIT</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-weight: 500; color: #6c757d;">Year</label>
                                    <select class="form-select" style="width:150px; margin-left:10px;" id="yearFilter" onchange="filterContributors()">
                                        <option value="">All Years</option>
                                        <option value="1">1st Year</option>
                                        <option value="2">2nd Year</option>
                                        <option value="3">3rd Year</option>
                                        <option value="4">4th Year</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="font-weight: 500; color: #6c757d;">House</label>
                                    <select class="form-select" style="width:150px; margin-left:10px;" id="houseFilter" onchange="filterContributors()">
                                        <option value="">All Houses</option>
                                        <?php foreach ($houses as $house_key => $house_info): ?>
                                            <option value="<?php echo $house_key; ?>"><?php echo $house_info['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Contributors Table -->
                            <div class="table-responsive" style='max-height: 500px; overflow-y: auto;'>
                                <table class="table table-hover">
                                    <thead style="background: #f8f9fa; position: sticky; top: 0; z-index: 10;">
                                        <tr>
                                            <th style="border: none; font-weight: 600; color: #495057;">Rank</th>
                                            <th style="border: none; font-weight: 600; color: #495057;">Name</th>
                                            <th style="border: none; font-weight: 600; color: #495057;">Branch</th>
                                            <th style="border: none; font-weight: 600; color: #495057;">Year</th>
                                            <th style="border: none; font-weight: 600; color: #495057;">House</th>
                                            <th style="border: none; font-weight: 600; color: #495057;">Points</th>
                                        </tr>
                                    </thead>
                                    <tbody id="contributorsTable">
                                        <!-- Contributors will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            
                        <!-- Show More Button -->
                        <div class="text-center mt-4">
                            <button class="btn btn-outline-primary show-more-btn" id="showMoreBtn" onclick="loadMoreContributors()" style="display: none;">
                                <i class="fas fa-plus me-2"></i>
                                Show More Contributors (<span id="remainingCount">0</span> more)
                            </button>
                        </div
                    </div>
                </div>
            </div>
            

        </div>
    </div>
    
    <!-- Footer -->
    <?php include "footer.php"; ?>
    
    <script>
        let currentOffset = 0;
        let currentLimit = 7;
        let totalContributors = 0;
        let isLoading = false;

        // Load contributors on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadContributors();
        });

        function filterContributors() {
            currentOffset = 0;
            loadContributors();
        }

        function loadContributors() {
            if (isLoading) return;
            isLoading = true;

            const branch = document.getElementById('branchFilter').value;
            const year = document.getElementById('yearFilter').value;
            const house = document.getElementById('houseFilter').value;

            const params = new URLSearchParams({
                action: 'get_contributors',
                offset: currentOffset,
                limit: currentLimit,
                branch: branch,
                year: year,
                house: house
            });

            fetch('get_contributors.php?' + params)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('contributorsTable');
                        
                        if (currentOffset === 0) {
                            tbody.innerHTML = '';
                        }
                        
                        data.contributors.forEach((contributor, index) => {
                            // Debug: log house names to see what's coming from database
                            console.log('Contributor house name:', contributor.house_name);
                            const row = createContributorRow(contributor, currentOffset + index + 1);
                            tbody.appendChild(row);
                        });
                        
                        totalContributors = data.total;
                        updateShowMoreButton();
                    }
                    isLoading = false;
                })
                .catch(error => {
                    console.error('Error loading contributors:', error);
                    isLoading = false;
                });
        }

        function loadMoreContributors() {
            currentOffset += currentLimit;
            currentLimit = 10; // Load 10 more after initial 7
            loadContributors();
        }

        function createContributorRow(contributor, rank) {
            const row = document.createElement('tr');
            row.style.borderBottom = '1px solid #e9ecef';
            row.style.cursor = 'pointer';
            row.classList.add('contributor-row');
            row.setAttribute('data-student-id', contributor.student_id);
            
            // Get house color with fallback mapping
            const houseColors = {
                'Aakash': '#4A90E2',
                'Jal': '#2196F3', 
                'Vayu': '#4CAF50',
                'PRUDHVI': '#8D6E63',
                'Agni': '#F44336',
                // Add potential database variations
                'Aakash House': '#4A90E2',
                'Jal House': '#2196F3',
                'Vayu House': '#4CAF50',
                'PRUDHVI House': '#8D6E63',
                'Agni House': '#F44336',
                'Alpha House': '#4A90E2',
                'Beta House': '#2196F3',
                'Gamma House': '#4CAF50',
                'Delta House': '#8D6E63',
                'Epsilon House': '#F44336',
                'Sky House': '#4A90E2',
                'Water House': '#2196F3',
                'Wind House': '#4CAF50',
                'Earth House': '#8D6E63',
                'Fire House': '#F44336'
            };
            
            // Try exact match first, then try to find partial match
            let houseColor = houseColors[contributor.house_name];
            if (!houseColor) {
                // Try to find a partial match
                const houseName = contributor.house_name.toLowerCase();
                if (houseName.includes('aakash') || houseName.includes('alpha') || houseName.includes('sky')) {
                    houseColor = '#4A90E2';
                } else if (houseName.includes('jal') || houseName.includes('beta') || houseName.includes('water')) {
                    houseColor = '#2196F3';
                } else if (houseName.includes('vayu') || houseName.includes('gamma') || houseName.includes('wind')) {
                    houseColor = '#4CAF50';
                } else if (houseName.includes('PRUDHVI') || houseName.includes('delta') || houseName.includes('earth')) {
                    houseColor = '#8D6E63';
                } else if (houseName.includes('agni') || houseName.includes('epsilon') || houseName.includes('fire')) {
                    houseColor = '#F44336';
                } else {
                    houseColor = '#6c757d'; // Default gray
                }
            }
            
            row.innerHTML = `
                <td style="border: none; padding: 12px 8px;">
                    <span class="badge ${rank <= 3 ? 'bg-warning' : 'bg-secondary'}" style="font-size: 0.9rem;">
                        ${rank}
                    </span>
                </td>
                <td style="border: none; padding: 12px 8px; font-weight: 500; color: #2c3e50;">
                    <div class="d-flex align-items-center">
                        ${contributor.name}
                        <i class="fas fa-chevron-down ms-2 expand-icon" style="font-size: 0.8rem; color: #6c757d; transition: transform 0.3s ease;"></i>
                    </div>
                </td>
                <td style="border: none; padding: 12px 8px; color: #6c757d;">
                    ${contributor.branch}
                </td>
                <td style="border: none; padding: 12px 8px; color: #6c757d;">
                    ${contributor.year}
                </td>
                <td style="border: none; ">
                    <span style="background: ${houseColor}; padding: 5px; border-radius:50px; color:white;">                        ${contributor.house_name}

                    </span>
                </td>
                <td style="border: none; padding: 12px 8px;">
                    <span style="font-weight: 600; color: #2c3e50; font-size: 1.1rem;">
                        ${contributor.total_points}
                    </span>
                </td>
            `;
            
            // Add click event for expansion
            row.addEventListener('click', function() {
                toggleContributorDetails(this, contributor.student_id);
            });
            
            return row;
        }

        function updateShowMoreButton() {
            const showMoreBtn = document.getElementById('showMoreBtn');
            const remainingCount = document.getElementById('remainingCount');
            const currentlyShown = currentOffset + currentLimit;
            
            if (currentlyShown < totalContributors) {
                const remaining = totalContributors - currentlyShown;
                remainingCount.textContent = remaining;
                showMoreBtn.style.display = 'inline-block';
            } else {
                showMoreBtn.style.display = 'none';
            }
        }

        function toggleContributorDetails(row, studentId) {
            const expandIcon = row.querySelector('.expand-icon');
            const nextRow = row.nextElementSibling;
            
            // Check if details row already exists
            if (nextRow && nextRow.classList.contains('details-row')) {
                // Toggle existing details
                if (nextRow.style.display === 'none') {
                    nextRow.style.display = 'table-row';
                    expandIcon.style.transform = 'rotate(180deg)';
                } else {
                    nextRow.style.display = 'none';
                    expandIcon.style.transform = 'rotate(0deg)';
                }
            } else {
                // Load and create details row
                loadContributorDetails(row, studentId);
                expandIcon.style.transform = 'rotate(180deg)';
            }
        }

        function loadContributorDetails(row, studentId) {
            // Show loading state
            const loadingRow = document.createElement('tr');
            loadingRow.classList.add('details-row');
            loadingRow.innerHTML = `
                <td colspan="6" style="border: none; padding: 20px; background: #f8f9fa;">
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Loading point details...
                    </div>
                </td>
            `;
            row.parentNode.insertBefore(loadingRow, row.nextSibling);

            // Fetch detailed points
            fetch(`get_contributors.php?action=get_contributor_details&student_id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadingRow.innerHTML = createDetailsContent(data.details);
                    } else {
                        loadingRow.innerHTML = `
                            <td colspan="6" style="border: none; padding: 20px; background: #f8f9fa;">
                                <div class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Error loading details
                                </div>
                            </td>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading contributor details:', error);
                    loadingRow.innerHTML = `
                        <td colspan="6" style="border: none; padding: 20px; background: #f8f9fa;">
                            <div class="text-center text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error loading details
                            </div>
                        </td>
                    `;
                });
        }

        function createDetailsContent(details) {
            const categories = [
                { key: 'participation', label: 'Participation Events', icon: 'fas fa-users', color: '#17a2b8' },
                { key: 'wins', label: 'Won Events', icon: 'fas fa-trophy', color: '#ffc107' },
                { key: 'organized', label: 'Organized Events', icon: 'fas fa-cogs', color: '#28a745' },
                { key: 'appreciations', label: 'Appreciations', icon: 'fas fa-star', color: '#fd7e14' },
                { key: 'penalties', label: 'Penalties', icon: 'fas fa-minus-circle', color: '#dc3545' }
            ];

            let content = `
                <td colspan="6" style="border: none; padding: 0; background: #f8f9fa;">
                    <div style="padding: 20px;">
                        <h6 style="color: #2c3e50; margin-bottom: 20px; font-weight: 600;">
                            <i class="fas fa-chart-pie me-2"></i>Point Breakdown
                        </h6>
                        <div class="row g-3">
            `;

            categories.forEach(category => {
                const categoryData = details[category.key] || { total_points: 0, events: [] };
                const hasEvents = categoryData.events && categoryData.events.length > 0;
                
                content += `
                    <div class="col-md-6">
                        <div class="card border-0 h-100" style="background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 35px; height: 35px; background: ${category.color}20;">
                                        <i class="${category.icon}" style="color: ${category.color}; font-size: 0.9rem;"></i>
                                    </div>
                                    <div>
                                        <h6 style="margin: 0; font-size: 0.9rem; color: #2c3e50;">${category.label}</h6>
                                        <div style="font-weight: 600; color: ${category.color}; font-size: 1.1rem;">
                                            ${category.key === 'penalties' ? '-' : ''}${Math.abs(categoryData.total_points)} points
                                        </div>
                                    </div>
                                </div>
                `;

                if (hasEvents) {
                    content += `
                        <div style="max-height: 120px; overflow-y: auto;">
                            <table class="table table-sm mb-0" style="font-size: 0.8rem;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="border: none; padding: 4px 8px; font-weight: 500;">Event</th>
                                        <th style="border: none; padding: 4px 8px; font-weight: 500; text-align: right;">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    categoryData.events.forEach(event => {
                        content += `
                            <tr>
                                <td style="border: none; padding: 4px 8px; color: #6c757d;">${event.event_title}</td>
                                <td style="border: none; padding: 4px 8px; text-align: right; font-weight: 500; color: ${category.color};">
                                    ${category.key === 'penalties' ? '-' : ''}${Math.abs(event.points)}
                                </td>
                            </tr>
                        `;
                    });
                    
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += `
                        <div class="text-center text-muted" style="font-size: 0.8rem; padding: 10px;">
                            No ${category.label.toLowerCase()} found
                        </div>
                    `;
                }

                content += `
                            </div>
                        </div>
                    </div>
                `;
            });

            content += `
                        </div>
                    </div>
                </td>
            `;

            return content;
        }
    </script>

    <style>
        /* Contributor row styles */
        .contributor-row:hover {
            background-color: #f8f9fa !important;
        }
        
        .contributor-row .expand-icon {
            transition: transform 0.3s ease;
        }
        
        .details-row {
            background-color: #f8f9fa !important;
        }
        
        .details-row td {
            border-top: 1px solid #dee2e6 !important;
        }
        
        /* Custom scrollbar for contributors table */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Custom scrollbar for event lists */
        .details-row .table tbody {
            max-height: 120px;
            overflow-y: auto;
        }
        
        .details-row .table tbody::-webkit-scrollbar {
            width: 4px;
        }
        
        .details-row .table tbody::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }
        
        .details-row .table tbody::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }
        
        .details-row .table tbody::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .house-link {
            display: block;
            text-decoration: none;
            height: 100%;
        }
        
        .house-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform: translateY(0);
            height: 220px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .house-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .house-card:hover .house-overlay {
            opacity: 1;
        }
        
        .house-card:hover .house-icon-bg {
            transform: scale(1.1) rotate(5deg);
        }
        
        .house-icon-bg {
            transition: all 0.3s ease;
        }
        
        .house-title {
            transition: all 0.3s ease;
        }
        
        .house-card:hover .house-title {
            transform: scale(1.05);
        }
        
        .house-stats {
            transition: all 0.3s ease;
        }
        
        .house-card:hover .house-stats {
            transform: scale(1.05);
        }
        
        .table th {
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.8rem;
        }
        
        /* Ensure proper footer positioning */
        .main-content {
            padding: 40px 0;
            min-height: 60vh;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .house-card {
                height: 200px;
            }
            
            .house-icon-bg {
                width: 50px !important;
                height: 50px !important;
            }
            
            .house-icon-bg i {
                font-size: 1.5rem !important;
            }
            
            .house-title {
                font-size: 1rem !important;
            }
            
            .house-description {
                font-size: 0.7rem !important;
                padding: 0 5px !important;
            }
            
            .stats-row {
                gap: 15px !important;
            }
            
            .stat-item {
                font-size: 0.65rem !important;
                max-width: 50px !important;
            }
        }
        
        @media (max-width: 576px) {
            .col-sm-6 {
                width: 50%;
            }
            
            .house-card {
                height: 180px;
            }
            
            .house-icon-bg {
                width: 40px !important;
                height: 40px !important;
            }
            
            .house-icon-bg i {
                font-size: 1.2rem !important;
            }
            
            .house-title {
                font-size: 0.9rem !important;
            }
            
            .house-description {
                font-size: 0.65rem !important;
                padding: 0 3px !important;
            }
            
            .stats-row {
                gap: 10px !important;
            }
            
            .stat-item {
                font-size: 0.6rem !important;
                max-width: 40px !important;
            }
        }
        
        /* Ensure equal height for all cards in a row */
        .row .col-lg-2,
        .row .col-md-4,
        .row .col-sm-6 {
            display: flex;
        }
        
        /* House-specific animations */
        .house-card[data-house="Aakash"]:hover {
            border-color: #4A90E2;
        }
        
        .house-card[data-house="Jal"]:hover {
            border-color: #2196F3;
        }
        
        .house-card[data-house="Vayu"]:hover {
            border-color: #4CAF50;
        }
        
        .house-card[data-house="PRUDHVI"]:hover {
            border-color: #8D6E63;
        }
        
        .house-card[data-house="Agni"]:hover {
            border-color: #F44336;
        }
    </style>
</body>
</html> 