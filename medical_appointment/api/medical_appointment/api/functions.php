<?php
function slotStatus($available) {
    if ($available <= 0) {
        return "Fully Booked";
    }
    if ($available <= 20) {
        return "Almost Full";
    }
    return "Available";
}

function usedSlots($schedule_id) {
    $result = row("SELECT COUNT(*) AS total FROM appointments WHERE schedule_id = ? AND status != 'Cancelled'", "i", [$schedule_id]);
    return $result["total"];
}

function nextSlot($schedule_id, $max_slots) {
    $taken = rows("SELECT slot_number FROM appointments WHERE schedule_id = ? AND slot_number IS NOT NULL", "i", [$schedule_id]);
    $numbers = array_column($taken, "slot_number");
    for ($i = 1; $i <= $max_slots; $i++) {
        if (!in_array($i, $numbers)) {
            return $i;
        }
    }
    return 0;
}

function slotTime($start_time, $slot) {
    return date("H:i:s", strtotime($start_time) + ($slot - 1) * 300);
}

function notify($user_id, $appointment_id, $message) {
    run("INSERT INTO notifications (user_id, appointment_id, message) VALUES (?, ?, ?)", "iis", [$user_id, $appointment_id, $message]);
}

function changeStatus($appointment_id, $status) {
    $a = row("SELECT a.*, d.doctor_name FROM appointments a JOIN doctors d ON d.doctor_id = a.doctor_id WHERE a.appointment_id = ?", "i", [$appointment_id]);
    if (!$a || $a["status"] == "Cancelled" || $a["status"] == $status) {
        return;
    }

    if ($status == "Cancelled") {
        run("UPDATE appointments SET status = 'Cancelled', slot_number = NULL WHERE appointment_id = ?", "i", [$appointment_id]);
    } else {
        run("UPDATE appointments SET status = ? WHERE appointment_id = ?", "si", [$status, $appointment_id]);
    }

    $date = date("F j, Y", strtotime($a["appointment_date"]));
    $doctor = $a["doctor_name"];

    if ($status == "Confirmed") {
        $message = "Appointment Confirmed — Your appointment with $doctor on $date has been confirmed by the clinic.";
    } elseif ($status == "Cancelled") {
        $message = "Appointment Cancelled — Your appointment scheduled for $date has been cancelled.";
    } elseif ($status == "Completed") {
        $message = "Appointment Completed — Your appointment with $doctor on $date is completed. Thank you!";
    } else {
        $message = "Appointment Updated — Your appointment on $date was marked as $status.";
    }
    notify($a["user_id"], $appointment_id, $message);
}

function sendReminders($user_id) {
    $tomorrow = date("Y-m-d", strtotime("+1 day"));
    $list = rows("SELECT a.appointment_id, a.appointment_time, d.doctor_name FROM appointments a JOIN doctors d ON d.doctor_id = a.doctor_id WHERE a.user_id = ? AND a.appointment_date = ? AND a.status IN ('Pending', 'Confirmed')", "is", [$user_id, $tomorrow]);

    foreach ($list as $a) {
        $sent = row("SELECT notification_id FROM notifications WHERE appointment_id = ? AND message LIKE 'Appointment Reminder%'", "i", [$a["appointment_id"]]);
        if (!$sent) {
            $time = date("g:i A", strtotime($a["appointment_time"]));
            notify($user_id, $a["appointment_id"], "Appointment Reminder — You have an appointment with " . $a["doctor_name"] . " tomorrow at $time.");
        }
    }
}

function navLink($file, $text) {
    $class = basename($_SERVER["PHP_SELF"]) == $file ? ' class="active"' : "";
    return '<a href="' . $file . '"' . $class . '>' . $text . '</a>';
}

function getSchedule($doctor_id, $date) {
    $last = date("Y-m-d", strtotime("+90 days"));
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) || !strtotime($date) || $date < date("Y-m-d") || $date > $last) {
        return null;
    }
    $sql = "SELECT * FROM schedules WHERE doctor_id = ? AND appointment_date = ?";
    $found = row($sql, "is", [$doctor_id, $date]);
    if (!$found) {
        run("INSERT IGNORE INTO schedules (doctor_id, appointment_date) VALUES (?, ?)", "is", [$doctor_id, $date]);
        $found = row($sql, "is", [$doctor_id, $date]);
    }
    return $found;
}
