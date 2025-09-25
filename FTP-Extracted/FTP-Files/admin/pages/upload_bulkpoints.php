<?php
$processedCount = 0;
$updatedCount = 0;
$errorCount = 0;
$errors = [];

ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: login.php?session_expired=true");
    exit();
}

$_SESSION['last_activity'] = time();
require '../utils/connect.php';

$username = $_SESSION['username'];

// Get admin HID
$query = "SELECT hid FROM admins WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
$hid = $adminData['hid'] ?? null;

ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/apache/logs/error.log');
error_log("🔍 Debugging Started");

header('Content-Type: application/json');

try {
    // Start MySQL transaction
    $conn->begin_transaction();

    // Get JSON data
    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    error_log("Received data: " . $inputData);

    if (!$data || !is_array($data)) {
        throw new Exception("Invalid data format received");
    }

    foreach ($data as $entry) {
        if (!isset($entry['registrationNumber'], $entry['status'], $entry['event'])) {
            $errorMessage = "❌ Missing required fields in data entry";
            error_log($errorMessage);
            $errors[] = $errorMessage;
            $errorCount++;
            continue;
        }

        $registrationNumber = $entry['registrationNumber'];
        $status = strtolower($entry['status']);
        $event = $entry['event'];
        $add_on_points = isset($entry['add_on_points']) ? (int) $entry['add_on_points'] : 0;

        error_log("Processing: " . json_encode($entry));

        // Check if student exists
        $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $registrationNumber);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();

        if (!$student) {
            $errorMessage = "❌ Student not found: " . $registrationNumber;
            error_log($errorMessage);
            $errors[] = $errorMessage;
            $errorCount++;
            continue;
        }

        error_log("✅ Student Found: " . json_encode($student));

        // Check if event exists
        $stmt = $conn->prepare("SELECT event_id, participate_points, winner_points FROM events WHERE title = ?");
        $stmt->bind_param("s", $event);
        $stmt->execute();
        $eventData = $stmt->get_result()->fetch_assoc();

        if (!$eventData) {
            $errorMessage = "❌ Event not found: " . $event;
            error_log($errorMessage);
            $errors[] = $errorMessage;
            $errorCount++;
            continue;
        }

        error_log("✅ Event Found: " . json_encode($eventData));

        // Update registration status
        $stmt = $conn->prepare("UPDATE registrations SET status = 'confirmed' WHERE student_id = ? AND event_id = ?");
        $stmt->bind_param("si", $student['student_id'], $eventData['event_id']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            error_log("✅ Updated registration status for student_id: " . $student['student_id'] . ", event_id: " . $eventData['event_id']);
        } else {
            $errorMessage = "⚠️ No registration found to update for student_id: " . $student['student_id'] . ", event_id: " . $eventData['event_id'];
            error_log($errorMessage);
            $errors[] = $errorMessage;
        }

        // Determine points
        $points = ($status === "winner") ? $eventData['winner_points'] : $eventData['participate_points'];
        $points += $add_on_points;

        if ($status === "participate" || $status === "participant" || $status === "runner") {
            // Check if participant exists
            $stmt = $conn->prepare("SELECT participant_id FROM participants WHERE student_id = ? AND event_id = ?");
            $stmt->bind_param("si", $student['student_id'], $eventData['event_id']);
            $stmt->execute();
            $existingParticipant = $stmt->get_result()->fetch_assoc();

            if ($existingParticipant) {
                $stmt = $conn->prepare("UPDATE participants SET points = ? WHERE student_id = ? AND event_id = ?");
                $stmt->bind_param("isi", $points, $student['student_id'], $eventData['event_id']);
                $stmt->execute();
                error_log("🔄 Updated participant/runner for student_id: " . $student['student_id'] . ", event_id: " . $eventData['event_id']);
            } else {
                $stmt = $conn->prepare("INSERT INTO participants (student_id, event_id, points) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $student['student_id'], $eventData['event_id'], $points);
                $stmt->execute();
                error_log("✅ Inserted participant/runner for student_id: " . $student['student_id'] . ", event_id: " . $eventData['event_id']);
            }
        } elseif ($status === "winner") {
            $stmt = $conn->prepare("SELECT winner_id FROM winners WHERE student_id = ? AND event_id = ?");
            $stmt->bind_param("si", $student['student_id'], $eventData['event_id']);
            $stmt->execute();
            $existingWinner = $stmt->get_result()->fetch_assoc();

            if ($existingWinner) {
                $stmt = $conn->prepare("UPDATE winners SET points = ? WHERE student_id = ? AND event_id = ?");
                $stmt->bind_param("isi", $points, $student['student_id'], $eventData['event_id']);
                $stmt->execute();
                error_log("🔄 Updated winner record for student_id: " . $student['student_id'] . ", event_id: " . $eventData['event_id']);
            } else {
                $stmt = $conn->prepare("INSERT INTO winners (student_id, event_id, points) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $student['student_id'], $eventData['event_id'], $points);
                $stmt->execute();
                error_log("✅ Inserted winner record for student_id: " . $student['student_id'] . ", event_id: " . $eventData['event_id']);
            }
        } else {
            $errorMessage = "⚠️ Unknown status: " . $status . " for student_id: " . $student['student_id'];
            error_log($errorMessage);
            $errors[] = $errorMessage;
            $errorCount++;
        }

        $updatedCount++;
        $processedCount++;
    }

    // Commit transaction
    $conn->commit();

    $response = [
        "success" => true,
        "processed" => $processedCount,
        "updated" => $updatedCount,
        "errors" => $errorCount,
        "error_details" => $errors
    ];
} catch (Exception $e) {
    $conn->rollback();
    error_log("❌ Error: " . $e->getMessage());
    $response = [
        "success" => false,
        "message" => $e->getMessage()
    ];
}

echo json_encode($response);
exit;
?>
