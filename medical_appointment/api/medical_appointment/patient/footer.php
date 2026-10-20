</div>
<div class="footer">
    <div class="cols">
        <div>
            <div class="brand">MiraWell</div>
            <p>Book your doctor's appointment online and skip the long wait.</p>
        </div>
        <div>
            <h4>Patients</h4>
            <a href="book.php">Book Appointment</a>
            <?php if (isset($_SESSION["user_id"])) { ?>
                <a href="my_appointments.php">My Appointments</a>
                <a href="notifications.php">Notifications</a>
                <a href="profile.php">Profile</a>
            <?php } else { ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php } ?>
        </div>
        <div>
            <h4>Clinic</h4>
            <a href="index.php">Home</a>
            <a href="../admin/login.php">Staff Portal</a>
        </div>
    </div>
    <div class="copy">&copy; <?= date("Y") ?> MiraWell Health Clinic. All rights reserved.</div>
</div>
<script src="../style/script.js"></script>
</body>
</html>
