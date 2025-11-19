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
    <title>Results Management - Academic Results System</title>
    <style>
        .breadcrumb {
            padding: 15px 40px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
        }
        .breadcrumb a {
            color: white;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="breadcrumb">
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a> / 
        <span>Results Management</span>
    </div>

    <div class="main">
        <br><br>
        <form action="" method="post" id="deleteForm">
            <fieldset>
                <legend><i class="fa fa-trash"></i> Delete Result Record</legend>
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
                <legend><i class="fa fa-edit"></i> Update Result Record</legend>
                
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

        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this result? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
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
