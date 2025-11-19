<?php
    session_start();
    require_once("db_config.php");
    require_once("auth_check.php");

    if(isset($_POST['roll_number'], $_POST['subject_1'], $_POST['subject_2'], $_POST['subject_3'], $_POST['subject_4'], $_POST['subject_5'], $_POST['submit_results']))
    {
        $roll_number = intval($_POST['roll_number']);
        $course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : null;
        $subject_1 = intval($_POST['subject_1']);
        $subject_2 = intval($_POST['subject_2']);
        $subject_3 = intval($_POST['subject_3']);
        $subject_4 = intval($_POST['subject_4']);
        $subject_5 = intval($_POST['subject_5']);

        $total_marks = $subject_1 + $subject_2 + $subject_3 + $subject_4 + $subject_5;
        $grade_percentage = round($total_marks / 5, 2);

        // Validation
        $errors = [];
        if (empty($course_name)) {
            $errors[] = "Please select course";
        }
        if (empty($roll_number) || $roll_number <= 0) {
            $errors[] = "Please enter valid roll number";
        }
        if($subject_1 > 100 || $subject_2 > 100 || $subject_3 > 100 || $subject_4 > 100 || $subject_5 > 100 || 
           $subject_1 < 0 || $subject_2 < 0 || $subject_3 < 0 || $subject_4 < 0 || $subject_5 < 0) {
            $errors[] = "Marks must be between 0 and 100";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(". ", $errors);
            header("Location: add_results.php");
            exit();
        }

        // Get student name
        $name_query = "SELECT full_name FROM student_records WHERE roll_number = ? AND enrolled_course = ?";
        $name_stmt = mysqli_prepare($db_connection, $name_query);
        mysqli_stmt_bind_param($name_stmt, "is", $roll_number, $course_name);
        mysqli_stmt_execute($name_stmt);
        $name_result = mysqli_stmt_get_result($name_stmt);
        
        if(mysqli_num_rows($name_result) == 0) {
            $_SESSION['error'] = "Student not found in this course!";
            mysqli_stmt_close($name_stmt);
            header("Location: add_results.php");
            exit();
        }
        
        $name_row = mysqli_fetch_assoc($name_result);
        $student_name = $name_row['full_name'];
        mysqli_stmt_close($name_stmt);

        // Check if result already exists
        $check_query = "SELECT roll_number FROM exam_results WHERE roll_number = ? AND course_name = ?";
        $check_stmt = mysqli_prepare($db_connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "is", $roll_number, $course_name);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Result for this student already exists. Please use update function in Manage Results.";
            mysqli_stmt_close($check_stmt);
            header("Location: add_results.php");
            exit();
        }
        mysqli_stmt_close($check_stmt);

        // Insert result using prepared statement
        $insert_query = "INSERT INTO exam_results (student_name, roll_number, course_name, subject_1, subject_2, subject_3, subject_4, subject_5, total_marks, grade_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_connection, $insert_query);
        mysqli_stmt_bind_param($stmt, "sisiiiiiii", $student_name, $roll_number, $course_name, $subject_1, $subject_2, $subject_3, $subject_4, $subject_5, $total_marks, $grade_percentage);
        $insert_result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$insert_result) {
            $_SESSION['error'] = "Error: Could not save results. Please try again.";
            header("Location: add_results.php");
            exit();
        }
        else{
            $_SESSION['success'] = "Results saved successfully!";
            header("Location: add_results.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <link rel="stylesheet" href="./css/form.css">
    <script src="./js/main.js"></script>
    <title>Enter Examination Results - Academic Results System</title>
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
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="breadcrumb">
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a> / 
        <a href="manage_results.php">Results Management</a> / 
        <span>Enter Results</span>
    </div>

    <div class="main">
        <form action="" method="post" id="resultsForm">
            <fieldset>
            <legend><i class="fa fa-edit"></i> Enter Student Marks</legend>

                <?php
                    if ($db_connection) {
                        $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                        $course_result = mysqli_query($db_connection, $course_query);
                        
                        if ($course_result && mysqli_num_rows($course_result) > 0) {
                            echo '<select name="course_name" required>';
                            echo '<option value="" selected disabled>Select Course</option>';
                            
                            while($course_row = mysqli_fetch_array($course_result)) {
                                $course_display = htmlspecialchars($course_row['course_name']);
                                echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                            }
                            echo '</select>';
                        } else {
                            echo '<p style="color: #e74c3c; padding: 10px; text-align: center;">No courses available. <a href="add_classes.php" style="color: #27ae60;">Create a course first</a></p>';
                        }
                    }
                ?>

                <input type="number" name="roll_number" placeholder="Roll Number" required min="1">
                <input type="number" name="subject_1" id="" placeholder="Subject 1 Marks" min="0" max="100" required>
                <input type="number" name="subject_2" id="" placeholder="Subject 2 Marks" min="0" max="100" required>
                <input type="number" name="subject_3" id="" placeholder="Subject 3 Marks" min="0" max="100" required>
                <input type="number" name="subject_4" id="" placeholder="Subject 4 Marks" min="0" max="100" required>
                <input type="number" name="subject_5" id="" placeholder="Subject 5 Marks" min="0" max="100" required>
                <input type="submit" value="Submit Results" name="submit_results" id="submitBtn">
            </fieldset>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
        <?php if (isset($_SESSION['error'])): ?>
            showError('<?php echo addslashes($_SESSION['error']); ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            showSuccess('<?php echo addslashes($_SESSION['success']); ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        document.getElementById('resultsForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.value = 'Submitting...';
        });
    
        });
    </script>

    <script src="./js/toast.js"></script>
</body>
</html>
