<?php
    session_start();
    require_once("db_config.php");
    
    // Handle registration
    if (isset($_POST["full_name"], $_POST["email"], $_POST["roll_number"], $_POST["password"], $_POST["confirm_password"], $_POST["course_name"], $_POST["signup_submit"]))
    {
        if (!$db_connection) {
            $_SESSION['error'] = "Database connection failed. Please try again later.";
            header("Location: student_signup.php");
            exit();
        }

        $full_name = trim($_POST["full_name"]);
        $email = trim($_POST["email"]);
        $roll_number = intval($_POST["roll_number"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $course_name = trim($_POST["course_name"]);

        // Validation
        $errors = [];

        if (empty($full_name) || strlen($full_name) < 3) {
            $errors[] = "Full name must be at least 3 characters long.";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        if (empty($roll_number) || $roll_number <= 0) {
            $errors[] = "Please enter a valid roll number.";
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match!";
        }

        if (empty($course_name)) {
            $errors[] = "Please select a course.";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(" ", $errors);
            header("Location: student_signup.php");
            exit();
        }

        // Check if email already exists
        $check_email = "SELECT email FROM student_records WHERE email = ?";
        $stmt_check = mysqli_prepare($db_connection, $check_email);
        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            
            if(mysqli_num_rows($result_check) > 0) {
                $_SESSION['error'] = "Email already registered. Please use a different email or login.";
                mysqli_stmt_close($stmt_check);
                header("Location: student_signup.php");
                exit();
            }
            mysqli_stmt_close($stmt_check);
        }

        // Check if roll number already exists for this course
        $check_roll = "SELECT roll_number FROM student_records WHERE roll_number = ? AND enrolled_course = ?";
        $stmt_roll = mysqli_prepare($db_connection, $check_roll);
        if ($stmt_roll) {
            mysqli_stmt_bind_param($stmt_roll, "is", $roll_number, $course_name);
            mysqli_stmt_execute($stmt_roll);
            $result_roll = mysqli_stmt_get_result($stmt_roll);
            
            if(mysqli_num_rows($result_roll) > 0) {
                $_SESSION['error'] = "Roll number already exists for this course. Please contact administrator.";
                mysqli_stmt_close($stmt_roll);
                header("Location: student_signup.php");
                exit();
            }
            mysqli_stmt_close($stmt_roll);
        }

        // Verify course exists
        $verify_course = "SELECT course_name FROM courses WHERE course_name = ?";
        $stmt_verify = mysqli_prepare($db_connection, $verify_course);
        if ($stmt_verify) {
            mysqli_stmt_bind_param($stmt_verify, "s", $course_name);
            mysqli_stmt_execute($stmt_verify);
            $result_verify = mysqli_stmt_get_result($stmt_verify);
            
            if(mysqli_num_rows($result_verify) == 0) {
                $_SESSION['error'] = "Invalid course selected. Please refresh the page and try again.";
                mysqli_stmt_close($stmt_verify);
                header("Location: student_signup.php");
                exit();
            }
            mysqli_stmt_close($stmt_verify);
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert student using prepared statement
        $insert_query = "INSERT INTO student_records (full_name, email, roll_number, enrolled_course, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_connection, $insert_query);
        
        if (!$stmt) {
            $_SESSION['error'] = "Database error. Please contact administrator.";
            header("Location: student_signup.php");
            exit();
        }

        mysqli_stmt_bind_param($stmt, "ssiss", $full_name, $email, $roll_number, $course_name, $hashed_password);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            mysqli_stmt_close($stmt);
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: student_login.php");
            exit();
        } else {
            $error_code = mysqli_errno($db_connection);
            $user_message = "Registration failed. ";
            if ($error_code == 1062) {
                $user_message .= "This email or roll number is already registered.";
            } elseif ($error_code == 1452) {
                $user_message .= "Invalid course selected.";
            } else {
                $user_message .= "Please try again or contact administrator.";
            }
            $_SESSION['error'] = $user_message;
            mysqli_stmt_close($stmt);
            header("Location: student_signup.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Academic Results System</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    </head>
<body>
    <div class="title">
        <span>Student Registration</span>
    </div>
    <div style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
        <a href="index.html" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fa fa-home"></i> Home
        </a>
        <a href="student_login.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: 600; padding: 8px 15px; border-radius: 20px; background: rgba(255,255,255,0.2); transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fa fa-sign-in"></i> Login
        </a>
    </div>

    <div class="main" style="grid-template-columns: 1fr; max-width: 600px; margin: 100px auto 0;">
        <div class="login">
            <form action="" method="post" name="student_signup_form" id="signupForm" onsubmit="return validateForm()">
                <fieldset>
                    <legend class="heading">
                        <i class="fa fa-user-plus"></i> Create Student Account
                    </legend>
                    <input type="text" name="full_name" id="full_name" placeholder="Full Name" autocomplete="off" required minlength="3">
                    <input type="email" name="email" id="email" placeholder="Email Address" autocomplete="off" required>
                    <input type="number" name="roll_number" id="roll_number" placeholder="Roll Number" autocomplete="off" required min="1">
                    <input type="password" name="password" id="password" placeholder="Password (min 6 characters)" autocomplete="off" required minlength="6">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" autocomplete="off" required>
                    
                    <?php
                        if ($db_connection) {
                            $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                            $course_result = mysqli_query($db_connection, $course_query);
                            
                            if ($course_result && mysqli_num_rows($course_result) > 0) {
                                echo '<select name="course_name" id="course_name" required>';
                                echo '<option value="" selected disabled>Select Your Course</option>';
                                
                                while($course_row = mysqli_fetch_array($course_result)){
                                    $course_display = htmlspecialchars($course_row['course_name']);
                                    echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<p style="color: #e74c3c; padding: 10px; text-align: center;">No courses available. Please contact administrator.</p>';
                            }
                        } else {
                            echo '<p style="color: #e74c3c; padding: 10px; text-align: center;">Database connection error. Please try again later.</p>';
                        }
                    ?>
                    
                    <input type="submit" value="Register" name="signup_submit" id="submitBtn">
                    <p style="text-align: center; margin-top: 20px; color: #666;">
                        Already have an account? <a href="student_login.php" style="color: #27ae60; text-decoration: none; font-weight: 600;">Login here</a>
                    </p>
                </fieldset>
            </form>    
        </div>
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

        function validateForm() {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm_password').value;
            var fullName = document.getElementById('full_name').value;
            var email = document.getElementById('email').value;
            var rollNumber = document.getElementById('roll_number').value;
            var course = document.getElementById('course_name').value;

            var submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.value = 'Registering...';

            if (password !== confirmPassword) {
                showError('Passwords do not match!');
                submitBtn.disabled = false;
                submitBtn.value = 'Register';
                return false;
            }

            if (password.length < 6) {
                showError('Password must be at least 6 characters long.');
                submitBtn.disabled = false;
                submitBtn.value = 'Register';
                return false;
            }

            if (fullName.length < 3) {
                showError('Full name must be at least 3 characters long.');
                submitBtn.disabled = false;
                submitBtn.value = 'Register';
                return false;
            }

            if (!email.includes('@')) {
                showError('Please enter a valid email address.');
                submitBtn.disabled = false;
                submitBtn.value = 'Register';
                return false;
            }

            if (!course) {
                showError('Please select a course.');
                submitBtn.disabled = false;
                submitBtn.value = 'Register';
                return false;
            }

            return true;
        }

        window.addEventListener('load', function() {
            var submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.value = 'Register';
            }
        });
    
        });
    </script>

    <script src="./js/toast.js"></script>
</body>
</html>
