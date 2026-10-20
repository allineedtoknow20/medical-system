<?php
include "../api/config.php";
needLogin();

$user_id = $_SESSION["user_id"];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $address = trim($_POST["address"]);

    $used = row("SELECT user_id FROM users WHERE email = ? AND user_id != ?", "si", [$email, $user_id]);

    if ($name == "" || $email == "" || $contact == "" || $address == "") {
        $error = "Please fill in all the fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email is not valid.";
    } elseif ($used) {
        $error = "That email is already used by another account.";
    } else {
        run("UPDATE users SET name = ?, email = ?, contact = ?, address = ? WHERE user_id = ?", "ssssi", [$name, $email, $contact, $address, $user_id]);
        $_SESSION["name"] = $name;
        $message = "Profile updated.";
    }
}

$user = row("SELECT * FROM users WHERE user_id = ?", "i", [$user_id]);

$title = "Profile";
include "header.php";
?>

<div class="box small-box">
    <h2>My Profile</h2>

    <?php if ($message != "") { ?>
        <div class="success"><?= e($message) ?></div>
    <?php } ?>
    <?php if ($error != "") { ?>
        <div class="error"><?= e($error) ?></div>
    <?php } ?>

    <p>Patient ID: <b><?= $user["user_id"] ?></b></p>
    <p>Username: <b><?= e($user["username"]) ?></b></p>
    <p>Date of Birth: <b><?= date("F j, Y", strtotime($user["date_of_birth"])) ?></b> (<?= e($user["gender"]) ?>)</p>

    <form method="post">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= e($user["name"]) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= e($user["email"]) ?>" required>

        <label>Contact Number</label>
        <input type="text" name="contact" value="<?= e($user["contact"]) ?>" required>

        <label>Address</label>
        <input type="text" name="address" value="<?= e($user["address"]) ?>" required>

        <button type="submit">Save</button>
    </form>
</div>

<?php include "footer.php"; ?>
