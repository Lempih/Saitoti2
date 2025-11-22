<?php
/**
 * Logout Handler
 * Destroys session and redirects to login page
 */
   session_start();
   
   // Unset all session variables
   $_SESSION = array();
   
   // Destroy the session cookie
   if (isset($_COOKIE[session_name()])) {
       setcookie(session_name(), '', time()-42000, '/');
   }
   
   // Destroy the session
   session_destroy();
   
   // Set success message
   session_start();
   $_SESSION['success'] = "You have been successfully logged out.";
   header("Location: login.php");
   exit();
?>
