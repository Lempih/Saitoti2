<?php
/**
 * Authentication and Session Management
 * Verifies user login status and maintains session
 */
   session_start();
   require_once('db_config.php');
   
   $current_user = $_SESSION['logged_in_user'] ?? null;
   
   if ($current_user) {
       if ($db_connection) {
           // Verify user still exists in database
           $verify_query = "SELECT admin_username FROM administrators WHERE admin_username = ?";
           $stmt = mysqli_prepare($db_connection, $verify_query);
           if ($stmt) {
               mysqli_stmt_bind_param($stmt, "s", $current_user);
               mysqli_stmt_execute($stmt);
               $result = mysqli_stmt_get_result($stmt);
               $user_data = mysqli_fetch_assoc($result);
               mysqli_stmt_close($stmt);
               
               if ($user_data) {
                   $authenticated_user = $user_data['admin_username'];
               } else {
                   // User no longer exists, destroy session
                   session_destroy();
                   $_SESSION['error'] = "Your session has expired. Please login again.";
                   header("Location: login.php");
                   exit();
               }
           } else {
               // Database error, but allow access
               $authenticated_user = $current_user;
           }
       } else {
           // Database connection failed, but allow access if session exists
           $authenticated_user = $current_user;
       }
   } else {
       // No session found, redirect to login
       $_SESSION['error'] = "Please login to access this page.";
       header("Location: login.php");
       exit();
   }
?>
