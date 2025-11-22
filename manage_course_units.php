<?php
session_start();
require_once("db_config.php");
require_once('auth_check.php');

// Handle form submission - Add new unit
if (isset($_POST['course_name'], $_POST['unit_name'], $_POST['submit_unit'])) {
    $course_name = trim($_POST['course_name']);
    $unit_name = trim($_POST['unit_name']);
    $unit_code = isset($_POST['unit_code']) ? trim($_POST['unit_code']) : '';
    $display_order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 1;
    
    // Check if course_units table exists
    $check_table = "SHOW TABLES LIKE 'course_units'";
    $table_exists = mysqli_query($db_connection, $check_table) && mysqli_num_rows(mysqli_query($db_connection, $check_table)) > 0;
    
    if (!$table_exists) {
        $_SESSION['error'] = "Course units table not found. Please run create_course_units_table.php first.";
        header("Location: create_course_units_table.php");
        exit();
    }
    
    // Validation
    $errors = [];
    if (empty($course_name)) {
        $errors[] = "Please select a course";
    }
    if (empty($unit_name) || strlen($unit_name) < 2) {
        $errors[] = "Unit name must be at least 2 characters";
    }
    
    if (empty($errors)) {
        // Insert new unit
        $insert_query = "INSERT INTO course_units (course_name, unit_name, unit_code, display_order) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($db_connection, $insert_query);
        mysqli_stmt_bind_param($stmt, "sssi", $course_name, $unit_name, $unit_code, $display_order);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            $_SESSION['success'] = "Course unit added successfully!";
        } else {
            $_SESSION['error'] = "Error adding unit: " . mysqli_error($db_connection);
        }
        header("Location: manage_course_units.php");
        exit();
    } else {
        $_SESSION['error'] = implode(". ", $errors);
        header("Location: manage_course_units.php");
        exit();
    }
}

// Handle delete
if (isset($_GET['delete_unit']) && is_numeric($_GET['delete_unit'])) {
    $unit_id = intval($_GET['delete_unit']);
    $delete_query = "DELETE FROM course_units WHERE unit_id = ?";
    $stmt = mysqli_prepare($db_connection, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $unit_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    if ($result) {
        $_SESSION['success'] = "Course unit deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting unit.";
    }
    header("Location: manage_course_units.php");
    exit();
}

// Get all courses
$courses_list = [];
if ($db_connection) {
    $courses_query = "SELECT course_name FROM courses ORDER BY course_name";
    $courses_result = mysqli_query($db_connection, $courses_query);
    if ($courses_result) {
        while ($row = mysqli_fetch_assoc($courses_result)) {
            $courses_list[] = $row['course_name'];
        }
    }
}

// Get all course units grouped by course
$course_units = [];
$check_table = "SHOW TABLES LIKE 'course_units'";
$table_exists = mysqli_query($db_connection, $check_table) && mysqli_num_rows(mysqli_query($db_connection, $check_table)) > 0;

if ($table_exists && $db_connection) {
    $units_query = "SELECT * FROM course_units ORDER BY course_name, display_order, unit_name";
    $units_result = mysqli_query($db_connection, $units_query);
    if ($units_result) {
        while ($row = mysqli_fetch_assoc($units_result)) {
            $course_units[$row['course_name']][] = $row;
        }
    }
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
    <title>Manage Course Units - Academic Results System</title>
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
        .units-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-top: 30px;
        }
        .form-section, .list-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .form-section h3, .list-section h3 {
            color: #27ae60;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        .course-unit-group {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .course-unit-group h4 {
            color: #27ae60;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .unit-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #27ae60;
        }
        .unit-info {
            flex: 1;
        }
        .unit-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .unit-code {
            font-size: 0.9rem;
            color: #666;
        }
        .unit-actions a {
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .unit-actions a:hover {
            background: #c0392b;
        }
        .no-units {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        @media (max-width: 1024px) {
            .units-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/admin_nav.php'); ?>
    
    <div class="breadcrumb">
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a> / 
        <a href="admin_capabilities.php">Admin Capabilities</a> / 
        <span>Manage Course Units</span>
    </div>

    <div class="main">
        <h2><i class="fa fa-graduation-cap"></i> Course Units Management</h2>
        
        <?php if (!$table_exists): ?>
            <div style="background: #fff3cd; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0;">
                <p style="color: #856404; margin: 0;">Course units table not found. 
                    <a href="create_course_units_table.php" style="color: #27ae60; font-weight: 600;">Click here to create it</a>
                </p>
            </div>
        <?php else: ?>
            <div class="units-container">
                <!-- Add Unit Form -->
                <div class="form-section">
                    <h3><i class="fa fa-plus-circle"></i> Add Course Unit</h3>
                    <form method="post" action="">
                        <select name="course_name" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                            <option value="" disabled selected>Select Course</option>
                            <?php foreach ($courses_list as $course): ?>
                                <option value="<?php echo htmlspecialchars($course); ?>">
                                    <?php echo htmlspecialchars($course); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="unit_name" placeholder="Unit Name (e.g., Database Systems)" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <input type="text" name="unit_code" placeholder="Unit Code (Optional, e.g., CS301)" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <input type="number" name="display_order" placeholder="Display Order" value="1" min="1" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <input type="submit" name="submit_unit" value="Add Unit" style="width: 100%; padding: 15px; background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                    </form>
                </div>
                
                <!-- Units List -->
                <div class="list-section">
                    <h3><i class="fa fa-list"></i> Course Units</h3>
                    <?php if (empty($course_units)): ?>
                        <div class="no-units">
                            <i class="fa fa-graduation-cap" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #ddd;"></i>
                            <p>No course units defined yet.</p>
                            <p style="margin-top: 10px;">Add units using the form on the left.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($course_units as $course_name => $units): ?>
                            <div class="course-unit-group">
                                <h4><i class="fa fa-book"></i> <?php echo htmlspecialchars($course_name); ?></h4>
                                <?php foreach ($units as $unit): ?>
                                    <div class="unit-item">
                                        <div class="unit-info">
                                            <div class="unit-name"><?php echo htmlspecialchars($unit['unit_name']); ?></div>
                                            <?php if (!empty($unit['unit_code'])): ?>
                                                <div class="unit-code">Code: <?php echo htmlspecialchars($unit['unit_code']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="unit-actions">
                                            <a href="?delete_unit=<?php echo $unit['unit_id']; ?>" onclick="return confirm('Are you sure you want to delete this unit?');">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

