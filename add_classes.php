<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/form.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <script src="./js/main.js"></script>
    <title>Create New Course</title>
</head>
<body>
        
    <div class="title">
        <a href="dashboard.php"><img src="./images/logo1.png" alt="Logo" class="logo"></a>
        <span class="heading">Control Panel</span>
        <a href="logout.php" style="color: white"><span class="fa fa-sign-out fa-2x">Logout</span></a>
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
        <form action="" method="post">
            <fieldset>
                <legend>Create New Course</legend>
                <input type="text" name="course_name" placeholder="Course Name" required>
                <input type="number" name="course_id" placeholder="Course ID (Numeric)" required>
                <input type="submit" value="Create Course" name="submit_course">
            </fieldset>        
        </form>
    </div>

    <div class="footer">
    </div>
</body>
</html>

<?php 
	require_once('db_config.php');
    require_once('auth_check.php');

    if (isset($_POST['course_name'], $_POST['course_id'], $_POST['submit_course'])) {
        $course_name = trim($_POST["course_name"]);
        $course_id = trim($_POST["course_id"]);

        // Validation
        if (empty($course_name) || empty($course_id) || !is_numeric($course_id) || $course_id <= 0) {
            if(empty($course_name))
                echo '<p class="error">Please enter course name</p>';
            if(empty($course_id))
                echo '<p class="error">Please enter course ID</p>';
            if(!is_numeric($course_id) || $course_id <= 0)
                echo '<p class="error">Course ID must be a positive number</p>';
            exit();
        }

        // Check if course ID already exists
        $check_query = "SELECT course_id FROM courses WHERE course_id = ?";
        $check_stmt = mysqli_prepare($db_connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $course_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if(mysqli_num_rows($check_result) > 0) {
            echo '<script language="javascript">';
            echo 'alert("Course ID already exists. Please use a different ID.")';
            echo '</script>';
            mysqli_stmt_close($check_stmt);
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
            echo '<script language="javascript">';
            echo 'alert("Error: Could not create course. It may already exist.")';
            echo '</script>';
        } else {
            echo '<script language="javascript">';
            echo 'alert("Course created successfully!")';
            echo 'window.location.href = "add_classes.php";';
            echo '</script>';
        }
    }
?>
