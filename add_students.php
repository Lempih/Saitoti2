<?php
    session_start();
    require_once('db_config.php');
    require_once('auth_check.php');

    // Handle form submission
    if(isset($_POST['student_name'], $_POST['roll_number'], $_POST['submit_student'])) {
        $student_name = trim($_POST['student_name']);
        $roll_number = intval($_POST['roll_number']);
        $course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : null;

        // Validation
        $errors = [];
        if (empty($student_name) || strlen($student_name) < 3) {
            $errors[] = "Please enter student name (min 3 characters)";
        }
        if (empty($course_name)) {
            $errors[] = "Please select a course";
        }
        if (empty($roll_number) || $roll_number <= 0) {
            $errors[] = "Please enter a valid roll number";
        }
        if (!preg_match("/^[a-zA-Z\s]+$/", $student_name)) {
            $errors[] = "Name should only contain letters and spaces";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(". ", $errors);
            header("Location: add_students.php");
            exit();
        }

        // Check if student with same name and roll number already exists
        $check_query = "SELECT full_name, roll_number FROM student_records WHERE full_name = ? AND roll_number = ?";
        $check_stmt = mysqli_prepare($db_connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "si", $student_name, $roll_number);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Student with this name and roll number already exists!";
            mysqli_stmt_close($check_stmt);
            header("Location: add_students.php");
            exit();
        }
        mysqli_stmt_close($check_stmt);
        
        // Insert student using prepared statement
        $insert_query = "INSERT INTO student_records (full_name, roll_number, enrolled_course) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($db_connection, $insert_query);
        mysqli_stmt_bind_param($stmt, "sis", $student_name, $roll_number, $course_name);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$result) {
            $_SESSION['error'] = "Error: Could not register student. Please check the details.";
            header("Location: add_students.php");
            exit();
        }
        else{
            $_SESSION['success'] = "Student registered successfully!";
            header("Location: add_students.php");
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
    <link rel="stylesheet" type="text/css" href="./css/form.css" media="all">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <script src="./js/main.js"></script>
    <title>Register New Student - Academic Results System</title>
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
        <a href="manage_students.php">Student Management</a> / 
        <span>Register Student</span>
    </div>

    <div class="main">
        <form action="" method="post" id="studentForm">
            <fieldset>
                <legend><i class="fa fa-user-plus"></i> Register New Student</legend>
                <input type="text" name="student_name" placeholder="Full Name" required minlength="3">
                <input type="number" name="roll_number" placeholder="Roll Number" required min="1">
                <?php
                    if ($db_connection) {
                        $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                        $course_result = mysqli_query($db_connection, $course_query);
                        
                        if ($course_result && mysqli_num_rows($course_result) > 0) {
                            echo '<select name="course_name" required>';
                            echo '<option value="" selected disabled>Select Course</option>';
                            
                            while($course_row = mysqli_fetch_array($course_result)){
                                $course_display = htmlspecialchars($course_row['course_name']);
                                echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                            }
                            echo '</select>';
                        } else {
                            echo '<p style="color: #e74c3c; padding: 10px; text-align: center;">No courses available. <a href="add_classes.php" style="color: #27ae60;">Create a course first</a></p>';
                        }
                    }
                ?>
                <input type="submit" value="Register Student" name="submit_student" id="submitBtn">
            </fieldset>
        </form>
    </div>

    <div class="footer">
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

        document.getElementById('studentForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.value = 'Registering...';
        });
    
        });
    </script>
    <script src="./js/toast.js"></script>
</body>
</html>
