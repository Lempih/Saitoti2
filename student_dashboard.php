<?php
    session_start();
    require_once("db_config.php");

    // Check if student is logged in
    if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
        $_SESSION['error'] = "Please login to access your dashboard.";
        header("Location: student_login.php");
        exit();
    }

    // Verify session data exists
    if (!isset($_SESSION['student_email']) || !isset($_SESSION['student_name']) || !isset($_SESSION['student_registration']) || !isset($_SESSION['student_course'])) {
        session_destroy();
        $_SESSION['error'] = "Session expired. Please login again.";
        header("Location: student_login.php");
        exit();
    }

    $student_email = $_SESSION['student_email'];
    $student_name = $_SESSION['student_name'];
    $student_registration = $_SESSION['student_registration'];
    $student_course = $_SESSION['student_course'];
    $student_profile_picture = null;
    
    // Verify student still exists in database and get profile picture
    if ($db_connection) {
        // Check which column exists (migration support)
        $check_reg_col = "SHOW COLUMNS FROM student_records LIKE 'registration_number'";
        $reg_check = mysqli_query($db_connection, $check_reg_col);
        $has_registration_col = $reg_check && mysqli_num_rows($reg_check) > 0;
        
        if ($has_registration_col) {
            $verify_query = "SELECT email, profile_picture FROM student_records WHERE email = ? AND registration_number = ? AND enrolled_course = ?";
            $verify_stmt = mysqli_prepare($db_connection, $verify_query);
            if ($verify_stmt) {
                mysqli_stmt_bind_param($verify_stmt, "sss", $student_email, $student_registration, $student_course);
            }
        } else {
            // Fallback to roll_number if migration not done
            $verify_query = "SELECT email, profile_picture FROM student_records WHERE email = ? AND roll_number = ? AND enrolled_course = ?";
            $verify_stmt = mysqli_prepare($db_connection, $verify_query);
            if ($verify_stmt) {
                mysqli_stmt_bind_param($verify_stmt, "sis", $student_email, $student_registration, $student_course);
            }
        }
        
        if (isset($verify_stmt) && $verify_stmt) {
            mysqli_stmt_execute($verify_stmt);
            $verify_result = mysqli_stmt_get_result($verify_stmt);
            
            if (mysqli_num_rows($verify_result) == 0) {
                mysqli_stmt_close($verify_stmt);
                session_destroy();
                $_SESSION['error'] = "Your account was not found. Please contact administrator.";
                header("Location: student_login.php");
                exit();
            } else {
                $student_data = mysqli_fetch_assoc($verify_result);
                $student_profile_picture = $student_data['profile_picture'] ?? null;
            }
            mysqli_stmt_close($verify_stmt);
        }
    }

    // Get student's results
    if ($db_connection) {
        // Check which column exists in exam_results
        $check_results_reg = "SHOW COLUMNS FROM exam_results LIKE 'registration_number'";
        $results_reg_check = mysqli_query($db_connection, $check_results_reg);
        $has_results_reg_col = $results_reg_check && mysqli_num_rows($results_reg_check) > 0;
        
        if ($has_results_reg_col) {
            $result_query = "SELECT subject_1, subject_2, subject_3, subject_4, subject_5, total_marks, grade_percentage, created_at FROM exam_results WHERE registration_number = ? AND course_name = ? ORDER BY created_at DESC LIMIT 1";
            $stmt = mysqli_prepare($db_connection, $result_query);
            mysqli_stmt_bind_param($stmt, "ss", $student_registration, $student_course);
        } else {
            // Fallback to roll_number
            $result_query = "SELECT subject_1, subject_2, subject_3, subject_4, subject_5, total_marks, grade_percentage, created_at FROM exam_results WHERE roll_number = ? AND course_name = ? ORDER BY created_at DESC LIMIT 1";
            $stmt = mysqli_prepare($db_connection, $result_query);
            mysqli_stmt_bind_param($stmt, "is", $student_registration, $student_course);
        }
        mysqli_stmt_execute($stmt);
        $result_data = mysqli_stmt_get_result($stmt);
        $has_results = mysqli_num_rows($result_data) > 0;
        $result_row = $has_results ? mysqli_fetch_assoc($result_data) : null;
        mysqli_stmt_close($stmt);
    } else {
        $has_results = false;
        $result_row = null;
    }
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
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.3);
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
            color: #27ae60;
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
            transition: all 0.3s ease;
        }
        .subject-score:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.2);
        }
        .subject-score .label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        .subject-score .score {
            font-size: 2rem;
            font-weight: 700;
            color: #27ae60;
        }
        .total-score {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
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
        
        .portal-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        .portal-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .portal-section h3 {
            color: #27ae60;
            margin-bottom: 20px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        
        .quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 25px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.2);
            border-color: #27ae60;
        }
        
        .quick-action-btn i {
            font-size: 2.5rem;
            color: #27ae60;
            margin-bottom: 10px;
        }
        
        .quick-action-btn span {
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .info-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            text-align: center;
        }
        
        .info-card i {
            font-size: 2rem;
            color: #27ae60;
            margin-bottom: 10px;
        }
        
        .info-card-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 8px;
        }
        
        .info-card-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
        }
        
        .grade-indicator {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            margin-top: 15px;
            font-size: 1.1rem;
        }
        
        .grade-excellent {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
        }
        
        .grade-good {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .grade-average {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }
        
        .grade-poor {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        @media (max-width: 1024px) {
            .portal-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/student_nav.php'); ?>
    
    <div class="breadcrumb">
        <a href="student_dashboard.php"><i class="fa fa-home"></i> My Dashboard</a>
    </div>

    <div class="main">
        <div class="welcome-card" style="display: flex; align-items: center; gap: 30px;">
            <div style="flex-shrink: 0;">
                <?php if ($student_profile_picture && file_exists($student_profile_picture)): ?>
                    <img src="<?php echo htmlspecialchars($student_profile_picture); ?>" 
                         alt="Profile Picture" 
                         style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                <?php else: ?>
                    <div style="width: 120px; height: 120px; border-radius: 50%; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; border: 4px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                        <i class="fa fa-user" style="font-size: 4rem; color: white;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div style="flex: 1;">
                <h2><i class="fa fa-user"></i> Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($student_email); ?></p>
                <p><strong>Registration Number:</strong> <?php echo htmlspecialchars($student_registration); ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($student_course); ?></p>
            </div>
        </div>

        <!-- Portal Sections -->
        <div class="portal-container">
            <!-- Quick Actions -->
            <div class="portal-section">
                <h3><i class="fa fa-bolt"></i> Quick Actions</h3>
                <div class="quick-actions">
                    <a href="student.php?course=<?php echo urlencode($student_course); ?>&regno=<?php echo urlencode($student_registration); ?>" class="quick-action-btn">
                        <i class="fa fa-file-text"></i>
                        <span>View Results</span>
                    </a>
                    <a href="student.php?course=<?php echo urlencode($student_course); ?>&regno=<?php echo urlencode($student_registration); ?>" class="quick-action-btn">
                        <i class="fa fa-print"></i>
                        <span>Print Results</span>
                    </a>
                    <a href="index.html" class="quick-action-btn">
                        <i class="fa fa-home"></i>
                        <span>Homepage</span>
                    </a>
                    <a href="student_logout.php" class="quick-action-btn" style="border-color: #e74c3c;">
                        <i class="fa fa-sign-out" style="color: #e74c3c;"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
            
            <!-- Student Information -->
            <div class="portal-section">
                <h3><i class="fa fa-info-circle"></i> My Information</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <i class="fa fa-id-card"></i>
                        <div class="info-card-label">Registration Number</div>
                        <div class="info-card-value"><?php echo htmlspecialchars($student_registration); ?></div>
                    </div>
                    <div class="info-card">
                        <i class="fa fa-book"></i>
                        <div class="info-card-label">Course</div>
                        <div class="info-card-value"><?php echo htmlspecialchars($student_course); ?></div>
                    </div>
                    <div class="info-card">
                        <i class="fa fa-envelope"></i>
                        <div class="info-card-label">Email</div>
                        <div class="info-card-value" style="font-size: 0.95rem; word-break: break-all;"><?php echo htmlspecialchars($student_email); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($has_results && $result_row): 
            $percentage = floatval($result_row['grade_percentage']);
            $grade_class = 'grade-average';
            $grade_text = 'Average';
            if ($percentage >= 90) {
                $grade_class = 'grade-excellent';
                $grade_text = 'Excellent';
            } elseif ($percentage >= 75) {
                $grade_class = 'grade-good';
                $grade_text = 'Good';
            } elseif ($percentage >= 50) {
                $grade_class = 'grade-average';
                $grade_text = 'Average';
            } else {
                $grade_class = 'grade-poor';
                $grade_text = 'Needs Improvement';
            }
        ?>
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
                    <div class="grade-indicator <?php echo $grade_class; ?>">
                        <?php echo $grade_text; ?>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="student.php?course=<?php echo urlencode($student_course); ?>&regno=<?php echo urlencode($student_registration); ?>" 
                       class="btn btn-primary" 
                       style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%); color: white; text-decoration: none; border-radius: 30px; font-weight: 600; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4); transition: all 0.3s ease;">
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
    
        });
    </script>
    <script src="./js/toast.js"></script>
</body>
</html>
