<?php
include "../api/config.php";
needLogin();

$appointments = rows("SELECT a.*, d.doctor_name, dep.department_name FROM appointments a
    JOIN doctors d ON d.doctor_id = a.doctor_id
    JOIN departments dep ON dep.department_id = d.department_id
    WHERE a.user_id = ? AND (a.status NOT IN ('Pending', 'Confirmed') OR a.appointment_date < ?)
    ORDER BY a.appointment_date DESC", "is", [$_SESSION["user_id"], date("Y-m-d")]);

$title = "Appointment History";
include "header.php";
?>

<div class="page-title">
    <h2>Appointment History</h2>
    <p>Past, completed and cancelled appointments.</p>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>Appointment No.</th>
            <th>Department</th>
            <th>Doctor</th>
            <th>Reason</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
        <?php foreach ($appointments as $a) { ?>
            <tr>
                <td><?= e($a["appointment_no"]) ?></td>
                <td><?= e($a["department_name"]) ?></td>
                <td><?= e($a["doctor_name"]) ?></td>
                <td><?= e($a["reason"]) ?></td>
                <td><?= date("M j, Y", strtotime($a["appointment_date"])) ?></td>
                <td><?= date("g:i A", strtotime($a["appointment_time"])) ?></td>
                <td><span class="status <?= css($a["status"]) ?>"><?= e($a["status"]) ?></span></td>
            </tr>
        <?php } ?>
        <?php if (!$appointments) { ?>
            <tr><td colspan="7">No appointment history yet.</td></tr>
        <?php } ?>
    </table>
</div>

<?php include "footer.php"; ?>
