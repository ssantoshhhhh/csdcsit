<?php
include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>Setting up Section Contact Tables...</h2>";

// Define sections and their contact tables
$sections = [
    '28csit_a_attendance' => '28csit_a_contacts',
    '28csit_b_attendance' => '28csit_b_contacts',
    '28csd_attendance' => '28csd_contacts',
    '27csit_attendance' => '27csit_contacts',
    '27csd_attendance' => '27csd_contacts',
    '26csd_attendance' => '26csd_contacts'
];

$classes = [
    '28csit_a_attendance' => '2/4 CSIT-A',
    '28csit_b_attendance' => '2/4 CSIT-B',
    '28csd_attendance'    => '2/4 CSD',
    '27csit_attendance'   => '3/4 CSIT',
    '27csd_attendance'    => '3/4 CSD',
    '26csd_attendance'    => '4/4 CSD',
];

foreach ($sections as $attendance_table => $contact_table) {
    // Check if attendance table exists
    $attendance_exists = mysqli_query($conn, "SHOW TABLES LIKE '$attendance_table'");
    if (mysqli_num_rows($attendance_exists) == 0) {
        echo "<p>⚠️ Attendance table '$attendance_table' does not exist. Skipping contact table creation.</p>";
        continue;
    }
    
    // Create contact table
    $create_contact_table = "
    CREATE TABLE IF NOT EXISTS `$contact_table` (
      id int(11) NOT NULL AUTO_INCREMENT,
      register_no varchar(20) NOT NULL,
      student_name varchar(100) NOT NULL,
      parent_phone varchar(15) NOT NULL,
      created_at timestamp DEFAULT CURRENT_TIMESTAMP,
      updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY unique_student (register_no)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (mysqli_query($conn, $create_contact_table)) {
        echo "<p>✅ Contact table '$contact_table' created successfully for " . $classes[$attendance_table] . "</p>";
        
        // Get students from attendance table and populate contact table with default data
        $students_query = "SELECT DISTINCT register_no FROM `$attendance_table` ORDER BY register_no";
        $students_result = mysqli_query($conn, $students_query);
        
        if ($students_result) {
            $student_count = 0;
            while ($student = mysqli_fetch_assoc($students_result)) {
                $register_no = $student['register_no'];
                
                // Check if contact already exists
                $check_query = "SELECT id FROM `$contact_table` WHERE register_no = ?";
                $stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($stmt, "s", $register_no);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) == 0) {
                    // Insert default contact data
                    $insert_query = "INSERT INTO `$contact_table` (register_no, student_name, parent_phone) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $insert_query);
                    $student_name = 'Student ' . $register_no;
                    $parent_phone = '8639081207';
                    mysqli_stmt_bind_param($stmt, "sss", $register_no, $student_name, $parent_phone);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $student_count++;
                    }
                }
            }
            echo "<p>📝 Added $student_count students to contact table with default data</p>";
        }
    } else {
        echo "<p>❌ Error creating contact table '$contact_table': " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>🎉 Contact Tables Setup Complete!</h3>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to Faculty Dashboard</li>";
echo "<li>Click 'Manage Contacts' button</li>";
echo "<li>Select a section and update student names and parent phone numbers</li>";
echo "<li>Test the WhatsApp messaging feature</li>";
echo "</ol>";

echo "<p><a href='login.php' class='btn btn-primary'>Go to Login</a></p>";
?> 