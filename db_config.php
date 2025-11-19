<?php
/**
 * Database Connection Configuration
 * Academic Results Management System
 */
	$db_host = "127.0.0.1"; // Use 127.0.0.1 instead of localhost to avoid socket issues
	$db_user = "root";
	$db_pass = "";
	$db_name = "academic_results_db";
	
	// Establish database connection
	$db_connection = mysqli_connect($db_host, $db_user, $db_pass);
	
	if (!$db_connection) {
		die("
		<div style='font-family: Poppins, sans-serif; padding: 40px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: white;'>
			<h1 style='font-size: 2rem; margin-bottom: 20px;'>Database Connection Error</h1>
			<p style='font-size: 1.2rem; margin-bottom: 30px;'>Unable to connect to MySQL database.</p>
			<div style='background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto;'>
				<p style='margin-bottom: 15px;'><strong>Please ensure:</strong></p>
				<ul style='text-align: left; display: inline-block;'>
					<li style='margin: 10px 0;'>MySQL is installed and running</li>
					<li style='margin: 10px 0;'>Start MySQL with: <code style='background: rgba(0,0,0,0.3); padding: 5px 10px; border-radius: 5px;'>brew services start mysql</code></li>
					<li style='margin: 10px 0;'>Database 'academic_results_db' exists</li>
				</ul>
				<p style='margin-top: 20px; color: #ffd700;'>Error: " . mysqli_connect_error() . "</p>
			</div>
		</div>
		");
	}
	
	// Select database
	$database_selected = mysqli_select_db($db_connection, $db_name);
	
	if (!$database_selected) {
		die("
		<div style='font-family: Poppins, sans-serif; padding: 40px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: white;'>
			<h1 style='font-size: 2rem; margin-bottom: 20px;'>Database Not Found</h1>
			<p style='font-size: 1.2rem; margin-bottom: 30px;'>The database 'academic_results_db' does not exist.</p>
			<div style='background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto;'>
				<p style='margin-bottom: 15px;'><strong>To fix this:</strong></p>
				<ol style='text-align: left; display: inline-block;'>
					<li style='margin: 10px 0;'>Create database: <code style='background: rgba(0,0,0,0.3); padding: 5px 10px; border-radius: 5px;'>mysql -u root -e \"CREATE DATABASE academic_results_db;\"</code></li>
					<li style='margin: 10px 0;'>Import schema: <code style='background: rgba(0,0,0,0.3); padding: 5px 10px; border-radius: 5px;'>mysql -u root academic_results_db < database/academic_results_db.sql</code></li>
				</ol>
			</div>
		</div>
		");
	}
	
	// Set charset to utf8mb4 for proper character support
	mysqli_set_charset($db_connection, "utf8mb4");
?>
