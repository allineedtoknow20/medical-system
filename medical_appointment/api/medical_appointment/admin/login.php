<?php
include "../api/config.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $admin = row("SELECT * FROM admins WHERE username = ?", "s", [$username]);

    if ($admin && password_verify($password, $admin["password"])) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $admin["admin_id"];
        $_SESSION["admin_name"] = $admin["name"];
        header("Location: index.php");
        exit;
    }
    $error = "Wrong username or password.";
}

$title = "Admin Login";
include "header.php";
?>

<div class="auth">
    <div class="auth-side">
        <div>
            <span class="eyebrow">Staff Portal</span>
            <h2>MiraWell Administration</h2>
            <p>Sign in to manage the clinic.</p>
            <ul>
                <li>Manage departments and doctors</li>
                <li>Set doctor schedules</li>
                <li>Confirm and reschedule appointments</li>
            </ul>
        </div>
    </div>
    <div class="auth-form">
        <h2>Admin Login</h2>
        <p class="sub">Enter your staff username and password.</p>

        <?php if ($error != "") { ?>
            <div class="error"><?= e($error) ?></div>
        <?php } ?>

        <form method="post">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
