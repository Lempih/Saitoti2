<?php
/**
 * Get Course Units API
 * Returns JSON list of course units for a given course
 */

require_once('db_config.php');

header('Content-Type: application/json');

if (!$db_connection) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Check if course_units table exists
$check_table = "SHOW TABLES LIKE 'course_units'";
$table_exists = mysqli_query($db_connection, $check_table) && mysqli_num_rows(mysqli_query($db_connection, $check_table)) > 0;

if (!$table_exists) {
    echo json_encode(['success' => false, 'error' => 'Course units table not found']);
    exit();
}

$course_name = isset($_GET['course']) ? trim($_GET['course']) : '';

if (empty($course_name)) {
    echo json_encode(['success' => false, 'error' => 'Course name required']);
    exit();
}

// Get course units
$query = "SELECT unit_name, unit_code, display_order FROM course_units WHERE course_name = ? ORDER BY display_order, unit_name";
$stmt = mysqli_prepare($db_connection, $query);
mysqli_stmt_bind_param($stmt, "s", $course_name);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$units = [];
while ($row = mysqli_fetch_assoc($result)) {
    $units[] = [
        'unit_name' => $row['unit_name'],
        'unit_code' => $row['unit_code'],
        'display_order' => $row['display_order']
    ];
}

mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'units' => $units,
    'count' => count($units)
]);

