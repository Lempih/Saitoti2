<?php
    require_once("db_config.php");
    
    // Get statistics
    $total_courses = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM courses"));
    $total_students = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM student_records"));
    $total_results = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM exam_results"));
?>
        
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <link rel="stylesheet" href="normalize.css">
    <script src="./js/main.js"></script>
    <title>Control Panel - Academic Results System</title>
    <style>
        .main{
            border-radius: 10px;
            border-width: 5px;
            border-style: solid;
            padding: 20px;
            margin: 7% 20% 0 20%;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
    </style>
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
        <h2 style="text-align: center; margin-bottom: 30px;">System Overview</h2>
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
</body>
</html>

<?php
   require_once('auth_check.php');
?>
