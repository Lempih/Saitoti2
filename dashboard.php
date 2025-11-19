<?php
    session_start();
    require_once("db_config.php");
    require_once('auth_check.php');
    
    // Get statistics
    if ($db_connection) {
        $total_courses = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM courses"));
        $total_students = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM student_records"));
        $total_results = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM exam_results"));
    } else {
        $total_courses = [0];
        $total_students = [0];
        $total_results = [0];
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
    <link rel="stylesheet" href="normalize.css">
    <script src="./js/main.js"></script>
    <script src="./js/toast.js"></script>
    <title>Control Panel - Academic Results System</title>
    <style>
        .main h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #27ae60;
            font-size: 2rem;
            font-weight: 700;
        }
    </style>
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
        <h2>System Overview</h2>
        <div class="stat-card">
            <p><strong>Total Courses:</strong> <?php echo $total_courses[0]; ?></p>
        </div>
        <div class="stat-card">
            <p><strong>Total Students:</strong> <?php echo $total_students[0]; ?></p>
        </div>
        <div class="stat-card">
            <p><strong>Total Results Recorded:</strong> <?php echo $total_results[0]; ?></p>
        </div>
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
    </script>
</body>
</html>
