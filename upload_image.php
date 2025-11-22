<?php
/**
 * Image Upload Handler
 * Handles image uploads for student profiles and other purposes
 */

session_start();
require_once('db_config.php');

// Check if user is authenticated (either admin or student)
$is_admin = isset($_SESSION['logged_in_user']);
$is_student = isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true;

if (!$is_admin && !$is_student) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Configuration
$upload_dir = __DIR__ . '/images/uploads/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error_msg = 'No file uploaded or upload error occurred.';
    if (isset($_FILES['image']['error'])) {
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_msg = 'File size exceeds limit.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_msg = 'File was only partially uploaded.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_msg = 'No file was uploaded.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_msg = 'Missing temporary folder.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_msg = 'Failed to write file to disk.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_msg = 'File upload stopped by extension.';
                break;
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $error_msg]);
    } else {
        $_SESSION['error'] = $error_msg;
        header('Location: ' . ($is_admin ? 'add_students.php' : 'student_signup.php'));
    }
    exit();
}

$file = $_FILES['image'];
$file_type = mime_content_type($file['tmp_name']);
$file_size = $file['size'];

// Validate file type
if (!in_array($file_type, $allowed_types)) {
    $error_msg = 'Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.';
    if (isset($_POST['ajax'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $error_msg]);
    } else {
        $_SESSION['error'] = $error_msg;
        header('Location: ' . ($is_admin ? 'add_students.php' : 'student_signup.php'));
    }
    exit();
}

// Validate file size
if ($file_size > $max_size) {
    $error_msg = 'File size exceeds 5MB limit.';
    if (isset($_POST['ajax'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $error_msg]);
    } else {
        $_SESSION['error'] = $error_msg;
        header('Location: ' . ($is_admin ? 'add_students.php' : 'student_signup.php'));
    }
    exit();
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$unique_name = uniqid('img_', true) . '_' . time() . '.' . $extension;
$upload_path = $upload_dir . $unique_name;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    $error_msg = 'Failed to save uploaded file.';
    if (isset($_POST['ajax'])) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $error_msg]);
    } else {
        $_SESSION['error'] = $error_msg;
        header('Location: ' . ($is_admin ? 'add_students.php' : 'student_signup.php'));
    }
    exit();
}

// Return success response
$relative_path = 'images/uploads/' . $unique_name;
$full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
            '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $relative_path;

if (isset($_POST['ajax'])) {
    echo json_encode([
        'success' => true,
        'path' => $relative_path,
        'url' => $full_url,
        'filename' => $unique_name
    ]);
} else {
    // Store in session for form submission
    $_SESSION['uploaded_image_path'] = $relative_path;
    $_SESSION['success'] = 'Image uploaded successfully!';
    header('Location: ' . ($is_admin ? 'add_students.php' : 'student_signup.php'));
}
exit();

