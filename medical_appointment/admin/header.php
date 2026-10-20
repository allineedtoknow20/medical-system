<?php
$loggedIn = isset($_SESSION["admin_id"]);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? "Admin") ?> | MiraWell Staff</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<div class="topbar">
    <div class="inner">
        <span>MiraWell Staff Portal</span>
        <span>
            <?php if ($loggedIn) { ?>
                Signed in as <?= e($_SESSION["admin_name"]) ?>
            <?php } ?>
            <a href="../patient/index.php">Back to website</a>
        </span>
    </div>
</div>
<div class="navbar">
    <a class="logo" href="<?= $loggedIn ? "index.php" : "login.php" ?>">
        <span class="mark"></span>
        <span>MiraWell<small>Health Clinic</small></span>
    </a>
    <div class="links">
        <?php if ($loggedIn) { ?>
            <?= navLink("index.php", "Dashboard") ?>
            <?= navLink("departments.php", "Departments") ?>
            <?= navLink("doctors.php", "Doctors") ?>
            <?= navLink("schedules.php", "Schedules") ?>
            <?= navLink("appointments.php", "Appointments") ?>
            <?= navLink("notifications.php", "Notifications") ?>
            <a class="nav-btn" href="logout.php">Logout</a>
        <?php } else { ?>
            <a class="nav-btn" href="login.php">Staff Login</a>
        <?php } ?>
    </div>
</div>
<div class="container">
