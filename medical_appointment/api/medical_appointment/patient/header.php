<?php
$unread = 0;
if (isset($_SESSION["user_id"])) {
    $count = row("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0", "i", [$_SESSION["user_id"]]);
    $unread = $count["total"];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? "MiraWell") ?> | MiraWell</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<div class="topbar">
    <div class="inner">
        <span>MiraWell Health Clinic</span>
        <span>
            <?php if (isset($_SESSION["user_id"])) { ?>
                Signed in as <?= e($_SESSION["name"]) ?>
            <?php } else { ?>
                <a href="login.php">Patient Portal</a>
                <a href="../admin/login.php">Staff</a>
            <?php } ?>
        </span>
    </div>
</div>
<div class="navbar">
    <a class="logo" href="index.php">
        <span class="mark"></span>
        <span>MiraWell<small>Health Clinic</small></span>
    </a>
    <div class="links">
        <?php if (isset($_SESSION["user_id"])) { ?>
            <?= navLink("dashboard.php", "Dashboard") ?>
            <?= navLink("my_appointments.php", "My Appointments") ?>
            <?= navLink("history.php", "History") ?>
            <?= navLink("notifications.php", "Notifications ($unread)") ?>
            <?= navLink("profile.php", "Profile") ?>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <?= navLink("index.php", "Home") ?>
            <?= navLink("login.php", "Login") ?>
            <?= navLink("register.php", "Register") ?>
        <?php } ?>
        <a class="nav-btn" href="book.php">Book Appointment</a>
    </div>
</div>
<?php if (!isset($home)) { ?>
<div class="container">
<?php } ?>
