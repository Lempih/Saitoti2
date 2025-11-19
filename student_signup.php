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

    <div class="main" style="grid-template-columns: 1fr; max-width: 600px; margin: 100px auto 0;">
        <div class="login">
            <form action="" method="post" name="student_signup_form">
                <fieldset>
                    <legend class="heading">
                        <i class="fa fa-user-plus"></i> Create Student Account
                    </legend>
                    <input type="text" name="full_name" placeholder="Full Name" autocomplete="off" required>
                    <input type="email" name="email" placeholder="Email Address" autocomplete="off" required>
                    <input type="number" name="roll_number" placeholder="Roll Number" autocomplete="off" required>
                    <input type="password" name="password" placeholder="Password (min 6 characters)" autocomplete="off" required minlength="6">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" autocomplete="off" required>
                    
                    <?php
                        require_once('db_config.php');

                        if ($db_connection) {
                            $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                            $course_result = mysqli_query($db_connection, $course_query);
                            
                            if ($course_result && mysqli_num_rows($course_result) > 0) {
                                echo '<select name="course_name" required>';
                                echo '<option value="" selected disabled>Select Your Course</option>';
                                
                                while($course_row = mysqli_fetch_array($course_result)){
                                    $course_display = htmlspecialchars($course_row['course_name']);
                                    echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                                }
                                echo '</select>';
                            } else {
                                echo '<p style="color: red; padding: 10px;">No courses available. Please contact administrator.</p>';
                            }
                        } else {
                            echo '<p style="color: red; padding: 10px;">Database connection error. Please try again later.</p>';
                        }
                    ?>
                    
                    <input type="submit" value="Register" name="signup_submit">
                    <p style="text-align: center; margin-top: 20px; color: #666;">
                        Already have an account? <a href="student_login.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Login here</a>
                    </p>
                </fieldset>
            </form>    
        </div>
    </div>

</body>
</html>

<?php
    require_once("db_config.php");
    session_start();

    if (isset($_POST["full_name"], $_POST["email"], $_POST["roll_number"], $_POST["password"], $_POST["confirm_password"], $_POST["course_name"], $_POST["signup_submit"]))
    {
        if (!$db_connection) {
            echo '<div style="position: fixed; top: 20px; right: 20px; background: #e74c3c; color: white; padding: 20px; border-radius: 10px; z-index: 9999;">
                    <strong>Error:</strong> Database connection failed. Please contact administrator.
                  </div>';
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
            echo '<script language="javascript">';
            echo 'alert("' . implode("\\n", $errors) . '")';
            echo '</script>';
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
                echo '<script language="javascript">';
                echo 'alert("Email already registered. Please use a different email or login.")';
                echo '</script>';
                mysqli_stmt_close($stmt_check);
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
                echo '<script language="javascript">';
                echo 'alert("Roll number already exists for this course. Please contact administrator.")';
                echo '</script>';
                mysqli_stmt_close($stmt_roll);
                exit();
            }
            mysqli_stmt_close($stmt_roll);
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Verify course exists
        $verify_course = "SELECT course_name FROM courses WHERE course_name = ?";
        $stmt_verify = mysqli_prepare($db_connection, $verify_course);
        if ($stmt_verify) {
            mysqli_stmt_bind_param($stmt_verify, "s", $course_name);
            mysqli_stmt_execute($stmt_verify);
            $result_verify = mysqli_stmt_get_result($stmt_verify);
            
            if(mysqli_num_rows($result_verify) == 0) {
                echo '<script language="javascript">';
                echo 'alert("Invalid course selected. Please refresh the page and try again.")';
                echo '</script>';
                mysqli_stmt_close($stmt_verify);
                exit();
            }
            mysqli_stmt_close($stmt_verify);
        }

        // Insert student using prepared statement
        // Note: full_name and roll_number are composite primary key, so we need both
        $insert_query = "INSERT INTO student_records (full_name, email, roll_number, enrolled_course, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_connection, $insert_query);
        
        if (!$stmt) {
            $error = mysqli_error($db_connection);
            echo '<div style="position: fixed; top: 20px; right: 20px; background: #e74c3c; color: white; padding: 20px; border-radius: 10px; z-index: 9999; max-width: 400px;">
                    <strong>Database Error:</strong><br>' . htmlspecialchars($error) . '
                  </div>';
            echo '<script language="javascript">';
            echo 'alert("Database error: ' . addslashes($error) . '\\n\\nPlease contact administrator.")';
            echo '</script>';
            exit();
        }

        mysqli_stmt_bind_param($stmt, "ssiss", $full_name, $email, $roll_number, $course_name, $hashed_password);
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            mysqli_stmt_close($stmt);
            echo '<script language="javascript">';
            echo 'alert("Registration successful! You can now login.")';
            echo 'window.location.href = "student_login.php";';
            echo '</script>';
            exit();
        } else {
            $error = mysqli_error($db_connection);
            $error_code = mysqli_errno($db_connection);
            mysqli_stmt_close($stmt);
            
            // More user-friendly error messages
            $user_message = "Registration failed. ";
            if ($error_code == 1062) {
                $user_message .= "This email or roll number is already registered.";
            } elseif ($error_code == 1452) {
                $user_message .= "Invalid course selected. Please refresh and try again.";
            } else {
                $user_message .= "Error: " . $error;
            }
            
            echo '<script language="javascript">';
            echo 'alert("' . addslashes($user_message) . '\\n\\nPlease try again or contact administrator.")';
            echo '</script>';
        }
    }
?>
