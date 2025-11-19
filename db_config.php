<?php
/**
 * Database Connection Configuration
 * Academic Results Management System
 */
	$db_host = "localhost";
	$db_user = "root";
	$db_pass = "";
	$db_name = "academic_results_db";
	
	// Establish database connection with error handling
	$db_connection = @mysqli_connect($db_host, $db_user, $db_pass);
	
	if (!$db_connection) {
		// Check if MySQL is running
		$error_msg = "Database connection failed. Please ensure MySQL is running.";
		if (function_exists('mysqli_connect_error')) {
			$error_msg .= " Error: " . mysqli_connect_error();
		}
		// Don't die immediately - allow pages to load without database for demo purposes
		$db_connection = false;
	} else {
		// Select database
		$database_selected = @mysqli_select_db($db_connection, $db_name);
		
		if (!$database_selected) {
			// Database doesn't exist - create it or show helpful message
			$db_connection = false;
		} else {
			// Set charset to utf8mb4 for proper character support
			mysqli_set_charset($db_connection, "utf8mb4");
		}
	}
?>

