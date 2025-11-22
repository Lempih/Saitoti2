<?php
/**
 * Student Navigation Component
 * Reusable navigation for student pages
 * Note: Authentication check should be done in the page that includes this component
 */
?>
<div class="title">
    <a href="student_dashboard.php"><img src="./images/logo1.png" alt="Logo" class="logo"></a>
    <span class="heading">Student Portal</span>
    <a href="student_logout.php" style="color: #27ae60">
        <span class="fa fa-sign-out fa-2x">Logout</span>
    </a>
</div>

<div class="nav">
    <ul>
        <li>
            <a href="student_dashboard.php" class="dropbtn">
                <i class="fa fa-dashboard"></i> My Dashboard
            </a>
        </li>
        <li>
            <a href="student.php?course=<?php echo urlencode($_SESSION['student_course']); ?>&regno=<?php echo urlencode($_SESSION['student_registration'] ?? $_SESSION['student_roll'] ?? ''); ?>" class="dropbtn">
                <i class="fa fa-file-text"></i> View Results
            </a>
        </li>
        <li>
            <a href="index.html" class="dropbtn">
                <i class="fa fa-home"></i> Home
            </a>
        </li>
    </ul>
</div>

