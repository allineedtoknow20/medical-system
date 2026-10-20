<?php
include "../api/config.php";
needAdmin();

$message = "";
$error = "";

$doctors = rows("SELECT * FROM doctors ORDER BY doctor_name");
$doctor_id = (int) ($_GET["doctor"] ?? $_POST["doctor"] ?? ($doctors[0]["doctor_id"] ?? 0));
$doctor = row("SELECT * FROM doctors WHERE doctor_id = ?", "i", [$doctor_id]);

if ($doctor && isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];
    if (usedSlots($id) > 0) {
        $error = "This date already has appointments, so it cannot be deleted.";
    } else {
        run("DELETE FROM schedules WHERE schedule_id = ? AND doctor_id = ?", "ii", [$id, $doctor_id]);
        $message = "Date deleted.";
    }
}

if ($doctor && $_SERVER["REQUEST_METHOD"] == "POST") {
    $from = $_POST["from_date"];
    $to = $_POST["to_date"] != "" ? $_POST["to_date"] : $from;
    $start = $_POST["start_time"];
    $end = $_POST["end_time"];
    $max = (int) $_POST["max_slots"];

    if ($from == "" || !strtotime($from) || !strtotime($to)) {
        $error = "Please enter a valid date.";
    } elseif ($from < date("Y-m-d")) {
        $error = "The date cannot be in the past.";
    } elseif ($to < $from) {
        $error = "The end date must be after the start date.";
    } elseif ($start == "" || $end == "" || $end <= $start) {
        $error = "Please enter a valid start and end time.";
    } elseif ($max < 1 || $max > 100) {
        $error = "Maximum slots must be from 1 to 100.";
    } else {
        $day = strtotime($from);
        $last = strtotime($to);
        $added = 0;
        while ($day <= $last) {
            $date = date("Y-m-d", $day);
            if (strpos($doctor["available_days"], date("D", $day)) !== false) {
                $exists = row("SELECT schedule_id FROM schedules WHERE doctor_id = ? AND appointment_date = ?", "is", [$doctor_id, $date]);
                if (!$exists) {
                    run("INSERT INTO schedules (doctor_id, appointment_date, start_time, end_time, max_slots) VALUES (?, ?, ?, ?, ?)", "isssi", [$doctor_id, $date, $start, $end, $max]);
                    $added++;
                }
            }
            $day = strtotime("+1 day", $day);
        }
        $message = "$added date(s) added.";
    }
}

$dates = [];
if ($doctor) {
    $dates = rows("SELECT s.schedule_id, s.appointment_date, s.start_time, s.end_time, s.max_slots, COUNT(a.appointment_id) AS used
        FROM schedules s
        LEFT JOIN appointments a ON a.schedule_id = s.schedule_id AND a.status != 'Cancelled'
        WHERE s.doctor_id = ? AND s.appointment_date >= ?
        GROUP BY s.schedule_id, s.appointment_date, s.start_time, s.end_time, s.max_slots
        ORDER BY s.appointment_date", "is", [$doctor_id, date("Y-m-d")]);
}

$title = "Manage Schedules";
include "header.php";
?>

<div class="box">
    <h2>Manage Schedules</h2>

    <?php if ($message != "") { ?>
        <div class="success"><?= e($message) ?></div>
    <?php } ?>
    <?php if ($error != "") { ?>
        <div class="error"><?= e($error) ?></div>
    <?php } ?>

    <form method="get">
        <label>Doctor</label>
        <select name="doctor" onchange="this.form.submit()">
            <?php foreach ($doctors as $d) { ?>
                <option value="<?= $d["doctor_id"] ?>" <?= $d["doctor_id"] == $doctor_id ? "selected" : "" ?>><?= e($d["doctor_name"]) ?> (<?= e($d["available_days"]) ?>)</option>
            <?php } ?>
        </select>
    </form>
</div>

<?php if ($doctor) { ?>
<div class="box">
    <h2>Add Available Dates</h2>
    <form method="post">
        <input type="hidden" name="doctor" value="<?= $doctor_id ?>">

        <label>From Date</label>
        <input type="date" name="from_date" required>

        <label>To Date (leave empty for one day only)</label>
        <input type="date" name="to_date">

        <label>Start Time</label>
        <input type="time" name="start_time" value="08:00" required>

        <label>End Time</label>
        <input type="time" name="end_time" value="17:00" required>

        <label>Maximum Slots (1 to 100)</label>
        <input type="number" name="max_slots" value="100" min="1" max="100" required>

        <button type="submit">Add</button>
    </form>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Total</th>
            <th>Used</th>
            <th>Available</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php foreach ($dates as $d) {
            $available = $d["max_slots"] - $d["used"];
            $status = slotStatus($available);
        ?>
            <tr>
                <td><?= date("D, M j, Y", strtotime($d["appointment_date"])) ?></td>
                <td><?= date("g:i A", strtotime($d["start_time"])) ?> - <?= date("g:i A", strtotime($d["end_time"])) ?></td>
                <td><?= $d["max_slots"] ?></td>
                <td><?= $d["used"] ?></td>
                <td><?= $available ?></td>
                <td><span class="status <?= css($status) ?>"><?= e($status) ?></span></td>
                <td><a class="btn btn-red" href="schedules.php?doctor=<?= $doctor_id ?>&delete=<?= $d["schedule_id"] ?>" data-confirm="Delete this date?">Delete</a></td>
            </tr>
        <?php } ?>
        <?php if (!$dates) { ?>
            <tr><td colspan="7">No upcoming dates for this doctor.</td></tr>
        <?php } ?>
    </table>
</div>
<?php } else { ?>
    <div class="box">Add a doctor first.</div>
<?php } ?>

<?php include "footer.php"; ?>
