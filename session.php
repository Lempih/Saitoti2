<?php
/**
 * Session Management (Legacy Support)
 * This file is maintained for backward compatibility
 * New code should use auth_check.php directly
 */
require_once('auth_check.php');

// Maintain backward compatibility
if (!isset($login_session)) {
	$login_session = $authenticated_user ?? null;
}
?>
