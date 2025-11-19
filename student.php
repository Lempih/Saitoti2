<?php
    session_start();
    require_once("db_config.php");

    if(!isset($_GET['course']))
        $course = null;
    else
        $course = trim($_GET['course']);
    
    $roll_number = isset($_GET['rollno']) ? intval($_GET['rollno']) : null;

    // Validation
    if (empty($course) || empty($roll_number) || $roll_number <= 0) {
        $error_msg = [];
        if(empty($course))
            $error_msg[] = "Please select your course";
        if(empty($roll_number) || $roll_number <= 0)
            $error_msg[] = "Please enter a valid roll number";
    } else {
        if (!$db_connection) {
            $error_msg = ["Database connection failed. Please try again later."];
        } else {
            // Get student name using prepared statement
            $name_query = "SELECT full_name FROM student_records WHERE roll_number = ? AND enrolled_course = ?";
            $name_stmt = mysqli_prepare($db_connection, $name_query);
            mysqli_stmt_bind_param($name_stmt, "is", $roll_number, $course);
            mysqli_stmt_execute($name_stmt);
            $name_result = mysqli_stmt_get_result($name_stmt);
            
            if(mysqli_num_rows($name_result) == 0) {
                $error_msg = ["Student not found in this course."];
            } else {
                $name_row = mysqli_fetch_assoc($name_result);
                $student_name = $name_row['full_name'];
                mysqli_stmt_close($name_stmt);

                // Get result using prepared statement
                $result_query = "SELECT subject_1, subject_2, subject_3, subject_4, subject_5, total_marks, grade_percentage FROM exam_results WHERE roll_number = ? AND course_name = ?";
                $result_stmt = mysqli_prepare($db_connection, $result_query);
                mysqli_stmt_bind_param($result_stmt, "is", $roll_number, $course);
                mysqli_stmt_execute($result_stmt);
                $result_data = mysqli_stmt_get_result($result_stmt);
                
                if(mysqli_num_rows($result_data) == 0){
                    $error_msg = ["No results found for this student."];
                } else {
                    $result_row = mysqli_fetch_assoc($result_data);
                    $subject_1 = $result_row['subject_1'];
                    $subject_2 = $result_row['subject_2'];
                    $subject_3 = $result_row['subject_3'];
                    $subject_4 = $result_row['subject_4'];
                    $subject_5 = $result_row['subject_5'];
                    $total_marks = $result_row['total_marks'];
                    $grade_percentage = $result_row['grade_percentage'];
                    $has_results = true;
                }
                if (isset($result_stmt)) mysqli_stmt_close($result_stmt);
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <title>Student Result View - Academic Results System</title>
    <style>
        .nav-bar {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .nav-bar a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        .nav-bar a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        .nav-bar .logo {
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="logo">
            <i class="fa fa-graduation-cap"></i> Academic Results System
        </div>
        <div>
            <a href="index.html"><i class="fa fa-home"></i> Home</a>
            <a href="login.php"><i class="fa fa-search"></i> Quick Results</a>
            <?php if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true): ?>
                <a href="student_dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a>
            <?php else: ?>
                <a href="student_login.php"><i class="fa fa-sign-in"></i> Student Login</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($error_msg) && !empty($error_msg)): ?>
        <div class="container">
            <div class="error">
                <i class="fa fa-exclamation-circle"></i>
                <p><?php echo implode(" ", $error_msg); ?></p>
                <a href="login.php" style="color: #27ae60; text-decoration: none; font-weight: 600; margin-top: 20px; display: inline-block; padding: 10px 20px; background: #f0f0f0; border-radius: 5px;">
                    <i class="fa fa-arrow-left"></i> Go Back
                </a>
            </div>
        </div>
        <script src="./js/toast.js"></script>
        <script>
            // Wait for toast to be ready
            if (typeof showError === 'function') {
                showError('<?php echo addslashes(implode(" ", $error_msg)); ?>');
            } else {
                // Fallback if toast not ready
                setTimeout(function() {
                    if (typeof showError === 'function') {
                        showError('<?php echo addslashes(implode(" ", $error_msg)); ?>');
                    }
                }, 500);
            }
        </script>
    <?php elseif (isset($has_results) && $has_results): ?>
    <div class="container">
        <div class="details">
            <span>Student Name:</span> <?php echo htmlspecialchars($student_name); ?> <br>
            <span>Course:</span> <?php echo htmlspecialchars($course); ?> <br>
            <span>Roll Number:</span> <?php echo htmlspecialchars($roll_number); ?> <br>
        </div>

        <div class="main">
            <div class="s1">
                <p>Subjects</p>
                <p>Subject 1</p>
                <p>Subject 2</p>
                <p>Subject 3</p>
                <p>Subject 4</p>
                <p>Subject 5</p>
            </div>
            <div class="s2">
                <p>Marks Obtained</p>
                <?php echo '<p>'.$subject_1.'</p>';?>
                <?php echo '<p>'.$subject_2.'</p>';?>
                <?php echo '<p>'.$subject_3.'</p>';?>
                <?php echo '<p>'.$subject_4.'</p>';?>
                <?php echo '<p>'.$subject_5.'</p>';?>
            </div>
        </div>

        <div class="result">
            <?php echo '<p>Total Marks: &nbsp;'.$total_marks.'</p>';?>
            <?php echo '<p>Percentage: &nbsp;'.number_format($grade_percentage, 2).'%</p>';?>
        </div>

        <div class="button">
            <button onclick="window.print()" style="background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%); color: white; border: none; padding: 15px 40px; border-radius: 30px; font-size: 1.1rem; font-weight: 600; cursor: pointer; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(39, 174, 96, 0.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(39, 174, 96, 0.4)'">
                <i class="fa fa-print"></i> Print Result
            </button>
        </div>
    </div>
    <?php endif; ?>
    <script src="./js/toast.js"></script>
</body>
</html>
