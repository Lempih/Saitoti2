<?php
    session_start();
    require_once("db_config.php");
    require_once("init_auth.php");
    
    // Redirect if already logged in
    if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
        header("Location: student_dashboard.php");
        exit();
    }
    
    // Handle registration - Registration number acts as both identifier and password
    if (isset($_POST["full_name"], $_POST["email"], $_POST["registration_number"], $_POST["course_name"], $_POST["signup_submit"]))
    {
        if (!$db_connection) {
            $_SESSION['error'] = "Database connection failed. Please try again later.";
            header("Location: student_signup.php");
            exit();
        }

        $full_name = trim($_POST["full_name"]);
        $email = trim($_POST["email"]);
        $registration_number = trim($_POST["registration_number"]);
        $course_name = trim($_POST["course_name"]);

        // Validation
        $errors = [];

        if (empty($full_name) || strlen($full_name) < 3) {
            $errors[] = "Full name must be at least 3 characters long.";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }

        if (empty($registration_number) || strlen($registration_number) < 3) {
            $errors[] = "Please enter a valid registration number (minimum 3 characters).";
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

        // Check if registration number already exists (it must be unique across all courses)
        // First check which column exists
        $check_reg_col = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
        $reg_col_check = mysqli_query($db_connection, $check_reg_col);
        $has_registration_col = $reg_col_check && mysqli_num_rows($reg_col_check) > 0;
        
        if ($has_registration_col) {
            $check_reg = "SELECT registration_number FROM student_records WHERE registration_number = ?";
            $stmt_reg = mysqli_prepare($db_connection, $check_reg);
            if ($stmt_reg) {
                mysqli_stmt_bind_param($stmt_reg, "s", $registration_number);
                mysqli_stmt_execute($stmt_reg);
                $result_reg = mysqli_stmt_get_result($stmt_reg);
                
                if(mysqli_num_rows($result_reg) > 0) {
                    $_SESSION['error'] = "Registration number already exists. Please contact administrator if this is yours.";
                    mysqli_stmt_close($stmt_reg);
                    header("Location: student_signup.php");
                    exit();
                }
                mysqli_stmt_close($stmt_reg);
            }
        } else {
            // Check roll_number if migration not done
            $check_roll = "SELECT roll_number FROM student_records WHERE roll_number = ?";
            $stmt_roll = mysqli_prepare($db_connection, $check_roll);
            if ($stmt_roll) {
                mysqli_stmt_bind_param($stmt_roll, "s", $registration_number);
                mysqli_stmt_execute($stmt_roll);
                $result_roll = mysqli_stmt_get_result($stmt_roll);
                
                if(mysqli_num_rows($result_roll) > 0) {
                    $_SESSION['error'] = "Registration number already exists. Please contact administrator.";
                    mysqli_stmt_close($stmt_roll);
                    header("Location: student_signup.php");
                    exit();
                }
                mysqli_stmt_close($stmt_roll);
            }
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

        // Verify email and password columns exist before inserting
        $check_email_col = "SHOW COLUMNS FROM student_records LIKE 'email'";
        $check_password_col = "SHOW COLUMNS FROM student_records LIKE 'password'";
        $email_result = mysqli_query($db_connection, $check_email_col);
        $password_result = mysqli_query($db_connection, $check_password_col);
        $email_col_exists = $email_result && mysqli_num_rows($email_result) > 0;
        $password_col_exists = $password_result && mysqli_num_rows($password_result) > 0;
        
        if (!$email_col_exists || !$password_col_exists) {
            $_SESSION['error'] = "System not fully configured. Please run update_database.php first or contact administrator.";
            header("Location: student_signup.php");
            exit();
        }

        // Handle profile picture upload
        $profile_picture_path = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/images/uploads/';
            
            // Create upload directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $file_type = mime_content_type($file['tmp_name']);
            
            // Validate file type
            if (in_array($file_type, $allowed_types) && $file['size'] <= $max_size) {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $unique_name = uniqid('profile_', true) . '_' . time() . '.' . $extension;
                $upload_path = $upload_dir . $unique_name;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $profile_picture_path = 'images/uploads/' . $unique_name;
                }
            }
        }

        // Check/migrate registration_number column
        $check_reg_col = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
        $reg_col_check = mysqli_query($db_connection, $check_reg_col);
        $has_registration_col = $reg_col_check && mysqli_num_rows($reg_col_check) > 0;
        
        if (!$has_registration_col) {
            // Try to migrate roll_number to registration_number
            $check_roll = "SHOW COLUMNS FROM student_records LIKE 'roll_number'";
            $roll_exists = mysqli_query($db_connection, $check_roll);
            if ($roll_exists && mysqli_num_rows($roll_exists) > 0) {
                @mysqli_query($db_connection, "ALTER TABLE student_records CHANGE COLUMN roll_number registration_number VARCHAR(50) NOT NULL");
            } else {
                @mysqli_query($db_connection, "ALTER TABLE student_records ADD COLUMN registration_number VARCHAR(50) NOT NULL");
            }
        }

        // Check if profile_picture column exists, if not add it
        $check_profile_col = "SHOW COLUMNS FROM student_records LIKE 'profile_picture'";
        $profile_col_result = mysqli_query($db_connection, $check_profile_col);
        $profile_col_exists = $profile_col_result && mysqli_num_rows($profile_col_result) > 0;
        
        if (!$profile_col_exists) {
            $alter_profile = "ALTER TABLE student_records ADD COLUMN profile_picture VARCHAR(255) NULL";
            @mysqli_query($db_connection, $alter_profile);
        }

        // Check if email column exists
        $check_email_col = "SHOW COLUMNS FROM student_records LIKE 'email'";
        $email_col_check = mysqli_query($db_connection, $check_email_col);
        $has_email_col = $email_col_check && mysqli_num_rows($email_col_check) > 0;

        // Insert student using prepared statement
        // Registration number acts as password - no separate password field needed
        if ($has_email_col) {
            if ($profile_col_exists) {
                $insert_query = "INSERT INTO student_records (full_name, email, registration_number, enrolled_course, profile_picture) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($db_connection, $insert_query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sssss", $full_name, $email, $registration_number, $course_name, $profile_picture_path);
                }
            } else {
                $insert_query = "INSERT INTO student_records (full_name, email, registration_number, enrolled_course) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($db_connection, $insert_query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $registration_number, $course_name);
                }
            }
        } else {
            // Fallback without email
            if ($profile_col_exists) {
                $insert_query = "INSERT INTO student_records (full_name, registration_number, enrolled_course, profile_picture) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($db_connection, $insert_query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssss", $full_name, $registration_number, $course_name, $profile_picture_path);
                }
            } else {
                $insert_query = "INSERT INTO student_records (full_name, registration_number, enrolled_course) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($db_connection, $insert_query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "sss", $full_name, $registration_number, $course_name);
                }
            }
        }
        
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . mysqli_error($db_connection) . ". Please contact administrator.";
            header("Location: student_signup.php");
            exit();
        }
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
            <form action="" method="post" name="student_signup_form" id="signupForm" enctype="multipart/form-data" onsubmit="return validateForm()">
                <fieldset>
                    <legend class="heading">
                        <i class="fa fa-user-plus"></i> Create Student Account
                    </legend>
                    
                    <!-- Profile Picture Upload -->
                    <div style="margin-bottom: 15px;">
                        <label for="profile_picture" style="display: block; margin-bottom: 8px; color: #333; font-weight: 500;">
                            <i class="fa fa-camera"></i> Profile Picture (Optional)
                        </label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="width: 100%; padding: 10px; border: 2px dashed #ddd; border-radius: 5px; background: #f9f9f9;">
                    </div>
                    
                    <input type="text" name="full_name" id="full_name" placeholder="Full Name" autocomplete="off" required minlength="3">
                    <input type="email" name="email" id="email" placeholder="Email Address" autocomplete="off" required>
                    <input type="text" name="registration_number" id="registration_number" placeholder="Registration Number" autocomplete="off" required minlength="3">
                    <p style="text-align: center; margin-top: 10px; color: #666; font-size: 0.9rem;">
                        <i class="fa fa-info-circle"></i> Your registration number will be used to login
                    </p>
                    
                    <?php
                        // Check if database connection exists
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

    <script src="./js/toast.js"></script>
    <script src="./js/image-preview.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize image preview
            const imagePreview = new ImagePreview({
                inputSelector: '#profile_picture',
                maxSize: 5 * 1024 * 1024, // 5MB
                allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
            });
            
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

        function validateForm() {
            var fullName = document.getElementById('full_name').value;
            var email = document.getElementById('email').value;
            var registrationNumber = document.getElementById('registration_number').value;
            var course = document.getElementById('course_name').value;

            var submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.value = 'Registering...';

            if (registrationNumber.length < 3) {
                showError('Registration number must be at least 3 characters long.');
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
</body>
</html>
