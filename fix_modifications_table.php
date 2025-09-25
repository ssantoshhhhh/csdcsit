<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './connect.php';

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

echo "<h2>Fix Attendance Modifications Table</h2>";

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'attendance_modifications'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ attendance_modifications table exists<br>";
    
    // Check table structure
    $structure_result = mysqli_query($conn, "DESCRIBE attendance_modifications");
    $existing_columns = [];
    while ($row = mysqli_fetch_assoc($structure_result)) {
        $existing_columns[] = $row['Field'];
    }
    
    echo "<h3>Current Table Structure:</h3>";
    echo "<ul>";
    foreach ($existing_columns as $column) {
        echo "<li>$column</li>";
    }
    echo "</ul>";
    
    // Check if changes_made column exists
    if (!in_array('changes_made', $existing_columns)) {
        echo "❌ 'changes_made' column is missing. Adding it...<br>";
        
        $add_column_sql = "ALTER TABLE attendance_modifications ADD COLUMN changes_made TEXT NOT NULL AFTER modification_reason";
        $result = mysqli_query($conn, $add_column_sql);
        
        if ($result) {
            echo "✅ 'changes_made' column added successfully<br>";
        } else {
            echo "❌ Error adding column: " . mysqli_error($conn) . "<br>";
        }
    } else {
        echo "✅ 'changes_made' column already exists<br>";
    }
    
    // Check if other required columns exist
    $required_columns = ['id', 'table_name', 'attendance_date', 'session', 'faculty_name', 'modification_reason', 'changes_made', 'modified_at'];
    $missing_columns = array_diff($required_columns, $existing_columns);
    
    if (!empty($missing_columns)) {
        echo "<h3>Missing Columns:</h3>";
        echo "<ul>";
        foreach ($missing_columns as $column) {
            echo "<li>$column</li>";
        }
        echo "</ul>";
        
        // Add missing columns
        foreach ($missing_columns as $column) {
            $column_type = '';
            switch ($column) {
                case 'id':
                    $column_type = 'INT(11) NOT NULL AUTO_INCREMENT';
                    break;
                case 'table_name':
                    $column_type = 'VARCHAR(50) NOT NULL';
                    break;
                case 'attendance_date':
                    $column_type = 'DATE NOT NULL';
                    break;
                case 'session':
                    $column_type = 'VARCHAR(20) NOT NULL';
                    break;
                case 'faculty_name':
                    $column_type = 'VARCHAR(100) NOT NULL';
                    break;
                case 'modification_reason':
                    $column_type = 'TEXT NOT NULL';
                    break;
                case 'changes_made':
                    $column_type = 'TEXT NOT NULL';
                    break;
                case 'modified_at':
                    $column_type = 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP';
                    break;
            }
            
            if ($column_type) {
                $add_sql = "ALTER TABLE attendance_modifications ADD COLUMN $column $column_type";
                $result = mysqli_query($conn, $add_sql);
                if ($result) {
                    echo "✅ Added column: $column<br>";
                } else {
                    echo "❌ Error adding column $column: " . mysqli_error($conn) . "<br>";
                }
            }
        }
    }
    
} else {
    echo "❌ attendance_modifications table does not exist. Creating it...<br>";
    
    // Create the table with correct structure
    $create_table_sql = "
    CREATE TABLE `attendance_modifications` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `table_name` varchar(50) NOT NULL,
        `attendance_date` date NOT NULL,
        `session` varchar(20) NOT NULL,
        `faculty_name` varchar(100) NOT NULL,
        `modification_reason` text NOT NULL,
        `changes_made` text NOT NULL,
        `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_table_date` (`table_name`, `attendance_date`),
        KEY `idx_faculty` (`faculty_name`),
        KEY `idx_modified_at` (`modified_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $result = mysqli_query($conn, $create_table_sql);
    if ($result) {
        echo "✅ attendance_modifications table created successfully<br>";
    } else {
        echo "❌ Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

// Test insert
echo "<h3>Testing Table Functionality</h3>";
$test_insert = mysqli_query($conn, "INSERT INTO attendance_modifications (table_name, attendance_date, session, faculty_name, modification_reason, changes_made) VALUES ('test_table', '2025-01-29', 'Test', 'Test Faculty', 'Test reason', 'Test changes')");

if ($test_insert) {
    echo "✅ Test insert successful<br>";
    // Clean up test data
    mysqli_query($conn, "DELETE FROM attendance_modifications WHERE table_name = 'test_table'");
    echo "✅ Test data cleaned up<br>";
} else {
    echo "❌ Test insert failed: " . mysqli_error($conn) . "<br>";
}

echo "<h3>Fix Complete</h3>";
echo "<p><a href='attendance_entry.php'>Return to Attendance Entry</a></p>";
?> 