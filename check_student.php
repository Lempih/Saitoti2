<?php
/**
 * Check Student Helper
 * Admin tool to check if a student exists and in which course
 */

session_start();
require_once("db_config.php");
require_once('auth_check.php');

$search_result = null;
$error_msg = null;

if (isset($_POST['check_student'])) {
    $search_reg = isset($_POST['registration_number']) ? trim($_POST['registration_number']) : '';
    $search_course = isset($_POST['course_name']) ? trim($_POST['course_name']) : '';
    
    if (empty($search_reg) && empty($search_course)) {
        $error_msg = "Please enter a registration number or select a course to search.";
    } else if ($db_connection) {
        // Check which column exists
        $check_reg_col = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
        $reg_check = mysqli_query($db_connection, $check_reg_col);
        $has_registration_col = $reg_check && mysqli_num_rows($reg_check) > 0;
        
        if ($has_registration_col) {
            if (!empty($search_reg)) {
                $query = "SELECT full_name, registration_number, enrolled_course, email, created_at FROM student_records WHERE LOWER(TRIM(registration_number)) LIKE LOWER(TRIM(?))";
                $stmt = mysqli_prepare($db_connection, $query);
                $search_param = '%' . $search_reg . '%';
                mysqli_stmt_bind_param($stmt, "s", $search_param);
            } else {
                $query = "SELECT full_name, registration_number, enrolled_course, email, created_at FROM student_records WHERE LOWER(TRIM(enrolled_course)) = LOWER(TRIM(?))";
                $stmt = mysqli_prepare($db_connection, $query);
                mysqli_stmt_bind_param($stmt, "s", $search_course);
            }
        } else {
            if (!empty($search_reg)) {
                $query = "SELECT full_name, roll_number AS registration_number, enrolled_course, email, created_at FROM student_records WHERE LOWER(TRIM(roll_number)) LIKE LOWER(TRIM(?))";
                $stmt = mysqli_prepare($db_connection, $query);
                $search_param = '%' . $search_reg . '%';
                mysqli_stmt_bind_param($stmt, "s", $search_param);
            } else {
                $query = "SELECT full_name, roll_number AS registration_number, enrolled_course, email, created_at FROM student_records WHERE LOWER(TRIM(enrolled_course)) = LOWER(TRIM(?))";
                $stmt = mysqli_prepare($db_connection, $query);
                mysqli_stmt_bind_param($stmt, "s", $search_course);
            }
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $search_result = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $search_result[] = $row;
        }
        mysqli_stmt_close($stmt);
        
        if (empty($search_result)) {
            $error_msg = "No students found matching your search criteria.";
        }
    }
}

// Get all courses
$courses_list = [];
if ($db_connection) {
    $courses_query = "SELECT course_name FROM courses ORDER BY course_name";
    $courses_result = mysqli_query($db_connection, $courses_query);
    if ($courses_result) {
        while ($row = mysqli_fetch_assoc($courses_result)) {
            $courses_list[] = $row['course_name'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <title>Check Student - Academic Results System</title>
    <style>
        .breadcrumb {
            padding: 15px 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
        }
        .breadcrumb a {
            color: white;
            text-decoration: none;
        }
        .search-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .search-box h3 {
            color: #27ae60;
            margin-bottom: 20px;
        }
        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        .search-form input, .search-form select {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        .search-form button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        .results-table {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .error-box {
            background: #ffebee;
            color: #c62828;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #f44336;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="breadcrumb">
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a> / 
        <span>Check Student</span>
    </div>

    <div class="main">
        <h2 style="text-align: center; color: #27ae60; margin-bottom: 30px;">
            <i class="fa fa-search"></i> Check Student Information
        </h2>
        
        <div class="search-box">
            <h3><i class="fa fa-search"></i> Search for Student</h3>
            <form method="post" class="search-form">
                <div>
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 600;">Registration Number:</label>
                    <input type="text" name="registration_number" placeholder="Enter registration number (e.g., 21/01631)" value="<?php echo isset($_POST['registration_number']) ? htmlspecialchars($_POST['registration_number']) : ''; ?>">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 600;">Or Select Course:</label>
                    <select name="course_name">
                        <option value="">All Courses</option>
                        <?php foreach ($courses_list as $course): ?>
                            <option value="<?php echo htmlspecialchars($course); ?>" <?php echo (isset($_POST['course_name']) && $_POST['course_name'] === $course) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" name="check_student">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($error_msg): ?>
            <div class="error-box">
                <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($search_result && !empty($search_result)): ?>
            <div class="results-table">
                <h3 style="color: #27ae60; margin-bottom: 20px;">
                    <i class="fa fa-list"></i> Search Results (<?php echo count($search_result); ?> found)
                </h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Full Name</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Registration Number</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Course</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Email</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">View Results</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_result as $student): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td style="padding: 12px; font-weight: 600; color: #27ae60;"><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($student['enrolled_course']); ?></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                <td style="padding: 12px;">
                                    <a href="student.php?course=<?php echo urlencode($student['enrolled_course']); ?>&regno=<?php echo urlencode($student['registration_number']); ?>" 
                                       style="color: #27ae60; text-decoration: none; font-weight: 600;">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2024 Academic Results Management System</p>
    </div>
</body>
</html>

