<?php
    require_once("db_config.php");
    session_start();

    // Check if student is logged in
    if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
        header("Location: student_login.php");
        exit();
    }

    $student_email = $_SESSION['student_email'];
    $student_name = $_SESSION['student_name'];
    $student_roll = $_SESSION['student_roll'];
    $student_course = $_SESSION['student_course'];

    // Get student's results
    $result_query = "SELECT subject_1, subject_2, subject_3, subject_4, subject_5, total_marks, grade_percentage, created_at FROM exam_results WHERE roll_number = ? AND course_name = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = mysqli_prepare($db_connection, $result_query);
    mysqli_stmt_bind_param($stmt, "is", $student_roll, $student_course);
    mysqli_stmt_execute($stmt);
    $result_data = mysqli_stmt_get_result($stmt);
    $has_results = mysqli_num_rows($result_data) > 0;
    $result_row = $has_results ? mysqli_fetch_assoc($result_data) : null;
    mysqli_stmt_close($stmt);
?>
        
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <script src="./js/main.js"></script>
    <title>Student Dashboard - Academic Results System</title>
    <style>
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        .welcome-card h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        .welcome-card p {
            margin: 5px 0;
            opacity: 0.95;
        }
        .result-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .result-card h3 {
            color: #667eea;
            margin-bottom: 20px;
        }
        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .subject-score {
            background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e0e0e0;
        }
        .subject-score .label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        .subject-score .score {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
        }
        .total-score {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin-top: 20px;
        }
        .total-score .label {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .total-score .score {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .no-results i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
        
    <div class="title">
        <a href="student_dashboard.php"><img src="./images/logo1.png" alt="Logo" class="logo"></a>
        <span class="heading">Student Dashboard</span>
        <a href="student_logout.php" style="color: #667eea">
            <span class="fa fa-sign-out fa-2x">Logout</span>
        </a>
    </div>

    <div class="main">
        <div class="welcome-card">
            <h2><i class="fa fa-user"></i> Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($student_email); ?></p>
            <p><strong>Roll Number:</strong> <?php echo htmlspecialchars($student_roll); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($student_course); ?></p>
        </div>

        <?php if ($has_results && $result_row): ?>
            <div class="result-card">
                <h3><i class="fa fa-graduation-cap"></i> Latest Examination Results</h3>
                
                <div class="result-grid">
                    <div class="subject-score">
                        <div class="label">Subject 1</div>
                        <div class="score"><?php echo $result_row['subject_1']; ?></div>
                    </div>
                    <div class="subject-score">
                        <div class="label">Subject 2</div>
                        <div class="score"><?php echo $result_row['subject_2']; ?></div>
                    </div>
                    <div class="subject-score">
                        <div class="label">Subject 3</div>
                        <div class="score"><?php echo $result_row['subject_3']; ?></div>
                    </div>
                    <div class="subject-score">
                        <div class="label">Subject 4</div>
                        <div class="score"><?php echo $result_row['subject_4']; ?></div>
                    </div>
                    <div class="subject-score">
                        <div class="label">Subject 5</div>
                        <div class="score"><?php echo $result_row['subject_5']; ?></div>
                    </div>
                </div>

                <div class="total-score">
                    <div class="label">Total Marks</div>
                    <div class="score"><?php echo $result_row['total_marks']; ?> / 500</div>
                    <div style="margin-top: 15px; font-size: 1.5rem;">
                        Percentage: <?php echo number_format($result_row['grade_percentage'], 2); ?>%
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="student.php?course=<?php echo urlencode($student_course); ?>&rollno=<?php echo $student_roll; ?>" 
                       class="btn btn-primary" 
                       style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 30px; font-weight: 600; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                        <i class="fa fa-print"></i> View & Print Results
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="result-card">
                <div class="no-results">
                    <i class="fa fa-file-text-o"></i>
                    <h3>No Results Available</h3>
                    <p>Your examination results have not been published yet.</p>
                    <p style="margin-top: 20px; color: #999;">Please check back later or contact your administrator.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2024 Academic Results Management System</p>
    </div>
</body>
</html>

