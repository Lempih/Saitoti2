<?php 
	session_start();
	require_once('db_config.php');
    require_once('auth_check.php');

    if (isset($_POST['course_name'], $_POST['course_id'], $_POST['submit_course'])) {
        $course_name = trim($_POST["course_name"]);
        $course_id = trim($_POST["course_id"]);

        // Validation
        $errors = [];
        if (empty($course_name)) {
            $errors[] = "Please enter course name";
        }
        if (empty($course_id) || !is_numeric($course_id) || $course_id <= 0) {
            $errors[] = "Course ID must be a positive number";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(". ", $errors);
            header("Location: add_classes.php");
            exit();
        }

        // Check if course ID already exists
        $check_query = "SELECT course_id FROM courses WHERE course_id = ?";
        $check_stmt = mysqli_prepare($db_connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $course_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Course ID already exists. Please use a different ID.";
            mysqli_stmt_close($check_stmt);
            header("Location: add_classes.php");
            exit();
        }
        mysqli_stmt_close($check_stmt);

        // Insert new course using prepared statement
        $insert_query = "INSERT INTO courses (course_name, course_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($db_connection, $insert_query);
        mysqli_stmt_bind_param($stmt, "si", $course_name, $course_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$result) {
            $_SESSION['error'] = "Error: Could not create course. It may already exist.";
            header("Location: add_classes.php");
            exit();
        } else {
            $_SESSION['success'] = "Course created successfully!";
            header("Location: add_classes.php");
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
    <link rel="stylesheet" href="./css/form.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <script src="./js/main.js"></script>
    <script src="./js/toast.js"></script>
    <title>Create New Course - Academic Results System</title>
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
        <a href="manage_classes.php">Course Management</a> / 
        <span>Create New Course</span>
    </div>

    <div class="main">
        <form action="" method="post" id="courseForm">
            <fieldset>
                <legend><i class="fa fa-plus-circle"></i> Create New Course</legend>
                <input type="text" name="course_name" placeholder="Course Name" required>
                <input type="number" name="course_id" placeholder="Course ID (Numeric)" required min="1">
                <input type="submit" value="Create Course" name="submit_course" id="submitBtn">
            </fieldset>        
        </form>
    </div>

    <div class="footer">
    </div>

    <script>
        <?php if (isset($_SESSION['error'])): ?>
            showError('<?php echo addslashes($_SESSION['error']); ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            showSuccess('<?php echo addslashes($_SESSION['success']); ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        document.getElementById('courseForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.value = 'Creating...';
        });
    </script>
</body>
</html>
