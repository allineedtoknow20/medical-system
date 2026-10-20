<?php
include "../api/config.php";
needLogin();

$id = (int) ($_GET["id"] ?? 0);
$a = row("SELECT a.*, d.doctor_name, dep.department_name FROM appointments a
    JOIN doctors d ON d.doctor_id = a.doctor_id
    JOIN departments dep ON dep.department_id = d.department_id
    WHERE a.appointment_id = ? AND a.user_id = ?", "ii", [$id, $_SESSION["user_id"]]);

if (!$a) {
    header("Location: my_appointments.php");
    exit;
}

$title = "Appointment Confirmation";
include "header.php";
?>

<div class="box small-box">
    <h2>Appointment Booked!</h2>
    <div class="success">Your appointment was saved. The clinic will confirm it soon.</div>

    <p>Appointment No.: <b><?= e($a["appointment_no"]) ?></b></p>
    <p>Queue / Slot Number: <b><?= str_pad($a["slot_number"], 3, "0", STR_PAD_LEFT) ?></b></p>
    <p>Patient: <?= e($_SESSION["name"]) ?></p>
    <p>Department: <?= e($a["department_name"]) ?></p>
    <p>Doctor: <?= e($a["doctor_name"]) ?></p>
    <p>Date: <?= date("F j, Y", strtotime($a["appointment_date"])) ?></p>
    <p>Time: <?= date("g:i A", strtotime($a["appointment_time"])) ?></p>
    <p>Reason: <?= e($a["reason"]) ?></p>
    <p>Status: <span class="status <?= css($a["status"]) ?>"><?= e($a["status"]) ?></span></p>

    <a class="btn" href="my_appointments.php">My Appointments</a>
    <a class="btn btn-gray" href="notifications.php">Notifications</a>
</div>

<?php include "footer.php"; ?>
