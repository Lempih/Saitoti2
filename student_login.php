<?php
    session_start();
    require_once("db_config.php");
    require_once("init_auth.php");
    
    // Redirect if already logged in
    if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
        header("Location: student_dashboard.php");
        exit();
    }
    
    // Handle login using registration_number as both identifier and password
    if (isset($_POST["registration_number"]) && isset($_POST["login_submit"]))
    {
        $registration_number = trim($_POST["registration_number"]);
        
        if (!$db_connection) {
            $_SESSION['error'] = "Database connection failed. Please try again later.";
            header("Location: student_login.php");
            exit();
        }
        
        // Check if registration_number column exists, if not check for roll_number (migration)
        $check_reg_col = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
        $col_check = mysqli_query($db_connection, $check_reg_col);
        $has_registration_col = $col_check && mysqli_num_rows($col_check) > 0;
        
        if (!$has_registration_col) {
            // Try to migrate
            $check_roll = "SHOW COLUMNS FROM student_records LIKE 'roll_number'";
            $roll_check = mysqli_query($db_connection, $check_roll);
            
            if ($roll_check && mysqli_num_rows($roll_check) > 0) {
                // Rename column
                @mysqli_query($db_connection, "ALTER TABLE student_records CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL");
                $has_registration_col = true;
            }
        }
        
        if (!$has_registration_col) {
            $_SESSION['error'] = "System not fully configured. Please run migrate_to_registration_number.php";
            header("Location: student_login.php");
            exit();
        }
        
        // Use registration_number as both identifier and password
        // Check if registration_number matches (it acts as password)
        $login_query = "SELECT full_name, email, registration_number, enrolled_course, profile_picture FROM student_records WHERE registration_number = ?";
        $stmt = mysqli_prepare($db_connection, $login_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $registration_number);
            mysqli_stmt_execute($stmt);
            $login_result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($login_result) == 1) {
                $student_data = mysqli_fetch_assoc($login_result);
                
                // Registration number acts as password - if it matches, login succeeds
                if ($student_data['registration_number'] === $registration_number) {
                    $_SESSION['student_logged_in'] = true;
                    $_SESSION['student_email'] = $student_data['email'];
                    $_SESSION['student_name'] = $student_data['full_name'];
                    $_SESSION['student_registration'] = $student_data['registration_number'];
                    $_SESSION['student_course'] = $student_data['enrolled_course'];
                    $_SESSION['success'] = "Login successful! Welcome back.";
                    mysqli_stmt_close($stmt);
                    header("Location: student_dashboard.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Invalid registration number.";
                }
            } else {
                $_SESSION['error'] = "Invalid registration number. Please check and try again.";
            }
            mysqli_stmt_close($stmt);
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
    <title>Student Login - Academic Results System</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="title">
        <span>Student Portal</span>
    </div>
    <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
        <a href="index.html" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fa fa-home"></i> Home
        </a>
        <a href="student_signup.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fa fa-user-plus"></i> Signup
        </a>
    </div>

    <div class="main">
        <div class="login">
            <form action="" method="post" name="student_login_form" id="loginForm">
                <fieldset>
                    <legend class="heading">
                        <i class="fa fa-sign-in"></i> Student Login
                    </legend>
                    <input type="text" name="registration_number" id="registration_number" placeholder="Registration Number" autocomplete="off" required>
                    <p style="text-align: center; margin-top: 10px; color: #666; font-size: 0.9rem;">
                        <i class="fa fa-info-circle"></i> Enter your registration number to login
                    </p>
                    <input type="submit" value="Login" name="login_submit" id="loginBtn">
                    <p style="text-align: center; margin-top: 20px; color: #666;">
                        Don't have an account? <a href="student_signup.php" style="color: #27ae60; text-decoration: none; font-weight: 600;">Sign up here</a>
                    </p>
                    <p style="text-align: center; margin-top: 10px;">
                        <a href="login.php" style="color: #27ae60; text-decoration: none; font-size: 0.9rem;">View Results (No Login Required)</a>
                    </p>
                </fieldset>
            </form>    
        </div>
        <div class="search">
            <form action="./student.php" method="get" id="quickResultForm">
                <fieldset>
                    <legend class="heading">
                        <i class="fa fa-search"></i> Quick Result Check
                    </legend>

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
                                echo '<p style="color: #e74c3c; padding: 10px; text-align: center; font-size: 0.9rem;">No courses available yet</p>';
                            }
                        }
                    ?>

                    <input type="number" name="rollno" placeholder="Enter Roll Number" required min="1">
                    <input type="submit" value="View Results">
                    <p style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem;">
                        Check results without logging in
                    </p>
                </fieldset>
            </form>
        </div>
    </div>

    <script src="./js/toast.js"></script>
    <script src="./js/password-toggle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['error'])): ?>
                setTimeout(function() {
                    if (typeof showError === 'function') {
                        showError('<?php echo addslashes($_SESSION['error']); ?>');
                    } else {
                        alert('<?php echo addslashes($_SESSION['error']); ?>');
                    }
                }, 200);
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                setTimeout(function() {
                    if (typeof showSuccess === 'function') {
                        showSuccess('<?php echo addslashes($_SESSION['success']); ?>');
                    }
                }, 200);
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            // Form submission handler
            var loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    var btn = document.getElementById('loginBtn');
                    if (btn && !btn.disabled) {
                        btn.disabled = true;
                        btn.value = 'Logging in...';
                    }
                    return true; // Allow form to submit
                });
            }
        });
    </script>
</body>
</html>
