<?php
include "../api/config.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $user = row("SELECT * FROM users WHERE username = ?", "s", [$username]);

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["name"] = $user["name"];
        header("Location: dashboard.php");
        exit;
    }
    $error = "Wrong username or password.";
}

$title = "Login";
include "header.php";
?>

<div class="auth">
    <div class="auth-side">
        <div>
            <span class="eyebrow">Patient Portal</span>
            <h2>Welcome back to MiraWell</h2>
            <p>Log in to book an appointment, check your queue number and read your notifications.</p>
        </div>
        <img src="../style/team.png" alt="MiraWell care team">
    </div>
    <div class="auth-form">
        <h2>Patient Login</h2>
        <p class="sub">Enter your username and password to continue.</p>

        <?php if (isset($_GET["registered"])) { ?>
            <div class="success">Registration successful. You can now log in.</div>
        <?php } ?>
        <?php if ($error != "") { ?>
            <div class="error"><?= e($error) ?></div>
        <?php } ?>

        <form method="post">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
            <a href="register.php">Create an account</a>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
