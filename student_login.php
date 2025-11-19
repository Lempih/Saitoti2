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

    <div class="main">
        <div class="login">
            <form action="" method="post" name="student_login_form">
                <fieldset>
                    <legend class="heading">
                        <i class="fa fa-sign-in"></i> Student Login
                    </legend>
                    <input type="email" name="email" placeholder="Email Address" autocomplete="off" required>
                    <input type="password" name="password" placeholder="Password" autocomplete="off" required>
                    <input type="submit" value="Login" name="login_submit">
                    <p style="text-align: center; margin-top: 20px; color: #666;">
                        Don't have an account? <a href="student_signup.php" style="color: #667eea; text-decoration: none; font-weight: 600;">Sign up here</a>
                    </p>
                    <p style="text-align: center; margin-top: 10px;">
                        <a href="login.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">View Results (No Login Required)</a>
                    </p>
                </fieldset>
            </form>    
        </div>
        <div class="search">
            <form action="./student.php" method="get">
                <fieldset>
                    <legend class="heading">
                        <i class="fa fa-search"></i> Quick Result Check
                    </legend>

                    <?php
                        require_once('db_config.php');

                        $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                        $course_result = mysqli_query($db_connection, $course_query);
                        
                        echo '<select name="course" required>';
                        echo '<option value="" selected disabled>Choose Course</option>';
                        
                        while($course_row = mysqli_fetch_array($course_result)){
                            $course_display = $course_row['course_name'];
                            echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                        }
                        echo '</select>';
                    ?>

                    <input type="text" name="rollno" placeholder="Enter Roll Number" required>
                    <input type="submit" value="View Results">
                    <p style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem;">
                        Check results without logging in
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

    if (isset($_POST["email"], $_POST["password"]) && isset($_POST["login_submit"]))
    {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        
        // Use prepared statement to prevent SQL injection
        $login_query = "SELECT full_name, email, roll_number, enrolled_course, password FROM student_records WHERE email = ?";
        $stmt = mysqli_prepare($db_connection, $login_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $login_result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($login_result) == 1) {
                $student_data = mysqli_fetch_assoc($login_result);
                
                // Verify password
                if (password_verify($password, $student_data['password'])) {
                    $_SESSION['student_logged_in'] = true;
                    $_SESSION['student_email'] = $student_data['email'];
                    $_SESSION['student_name'] = $student_data['full_name'];
                    $_SESSION['student_roll'] = $student_data['roll_number'];
                    $_SESSION['student_course'] = $student_data['enrolled_course'];
                    header("Location: student_dashboard.php");
                    exit();
                } else {
                    echo '<script language="javascript">';
                    echo 'alert("Invalid email or password.")';
                    echo '</script>';
                }
            } else {
                echo '<script language="javascript">';
                echo 'alert("Invalid email or password.")';
                echo '</script>';
            }
            mysqli_stmt_close($stmt);
        } else {
            echo '<script language="javascript">';
            echo 'alert("Database error. Please try again later.")';
            echo '</script>';
        }
    }
?>

