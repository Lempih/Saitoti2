<?php
/**
 * Authentication and Session Management
 * Verifies user login status and maintains session
 */
   require_once('db_config.php');
   session_start();
   
   $current_user = $_SESSION['logged_in_user'] ?? null;
   
   if ($current_user) {
       // Verify user still exists in database
       $verify_query = "SELECT admin_username FROM administrators WHERE admin_username = ?";
       $stmt = mysqli_prepare($db_connection, $verify_query);
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
           header("Location: login.php");
           exit();
       }
   } else {
       // No session found, redirect to login
       header("Location: login.php");
       exit();
   }
?>

