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
</head>
<body>
    <div class="title">
        <span>Academic Results Management System</span>
    </div>
    <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
        <a href="index.html" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fa fa-home"></i> Home
        </a>
        <a href="student_signup.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fa fa-user-plus"></i> Student Signup
        </a>
        <a href="student_login.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fa fa-sign-in"></i> Student Login
        </a>
    </div>

    <div class="main">
        <div class="login">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" name="admin_login_form" id="adminLoginForm" novalidate>
                <fieldset>
                    <legend class="heading"><i class="fa fa-user-shield"></i> Administrator Access</legend>
                    <input type="text" name="username" placeholder="Username" autocomplete="off" required>
                    <input type="password" name="password" placeholder="Password" autocomplete="off" required>
                    <input type="submit" value="Sign In" name="login_submit" id="adminLoginBtn">
                    <p style="text-align: center; margin-top: 15px; color: #666; font-size: 0.9rem;">
                        Default: <strong>administrator</strong> / <strong>admin2024</strong>
                    </p>
                </fieldset>
            </form>    
        </div>
        <div class="search">
            <form action="./student.php" method="get" id="quickResultForm">
                <fieldset>
                    <legend class="heading"><i class="fa fa-search"></i> Quick Result Check</legend>

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
    <script>
        // Wait for DOM and toast to be ready
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

            // Form submission handler - DO NOT prevent default
            var adminForm = document.getElementById('adminLoginForm');
            if (adminForm) {
                // Remove any existing handlers first
                var newForm = adminForm.cloneNode(true);
                adminForm.parentNode.replaceChild(newForm, adminForm);
                adminForm = newForm;
                
                adminForm.addEventListener('submit', function(e) {
                    console.log('Form submitting...');
                    // Don't prevent default - let form submit normally
                    var btn = document.getElementById('adminLoginBtn');
                    if (btn && !btn.disabled) {
                        btn.disabled = true;
                        btn.value = 'Signing in...';
                    }
                    // Form will submit normally - no e.preventDefault()
                }, false);
                
                // Also handle button click as fallback
                var btn = document.getElementById('adminLoginBtn');
                if (btn) {
                    btn.addEventListener('click', function(e) {
                        console.log('Button clicked');
                        // Don't prevent - let form submit
                        var form = document.getElementById('adminLoginForm');
                        if (form) {
                            // Check if form is valid
                            if (form.checkValidity()) {
                                console.log('Form is valid, submitting...');
                                // Form will submit
                            } else {
                                console.log('Form validation failed');
                                form.reportValidity();
                            }
                        }
                    }, false);
                }
            }
        });
    </script>

</body>
</html>
