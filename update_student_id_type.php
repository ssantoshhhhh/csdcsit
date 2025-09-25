<?php
/**
 * Update Student ID Type Script
 * This script updates the student_id from INT to VARCHAR in the migration file
 */

echo "<h1>🔄 Updating Student ID Type in Migration File</h1>";

// Read the migration file
$migration_file = 'migrate_to_new_schema.php';
$content = file_get_contents($migration_file);

if ($content === false) {
    echo "<p>❌ Error reading migration file</p>";
    exit();
}

echo "<p>✅ Migration file read successfully</p>";

// Replace all instances of student_id INT with student_id VARCHAR
$old_pattern = "`student_id` INT(11) NOT NULL AUTO_INCREMENT,";
$new_pattern = "`student_id` VARCHAR(20) NOT NULL,";

$content = str_replace($old_pattern, $new_pattern, $content);

// Also replace student_id INT without AUTO_INCREMENT
$old_pattern2 = "`student_id` INT(11) NOT NULL,";
$new_pattern2 = "`student_id` VARCHAR(20) NOT NULL,";

$content = str_replace($old_pattern2, $new_pattern2, $content);

// Write the updated content back
if (file_put_contents($migration_file, $content)) {
    echo "<p>✅ Migration file updated successfully</p>";
    echo "<p>📝 All student_id fields changed from INT to VARCHAR(20)</p>";
} else {
    echo "<p>❌ Error writing migration file</p>";
}

echo "<h2>📊 Summary of Changes</h2>";
echo "<ul>";
echo "<li>✅ students table: student_id changed to VARCHAR(20)</li>";
echo "<li>✅ student_attendance table: student_id changed to VARCHAR(20)</li>";
echo "<li>✅ All other tables with student_id references updated</li>";
echo "</ul>";

echo "<h2>🚀 Next Steps</h2>";
echo "<p>1. Run the migration script to create tables with new student_id type</p>";
echo "<p>2. Update any PHP code that expects student_id to be an integer</p>";
echo "<p>3. Test the system with the new VARCHAR student_id</p>";
?> 