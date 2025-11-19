<?php
    session_start();
    require_once('db_config.php');
    require_once('auth_check.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <link rel="stylesheet" type='text/css' href="css/manage.css">
    <script src="./js/main.js"></script>
    <script src="./js/toast.js"></script>
    <title>Course Management - Academic Results System</title>
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
        <span>Course Management</span>
    </div>

    <div class="main">
        <?php
            if ($db_connection) {
                $query = "SELECT course_name, course_id FROM courses ORDER BY course_id ASC";
                $result = mysqli_query($db_connection, $query);

                if (mysqli_num_rows($result) > 0) {
                   echo "<table>
                    <caption>All Registered Courses</caption>
                    <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    </tr>";

                    while($row = mysqli_fetch_array($result))
                      {
                      echo "<tr>";
                      echo "<td>" . htmlspecialchars($row['course_id']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                      echo "</tr>";
                      }

                    echo "</table>";
                } else {
                    echo "<p style='text-align: center; padding: 20px;'>No courses registered yet. <a href='add_classes.php' style='color: #27ae60; text-decoration: none; font-weight: 600;'>Create your first course</a></p>";
                }
            } else {
                echo "<p style='text-align: center; padding: 20px; color: #e74c3c;'>Database connection error. Please try again later.</p>";
            }
        ?>
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
