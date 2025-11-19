<?php
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/student.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <script src="./js/toast.js"></script>
    <title>Student Result View</title>
</head>
<body>
    <?php if (isset($error_msg) && !empty($error_msg)): ?>
        <div class="container">
            <div class="error">
                <i class="fa fa-exclamation-circle"></i>
                <p><?php echo implode(" ", $error_msg); ?></p>
                <a href="login.php" style="color: #27ae60; text-decoration: none; font-weight: 600; margin-top: 20px; display: inline-block;">← Go Back</a>
            </div>
        </div>
        <script>
            showError('<?php echo addslashes(implode(" ", $error_msg)); ?>');
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
            <button onclick="window.print()">Print Result</button>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
