<?php
   session_start();
   include '../utils/connect.php';
   if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: login.php?session_expired=true");
    exit();
}
$sql = "SELECT hid FROM admins WHERE username='" . $_SESSION['username'] . "'";
$result = $conn->query($sql);
$hid = $result->fetch_assoc()['hid'];
$query = "SELECT s.student_id as username, s.name, c.year, c.branch, c.section,
          COALESCE(
            (SELECT SUM(points) FROM organizers WHERE student_id = s.student_id),
            0
          ) +
          COALESCE(
            (SELECT SUM(points) FROM participants WHERE student_id = s.student_id),
            0
          ) +
          COALESCE(
            (SELECT SUM(points) FROM winners WHERE student_id = s.student_id),
            0
          ) as points
          FROM students s
          JOIN classes c ON s.class_id = c.class_id
          WHERE s.hid=$hid";
$result = $conn->query($query);
$students = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
$query = "SELECT title, event_date FROM events WHERE hid=$hid";
$eventsResult = $conn->query($query);
$events = [];
if ($eventsResult->num_rows > 0) {
    while ($row = $eventsResult->fetch_assoc()) {
        $events[] = $row;
    }
}

// Fetch todos for this house (using events table for now)
$query = "SELECT event_id, title, description FROM events WHERE hid=$hid ORDER BY event_id DESC";
$todoResult = $conn->query($query);
$todos = [];
if ($todoResult && $todoResult->num_rows > 0) {
    while ($row = $todoResult->fetch_assoc()) {
        $todos[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/index/style.css">
</head>
<body>
    <?php include '../utils/sidenavbar.php'; ?>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="mb-0">
                    <i class="bi bi-trophy text-primary"></i> House Dashboard
                </h2>
                <p class="text-muted">Comprehensive overview of student achievements</p>
            </div>
        </div>

        <div class="row mb-4 justify-content-center">
            <div class="col-md-2 stat-card">
                <h5>Total Students</h5>
                <p class="h5 text-primary" id="totalStudents">0</p>
            </div>
            <div class="col-md-2 stat-card">
                <h5>Avg Points</h5>
                <p class="h5 text-success" id="avgPoints">0</p>
            </div>
            <div class="col-md-2 stat-card">
                <h5>Top Performers</h5>
                <p class="h5 text-danger" id="topPerformer">-</p>
            </div>
            <div class="col-md-2 stat-card">
                <h5>Events Conducted</h5>
                <p class="h5 text-warning" id="totalEvents">0</p>            
            </div>
            <div class="col-md-2 stat-card">
                <h5>House Leaders</h5>
                <div class="members-list" style="font-size: 0.8em;">
                <?php
                    if ($hid == 1) {
                        echo "<p class='mb-1'>Deepak (Captain)</p>";
                        echo "<p class='mb-1'>Ganya (Vice-captain)</p>";
                    } elseif ($hid == 2) {
                        echo "<p class='mb-1'>Gayathri (Captain)</p>";
                        echo "<p class='mb-1'>Nikhila (Vice-captain)</p>";
                    } elseif ($hid == 3) {
                        echo "<p class='mb-1'>phani (Captain)</p>";
                        echo "<p class='mb-1'>Anna (Vice-captain)</p>";
                    } elseif ($hid == 4) {
                        echo "<p class='mb-1'>johndoe (Captain)</p>";
                        echo "<p class='mb-1'>john(Vice-captain)</p>";
                    } elseif ($hid == 5) {
                        echo "<p class='mb-1'>Chris (Captain)</p>";
                        echo "<p class='mb-1'>Olivia (Vice-captain)</p>";
                    } else {
                        echo "<p class='mb-1'>No leaders found</p>";
                    }
                ?>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Events We Conducted</h5>
                        <ul id="eventsList" class="list-group">
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Events we planning Further</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="todoInput" placeholder="Add new event title">
                            <input type="text" class="form-control" id="todoDescInput" placeholder="Description">
                            <button class="btn btn-primary" type="button" id="addTodoBtn">Add</button>
                        </div>
                        <ul class="list-group" id="todoList">
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search reg. number or name">
                </div>
            </div>
            <div class="col-md-2">
                <select id="yearFilter" class="form-select">
                    <option value="">All Years</option>
                    <option value="1st">1st Year</option>
                    <option value="2nd">2nd Year</option>
                    <option value="3rd">3rd Year</option>
                    <option value="4th">4th Year</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="classFilter" class="form-select">
                    <option value="">All Branches</option>
                    <option value="CSD">CSD</option>
                    <option value="CSIT A">CSIT A</option>
                    <option value="CSIT B">CSIT B</option>
                </select>
            </div>
            <div class="col-md-5 points-range-container">
                <div class="range-labels">
                    <span>Points Range:</span>
                    <span id="pointsRangeLabel">0 - 100</span>
                </div>
                <div class="dual-range">
                    <div class="range-track"></div>
                    <div class="range-progress" id="rangeProgress"></div>
                    <input type="range" id="minPointsRange" min="0" max="100" value="0">
                    <input type="range" id="maxPointsRange" min="0" max="100" value="100">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table table-hover" id="studentsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Reg. Number</th>
                        <th>Name</th>
                        <th>Year</th>
                        <th>Branch</th>
                        <th>Points</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-md-4 offset-md-8 text-end">
                <div class="dropdown export-dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li>
                            <a class="dropdown-item" href="#" id="exportExcel">
                                <i class="bi bi-file-earmark-excel text-success"></i> Export to Excel
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" id="exportPDF">
                                <i class="bi bi-file-earmark-pdf text-danger"></i> Export to PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

    <script>
        // Pass PHP variables to JavaScript
        const students = <?php echo json_encode($students); ?>;
        const events = <?php echo json_encode($events); ?>;
        const todos = <?php echo json_encode($todos); ?>;
    </script>
    <script src="../js/index/script.js"></script>
</body>
</html>