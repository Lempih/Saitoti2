<?php
/**
 * Public Navigation Component
 * For public pages (homepage, login pages)
 */
?>
<header class="header">
    <div class="header-content">
        <h1 class="logo-text">
            <i class="fa fa-graduation-cap"></i>
            <a href="index.html" style="text-decoration: none; color: #27ae60;">Academic Results System</a>
        </h1>
        <nav class="main-nav">
            <a href="index.html" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.html' || basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                <i class="fa fa-home"></i> Home
            </a>
            <a href="login.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'active' : ''; ?>">
                <i class="fa fa-user-shield"></i> Admin Login
            </a>
            <a href="student_signup.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'student_signup.php') ? 'active' : ''; ?>">
                <i class="fa fa-user-plus"></i> Student Signup
            </a>
            <a href="student_login.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'student_login.php') ? 'active' : ''; ?>">
                <i class="fa fa-sign-in"></i> Student Login
            </a>
            <a href="login.php" class="nav-link btn-primary">
                <i class="fa fa-search"></i> Quick Results
            </a>
        </nav>
    </div>
</header>

