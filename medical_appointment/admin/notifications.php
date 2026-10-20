<?php
include "../api/config.php";
needAdmin();

if (isset($_GET["delete"])) {
    run("DELETE FROM notifications WHERE notification_id = ?", "i", [(int) $_GET["delete"]]);
    header("Location: notifications.php");
    exit;
}

$notes = rows("SELECT n.*, u.name AS patient FROM notifications n JOIN users u ON u.user_id = n.user_id ORDER BY n.notification_id DESC LIMIT 100");

$title = "Manage Notifications";
include "header.php";
?>

<div class="page-title">
    <h2>Notifications Sent to Patients</h2>
    <p>The system creates a notification when an appointment is booked, confirmed, cancelled or rescheduled.</p>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>Date</th>
            <th>Patient</th>
            <th>Message</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php foreach ($notes as $n) { ?>
            <tr>
                <td><?= date("M j, g:i A", strtotime($n["created_at"])) ?></td>
                <td><?= e($n["patient"]) ?></td>
                <td><?= e($n["message"]) ?></td>
                <td><?= $n["is_read"] ? "Read" : "Unread" ?></td>
                <td><a class="btn btn-red" href="notifications.php?delete=<?= $n["notification_id"] ?>" data-confirm="Delete this notification?">Delete</a></td>
            </tr>
        <?php } ?>
        <?php if (!$notes) { ?>
            <tr><td colspan="5">No notifications yet.</td></tr>
        <?php } ?>
    </table>
</div>

<?php include "footer.php"; ?>
