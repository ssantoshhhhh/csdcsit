<?php
include './connect.php';

// Check database connection
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>Setting up Faculty WhatsApp Messaging System...</h2>";

// Create student_parent_contacts table
$create_student_contacts = "
CREATE TABLE IF NOT EXISTS student_parent_contacts (
  id int(11) NOT NULL AUTO_INCREMENT,
  register_no varchar(20) NOT NULL,
  student_name varchar(100) NOT NULL,
  parent_name varchar(100) NOT NULL,
  parent_phone varchar(15) NOT NULL,
  section varchar(50) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_student (register_no, section),
  INDEX idx_section (section),
  INDEX idx_register_no (register_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_student_contacts)) {
    echo "<p>✅ Student parent contacts table created successfully</p>";
} else {
    echo "<p>❌ Error creating student parent contacts table: " . mysqli_error($conn) . "</p>";
}

// Create faculty_credentials table
$create_faculty_credentials = "
CREATE TABLE IF NOT EXISTS faculty_credentials (
  id int(11) NOT NULL AUTO_INCREMENT,
  faculty_name varchar(100) NOT NULL,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  assigned_sections text NOT NULL,
  phone_number varchar(15) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_username (username),
  UNIQUE KEY unique_faculty_name (faculty_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_faculty_credentials)) {
    echo "<p>✅ Faculty credentials table created successfully</p>";
} else {
    echo "<p>❌ Error creating faculty credentials table: " . mysqli_error($conn) . "</p>";
}

// Check if faculty credentials already exist
$check_faculty = mysqli_query($conn, "SELECT COUNT(*) as count FROM faculty_credentials");
$faculty_count = mysqli_fetch_assoc($check_faculty)['count'];

if ($faculty_count == 0) {
    // Insert sample faculty credentials (password is 'password' hashed with bcrypt)
    $faculty_inserts = [
        ['A Krishna Veni', 'krishna_veni', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '28csit_a_attendance', '+919876543210', 'krishna.veni@srkr.edu.in'],
        ['N Aneela', 'aneela', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '28csit_a_attendance', '+919876543211', 'aneela@srkr.edu.in'],
        ['K Sunil Varma', 'sunil_varma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '28csit_b_attendance', '+919876543212', 'sunil.varma@srkr.edu.in'],
        ['K Bhanu Rajesh Naidu', 'bhanu_naidu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '28csit_b_attendance', '+919876543213', 'bhanu.naidu@srkr.edu.in'],
        ['A Satyam', 'satyam', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '28csd_attendance', '+919876543214', 'satyam@srkr.edu.in'],
        ['J Tulasi Rajesh', 'tulasi_rajesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '28csd_attendance', '+919876543215', 'tulasi.rajesh@srkr.edu.in'],
        ['P S V Surya Kumar', 'surya_kumar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '27csit_attendance', '+919876543216', 'surya.kumar@srkr.edu.in'],
        ['N Mouna', 'mouna', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '27csd_attendance', '+919876543217', 'mouna@srkr.edu.in'],
        ['A Aswini Priyanka', 'aswini_priyanka', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '26csd_attendance', '+919876543218', 'aswini.priyanka@srkr.edu.in']
    ];
    
    foreach ($faculty_inserts as $faculty) {
        $stmt = mysqli_prepare($conn, "INSERT INTO faculty_credentials (faculty_name, username, password, assigned_sections, phone_number, email) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $faculty[0], $faculty[1], $faculty[2], $faculty[3], $faculty[4], $faculty[5]);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>✅ Added faculty: " . $faculty[0] . " (username: " . $faculty[1] . ", password: password)</p>";
        } else {
            echo "<p>❌ Error adding faculty " . $faculty[0] . ": " . mysqli_error($conn) . "</p>";
        }
    }
} else {
    echo "<p>ℹ️ Faculty credentials already exist (count: $faculty_count)</p>";
}

// Note: Student parent contacts will be populated dynamically from section tables
echo "<p>ℹ️ Student data will be fetched dynamically from section tables</p>";

echo "<h3>🎉 Setup Complete!</h3>";
echo "<p><strong>Faculty Login Credentials:</strong></p>";
echo "<ul>";
echo "<li>Username: krishna_veni, Password: password</li>";
echo "<li>Username: aneela, Password: password</li>";
echo "<li>Username: sunil_varma, Password: password</li>";
echo "<li>Username: bhanu_naidu, Password: password</li>";
echo "<li>Username: satyam, Password: password</li>";
echo "<li>Username: tulasi_rajesh, Password: password</li>";
echo "<li>Username: surya_kumar, Password: password</li>";
echo "<li>Username: mouna, Password: password</li>";
echo "<li>Username: aswini_priyanka, Password: password</li>";
echo "</ul>";

echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Update the student_parent_contacts table with real student and parent data</li>";
echo "<li>Update faculty phone numbers and email addresses in faculty_credentials table</li>";
echo "<li>Test the WhatsApp messaging feature</li>";
echo "</ol>";

echo "<p><a href='login.php' class='btn btn-primary'>Go to Login</a></p>";
?> 