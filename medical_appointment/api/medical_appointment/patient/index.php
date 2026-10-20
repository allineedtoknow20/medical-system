<?php
include "../api/config.php";
$title = "Home";
$home = true;
$departments = rows("SELECT * FROM departments ORDER BY department_name");
include "header.php";
?>

<div class="hero">
    <div class="wrap">
        <div class="hero-copy">
            <h1>Welcome to MiraWell, <span>Where Care Meets Wellness.</span></h1>
            <p>Book your visit with MiraWell in a few clicks. Choose a doctor, pick a date and get your queue number right away.</p>
            <a class="btn" href="book.php">Book Appointment</a>
            <?php if (isset($_SESSION["user_id"])) { ?>
                <a class="btn btn-gray" href="my_appointments.php">My Appointments</a>
            <?php } else { ?>
                <a class="btn btn-gray" href="register.php">Create Account</a>
            <?php } ?>
        </div>
        <div class="hero-art">
            <img src="../style/team.png" alt="The MiraWell care team">
        </div>
    </div>
</div>

<div class="container home">
    <form class="quick" method="get" action="book.php">
        <div>
            <h3>Quick Booking</h3>
            <p>Start with the department you need.</p>
        </div>
        <select name="department" required>
            <option value="">Select department</option>
            <?php foreach ($departments as $d) { ?>
                <option value="<?= $d["department_id"] ?>"><?= e($d["department_name"]) ?></option>
            <?php } ?>
        </select>
        <button type="submit">Find a Doctor</button>
    </form>

    <div class="cta">
        <div>
            <h2>Take care of yourself today</h2>
            <p>Early checkups lead to better health. Reserve your slot now.</p>
        </div>
        <a class="btn" href="book.php">Book Appointment</a>
    </div>
</div>

<?php include "footer.php"; ?>
