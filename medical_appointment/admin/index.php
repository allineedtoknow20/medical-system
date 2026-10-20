<?php
include "../api/config.php";
needAdmin();

$patients = row("SELECT COUNT(*) AS total FROM users")["total"];
$doctors = row("SELECT COUNT(*) AS total FROM doctors")["total"];
$today = row("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND status != 'Cancelled'", "s", [date("Y-m-d")])["total"];
$pending = row("SELECT COUNT(*) AS total FROM appointments WHERE status = 'Pending'")["total"];

$title = "Admin Dashboard";
include "header.php";
?>

<div class="page-title">
    <h2>Welcome, <?= e($_SESSION["admin_name"]) ?></h2>
</div>

<div class="cards">
    <div class="card"><div class="number"><?= $patients ?></div>Patients</div>
    <div class="card"><div class="number"><?= $doctors ?></div>Doctors</div>
    <div class="card"><div class="number"><?= $today ?></div>Appointments Today</div>
    <div class="card"><div class="number"><?= $pending ?></div>Pending</div>
</div>

<div class="cards">
    <a class="card" href="departments.php">Manage Departments</a>
    <a class="card" href="doctors.php">Manage Doctors</a>
    <a class="card" href="schedules.php">Manage Schedules</a>
    <a class="card" href="appointments.php">Manage Appointments</a>
    <a class="card" href="notifications.php">Manage Notifications</a>
</div>

<?php include "footer.php"; ?>
