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
                    <input type="password" name="password" placeholder="Password" autocomplete="off" required minlength="6">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" autocomplete="off" required>
                    
                    <?php
                        require_once('db_config.php');

                        $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                        $course_result = mysqli_query($db_connection, $course_query);
                        
                        echo '<select name="course_name" required>';
                        echo '<option value="" selected disabled>Select Your Course</option>';
                        
                        while($course_row = mysqli_fetch_array($course_result)){
                            $course_display = $course_row['course_name'];
                            echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                        }
                        echo '</select>';
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
        $full_name = trim($_POST["full_name"]);
        $email = trim($_POST["email"]);
        $roll_number = intval($_POST["roll_number"]);
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $course_name = trim($_POST["course_name"]);

        // Validation
        if (empty($full_name) || empty($email) || empty($roll_number) || empty($password) || empty($course_name)) {
            echo '<script language="javascript">';
            echo 'alert("Please fill in all fields.")';
            echo '</script>';
            exit();
        }

        if ($password !== $confirm_password) {
            echo '<script language="javascript">';
            echo 'alert("Passwords do not match!")';
            echo '</script>';
            exit();
        }

        if (strlen($password) < 6) {
            echo '<script language="javascript">';
            echo 'alert("Password must be at least 6 characters long.")';
            echo '</script>';
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo '<script language="javascript">';
            echo 'alert("Please enter a valid email address.")';
            echo '</script>';
            exit();
        }

        // Check if email already exists
        $check_email = "SELECT email FROM student_records WHERE email = ?";
        $stmt_check = mysqli_prepare($db_connection, $check_email);
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

        // Check if roll number already exists for this course
        $check_roll = "SELECT roll_number FROM student_records WHERE roll_number = ? AND enrolled_course = ?";
        $stmt_roll = mysqli_prepare($db_connection, $check_roll);
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

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert student using prepared statement
        $insert_query = "INSERT INTO student_records (full_name, email, roll_number, enrolled_course, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_connection, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssiss", $full_name, $email, $roll_number, $course_name, $hashed_password);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            echo '<script language="javascript">';
            echo 'alert("Registration successful! You can now login.")';
            echo 'window.location.href = "student_login.php";';
            echo '</script>';
        } else {
            echo '<script language="javascript">';
            echo 'alert("Registration failed. Please try again.")';
            echo '</script>';
        }
    }
?>

