<?php
include "../api/config.php";
needLogin();

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST["id"];
    $a = row("SELECT * FROM appointments WHERE appointment_id = ? AND user_id = ?", "ii", [$id, $user_id]);
    if ($a && in_array($a["status"], ["Pending", "Confirmed"])) {
        changeStatus($id, "Cancelled");
    }
    header("Location: my_appointments.php");
    exit;
}

$appointments = rows("SELECT a.*, d.doctor_name, dep.department_name FROM appointments a
    JOIN doctors d ON d.doctor_id = a.doctor_id
    JOIN departments dep ON dep.department_id = d.department_id
    WHERE a.user_id = ? AND a.status IN ('Pending', 'Confirmed') AND a.appointment_date >= ?
    ORDER BY a.appointment_date, a.appointment_time", "is", [$user_id, date("Y-m-d")]);

$title = "My Appointments";
include "header.php";
?>

<div class="page-title">
    <h2>My Appointments</h2>
    <p>Your upcoming appointments are listed here.</p>
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
            <th>Queue</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php foreach ($appointments as $a) { ?>
            <tr>
                <td><?= e($a["appointment_no"]) ?></td>
                <td><?= e($a["department_name"]) ?></td>
                <td><?= e($a["doctor_name"]) ?></td>
                <td><?= e($a["reason"]) ?></td>
                <td><?= date("M j, Y", strtotime($a["appointment_date"])) ?></td>
                <td><?= date("g:i A", strtotime($a["appointment_time"])) ?></td>
                <td><?= str_pad($a["slot_number"], 3, "0", STR_PAD_LEFT) ?></td>
                <td><span class="status <?= css($a["status"]) ?>"><?= e($a["status"]) ?></span></td>
                <td>
                    <form method="post" class="inline">
                        <input type="hidden" name="id" value="<?= $a["appointment_id"] ?>">
                        <button class="btn-red" data-confirm="Cancel this appointment?">Cancel</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        <?php if (!$appointments) { ?>
            <tr><td colspan="9">No upcoming appointments. <a href="book.php">Book one now</a></td></tr>
        <?php } ?>
    </table>
</div>

<?php include "footer.php"; ?>
