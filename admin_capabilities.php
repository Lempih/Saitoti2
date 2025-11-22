<?php
session_start();
require_once("db_config.php");
require_once('auth_check.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/font-awesome-4.7.0/css/font-awesome.css">
    <title>Admin Capabilities - Academic Results System</title>
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
        .capabilities-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .capability-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #27ae60;
            transition: all 0.3s ease;
        }
        .capability-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.2);
        }
        .capability-card h3 {
            color: #27ae60;
            margin-bottom: 15px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .capability-card h3 i {
            font-size: 1.8rem;
        }
        .capability-card p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        .capability-card ul {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }
        .capability-card ul li {
            padding: 8px 0;
            color: #555;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .capability-card ul li:last-child {
            border-bottom: none;
        }
        .capability-card ul li i {
            color: #27ae60;
            font-size: 0.9rem;
        }
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 15px;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        .welcome-banner {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .welcome-banner h2 {
            color: white;
            margin-bottom: 10px;
        }
        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="breadcrumb">
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a> / 
        <span>Admin Capabilities</span>
    </div>

    <div class="main">
        <div class="welcome-banner">
            <h2><i class="fa fa-shield"></i> Administrator Capabilities</h2>
            <p>Complete overview of your administrative powers and system access</p>
        </div>
        
        <div class="capabilities-container">
            <!-- Course Management -->
            <div class="capability-card">
                <h3><i class="fa fa-book"></i> Course Management</h3>
                <p>Create and manage academic courses offered by the institution.</p>
                <ul>
                    <li><i class="fa fa-check"></i> Create new courses with unique IDs</li>
                    <li><i class="fa fa-check"></i> View all available courses</li>
                    <li><i class="fa fa-check"></i> Delete or modify existing courses</li>
                    <li><i class="fa fa-check"></i> Define course units/subjects</li>
                </ul>
                <a href="add_classes.php" class="action-btn">
                    <i class="fa fa-plus"></i> Create Course
                </a>
                <a href="manage_classes.php" class="action-btn" style="margin-left: 10px;">
                    <i class="fa fa-list"></i> View Courses
                </a>
            </div>

            <!-- Student Management -->
            <div class="capability-card">
                <h3><i class="fa fa-users"></i> Student Management</h3>
                <p>Register and manage student records in the system.</p>
                <ul>
                    <li><i class="fa fa-check"></i> Register new students manually</li>
                    <li><i class="fa fa-check"></i> View all registered students</li>
                    <li><i class="fa fa-check"></i> Search students by registration number</li>
                    <li><i class="fa fa-check"></i> Update student information</li>
                    <li><i class="fa fa-check"></i> Delete student records</li>
                </ul>
                <a href="add_students.php" class="action-btn">
                    <i class="fa fa-user-plus"></i> Register Student
                </a>
                <a href="manage_students.php" class="action-btn" style="margin-left: 10px;">
                    <i class="fa fa-users"></i> View Students
                </a>
            </div>

            <!-- Results Management -->
            <div class="capability-card">
                <h3><i class="fa fa-file-text"></i> Results Management</h3>
                <p>Enter, update, and manage examination results for all students.</p>
                <ul>
                    <li><i class="fa fa-check"></i> Enter examination results per course unit</li>
                    <li><i class="fa fa-check"></i> Input marks for each subject/unit</li>
                    <li><i class="fa fa-check"></i> View all results</li>
                    <li><i class="fa fa-check"></i> Update existing results</li>
                    <li><i class="fa fa-check"></i> Delete incorrect results</li>
                    <li><i class="fa fa-check"></i> Automatic grade calculation</li>
                </ul>
                <a href="add_results.php" class="action-btn">
                    <i class="fa fa-edit"></i> Enter Results
                </a>
                <a href="manage_results.php" class="action-btn" style="margin-left: 10px;">
                    <i class="fa fa-cog"></i> Manage Results
                </a>
            </div>

            <!-- System Administration -->
            <div class="capability-card">
                <h3><i class="fa fa-cog"></i> System Administration</h3>
                <p>Control system settings and database operations.</p>
                <ul>
                    <li><i class="fa fa-check"></i> Access system dashboard with statistics</li>
                    <li><i class="fa fa-check"></i> View system overview and analytics</li>
                    <li><i class="fa fa-check"></i> Clear all student and result data</li>
                    <li><i class="fa fa-check"></i> Run database migrations</li>
                    <li><i class="fa fa-check"></i> Fix database issues</li>
                </ul>
                <a href="dashboard.php" class="action-btn">
                    <i class="fa fa-dashboard"></i> Dashboard
                </a>
                <a href="clear_all_data.php" class="action-btn" style="margin-left: 10px; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                    <i class="fa fa-trash"></i> Clear Data
                </a>
            </div>

            <!-- Course Units Management -->
            <div class="capability-card">
                <h3><i class="fa fa-graduation-cap"></i> Course Units Management</h3>
                <p>Define and manage course units/subjects for each course.</p>
                <ul>
                    <li><i class="fa fa-check"></i> Define course units per course</li>
                    <li><i class="fa fa-check"></i> Set unit names and codes</li>
                    <li><i class="fa fa-check"></i> View all course units</li>
                    <li><i class="fa fa-check"></i> Update or delete units</li>
                    <li><i class="fa fa-check"></i> Results entry per unit</li>
                </ul>
                <a href="manage_course_units.php" class="action-btn">
                    <i class="fa fa-list"></i> Manage Units
                </a>
            </div>

            <!-- Security & Access -->
            <div class="capability-card">
                <h3><i class="fa fa-shield"></i> Security & Access</h3>
                <p>Secure administrative access to the system.</p>
                <ul>
                    <li><i class="fa fa-check"></i> Protected admin login</li>
                    <li><i class="fa fa-check"></i> Session management</li>
                    <li><i class="fa fa-check"></i> Secure authentication</li>
                    <li><i class="fa fa-check"></i> Admin logout functionality</li>
                    <li><i class="fa fa-check"></i> Access control for all admin features</li>
                </ul>
                <a href="logout.php" class="action-btn" style="background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Academic Results Management System</p>
    </div>

    <script src="./js/toast.js"></script>
</body>
</html>

