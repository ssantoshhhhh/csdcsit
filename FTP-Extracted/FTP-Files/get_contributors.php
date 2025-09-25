<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "connect.php";

header('Content-Type: application/json');

if (!isset($_GET['action'])) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit;
}

$action = $_GET['action'];

if ($action === 'get_contributor_details') {
    handleContributorDetails();
    exit;
} elseif ($action !== 'get_contributors') {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

// Get parameters
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 7;
$branch_filter = isset($_GET['branch']) ? mysqli_real_escape_string($conn, $_GET['branch']) : '';
$year_filter = isset($_GET['year']) ? mysqli_real_escape_string($conn, $_GET['year']) : '';
$house_filter = isset($_GET['house']) ? mysqli_real_escape_string($conn, $_GET['house']) : '';

// House mapping for database lookup
$house_mapping = [
    'Aakash' => ['Alpha House', 'Aakash House', 'Sky House', 'Aakash'],
    'Jal' => ['Beta House', 'Jal House', 'Water House', 'Jal'],
    'Vayu' => ['Gamma House', 'Vayu House', 'Wind House', 'Vayu'],
    'Pruthvi' => ['Delta House', 'Pruthvi House', 'Earth House', 'Pruthvi'],
    'Agni' => ['Epsilon House', 'Agni House', 'Fire House', 'Agni']
];

try {
    // Build the main query to get contributors with their total points
    $base_query = "
        SELECT 
            s.student_id,
            s.name,
            s.branch,
            c.year,
            h.name as house_name,
            COALESCE(
                (SELECT SUM(p.points) FROM participants p WHERE p.student_id = s.student_id AND p.points > 0), 0
            ) +
            COALESCE(
                (SELECT SUM(w.points) FROM winners w WHERE w.student_id = s.student_id AND w.points > 0), 0
            ) +
            COALESCE(
                (SELECT SUM(o.points) FROM organizers o WHERE o.student_id = s.student_id AND o.points > 0), 0
            ) +
            COALESCE(
                (SELECT SUM(a.points) FROM appreciations a WHERE a.student_id = s.student_id AND a.points > 0), 0
            ) -
            COALESCE(
                (SELECT SUM(ABS(pen.points)) FROM penalties pen WHERE pen.student_id = s.student_id), 0
            ) as total_points
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.class_id
        LEFT JOIN houses h ON s.hid = h.hid
        WHERE s.is_alumni = 0
    ";

    // Add filters
    $where_conditions = [];
    
    if (!empty($branch_filter)) {
        $where_conditions[] = "s.branch = '$branch_filter'";
    }
    
    if (!empty($year_filter)) {
        $where_conditions[] = "c.year = '$year_filter'";
    }
    
    if (!empty($house_filter)) {
        // Handle house filter with mapping
        if (isset($house_mapping[$house_filter])) {
            $house_names = array_map(function($name) use ($conn) {
                return "'" . mysqli_real_escape_string($conn, $name) . "'";
            }, $house_mapping[$house_filter]);
            $where_conditions[] = "h.name IN (" . implode(',', $house_names) . ")";
        }
    }
    
    if (!empty($where_conditions)) {
        $base_query .= " AND " . implode(' AND ', $where_conditions);
    }
    
    // Add having clause to only show contributors with points > 0
    $base_query .= " HAVING total_points > 0";
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM ($base_query) as contributors";
    $count_result = mysqli_query($conn, $count_query);
    $total_contributors = 0;
    
    if ($count_result) {
        $count_data = mysqli_fetch_assoc($count_result);
        $total_contributors = (int)$count_data['total'];
    }
    
    // Get contributors with pagination
    $final_query = $base_query . " ORDER BY total_points DESC, s.name ASC LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $final_query);
    
    $contributors = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Map database house names to display names
            $display_house_name = $row['house_name'];
            foreach ($house_mapping as $display_name => $db_names) {
                if (in_array($row['house_name'], $db_names)) {
                    $display_house_name = $display_name;
                    break;
                }
            }
            
            $contributors[] = [
                'student_id' => $row['student_id'],
                'name' => $row['name'],
                'branch' => $row['branch'],
                'year' => $row['year'] ?: 'N/A',
                'house_name' => $display_house_name,
                'total_points' => (int)$row['total_points']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'contributors' => $contributors,
        'total' => $total_contributors,
        'offset' => $offset,
        'limit' => $limit
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

function handleContributorDetails() {
    global $conn;
    
    if (!isset($_GET['student_id'])) {
        echo json_encode(['success' => false, 'error' => 'Student ID required']);
        return;
    }
    
    $student_id = mysqli_real_escape_string($conn, $_GET['student_id']);
    
    try {
        $details = [
            'participation' => ['total_points' => 0, 'events' => []],
            'wins' => ['total_points' => 0, 'events' => []],
            'organized' => ['total_points' => 0, 'events' => []],
            'appreciations' => ['total_points' => 0, 'events' => []],
            'penalties' => ['total_points' => 0, 'events' => []]
        ];
        
        // Get participation points
        $participation_query = "
            SELECT e.title as event_title, p.points, e.event_date
            FROM participants p
            JOIN events e ON p.event_id = e.event_id
            WHERE p.student_id = '$student_id' AND p.points > 0
            ORDER BY e.event_date DESC
        ";
        $participation_result = mysqli_query($conn, $participation_query);
        if ($participation_result) {
            while ($row = mysqli_fetch_assoc($participation_result)) {
                $details['participation']['events'][] = [
                    'event_title' => $row['event_title'],
                    'points' => (int)$row['points'],
                    'date' => $row['event_date']
                ];
                $details['participation']['total_points'] += (int)$row['points'];
            }
        }
        
        // Get winner points
        $winners_query = "
            SELECT e.title as event_title, w.points, w.position, e.event_date
            FROM winners w
            JOIN events e ON w.event_id = e.event_id
            WHERE w.student_id = '$student_id' AND w.points > 0
            ORDER BY e.event_date DESC
        ";
        $winners_result = mysqli_query($conn, $winners_query);
        if ($winners_result) {
            while ($row = mysqli_fetch_assoc($winners_result)) {
                $position_text = '';
                switch($row['position']) {
                    case 1: $position_text = ' (1st Place)'; break;
                    case 2: $position_text = ' (2nd Place)'; break;
                    case 3: $position_text = ' (3rd Place)'; break;
                    default: $position_text = ' (' . $row['position'] . 'th Place)'; break;
                }
                
                $details['wins']['events'][] = [
                    'event_title' => $row['event_title'] . $position_text,
                    'points' => (int)$row['points'],
                    'date' => $row['event_date']
                ];
                $details['wins']['total_points'] += (int)$row['points'];
            }
        }
        
        // Get organizer points
        $organizers_query = "
            SELECT e.title as event_title, o.points, o.role, e.event_date
            FROM organizers o
            JOIN events e ON o.event_id = e.event_id
            WHERE o.student_id = '$student_id' AND o.points > 0
            ORDER BY e.event_date DESC
        ";
        $organizers_result = mysqli_query($conn, $organizers_query);
        if ($organizers_result) {
            while ($row = mysqli_fetch_assoc($organizers_result)) {
                $role_text = $row['role'] ? ' (' . ucfirst($row['role']) . ')' : '';
                $details['organized']['events'][] = [
                    'event_title' => $row['event_title'] . $role_text,
                    'points' => (int)$row['points'],
                    'date' => $row['event_date']
                ];
                $details['organized']['total_points'] += (int)$row['points'];
            }
        }
        
        // Get appreciation points
        $appreciations_query = "
            SELECT e.title as event_title, a.points, a.reason, a.created_at
            FROM appreciations a
            JOIN events e ON a.event_id = e.event_id
            WHERE a.student_id = '$student_id' AND a.points > 0
            ORDER BY a.created_at DESC
        ";
        $appreciations_result = mysqli_query($conn, $appreciations_query);
        if ($appreciations_result) {
            while ($row = mysqli_fetch_assoc($appreciations_result)) {
                $reason_text = $row['reason'] ? ' - ' . $row['reason'] : '';
                $details['appreciations']['events'][] = [
                    'event_title' => $row['event_title'] . $reason_text,
                    'points' => (int)$row['points'],
                    'date' => $row['created_at']
                ];
                $details['appreciations']['total_points'] += (int)$row['points'];
            }
        }
        
        // Get penalties
        $penalties_query = "
            SELECT e.title as event_title, p.points, p.reason, p.created_at
            FROM penalties p
            JOIN events e ON p.event_id = e.event_id
            WHERE p.student_id = '$student_id'
            ORDER BY p.created_at DESC
        ";
        $penalties_result = mysqli_query($conn, $penalties_query);
        if ($penalties_result) {
            while ($row = mysqli_fetch_assoc($penalties_result)) {
                $reason_text = $row['reason'] ? ' - ' . $row['reason'] : '';
                $details['penalties']['events'][] = [
                    'event_title' => $row['event_title'] . $reason_text,
                    'points' => (int)$row['points'], // Keep negative value
                    'date' => $row['created_at']
                ];
                $details['penalties']['total_points'] += (int)$row['points']; // Keep negative value
            }
        }
        
        echo json_encode([
            'success' => true,
            'details' => $details
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>