<?php
    session_start();
    require_once("db_config.php");
    require_once('auth_check.php');
    
    // Get statistics
    if ($db_connection) {
        $total_courses = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM courses"));
        $total_students = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM student_records"));
        $total_results = mysqli_fetch_array(mysqli_query($db_connection, "SELECT COUNT(*) FROM exam_results"));
        
        // Get recent students (last 5)
        $recent_students_query = "SELECT full_name, registration_number, enrolled_course, created_at FROM student_records ORDER BY created_at DESC LIMIT 5";
        $recent_students_result = mysqli_query($db_connection, $recent_students_query);
        $recent_students = [];
        if ($recent_students_result) {
            while ($row = mysqli_fetch_assoc($recent_students_result)) {
                $recent_students[] = $row;
            }
        }
        
        // Get courses list
        $courses_query = "SELECT course_name FROM courses ORDER BY course_name";
        $courses_result = mysqli_query($db_connection, $courses_query);
        $courses_list = [];
        if ($courses_result) {
            while ($row = mysqli_fetch_assoc($courses_result)) {
                $courses_list[] = $row['course_name'];
            }
        }
    } else {
        $total_courses = [0];
        $total_students = [0];
        $total_results = [0];
        $recent_students = [];
        $courses_list = [];
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
    <title>Admin Dashboard - Academic Results System</title>
    <style>
        .main h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #27ae60;
            font-size: 2rem;
            font-weight: 700;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            font-size: 1rem;
        }
        
        .recent-list {
            list-style: none;
            padding: 0;
        }
        
        .recent-item {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-item:hover {
            background: #f8f9fa;
            padding-left: 20px;
        }
        
        .recent-item-info {
            flex: 1;
        }
        
        .recent-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .recent-item-details {
            font-size: 0.9rem;
            color: #666;
        }
        
        .recent-item-time {
            font-size: 0.85rem;
            color: #999;
        }
        
        .stats-enhanced {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .stat-card-enhanced {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            padding: 35px;
            border-radius: 15px;
            color: white;
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
            transition: all 0.3s ease;
        }
        
        .stat-card-enhanced:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(39, 174, 96, 0.4);
        }
        
        .stat-card-enhanced i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .stat-card-enhanced .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin: 10px 0;
        }
        
        .stat-card-enhanced .stat-label {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        @media (max-width: 1024px) {
            .portal-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="breadcrumb">
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
    </div>

    <div class="main">
        <h2><i class="fa fa-dashboard"></i> Admin Portal Dashboard</h2>
        
        <!-- Statistics -->
        <div class="stats-enhanced">
            <div class="stat-card-enhanced">
                <i class="fa fa-book"></i>
                <div class="stat-number"><?php echo $total_courses[0]; ?></div>
                <div class="stat-label">Total Courses</div>
            </div>
            <div class="stat-card-enhanced">
                <i class="fa fa-users"></i>
                <div class="stat-number"><?php echo $total_students[0]; ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card-enhanced">
                <i class="fa fa-file-text"></i>
                <div class="stat-number"><?php echo $total_results[0]; ?></div>
                <div class="stat-label">Total Results</div>
            </div>
        </div>
        
        <!-- Portal Sections -->
        <div class="portal-container">
            <!-- Quick Actions -->
            <div class="portal-section">
                <h3><i class="fa fa-bolt"></i> Quick Actions</h3>
                <div style="margin-bottom: 20px;">
                    <a href="admin_capabilities.php" class="quick-action-btn" style="background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%); color: white; border-color: #27ae60;">
                        <i class="fa fa-shield" style="color: white;"></i>
                        <span>Admin Capabilities</span>
                    </a>
                </div>
                <div class="quick-actions">
                    <a href="add_students.php" class="quick-action-btn">
                        <i class="fa fa-user-plus"></i>
                        <span>Register Student</span>
                    </a>
                    <a href="add_results.php" class="quick-action-btn">
                        <i class="fa fa-edit"></i>
                        <span>Enter Results</span>
                    </a>
                    <a href="add_classes.php" class="quick-action-btn">
                        <i class="fa fa-plus-circle"></i>
                        <span>Add Course</span>
                    </a>
                    <a href="manage_students.php" class="quick-action-btn">
                        <i class="fa fa-users"></i>
                        <span>View Students</span>
                    </a>
                    <a href="manage_results.php" class="quick-action-btn">
                        <i class="fa fa-cog"></i>
                        <span>Manage Results</span>
                    </a>
                    <a href="manage_classes.php" class="quick-action-btn">
                        <i class="fa fa-list"></i>
                        <span>View Courses</span>
                    </a>
                    <a href="clear_all_data.php" class="quick-action-btn" style="border-color: #e74c3c;">
                        <i class="fa fa-trash" style="color: #e74c3c;"></i>
                        <span>Clear All Data</span>
                    </a>
                    <a href="migrate_fix_fk.php" class="quick-action-btn" style="border-color: #3498db; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">
                        <i class="fa fa-database" style="color: white;"></i>
                        <span>Fix Database Migration</span>
                    </a>
                </div>
            </div>
            
            <!-- Recent Students -->
            <div class="portal-section">
                <h3><i class="fa fa-clock-o"></i> Recent Students</h3>
                <?php if (!empty($recent_students)): ?>
                    <ul class="recent-list">
                        <?php foreach ($recent_students as $student): ?>
                            <li class="recent-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                    <div class="recent-item-details">
                                        <i class="fa fa-id-card"></i> <?php echo htmlspecialchars($student['registration_number'] ?? 'N/A'); ?> | 
                                        <i class="fa fa-book"></i> <?php echo htmlspecialchars($student['enrolled_course']); ?>
                                    </div>
                                </div>
                                <div class="recent-item-time">
                                    <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 40px;">
                        <i class="fa fa-users" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                        No students registered yet.
                    </p>
                <?php endif; ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="manage_students.php" class="btn btn-primary" style="display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%); color: white; text-decoration: none; border-radius: 25px; font-weight: 600;">
                        <i class="fa fa-arrow-right"></i> View All Students
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Available Courses -->
        <?php if (!empty($courses_list)): ?>
        <div class="portal-section" style="margin-top: 30px; grid-column: 1 / -1;">
            <h3><i class="fa fa-book"></i> Available Courses</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px;">
                <?php foreach ($courses_list as $course): ?>
                    <div style="padding: 15px 25px; background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%); border: 2px solid #e0e0e0; border-radius: 25px; font-weight: 600; color: #333;">
                        <i class="fa fa-graduation-cap" style="color: #27ae60; margin-right: 8px;"></i>
                        <?php echo htmlspecialchars($course); ?>
                    </div>
                <?php endforeach; ?>
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
