<?php
/**
 * Admin Navigation Component
 * Reusable navigation for admin pages
 */
?>
<div class="title">
    <a href="dashboard.php"><img src="./images/logo1.png" alt="Logo" class="logo"></a>
    <span class="heading">Control Panel</span>
    <a href="logout.php" style="color: #27ae60">
        <span class="fa fa-sign-out fa-2x">Logout</span>
    </a>
</div>

<div class="nav">
    <ul>
        <li>
            <a href="dashboard.php" class="dropbtn">
                <i class="fa fa-dashboard"></i> Dashboard
            </a>
        </li>
        <li class="dropdown" onclick="toggleDisplay('1')">
            <a href="#" class="dropbtn">Course Management &nbsp
                <span class="fa fa-angle-down"></span>
            </a>
            <div class="dropdown-content" id="1">
                <a href="add_classes.php"><i class="fa fa-plus"></i> Create New Course</a>
                <a href="manage_classes.php"><i class="fa fa-list"></i> View All Courses</a>
            </div>
        </li>
        <li class="dropdown" onclick="toggleDisplay('2')">
            <a href="#" class="dropbtn">Student Management &nbsp
                <span class="fa fa-angle-down"></span>
            </a>
            <div class="dropdown-content" id="2">
                <a href="add_students.php"><i class="fa fa-user-plus"></i> Register Student</a>
                <a href="manage_students.php"><i class="fa fa-users"></i> View All Students</a>
            </div>
        </li>
        <li class="dropdown" onclick="toggleDisplay('3')">
            <a href="#" class="dropbtn">Results Management &nbsp
                <span class="fa fa-angle-down"></span>
            </a>
            <div class="dropdown-content" id="3">
                <a href="add_results.php"><i class="fa fa-edit"></i> Enter Examination Results</a>
                <a href="manage_results.php"><i class="fa fa-cog"></i> Manage Results</a>
            </div>
        </li>
    </ul>
</div>

