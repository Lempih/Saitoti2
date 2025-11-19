<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/student.css">
    <title>Student Result View</title>
</head>
<body>
    <?php
        require_once("db_config.php");

        if(!isset($_GET['course']))
            $course = null;
        else
            $course = trim($_GET['course']);
        
        $roll_number = isset($_GET['rollno']) ? intval($_GET['rollno']) : null;

        // Validation
        if (empty($course) || empty($roll_number) || $roll_number <= 0) {
            if(empty($course))
                echo '<p class="error">Please select your course</p>';
            if(empty($roll_number) || $roll_number <= 0)
                echo '<p class="error">Please enter a valid roll number</p>';
            exit();
        }

        // Get student name using prepared statement
        $name_query = "SELECT full_name FROM student_records WHERE roll_number = ? AND enrolled_course = ?";
        $name_stmt = mysqli_prepare($db_connection, $name_query);
        mysqli_stmt_bind_param($name_stmt, "is", $roll_number, $course);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        
        if(mysqli_num_rows($name_result) == 0) {
            echo '<p class="error">Student not found in this course.</p>';
            mysqli_stmt_close($name_stmt);
            exit();
        }
        
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
            echo "<div class='container'><p class='error'>No results found for this student.</p></div>";
            mysqli_stmt_close($result_stmt);
            exit();
        }
        
        $result_row = mysqli_fetch_assoc($result_data);
        $subject_1 = $result_row['subject_1'];
        $subject_2 = $result_row['subject_2'];
        $subject_3 = $result_row['subject_3'];
        $subject_4 = $result_row['subject_4'];
        $subject_5 = $result_row['subject_5'];
        $total_marks = $result_row['total_marks'];
        $grade_percentage = $result_row['grade_percentage'];
        mysqli_stmt_close($result_stmt);
    ?>

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
</body>
</html>
