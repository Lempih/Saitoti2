<?php
/**
 * Database Connection Configuration (Legacy Support)
 * This file is maintained for backward compatibility
 * New code should use db_config.php directly
 */
require_once('db_config.php');

// Maintain backward compatibility with old variable names
if (!isset($conn)) {
	$conn = $db_connection;
}
?>