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
    <title>Create New Course</title>
</head>
<body>
        
    <div class="title">
        <a href="dashboard.php"><img src="./images/logo1.png" alt="Logo" class="logo"></a>
        <span class="heading">Control Panel</span>
        <a href="logout.php" style="color: #27ae60">
            <span class="fa fa-sign-out fa-2x">Logout</span>
        </a>
    </div>

    <div class="nav">
        <ul>
            <li class="dropdown" onclick="toggleDisplay('1')">
                <a href="" class="dropbtn">Course Management &nbsp
                    <span class="fa fa-angle-down"></span>
                </a>
                <div class="dropdown-content" id="1">
                    <a href="add_classes.php">Create New Course</a>
                    <a href="manage_classes.php">View All Courses</a>
                </div>
            </li>
            <li class="dropdown" onclick="toggleDisplay('2')">
                <a href="#" class="dropbtn">Student Management &nbsp
                    <span class="fa fa-angle-down"></span>
                </a>
                <div class="dropdown-content" id="2">
                    <a href="add_students.php">Register Student</a>
                    <a href="manage_students.php">View All Students</a>
                </div>
            </li>
            <li class="dropdown" onclick="toggleDisplay('3')">
                <a href="#" class="dropbtn">Results Management &nbsp
                    <span class="fa fa-angle-down"></span>
                </a>
                <div class="dropdown-content" id="3">
                    <a href="add_results.php">Enter Examination Results</a>
                    <a href="manage_results.php">Manage Results</a>
                </div>
            </li>
        </ul>
    </div>

    <div class="main">
        <form action="" method="post" id="courseForm">
            <fieldset>
                <legend>Create New Course</legend>
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

        // Form submission handling
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.value = 'Creating...';
        });
    </script>
</body>
</html>
