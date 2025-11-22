<?php
    session_start();
    require_once("db_config.php");
    require_once("init_auth.php");
    
    // Redirect if already logged in
    if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
        header("Location: student_dashboard.php");
        exit();
    }
    
    // Handle login
    if (isset($_POST["email"], $_POST["password"]) && isset($_POST["login_submit"]))
    {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        
        if (!$db_connection) {
            $_SESSION['error'] = "Database connection failed. Please try again later.";
            header("Location: student_login.php");
            exit();
        }
        
        // Check if password column exists
        $check_password_col = "SHOW COLUMNS FROM student_records LIKE 'password'";
        $col_check = mysqli_query($db_connection, $check_password_col);
        
        if (!$col_check || mysqli_num_rows($col_check) == 0) {
            $_SESSION['error'] = "System not fully configured. Please contact administrator.";
            header("Location: student_login.php");
            exit();
        }
        
        // Use prepared statement to prevent SQL injection
        $login_query = "SELECT full_name, email, roll_number, enrolled_course, password FROM student_records WHERE email = ?";
        $stmt = mysqli_prepare($db_connection, $login_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $login_result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($login_result) == 1) {
                $student_data = mysqli_fetch_assoc($login_result);
                
                // Check if password column has a value
                if (empty($student_data['password'])) {
                    $_SESSION['error'] = "Your account is not fully set up. Please sign up again or contact administrator.";
                    mysqli_stmt_close($stmt);
                    header("Location: student_login.php");
                    exit();
                }
                
                // Verify password (handle both hashed and plain text for migration)
                $password_valid = false;
                if (password_verify($password, $student_data['password'])) {
                    $password_valid = true;
                } elseif (strlen($student_data['password']) < 60 && $student_data['password'] === $password) {
                    // Plain text password (for backward compatibility during migration)
                    // Re-hash it
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $update_hash = "UPDATE student_records SET password = ? WHERE email = ?";
                    $update_stmt = mysqli_prepare($db_connection, $update_hash);
                    if ($update_stmt) {
                        mysqli_stmt_bind_param($update_stmt, "ss", $hashed, $email);
                        mysqli_stmt_execute($update_stmt);
                        mysqli_stmt_close($update_stmt);
                    }
                    $password_valid = true;
                }
                
                if ($password_valid) {
                    $_SESSION['student_logged_in'] = true;
                    $_SESSION['student_email'] = $student_data['email'];
                    $_SESSION['student_name'] = $student_data['full_name'];
                    $_SESSION['student_roll'] = $student_data['roll_number'];
                    $_SESSION['student_course'] = $student_data['enrolled_course'];
                    $_SESSION['success'] = "Login successful! Welcome back.";
                    mysqli_stmt_close($stmt);
                    header("Location: student_dashboard.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Invalid email or password.";
                }
            } else {
                $_SESSION['error'] = "Invalid email or password.";
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
                    <input type="email" name="email" placeholder="Email Address" autocomplete="off" required>
                    <input type="password" name="password" id="password" placeholder="Password" autocomplete="off" required>
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
