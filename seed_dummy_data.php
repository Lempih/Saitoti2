<?php
/**
 * Dummy Data Seeder
 * Creates sample courses, students, and results for testing
 */

require_once('db_config.php');

if (!$db_connection) {
    die("Database connection failed!");
}

echo "<h2>Seeding Dummy Data...</h2>";

// 1. Create Courses
$courses = [
    ['course_id' => 1, 'course_name' => 'Computer Science'],
    ['course_id' => 2, 'course_name' => 'Business Administration'],
    ['course_id' => 3, 'course_name' => 'Engineering'],
    ['course_id' => 4, 'course_name' => 'Medicine'],
    ['course_id' => 5, 'course_name' => 'Law']
];

echo "<p>Creating courses...</p>";
foreach ($courses as $course) {
    $check = "SELECT course_id FROM courses WHERE course_id = ?";
    $stmt = mysqli_prepare($db_connection, $check);
    mysqli_stmt_bind_param($stmt, "i", $course['course_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        $insert = "INSERT INTO courses (course_id, course_name) VALUES (?, ?)";
        $stmt2 = mysqli_prepare($db_connection, $insert);
        mysqli_stmt_bind_param($stmt2, "is", $course['course_id'], $course['course_name']);
        mysqli_stmt_execute($stmt2);
        echo "<p>✓ Created course: {$course['course_name']}</p>";
    } else {
        echo "<p>→ Course already exists: {$course['course_name']}</p>";
    }
    mysqli_stmt_close($stmt);
}

// 2. Check if email and password columns exist, if not, add them
$check_email = "SHOW COLUMNS FROM student_records LIKE 'email'";
$result = mysqli_query($db_connection, $check_email);
if (mysqli_num_rows($result) == 0) {
    mysqli_query($db_connection, "ALTER TABLE student_records ADD COLUMN email VARCHAR(100) UNIQUE AFTER full_name");
    echo "<p>✓ Added email column</p>";
}

$check_password = "SHOW COLUMNS FROM student_records LIKE 'password'";
$result = mysqli_query($db_connection, $check_password);
if (mysqli_num_rows($result) == 0) {
    mysqli_query($db_connection, "ALTER TABLE student_records ADD COLUMN password VARCHAR(255) AFTER email");
    echo "<p>✓ Added password column</p>";
}

// 2. Create Students
$students = [
    ['name' => 'John Doe', 'roll' => 1001, 'course' => 'Computer Science', 'email' => 'john.doe@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
    ['name' => 'Jane Smith', 'roll' => 1002, 'course' => 'Computer Science', 'email' => 'jane.smith@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
    ['name' => 'Michael Johnson', 'roll' => 2001, 'course' => 'Business Administration', 'email' => 'michael.j@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
    ['name' => 'Sarah Williams', 'roll' => 2002, 'course' => 'Business Administration', 'email' => 'sarah.w@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
    ['name' => 'David Brown', 'roll' => 3001, 'course' => 'Engineering', 'email' => 'david.b@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
    ['name' => 'Emily Davis', 'roll' => 3002, 'course' => 'Engineering', 'email' => 'emily.d@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
    ['name' => 'Robert Wilson', 'roll' => 4001, 'course' => 'Medicine', 'email' => 'robert.w@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
    ['name' => 'Lisa Anderson', 'roll' => 5001, 'course' => 'Law', 'email' => 'lisa.a@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT)],
];

echo "<p>Creating students...</p>";
foreach ($students as $student) {
    $check = "SELECT roll_number FROM student_records WHERE roll_number = ? AND enrolled_course = ?";
    $stmt = mysqli_prepare($db_connection, $check);
    mysqli_stmt_bind_param($stmt, "is", $student['roll'], $student['course']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        // Check if email/password columns exist
        $has_email = false;
        $check_cols = "SHOW COLUMNS FROM student_records LIKE 'email'";
        $col_result = mysqli_query($db_connection, $check_cols);
        if (mysqli_num_rows($col_result) > 0) {
            $has_email = true;
        }
        
        if ($has_email) {
            $insert = "INSERT INTO student_records (full_name, roll_number, enrolled_course, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = mysqli_prepare($db_connection, $insert);
            mysqli_stmt_bind_param($stmt2, "siss", $student['name'], $student['roll'], $student['course'], $student['email'], $student['password']);
        } else {
            $insert = "INSERT INTO student_records (full_name, roll_number, enrolled_course) VALUES (?, ?, ?)";
            $stmt2 = mysqli_prepare($db_connection, $insert);
            mysqli_stmt_bind_param($stmt2, "sis", $student['name'], $student['roll'], $student['course']);
        }
        mysqli_stmt_execute($stmt2);
        echo "<p>✓ Created student: {$student['name']} (Roll: {$student['roll']})</p>";
    } else {
        echo "<p>→ Student already exists: {$student['name']}</p>";
    }
    mysqli_stmt_close($stmt);
}

// 3. Create Results
$results = [
    ['roll' => 1001, 'course' => 'Computer Science', 'name' => 'John Doe', 's1' => 85, 's2' => 90, 's3' => 88, 's4' => 92, 's5' => 87],
    ['roll' => 1002, 'course' => 'Computer Science', 'name' => 'Jane Smith', 's1' => 78, 's2' => 82, 's3' => 80, 's4' => 85, 's5' => 79],
    ['roll' => 2001, 'course' => 'Business Administration', 'name' => 'Michael Johnson', 's1' => 88, 's2' => 85, 's3' => 90, 's4' => 87, 's5' => 89],
    ['roll' => 2002, 'course' => 'Business Administration', 'name' => 'Sarah Williams', 's1' => 92, 's2' => 88, 's3' => 91, 's4' => 90, 's5' => 93],
    ['roll' => 3001, 'course' => 'Engineering', 'name' => 'David Brown', 's1' => 75, 's2' => 80, 's3' => 78, 's4' => 82, 's5' => 77],
    ['roll' => 3002, 'course' => 'Engineering', 'name' => 'Emily Davis', 's1' => 90, 's2' => 88, 's3' => 92, 's4' => 89, 's5' => 91],
    ['roll' => 4001, 'course' => 'Medicine', 'name' => 'Robert Wilson', 's1' => 95, 's2' => 93, 's3' => 94, 's4' => 96, 's5' => 95],
    ['roll' => 5001, 'course' => 'Law', 'name' => 'Lisa Anderson', 's1' => 87, 's2' => 85, 's3' => 89, 's4' => 88, 's5' => 86],
];

echo "<p>Creating results...</p>";
foreach ($results as $result) {
    $check = "SELECT roll_number FROM exam_results WHERE roll_number = ? AND course_name = ?";
    $stmt = mysqli_prepare($db_connection, $check);
    mysqli_stmt_bind_param($stmt, "is", $result['roll'], $result['course']);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        $total = $result['s1'] + $result['s2'] + $result['s3'] + $result['s4'] + $result['s5'];
        $percentage = round($total / 5, 2);
        
        $insert = "INSERT INTO exam_results (student_name, roll_number, course_name, subject_1, subject_2, subject_3, subject_4, subject_5, total_marks, grade_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($db_connection, $insert);
        mysqli_stmt_bind_param($stmt2, "sisiiiiiii", $result['name'], $result['roll'], $result['course'], $result['s1'], $result['s2'], $result['s3'], $result['s4'], $result['s5'], $total, $percentage);
        mysqli_stmt_execute($stmt2);
        echo "<p>✓ Created results for: {$result['name']} (Total: {$total}/500, Percentage: {$percentage}%)</p>";
    } else {
        echo "<p>→ Results already exist for: {$result['name']}</p>";
    }
    mysqli_stmt_close($stmt);
}

echo "<h3 style='color: green; margin-top: 30px;'>✓ Dummy data seeding completed!</h3>";
echo "<h4>Test Credentials:</h4>";
echo "<ul>";
echo "<li><strong>Admin:</strong> administrator / admin2024</li>";
echo "<li><strong>Student 1:</strong> john.doe@example.com / password123 (Roll: 1001, Course: Computer Science)</li>";
echo "<li><strong>Student 2:</strong> jane.smith@example.com / password123 (Roll: 1002, Course: Computer Science)</li>";
echo "<li><strong>Student 3:</strong> michael.j@example.com / password123 (Roll: 2001, Course: Business Administration)</li>";
echo "<li><strong>Quick Results:</strong> Use Roll Number 1001 with Course 'Computer Science'</li>";
echo "</ul>";
echo "<p><a href='index.html' style='padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px;'>Go to Homepage</a></p>";
?>

