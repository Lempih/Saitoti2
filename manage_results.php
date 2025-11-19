<?php
    session_start();
    require_once('db_config.php');
    require_once('auth_check.php');

    if(isset($_POST['course_name'], $_POST['roll_number'], $_POST['delete_result'])) {
        $course_name = trim($_POST['course_name']);
        $roll_number = intval($_POST['roll_number']);
        
        // Use prepared statement for deletion
        $delete_query = "DELETE FROM exam_results WHERE roll_number = ? AND course_name = ?";
        $stmt = mysqli_prepare($db_connection, $delete_query);
        mysqli_stmt_bind_param($stmt, "is", $roll_number, $course_name);
        $delete_result = mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if($affected_rows == 0){
            $_SESSION['error'] = "Result not found or already deleted.";
            header("Location: manage_results.php");
            exit();
        } else {
            $_SESSION['success'] = "Result deleted successfully!";
            header("Location: manage_results.php");
            exit();
        }
    }

    if(isset($_POST['rollno'], $_POST['subject_1'], $_POST['subject_2'], $_POST['subject_3'], $_POST['subject_4'], $_POST['subject_5'], $_POST['course'], $_POST['update_result'])) {
        $roll_number = intval($_POST['rollno']);
        $course_name = trim($_POST['course']);
        $subject_1 = intval($_POST['subject_1']);
        $subject_2 = intval($_POST['subject_2']);
        $subject_3 = intval($_POST['subject_3']);
        $subject_4 = intval($_POST['subject_4']);
        $subject_5 = intval($_POST['subject_5']);

        $total_marks = $subject_1 + $subject_2 + $subject_3 + $subject_4 + $subject_5;
        $grade_percentage = round($total_marks / 5, 2);
        
        // Validation
        if($subject_1 > 100 || $subject_2 > 100 || $subject_3 > 100 || $subject_4 > 100 || $subject_5 > 100 || 
           $subject_1 < 0 || $subject_2 < 0 || $subject_3 < 0 || $subject_4 < 0 || $subject_5 < 0) {
            $_SESSION['error'] = "Marks must be between 0 and 100";
            header("Location: manage_results.php");
            exit();
        }

        // Update using prepared statement
        $update_query = "UPDATE exam_results SET subject_1 = ?, subject_2 = ?, subject_3 = ?, subject_4 = ?, subject_5 = ?, total_marks = ?, grade_percentage = ? WHERE roll_number = ? AND course_name = ?";
        $stmt = mysqli_prepare($db_connection, $update_query);
        mysqli_stmt_bind_param($stmt, "iiiiidis", $subject_1, $subject_2, $subject_3, $subject_4, $subject_5, $total_marks, $grade_percentage, $roll_number, $course_name);
        $update_result = mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if($affected_rows == 0){
            $_SESSION['error'] = "Result not found. Please check the roll number and course.";
            header("Location: manage_results.php");
            exit();
        } else {
            $_SESSION['success'] = "Result updated successfully!";
            header("Location: manage_results.php");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <link rel="stylesheet" href="./css/form.css">
    <script src="./js/main.js"></script>
    <script src="./js/toast.js"></script>
    <title>Results Management</title>
</head>
<body>
        
    <div class="title">
        <a href="dashboard.php"><img src="./images/logo1.png" alt="Logo" class="logo"></a>
        <span class="heading">Control Panel</span>
        <a href="logout.php" style="color: #27ae60">
            <span class="fa fa-sign-out fa-2x">Logout</span>
        </a>
    </div>

    <div class="nav">
        <ul>
            <li class="dropdown" onclick="toggleDisplay('1')">
                <a href="" class="dropbtn">Course Management &nbsp
                    <span class="fa fa-angle-down"></span>
                </a>
                <div class="dropdown-content" id="1">
                    <a href="add_classes.php">Create New Course</a>
                    <a href="manage_classes.php">View All Courses</a>
                </div>
            </li>
            <li class="dropdown" onclick="toggleDisplay('2')">
                <a href="#" class="dropbtn">Student Management &nbsp
                    <span class="fa fa-angle-down"></span>
                </a>
                <div class="dropdown-content" id="2">
                    <a href="add_students.php">Register Student</a>
                    <a href="manage_students.php">View All Students</a>
                </div>
            </li>
            <li class="dropdown" onclick="toggleDisplay('3')">
                <a href="#" class="dropbtn">Results Management &nbsp
                    <span class="fa fa-angle-down"></span>
                </a>
                <div class="dropdown-content" id="3">
                    <a href="add_results.php">Enter Examination Results</a>
                    <a href="manage_results.php">Manage Results</a>
                </div>
            </li>
        </ul>
    </div>

    <div class="main">
        <br><br>
        <form action="" method="post" id="deleteForm">
            <fieldset>
                <legend>Delete Result Record</legend>
                <?php
                    if ($db_connection) {
                        $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                        $course_result = mysqli_query($db_connection, $course_query);
                        
                        if ($course_result && mysqli_num_rows($course_result) > 0) {
                            echo '<select name="course_name" required>';
                            echo '<option value="" selected disabled>Select Course</option>';
                            
                            while($course_row = mysqli_fetch_array($course_result)){
                                $course_display = htmlspecialchars($course_row['course_name']);
                                echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                            }
                            echo '</select>';
                        }
                    }
                ?>
                <input type="number" name="roll_number" placeholder="Roll Number" required min="1">
                <input type="submit" value="Delete Result" name="delete_result" id="deleteBtn">
            </fieldset>
        </form>
        <br><br>

        <form action="" method="post" id="updateForm">
            <fieldset>
                <legend>Update Result Record</legend>
                
                <?php
                    if ($db_connection) {
                        $course_query = "SELECT course_name FROM courses ORDER BY course_name ASC";
                        $course_result = mysqli_query($db_connection, $course_query);
                        
                        if ($course_result && mysqli_num_rows($course_result) > 0) {
                            echo '<select name="course" required>';
                            echo '<option value="" selected disabled>Select Course</option>';
                            
                            while($course_row = mysqli_fetch_array($course_result)){
                                $course_display = htmlspecialchars($course_row['course_name']);
                                echo '<option value="'.$course_display.'">'.$course_display.'</option>';
                            }
                            echo '</select>';
                        }
                    }
                ?>
                
                <input type="number" name="rollno" placeholder="Roll Number" required min="1">
                <input type="number" name="subject_1" id="" placeholder="Subject 1 Marks" min="0" max="100" required>
                <input type="number" name="subject_2" id="" placeholder="Subject 2 Marks" min="0" max="100" required>
                <input type="number" name="subject_3" id="" placeholder="Subject 3 Marks" min="0" max="100" required>
                <input type="number" name="subject_4" id="" placeholder="Subject 4 Marks" min="0" max="100" required>
                <input type="number" name="subject_5" id="" placeholder="Subject 5 Marks" min="0" max="100" required>
                <input type="submit" value="Update Result" name="update_result" id="updateBtn">
            </fieldset>
        </form>
    </div>

    <script>
        <?php if (isset($_SESSION['error'])): ?>
            showError('<?php echo addslashes($_SESSION['error']); ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            showSuccess('<?php echo addslashes($_SESSION['success']); ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        // Form submission handling
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('deleteBtn');
            btn.disabled = true;
            btn.value = 'Deleting...';
        });

        document.getElementById('updateForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('updateBtn');
            btn.disabled = true;
            btn.value = 'Updating...';
        });
    </script>
    
</body>
</html>
