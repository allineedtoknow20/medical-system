<?php
include "../api/config.php";

$errors = [];
$name = $email = $contact = $address = $dob = $gender = $username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $contact = trim($_POST["contact"]);
    $address = trim($_POST["address"]);
    $dob = $_POST["dob"];
    $gender = $_POST["gender"];
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($name == "" || $email == "" || $contact == "" || $address == "" || $dob == "" || $gender == "" || $username == "" || $password == "") {
        $errors[] = "Please fill in all the fields.";
    }
    if ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }
    if ($contact != "" && !preg_match("/^[0-9+ -]{7,15}$/", $contact)) {
        $errors[] = "Contact number is not valid.";
    }
    if ($dob != "" && (!strtotime($dob) || $dob > date("Y-m-d"))) {
        $errors[] = "Date of birth is not valid.";
    }
    if ($gender != "" && !in_array($gender, ["Male", "Female", "Other"])) {
        $errors[] = "Please choose a gender.";
    }
    if ($password != "" && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password != $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (!$errors) {
        $taken = row("SELECT user_id FROM users WHERE username = ? OR email = ?", "ss", [$username, $email]);
        if ($taken) {
            $errors[] = "Username or email is already used.";
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        run("INSERT INTO users (name, email, contact, address, date_of_birth, gender, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            "ssssssss", [$name, $email, $contact, $address, $dob, $gender, $username, $hash]);
        header("Location: login.php?registered=1");
        exit;
    }
}

$title = "Register";
include "header.php";
?>

<div class="auth">
    <div class="auth-side">
        <div>
            <span class="eyebrow">New Patient</span>
            <h2>Join MiraWell today</h2>
            <p>Create your free account and book your doctor online.</p>
            <ul>
                <li>Choose your own doctor and date</li>
                <li>Get your queue number instantly</li>
                <li>Receive updates for every appointment</li>
            </ul>
        </div>
        <img src="../style/team.png" alt="MiraWell care team">
    </div>
    <div class="auth-form">
        <h2>Patient Registration</h2>
        <p class="sub">Fill in your details to create an account.</p>

        <?php foreach ($errors as $error) { ?>
            <div class="error"><?= e($error) ?></div>
        <?php } ?>

        <form method="post" id="registerForm">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= e($name) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= e($email) ?>" required>

            <label>Contact Number</label>
            <input type="text" name="contact" value="<?= e($contact) ?>" required>

            <label>Address</label>
            <input type="text" name="address" value="<?= e($address) ?>" required>

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= e($dob) ?>" required>

            <label>Gender</label>
            <select name="gender" required>
                <option value="">-- Select --</option>
                <option <?= $gender == "Male" ? "selected" : "" ?>>Male</option>
                <option <?= $gender == "Female" ? "selected" : "" ?>>Female</option>
                <option <?= $gender == "Other" ? "selected" : "" ?>>Other</option>
            </select>

            <label>Username</label>
            <input type="text" name="username" value="<?= e($username) ?>" required>

            <label>Password</label>
            <input type="password" name="password" id="password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <button type="submit">Register</button>
            <a href="login.php">I already have an account</a>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
