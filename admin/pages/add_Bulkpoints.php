<?php
session_start();
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

$_SESSION['last_activity'] = time();
require '../utils/connect.php';
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel File Upload and Edit Pro</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/add_bulkpoint/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
   
</head>

<body>

    <?php include '../utils/sidenavbar.php'; ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center mb-4">
                    <i class="bi bi-file-earmark-spreadsheet text-primary"></i>
                    Add Points Through Excel
                </h2>
                <div id="dropZone" class="mb-4">
                    <input type="file" id="excelFile" accept=".xlsx, .xls" class="d-none">
                    <label for="excelFile" class="btn btn-outline-primary">
                        <i class="bi bi-cloud-upload"></i> Upload Excel File
                    </label>
                    <p class="text-muted mt-2">or drag and drop files here</p>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search registration number...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="Winner">Winner</option>
                            <option value="Participate">Participate</option>
                        </select>
                    </div>

                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-hover" id="dataTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Registration Number</th>
                                <th>Status</th>
                                <th>Add-on Points</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <select id="eventSelect" class="form-select">
                            <option value="">Select Event</option>
                            <?php
                            require '../utils/connect.php';

                            $username = $_SESSION['username'] ?? null;
                            if (!$username) {
                                echo "<div class='alert alert-danger'>Error: Username not found in session.</div>";
                                exit;
                            }

                            // Fetch hid from admins table
                            $query = "SELECT hid FROM admins WHERE username = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("s", $username);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $adminData = $result->fetch_assoc();
                            $hid = $adminData['hid'] ?? null;

                            // Check if hid is valid
                            if ($hid !== null) {
                                // Fetch events associated with this hid
                                $eventQuery = "SELECT DISTINCT title FROM events WHERE hid = ?";
                                $eventStmt = $conn->prepare($eventQuery);
                                $eventStmt->bind_param("i", $hid);
                                $eventStmt->execute();
                                $eventResult = $eventStmt->get_result();

                                if ($eventResult->num_rows > 0) {
                                    // Loop through each event and add it as an option
                                    while ($row = $eventResult->fetch_assoc()) {
                                        $eventTitle = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                                        echo "<option value='$eventTitle'>$eventTitle</option>";
                                    }
                                } else {
                                    echo "<option value=''>No Events Found</option>";
                                }

                                $eventStmt->close();
                            } else {
                                echo "<option value=''>Invalid Admin Data</option>";
                            }

                            $stmt->close();
                            $conn->close();
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Please select an event before uploading points
                        </div>
                    </div>
                </div>


                <div class="text-center mt-3">
                    <button id="submitData" class="btn btn-success" disabled>
                        <i class="bi bi-cloud-upload-fill"></i> Submit Changes
                    </button>
                    <button id="downloadSample" class="btn btn-info ms-2">
                        <i class="bi bi-download"></i> Download Sample
                    </button>
                </div>
            </div>

        </div>
    </div>

    <div class="toast-container"></div>

    <!-- Modals and Toasts would be added here in a full implementation -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="../js/add_bulkpoint/script.js"></script>


</body>

</html>