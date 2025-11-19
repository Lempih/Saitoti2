<?php
    session_start();
    require_once("db_config.php");

    if (isset($_POST["username"], $_POST["password"]) && isset($_POST["login_submit"]))
    {
        $input_username = trim($_POST["username"]);
        $input_password = trim($_POST["password"]);
        
        if (!$db_connection) {
            $_SESSION['error'] = "Database connection failed. Please try again later.";
            header("Location: login.php");
            exit();
        }
        
        // Use prepared statement to prevent SQL injection
        $login_query = "SELECT admin_username FROM administrators WHERE admin_username = ? AND admin_password = ?";
        $stmt = mysqli_prepare($db_connection, $login_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $input_username, $input_password);
            mysqli_stmt_execute($stmt);
            $login_result = mysqli_stmt_get_result($stmt);
            $row_count = mysqli_num_rows($login_result);
            mysqli_stmt_close($stmt);
            
            if($row_count == 1) {
                $_SESSION['logged_in_user'] = $input_username;
                $_SESSION['success'] = "Login successful! Welcome back.";
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid credentials. Please check your username and password.";
            }
        } else {
            $_SESSION['error'] = "Database error. Please try again later.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login - Academic Results System</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="./js/toast.js"></script>
</head>
<body>
    <div class="title">
        <span>Academic Results Management System</span>
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <a href="student_signup.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600;">
            <i class="fa fa-user-plus"></i> Student Signup
        </a>
        <a href="student_login.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600;">
            <i class="fa fa-sign-in"></i> Student Login
        </a>
        <a href="index.html" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600;">
            <i class="fa fa-home"></i> Home
        </a>
    </div>

    <div class="main">
        <div class="login">
            <form action="" method="post" name="admin_login_form" id="adminLoginForm">
                <fieldset>
                    <legend class="heading">Administrator Access</legend>
                    <input type="text" name="username" placeholder="Username" autocomplete="off" required>
                    <input type="password" name="password" placeholder="Password" autocomplete="off" required>
                    <input type="submit" value="Sign In" name="login_submit" id="adminLoginBtn">
                </fieldset>
            </form>    
        </div>
        <div class="search">
            <form action="./student.php" method="get">
                <fieldset>
                    <legend class="heading">Student Portal</legend>

                    <?php
                        if ($db_connection) {
                            $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                            $course_result = mysqli_query($db_connection, $course_query);
                            
                            if ($course_result && mysqli_num_rows($course_result) > 0) {
                                echo '<select name="course" required>';
                                echo '<option value="" selected disabled>Choose Course</option>';
                                
                                while($course_row = mysqli_fetch_array($course_result)){
                                    $course_display = htmlspecialchars($course_row['course_name']);
                                    echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<p style="color: #e74c3c; padding: 10px; text-align: center;">No courses available</p>';
                            }
                        }
                    ?>

                    <input type="number" name="rollno" placeholder="Enter Roll Number" required>
                    <input type="submit" value="View Results">
                    <p style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem;">
                        Check results without logging in
                    </p>
                </fieldset>
            </form>
        </div>
    </div>

    <script>
        // Show toast notifications
        <?php if (isset($_SESSION['error'])): ?>
            showError('<?php echo addslashes($_SESSION['error']); ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            showSuccess('<?php echo addslashes($_SESSION['success']); ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        // Form submission handling
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('adminLoginBtn');
            btn.disabled = true;
            btn.value = 'Signing in...';
        });
    </script>

</body>
</html>
