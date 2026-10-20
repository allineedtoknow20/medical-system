<?php
include "../api/config.php";
needLogin();

$user_id = $_SESSION["user_id"];
$notes = rows("SELECT * FROM notifications WHERE user_id = ? ORDER BY notification_id DESC", "i", [$user_id]);
run("UPDATE notifications SET is_read = 1 WHERE user_id = ?", "i", [$user_id]);

$title = "Notifications";
include "header.php";
?>

<div class="page-title">
    <h2>Notifications</h2>
    <p>New messages are highlighted in blue.</p>
</div>

<?php foreach ($notes as $n) { ?>
    <div class="note <?= $n["is_read"] ? "" : "unread" ?>">
        <?= e($n["message"]) ?>
        <small><?= date("M j, Y g:i A", strtotime($n["created_at"])) ?></small>
    </div>
<?php } ?>

<?php if (!$notes) { ?>
    <div class="note">You have no notifications.</div>
<?php } ?>

<?php include "footer.php"; ?>
