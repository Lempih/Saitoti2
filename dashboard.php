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
    <script src="./js/main.js"></script>
    <script src="./js/toast.js"></script>
    <title>Admin Dashboard - Academic Results System</title>
    <style>
        .main h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #27ae60;
            font-size: 2rem;
            font-weight: 700;
        }
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
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    </div>

    <div class="main">
        <h2><i class="fa fa-dashboard"></i> System Overview</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-top: 30px;">
            <div class="stat-card">
                <i class="fa fa-book"></i>
                <p><strong>Total Courses</strong><br><?php echo $total_courses[0]; ?></p>
            </div>
            <div class="stat-card">
                <i class="fa fa-users"></i>
                <p><strong>Total Students</strong><br><?php echo $total_students[0]; ?></p>
            </div>
            <div class="stat-card">
                <i class="fa fa-file-text"></i>
                <p><strong>Total Results</strong><br><?php echo $total_results[0]; ?></p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Academic Results Management System</p>
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
