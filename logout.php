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
   if(session_destroy()) {
        header("Location: login.php");
        exit();
   }
?>
