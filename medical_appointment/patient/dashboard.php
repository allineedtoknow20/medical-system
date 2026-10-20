<?php
include "../api/config.php";
needLogin();

$user_id = $_SESSION["user_id"];
sendReminders($user_id);

$next = row("SELECT a.*, d.doctor_name, dep.department_name FROM appointments a
    JOIN doctors d ON d.doctor_id = a.doctor_id
    JOIN departments dep ON dep.department_id = d.department_id
    WHERE a.user_id = ? AND a.status IN ('Pending', 'Confirmed') AND a.appointment_date >= ?
    ORDER BY a.appointment_date, a.appointment_time LIMIT 1", "is", [$user_id, date("Y-m-d")]);

$title = "Dashboard";
include "header.php";
?>

<div class="page-title">
    <h2>Welcome, <?= e($_SESSION["name"]) ?></h2>
    <p>Patient ID: <?= $user_id ?></p>
</div>

<div class="cards">
    <a class="card" href="book.php">Book Appointment</a>
    <a class="card" href="my_appointments.php">My Appointments</a>
    <a class="card" href="history.php">Appointment History</a>
    <a class="card" href="notifications.php">Notifications</a>
    <a class="card" href="profile.php">Profile</a>
</div>

<div class="box">
    <h2>Next Appointment</h2>
    <?php if ($next) { ?>
        <p>Appointment No.: <b><?= e($next["appointment_no"]) ?></b></p>
        <p>Doctor: <?= e($next["doctor_name"]) ?> (<?= e($next["department_name"]) ?>)</p>
        <p>Date: <?= date("F j, Y", strtotime($next["appointment_date"])) ?> at <?= date("g:i A", strtotime($next["appointment_time"])) ?></p>
        <p>Status: <span class="status <?= css($next["status"]) ?>"><?= e($next["status"]) ?></span></p>
    <?php } else { ?>
        <p>You have no upcoming appointment.</p>
        <a class="btn" href="book.php">Book Now</a>
    <?php } ?>
</div>

<?php include "footer.php"; ?>
