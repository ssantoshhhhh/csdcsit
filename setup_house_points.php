<?php
include "connect.php";

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// SQL to create house points table
$sql = "
CREATE TABLE IF NOT EXISTS house_points (
  id int(11) NOT NULL AUTO_INCREMENT,
  regd_no varchar(20) NOT NULL,
  name varchar(100) NOT NULL,
  year_section varchar(50) NOT NULL,
  house_name varchar(50) NOT NULL,
  total_points int(11) NOT NULL DEFAULT 0,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_student (regd_no),
  KEY idx_year_section (year_section),
  KEY idx_house_name (house_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if (mysqli_query($conn, $sql)) {
    echo "House points table created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Check if table is empty and insert sample data
$check_sql = "SELECT COUNT(*) as count FROM house_points";
$result = mysqli_query($conn, $check_sql);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $sample_data = "
    INSERT INTO house_points (regd_no, name, year_section, house_name, total_points) VALUES
    ('20CS001', 'John Doe', '2/4 CSIT-A', 'Aakash', 150),
    ('20CS002', 'Jane Smith', '2/4 CSIT-A', 'Jal', 200),
    ('20CS003', 'Mike Johnson', '2/4 CSIT-B', 'Vayu', 175),
    ('20CS004', 'Sarah Wilson', '2/4 CSD', 'Pruthvi', 225),
    ('20CS005', 'David Brown', '3/4 CSIT', 'Agni', 300),
    ('20CS006', 'Emily Davis', '3/4 CSD', 'Aakash', 250),
    ('20CS007', 'Chris Lee', '4/4 CSD', 'Jal', 400),
    ('20CS008', 'Alex Kumar', '2/4 CSIT-A', 'Vayu', 180),
    ('20CS009', 'Priya Sharma', '2/4 CSIT-B', 'Pruthvi', 220),
    ('20CS010', 'Rahul Singh', '3/4 CSIT', 'Agni', 350)
    ";
    
    if (mysqli_query($conn, $sample_data)) {
        echo "Sample data inserted successfully<br>";
    } else {
        echo "Error inserting sample data: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Table already contains data<br>";
}

echo "<br><a href='house_points.php'>Go to House Points Management</a>";
?> 