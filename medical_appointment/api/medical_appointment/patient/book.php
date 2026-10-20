<?php
include "../api/config.php";
needLogin();

$user_id = $_SESSION["user_id"];
$error = "";
$department_id = (int) ($_POST["department"] ?? $_GET["department"] ?? 0);
$doctor_id = (int) ($_POST["doctor"] ?? 0);
$date = $_POST["date"] ?? "";
$reason = trim($_POST["reason"] ?? "");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor = row("SELECT * FROM doctors WHERE doctor_id = ? AND status = 'Available'", "i", [$doctor_id]);

    if (!$doctor) {
        $error = "Please choose a department and a doctor.";
    } elseif ($date == "") {
        $error = "Please choose a date on the calendar.";
    } elseif ($reason == "") {
        $error = "Please enter the reason for your appointment.";
    } elseif (strlen($reason) > 255) {
        $error = "The reason is too long. Please keep it under 255 characters.";
    } else {
        $schedule = getSchedule($doctor_id, $date);

        if (!$schedule) {
            $error = "That date is not available. Please choose another date.";
        } else {
            $schedule_id = $schedule["schedule_id"];
            $conn->begin_transaction();
            row("SELECT schedule_id FROM schedules WHERE schedule_id = ? FOR UPDATE", "i", [$schedule_id]);

            $already = row("SELECT appointment_id FROM appointments WHERE schedule_id = ? AND user_id = ? AND status != 'Cancelled'", "ii", [$schedule_id, $user_id]);
            $slot = nextSlot($schedule_id, $schedule["max_slots"]);

            if ($already) {
                $error = "You already have an appointment on this date.";
                $conn->rollback();
            } elseif ($slot == 0) {
                $error = "Sorry, this date is fully booked. Please choose another date.";
                $conn->rollback();
            } else {
                $time = slotTime($schedule["start_time"], $slot);
                run("INSERT INTO appointments (appointment_no, user_id, doctor_id, schedule_id, appointment_date, appointment_time, slot_number, reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    "siiissis", [uniqid("TMP"), $user_id, $doctor_id, $schedule_id, $date, $time, $slot, $reason]);
                $id = $conn->insert_id;

                $number = "APPT-" . date("Ymd", strtotime($date)) . "-" . str_pad($id, 4, "0", STR_PAD_LEFT);
                run("UPDATE appointments SET appointment_no = ? WHERE appointment_id = ?", "si", [$number, $id]);

                $nice = date("F j, Y", strtotime($date));
                notify($user_id, $id, "Appointment Confirmed — Your appointment with " . $doctor["doctor_name"] . " on $nice has been successfully booked.");

                $conn->commit();
                header("Location: confirmation.php?id=" . $id);
                exit;
            }
        }
    }
}

$departments = rows("SELECT department_id, department_name FROM departments ORDER BY department_name");
$doctors = rows("SELECT doctor_id, department_id, doctor_name FROM doctors WHERE status = 'Available' ORDER BY doctor_name");

$slots = [];
$booked = rows("SELECT s.doctor_id, s.appointment_date, s.max_slots, COUNT(a.appointment_id) AS used
    FROM schedules s
    LEFT JOIN appointments a ON a.schedule_id = s.schedule_id AND a.status != 'Cancelled'
    WHERE s.appointment_date >= ? AND s.appointment_date <= ?
    GROUP BY s.schedule_id, s.doctor_id, s.appointment_date, s.max_slots", "ss", [date("Y-m-d"), date("Y-m-d", strtotime("+90 days"))]);
foreach ($booked as $b) {
    $slots[$b["doctor_id"]][$b["appointment_date"]] = ["max" => (int) $b["max_slots"], "used" => (int) $b["used"]];
}

$data = [
    "doctors" => $doctors,
    "slots" => $slots,
    "today" => date("Y-m-d"),
    "last" => date("Y-m-d", strtotime("+90 days")),
    "selected" => ["department" => $department_id, "doctor" => $doctor_id, "date" => $date],
];

$title = "Book Appointment";
include "header.php";
?>

<div class="auth">
    <div class="auth-side">
        <div>
            <span class="eyebrow">Appointments</span>
            <h2>Book your visit</h2>
            <p>It only takes a minute.</p>
            <ul>
                <li>Pick a department and doctor</li>
                <li>Choose any open date</li>
                <li>Tell us the reason for your visit</li>
                <li>Get your queue number right away</li>
            </ul>
        </div>
        <img src="../style/team.png" alt="MiraWell care team">
    </div>
    <div class="auth-form">
        <h2>Book an Appointment</h2>
        <p class="sub">Choose your doctor and date below.</p>

        <?php if ($error != "") { ?>
            <div class="error"><?= e($error) ?></div>
        <?php } ?>

        <form method="post" id="bookForm">
            <label>Patient</label>
            <input type="text" value="<?= e($_SESSION["name"]) ?>" disabled>

            <label>Department</label>
            <select name="department" id="department" required>
                <option value="">Select department</option>
                <?php foreach ($departments as $d) { ?>
                    <option value="<?= $d["department_id"] ?>"><?= e($d["department_name"]) ?></option>
                <?php } ?>
            </select>

            <label>Doctor</label>
            <select name="doctor" id="doctor" required>
                <option value="">Select doctor</option>
            </select>

            <label>Date</label>
            <div class="calendar" id="calendar"></div>
            <input type="hidden" name="date" id="date" value="<?= e($date) ?>">

            <div id="slotBox" style="display: none;">
                <div class="slot-numbers">
                    <div><b id="slotTotal">0</b>Total Slots</div>
                    <div><b id="slotUsed">0</b>Used Slots</div>
                    <div><b id="slotAvailable">0</b>Available</div>
                </div>
                <p class="slot-status"><span id="slotStatus" class="status"></span></p>
            </div>

            <label>Reason for Appointment</label>
            <textarea name="reason" rows="3" maxlength="255" placeholder="Example: fever and cough for 3 days" required><?= e($reason) ?></textarea>

            <button type="submit">Book Appointment</button>
        </form>
    </div>
</div>

<script>
    var bookingData = <?= json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

<?php include "footer.php"; ?>
