<?php
session_start();
include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Pagination settings
$events_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $events_per_page;

// Get total count of events
$count_query = "SELECT COUNT(*) as total FROM events";
$count_result = mysqli_query($conn, $count_query);
$total_events = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_events / $events_per_page);

// Fetch events with pagination
$events = [];
$query = "SELECT event_id, title, event_date, venue, description, image_path, start_time, end_time 
          FROM events 
          ORDER BY event_date DESC 
          LIMIT $events_per_page OFFSET $offset";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "./head.php"; ?>
    <title>Events Overview - SRKR Engineering College</title>
    <style>
        /* Ensure consistent font family with nav.php */
        body {
            background: #f8f9fa;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        
        /* Ensure navbar styling consistency */
        .navbar {
            background-color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 1rem;
            margin-top: 0.5rem;
            color: #5a67d8;
            font-weight: 400;
        }

        .back-nav {
            margin-bottom: 2rem;
            margin-top: 1rem;
        }

        .back-btn {
            background: white;
            border: 1px solid #e9ecef;
            color: #6c757d;
            padding: 12px 20px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .back-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .back-btn:hover::before {
            left: 100%;
        }

        .back-btn:hover {
            color: #667eea;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
        }

        .back-btn i {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(-2px);
        }

        .main-content {
            padding: 0 0 3rem 0;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }

        .event-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            border-color: #e0e0e0;
        }

        /* Event Header */
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 16px 20px 0 20px;
            position: relative;
            z-index: 2;
        }

        .event-date-badge {
            background: white;
            border-radius: 12px;
            padding: 8px 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 60px;
        }

        .event-month {
            font-size: 0.7rem;
            font-weight: 600;
            color: #667eea;
            letter-spacing: 0.5px;
        }

        .event-day {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1;
            margin-top: 2px;
        }

        .event-status-badge {
            margin-top: 4px;
        }

        .status-upcoming {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background: #e3f2fd;
            color: #1565c0;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Event Image */
        .event-image-container {
            height: 160px;
            margin: 16px 20px;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .event-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-image-placeholder {
            height: 100%;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .placeholder-icon {
            width: 48px;
            height: 48px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .placeholder-icon i {
            font-size: 20px;
            color: #667eea;
        }

        /* Event Content */
        .event-content {
            padding: 0 20px 20px 20px;
        }

        .event-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .event-meta-grid {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .meta-item i {
            width: 14px;
            color: #667eea;
            font-size: 0.75rem;
        }
        .event-description {
            color: #6c757d;
            line-height: 1.5;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }

        .event-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .view-details-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            color: #667eea;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .view-details-btn:hover {
            background: #667eea;
            color: white;
            transform: translateX(2px);
            border-color: #667eea;
        }

        .view-details-btn i {
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }

        .view-details-btn:hover i {
            transform: translateX(2px);
        }

        .pagination-wrapper {
            margin-top: 3rem;
            display: flex;
            justify-content: center;
        }

        .pagination {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }

        .pagination .page-link {
            border: none;
            color: #333;
            font-weight: 500;
            padding: 10px 15px;
            background: #fff;
            font-size: 14px;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-color: #667eea;
        }

        .pagination .page-link:hover {
            background: #f0f4ff;
            color: #667eea;
            border-color: #667eea;
        }

        .pagination .page-item.active .page-link:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            color: #fff;
        }

        .no-events {
            text-align: center;
            padding: 4rem 2rem;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .no-events i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .events-count {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #4c51bf;
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 1rem;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(168, 237, 234, 0.3);
        }

        /* Stats Cards Styles */
        .stats-container {
            padding: 0 15px;
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

        /* Filters Section */
        .filters-container {
            margin-bottom: 2rem;
        }

        .filters-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafbfc;
        }

        .filters-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filters-title i {
            color: #667eea;
            font-size: 0.9rem;
        }

        .clear-filters-btn {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6c757d;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .clear-filters-btn:hover {
            background: #e9ecef;
            color: #495057;
        }

        .filters-content {
            padding: 24px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .search-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 40px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            font-size: 0.9rem;
            background: #fafbfc;
            transition: all 0.3s ease;
            outline: none;
        }

        .search-input:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            color: #9e9e9e;
            font-size: 0.8rem;
            pointer-events: none;
        }

        .clear-search-btn {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: #9e9e9e;
            padding: 4px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .clear-search-btn:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .select-container {
            position: relative;
        }

        .filter-select {
            width: 100%;
            padding: 12px 40px 12px 16px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            font-size: 0.9rem;
            background: #fafbfc;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
            appearance: none;
        }

        .filter-select:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .select-arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9e9e9e;
            font-size: 0.7rem;
            pointer-events: none;
            transition: transform 0.3s ease;
        }

        .filter-select:focus + .select-arrow {
            transform: translateY(-50%) rotate(180deg);
        }

        .results-count {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .results-count span:first-child {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            line-height: 1;
            text-align: center;
        }

        .results-text {
            font-size: 0.7rem;
            color: #9e9e9e;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Enhanced Pagination Styles */
        .pagination-wrapper {
            margin-top: 3rem;
            display: flex;
            justify-content: center;
        }

        .pagination {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .pagination .page-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 16px;
            background: #fff;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .pagination .page-item.active .page-link {
            background: #667eea;
            color: #fff;
            border-color: #667eea;
        }

        .pagination .page-link:hover {
            background: #f8f9fa;
            color: #667eea;
            border-color: #667eea;
        }

        .pagination .page-item.active .page-link:hover {
            background: #5a67d8;
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            color: #9e9e9e;
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .pagination .page-item:first-child .page-link {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .pagination .page-item:last-child .page-link {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .no-results {
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border-radius: 16px;
            border: 1px solid #f0f0f0;
        }

        .no-results i {
            font-size: 3rem;
            color: #e9ecef;
            margin-bottom: 1rem;
        }

        .no-results h4 {
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .no-results p {
            color: #9e9e9e;
            margin: 0;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .event-image-container {
                height: 140px;
                margin: 12px 16px;
            }
            
            .event-header {
                padding: 12px 16px 0 16px;
            }
            
            .event-content {
                padding: 0 16px 16px 16px;
            }
            
            .event-date-badge {
                padding: 6px 10px;
                min-width: 50px;
            }
            
            .event-day {
                font-size: 1rem;
            }
            
            .event-month {
                font-size: 0.65rem;
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

            .filters-header {
                padding: 16px 20px;
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .filters-content {
                padding: 20px;
            }

            .results-count {
                margin-top: 8px;
            }
        }
        
        @media (max-width: 480px) {
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
    </style>
</head>
<body>
    <?php include "nav.php"; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="back-nav">
                <a href="houses_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Houses Dashboard
                </a>
            </div>

            <!-- Event Statistics Section -->
            <div class="stats-container mb-5">
                <?php
                // Calculate event statistics
                $total_events_count = 0;
                $upcoming_events = 0;
                $past_events = 0;
                $total_participants = 0;
                $total_winners = 0;
                $total_organizers = 0;
                
                // Get total events count
                $events_query = "SELECT COUNT(*) as total FROM events";
                $events_result = mysqli_query($conn, $events_query);
                if ($events_result) {
                    $events_data = mysqli_fetch_assoc($events_result);
                    $total_events_count = $events_data['total'];
                }
                
                // Get upcoming and past events
                $today = date('Y-m-d');
                $upcoming_query = "SELECT COUNT(*) as total FROM events WHERE event_date >= '$today'";
                $upcoming_result = mysqli_query($conn, $upcoming_query);
                if ($upcoming_result) {
                    $upcoming_data = mysqli_fetch_assoc($upcoming_result);
                    $upcoming_events = $upcoming_data['total'];
                }
                
                $past_query = "SELECT COUNT(*) as total FROM events WHERE event_date < '$today'";
                $past_result = mysqli_query($conn, $past_query);
                if ($past_result) {
                    $past_data = mysqli_fetch_assoc($past_result);
                    $past_events = $past_data['total'];
                }
                
                // Get total participants
                $participants_query = "SELECT COUNT(*) as total FROM participants";
                $participants_result = mysqli_query($conn, $participants_query);
                if ($participants_result) {
                    $participants_data = mysqli_fetch_assoc($participants_result);
                    $total_participants = $participants_data['total'];
                }
                
                // Get total winners
                $winners_query = "SELECT COUNT(*) as total FROM winners";
                $winners_result = mysqli_query($conn, $winners_query);
                if ($winners_result) {
                    $winners_data = mysqli_fetch_assoc($winners_result);
                    $total_winners = $winners_data['total'];
                }
                
                // Get total organizers
                $organizers_query = "SELECT COUNT(*) as total FROM organizers";
                $organizers_result = mysqli_query($conn, $organizers_query);
                if ($organizers_result) {
                    $organizers_data = mysqli_fetch_assoc($organizers_result);
                    $total_organizers = $organizers_data['total'];
                }
                
                // Define stats with clean design
                $stats = [
                    [
                        'title' => 'Total Events',
                        'value' => $total_events_count,
                        'icon' => 'fas fa-calendar-alt',
                        'icon_bg' => '#e3f2fd',
                        'icon_color' => '#1976d2'
                    ],
                    [
                        'title' => 'Upcoming Events', 
                        'value' => $upcoming_events,
                        'icon' => 'fas fa-clock',
                        'icon_bg' => '#e8f5e9',
                        'icon_color' => '#388e3c'
                    ],
                    [
                        'title' => 'Total Participants',
                        'value' => number_format($total_participants),
                        'icon' => 'fas fa-users',
                        'icon_bg' => '#fff3e0',
                        'icon_color' => '#f57c00'
                    ],
                    [
                        'title' => 'Total Winners',
                        'value' => number_format($total_winners),
                        'icon' => 'fas fa-trophy',
                        'icon_bg' => '#fce4ec',
                        'icon_color' => '#c2185b'
                    ],
                    [
                        'title' => 'Total Organizers',
                        'value' => number_format($total_organizers),
                        'icon' => 'fas fa-cogs',
                        'icon_bg' => '#f3e5f5',
                        'icon_color' => '#7b1fa2'
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

            <!-- Filters Section -->
            <div class="filters-container mb-4">
                <div class="filters-card">
                    <div class="filters-header">
                        <h5 class="filters-title">
                            <i class="fas fa-filter"></i>
                            Filter Events
                        </h5>
                        <button class="clear-filters-btn" onclick="clearAllFilters()">
                            <i class="fas fa-times"></i>
                            Clear All
                        </button>
                    </div>
                    
                    <div class="filters-content">
                        <div class="row g-3">
                            <!-- Search Filter -->
                            <div class="col-lg-4 col-md-6">
                                <div class="filter-group">
                                    <label class="filter-label">Search Events</label>
                                    <div class="search-input-container">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" id="searchInput" class="search-input" placeholder="Search by event name...">
                                        <button class="clear-search-btn" onclick="clearSearch()" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- House Filter -->
                            <div class="col-lg-3 col-md-6">
                                <div class="filter-group">
                                    <label class="filter-label">Organizing House</label>
                                    <div class="select-container">
                                        <select id="houseFilter" class="filter-select">
                                            <option value="">All Houses</option>
                                            <option value="Aakash">Aakash House</option>
                                            <option value="Jal">Jal House</option>
                                            <option value="Vayu">Vayu House</option>
                                            <option value="PRUDHVI">PRUDHVI House</option>
                                            <option value="Agni">Agni House</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="col-lg-3 col-md-6">
                                <div class="filter-group">
                                    <label class="filter-label">Event Status</label>
                                    <div class="select-container">
                                        <select id="statusFilter" class="filter-select">
                                            <option value="">All Events</option>
                                            <option value="upcoming">Upcoming</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Results Count -->
                            <div class="col-lg-2 col-md-6">
                                <div class="filter-group">
                                    <label class="filter-label">Results</label>
                                    <div class="results-count">
                                        <span id="resultsCount">1-<?php echo min(9, count($events)); ?> of <?php echo count($events); ?></span>
                                        <span class="results-text">events</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($events)): ?>
                <div class="events-grid" id="eventsGrid">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card" onclick="window.location.href='event_detail.php?event_id=<?php echo $event['event_id']; ?>'">
                            <!-- Event Header -->
                            <div class="event-header">
                                <div class="event-date-badge">
                                    <div class="event-month"><?php echo strtoupper(date('M', strtotime($event['event_date']))); ?></div>
                                    <div class="event-day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                </div>
                                <div class="event-status-badge">
                                    <?php 
                                    $event_date = strtotime($event['event_date']);
                                    $today = strtotime(date('Y-m-d'));
                                    if ($event_date >= $today) {
                                        echo '<span class="status-upcoming">Upcoming</span>';
                                    } else {
                                        echo '<span class="status-completed">Completed</span>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Event Image -->
                            <div class="event-image-container">
                                <?php if (!empty($event['image_path']) && file_exists($event['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                                <?php else: ?>
                                    <div class="event-image-placeholder">
                                        <div class="placeholder-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Event Content -->
                            <div class="event-content">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                                
                                <div class="event-meta-grid">
                                    <?php if (!empty($event['start_time'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo date('g:i A', strtotime($event['start_time'])); ?><?php if (!empty($event['end_time'])): ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?><?php endif; ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($event['venue'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($event['venue']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="event-description">
                                    <?php 
                                    $description = htmlspecialchars($event['description']);
                                    echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                    ?>
                                </p>
                                
                                <div class="event-footer">
                                    <a href="event_detail.php?event_id=<?php echo $event['event_id']; ?>" class="view-details-btn">
                                        <span>View Details</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Dynamic Pagination -->
                <div id="paginationContainer" class="pagination-wrapper">
                    <nav aria-label="Events pagination">
                        <ul class="pagination" id="paginationList">
                            <!-- Pagination will be generated by JavaScript -->
                        </ul>
                    </nav>
                </div>

                <!-- Original PHP Pagination (hidden) -->
                <div style="display: none;">
                <?php if ($total_pages > 1): ?>
                <div class="pagination-wrapper">
                    <nav aria-label="Events pagination">
                        <ul class="pagination">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-events">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Events Found</h3>
                    <p class="text-muted">There are currently no events to display. Check back later for updates!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include "footer.php"; ?>

    <script>
        // Store all events data for filtering
        const allEvents = <?php echo json_encode($events); ?>;
        let filteredEvents = [...allEvents];
        
        // Pagination settings
        const eventsPerPage = 9;
        let currentPage = 1;
        let totalPages = 1;

        // DOM elements
        const searchInput = document.getElementById('searchInput');
        const houseFilter = document.getElementById('houseFilter');
        const statusFilter = document.getElementById('statusFilter');
        const eventsGrid = document.getElementById('eventsGrid');
        const resultsCount = document.getElementById('resultsCount');
        const clearSearchBtn = document.querySelector('.clear-search-btn');

        // Event listeners
        searchInput.addEventListener('input', handleSearch);
        houseFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);

        function handleSearch() {
            const searchTerm = searchInput.value.trim();
            
            // Show/hide clear search button
            if (searchTerm) {
                clearSearchBtn.style.display = 'block';
            } else {
                clearSearchBtn.style.display = 'none';
            }
            
            applyFilters();
        }

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedHouse = houseFilter.value;
            const selectedStatus = statusFilter.value;
            const today = new Date().toISOString().split('T')[0];

            filteredEvents = allEvents.filter(event => {
                // Search filter
                const matchesSearch = !searchTerm || 
                    event.title.toLowerCase().includes(searchTerm) ||
                    event.description.toLowerCase().includes(searchTerm);

                // House filter (you may need to add house field to events table)
                const matchesHouse = !selectedHouse; // For now, show all since we don't have house data

                // Status filter
                let matchesStatus = true;
                if (selectedStatus) {
                    const eventDate = event.event_date;
                    const isUpcoming = eventDate >= today;
                    
                    if (selectedStatus === 'upcoming' && !isUpcoming) {
                        matchesStatus = false;
                    } else if (selectedStatus === 'completed' && isUpcoming) {
                        matchesStatus = false;
                    }
                }

                return matchesSearch && matchesHouse && matchesStatus;
            });

            // Reset to first page when filters change
            currentPage = 1;
            updatePagination();
            renderEvents();
            updateResultsCount();
        }

        function renderEvents() {
            if (filteredEvents.length === 0) {
                eventsGrid.innerHTML = `
                    <div class="no-results" style="grid-column: 1 / -1;">
                        <i class="fas fa-search"></i>
                        <h4>No Events Found</h4>
                        <p>Try adjusting your filters or search terms</p>
                    </div>
                `;
                document.getElementById('paginationContainer').style.display = 'none';
                return;
            }

            // Calculate pagination
            const startIndex = (currentPage - 1) * eventsPerPage;
            const endIndex = startIndex + eventsPerPage;
            const paginatedEvents = filteredEvents.slice(startIndex, endIndex);

            eventsGrid.innerHTML = paginatedEvents.map(event => {
                const eventDate = new Date(event.event_date);
                const today = new Date();
                const isUpcoming = eventDate >= today;
                
                const month = eventDate.toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
                const day = eventDate.getDate().toString().padStart(2, '0');
                
                const statusBadge = isUpcoming 
                    ? '<span class="status-upcoming">Upcoming</span>'
                    : '<span class="status-completed">Completed</span>';

                const startTime = event.start_time ? new Date('2000-01-01 ' + event.start_time).toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit', 
                    hour12: true 
                }) : '';

                const endTime = event.end_time ? new Date('2000-01-01 ' + event.end_time).toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit', 
                    hour12: true 
                }) : '';

                const timeDisplay = startTime ? `${startTime}${endTime ? ' - ' + endTime : ''}` : '';

                const description = event.description.length > 100 
                    ? event.description.substring(0, 100) + '...' 
                    : event.description;

                const imageHtml = event.image_path 
                    ? `<img src="${event.image_path}" alt="${event.title}" class="event-image">`
                    : `<div class="event-image-placeholder">
                         <div class="placeholder-icon">
                             <i class="fas fa-calendar-alt"></i>
                         </div>
                       </div>`;

                return `
                    <div class="event-card" onclick="window.location.href='event_detail.php?event_id=${event.event_id}'">
                        <div class="event-header">
                            <div class="event-date-badge">
                                <div class="event-month">${month}</div>
                                <div class="event-day">${day}</div>
                            </div>
                            <div class="event-status-badge">
                                ${statusBadge}
                            </div>
                        </div>

                        <div class="event-image-container">
                            ${imageHtml}
                        </div>
                        
                        <div class="event-content">
                            <h3 class="event-title">${event.title}</h3>
                            
                            <div class="event-meta-grid">
                                ${timeDisplay ? `
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span>${timeDisplay}</span>
                                </div>
                                ` : ''}
                                
                                ${event.venue ? `
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>${event.venue}</span>
                                </div>
                                ` : ''}
                            </div>
                            
                            <p class="event-description">${description}</p>
                            
                            <div class="event-footer">
                                <a href="event_detail.php?event_id=${event.event_id}" class="view-details-btn">
                                    <span>View Details</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Show pagination if there are results
            document.getElementById('paginationContainer').style.display = filteredEvents.length > eventsPerPage ? 'flex' : 'none';
        }

        function updatePagination() {
            totalPages = Math.ceil(filteredEvents.length / eventsPerPage);
            const paginationList = document.getElementById('paginationList');
            
            if (totalPages <= 1) {
                paginationList.innerHTML = '';
                return;
            }

            let paginationHTML = '';

            // Previous button
            if (currentPage > 1) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                `;
            }

            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            // First page and ellipsis
            if (startPage > 1) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(1); return false;">1</a>
                    </li>
                `;
                if (startPage > 2) {
                    paginationHTML += `
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    `;
                }
            }

            // Page numbers around current page
            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                    </li>
                `;
            }

            // Last page and ellipsis
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHTML += `
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    `;
                }
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>
                    </li>
                `;
            }

            // Next button
            if (currentPage < totalPages) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                `;
            }

            paginationList.innerHTML = paginationHTML;
        }

        function goToPage(page) {
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                renderEvents();
                updatePagination();
                
                // Scroll to top of events grid
                document.getElementById('eventsGrid').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
        }

        function updateResultsCount() {
            const startResult = filteredEvents.length === 0 ? 0 : (currentPage - 1) * eventsPerPage + 1;
            const endResult = Math.min(currentPage * eventsPerPage, filteredEvents.length);
            
            if (filteredEvents.length === 0) {
                resultsCount.textContent = '0';
            } else {
                resultsCount.textContent = `${startResult}-${endResult} of ${filteredEvents.length}`;
            }
        }

        function clearSearch() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            applyFilters();
        }

        function clearAllFilters() {
            searchInput.value = '';
            houseFilter.value = '';
            statusFilter.value = '';
            clearSearchBtn.style.display = 'none';
            currentPage = 1;
            applyFilters();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updatePagination();
            renderEvents();
            updateResultsCount();
        });
    </script>
</body>
</html>