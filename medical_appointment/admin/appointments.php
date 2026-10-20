<?php
include "../api/config.php";
needAdmin();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST["id"];
    $action = $_POST["action"];
    $a = row("SELECT * FROM appointments WHERE appointment_id = ?", "i", [$id]);

    if ($a && in_array($a["status"], ["Pending", "Confirmed"])) {
        if ($action == "confirm") {
            changeStatus($id, "Confirmed");
            $message = "Appointment confirmed.";
        } elseif ($action == "complete") {
            changeStatus($id, "Completed");
            $message = "Appointment marked as completed.";
        } elseif ($action == "noshow") {
            changeStatus($id, "No Show");
            $message = "Appointment marked as no show.";
        } elseif ($action == "cancel") {
            changeStatus($id, "Cancelled");
            $message = "Appointment cancelled.";
        } elseif ($action == "reschedule") {
            $new_date = $_POST["new_date"];
            $new = getSchedule($a["doctor_id"], $new_date);

            if (!$new) {
                $error = "Please choose a valid date within the next 90 days.";
            } elseif ($new["schedule_id"] == $a["schedule_id"]) {
                $error = "The appointment is already on that date.";
            } else {
                $conn->begin_transaction();
                row("SELECT schedule_id FROM schedules WHERE schedule_id = ? FOR UPDATE", "i", [$new["schedule_id"]]);
                $already = row("SELECT appointment_id FROM appointments WHERE schedule_id = ? AND user_id = ? AND status != 'Cancelled'", "ii", [$new["schedule_id"], $a["user_id"]]);
                $slot = nextSlot($new["schedule_id"], $new["max_slots"]);

                if ($already) {
                    $error = "The patient already has an appointment on that date.";
                    $conn->rollback();
                } elseif ($slot == 0) {
                    $error = "That date is fully booked.";
                    $conn->rollback();
                } else {
                    $time = slotTime($new["start_time"], $slot);
                    run("UPDATE appointments SET schedule_id = ?, appointment_date = ?, appointment_time = ?, slot_number = ? WHERE appointment_id = ?",
                        "issii", [$new["schedule_id"], $new_date, $time, $slot, $id]);
                    notify($a["user_id"], $id, "Appointment Updated — Your appointment schedule has been changed to " . date("F j, Y", strtotime($new_date)) . " at " . date("g:i A", strtotime($time)) . ".");
                    $conn->commit();
                    $message = "Appointment rescheduled.";
                }
            }
        }
    }
}

$appointments = rows("SELECT a.*, u.name AS patient, d.doctor_name, dep.department_name FROM appointments a
    JOIN users u ON u.user_id = a.user_id
    JOIN doctors d ON d.doctor_id = a.doctor_id
    JOIN departments dep ON dep.department_id = d.department_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 100");

$title = "Manage Appointments";
include "header.php";
?>

<div class="box">
    <h2>Manage Appointments</h2>

    <?php if ($message != "") { ?>
        <div class="success"><?= e($message) ?></div>
    <?php } ?>
    <?php if ($error != "") { ?>
        <div class="error"><?= e($error) ?></div>
    <?php } ?>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>Appointment No.</th>
            <th>Patient</th>
            <th>Department</th>
            <th>Doctor</th>
            <th>Reason</th>
            <th>Date</th>
            <th>Time</th>
            <th>Queue</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($appointments as $a) { ?>
            <tr>
                <td><?= e($a["appointment_no"]) ?></td>
                <td><?= e($a["patient"]) ?></td>
                <td><?= e($a["department_name"]) ?></td>
                <td><?= e($a["doctor_name"]) ?></td>
                <td><?= e($a["reason"]) ?></td>
                <td><?= date("M j, Y", strtotime($a["appointment_date"])) ?></td>
                <td><?= date("g:i A", strtotime($a["appointment_time"])) ?></td>
                <td><?= $a["slot_number"] ? str_pad($a["slot_number"], 3, "0", STR_PAD_LEFT) : "-" ?></td>
                <td><span class="status <?= css($a["status"]) ?>"><?= e($a["status"]) ?></span></td>
                <td>
                    <?php if (in_array($a["status"], ["Pending", "Confirmed"])) { ?>
                        <form method="post" class="inline">
                            <input type="hidden" name="id" value="<?= $a["appointment_id"] ?>">
                            <?php if ($a["status"] == "Pending") { ?>
                                <button name="action" value="confirm" class="btn-green">Confirm</button>
                            <?php } ?>
                            <button name="action" value="complete">Complete</button>
                            <button name="action" value="noshow" class="btn-gray">No Show</button>
                            <button name="action" value="cancel" class="btn-red" data-confirm="Cancel this appointment?">Cancel</button>
                        </form>
                        <br><br>
                        <form method="post" class="inline">
                            <input type="hidden" name="id" value="<?= $a["appointment_id"] ?>">
                            <input type="date" name="new_date" required>
                            <button name="action" value="reschedule">Reschedule</button>
                        </form>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        <?php if (!$appointments) { ?>
            <tr><td colspan="10">No appointments yet.</td></tr>
        <?php } ?>
    </table>
</div>

<?php include "footer.php"; ?>
