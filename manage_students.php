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
    <title>Student Management - Academic Results System</title>
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
        <span>Student Management</span>
    </div>

    <div class="main">
        <?php
            if ($db_connection) {
                $query = "SELECT full_name, roll_number, enrolled_course, profile_picture FROM student_records ORDER BY enrolled_course, roll_number ASC";
                $result = mysqli_query($db_connection, $query);

                if (mysqli_num_rows($result) > 0) {
                   echo "<table>
                    <caption>All Registered Students</caption>
                    <tr>
                    <th>Profile Picture</th>
                    <th>Student Name</th>
                    <th>Roll Number</th>
                    <th>Enrolled Course</th>
                    </tr>";

                    while($row = mysqli_fetch_array($result))
                      {
                        echo "<tr>";
                        $profile_picture = isset($row['profile_picture']) && !empty($row['profile_picture']) && file_exists($row['profile_picture']) 
                            ? htmlspecialchars($row['profile_picture']) 
                            : null;
                        
                        echo "<td style='text-align: center;'>";
                        if ($profile_picture) {
                            echo "<img src='" . $profile_picture . "' alt='Profile Picture' style='width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd; cursor: pointer;' onclick='window.open(\"" . $profile_picture . "\", \"_blank\")' title='Click to view full size'>";
                        } else {
                            echo "<div style='width: 50px; height: 50px; border-radius: 50%; background: #f0f0f0; display: inline-flex; align-items: center; justify-content: center;'><i class='fa fa-user' style='color: #999;'></i></div>";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['roll_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['enrolled_course']) . "</td>";
                        echo "</tr>";
                      }

                    echo "</table>";
                } else {
                    echo "<p style='text-align: center; padding: 20px;'>No students registered yet. <a href='add_students.php' style='color: #27ae60; text-decoration: none; font-weight: 600;'>Register your first student</a></p>";
                }
            } else {
                echo "<p style='text-align: center; padding: 20px; color: #e74c3c;'>Database connection error. Please try again later.</p>";
            }
        ?>
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
    
        });
    </script>
    <script src="./js/toast.js"></script>
</body>
</html>
