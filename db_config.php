<?php
/**
 * Database Connection Configuration
 * Academic Results Management System
 */
	$db_host = "localhost";
	$db_user = "root";
	$db_pass = "";
	$db_name = "academic_results_db";
	
	// Establish database connection
	$db_connection = mysqli_connect($db_host, $db_user, $db_pass);
	
	if (!$db_connection) {
		die("Connection failed: " . mysqli_connect_error());
	}
	
	// Select database
	$database_selected = mysqli_select_db($db_connection, $db_name);
	
	if (!$database_selected) {
		die("Database selection failed: " . mysqli_error($db_connection));
	}
	
	// Set charset to utf8mb4 for proper character support
	mysqli_set_charset($db_connection, "utf8mb4");
?>

