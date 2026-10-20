<?php
include "../api/config.php";
needAdmin();

$message = "";
$error = "";

if (isset($_GET["delete"])) {
    run("DELETE FROM doctors WHERE doctor_id = ?", "i", [(int) $_GET["delete"]]);
    $message = "Doctor deleted.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST["id"];
    $name = trim($_POST["name"]);
    $department_id = (int) $_POST["department_id"];
    $specialization = trim($_POST["specialization"]);
    $days = trim($_POST["days"]);
    $status = $_POST["status"];

    $department = row("SELECT department_id FROM departments WHERE department_id = ?", "i", [$department_id]);

    if ($name == "" || $specialization == "" || $days == "") {
        $error = "Please fill in all the fields.";
    } elseif (!$department) {
        $error = "Please choose a department.";
    } elseif (!in_array($status, ["Available", "Inactive"])) {
        $error = "Invalid status.";
    } elseif ($id > 0) {
        run("UPDATE doctors SET doctor_name = ?, department_id = ?, specialization = ?, available_days = ?, status = ? WHERE doctor_id = ?",
            "sisssi", [$name, $department_id, $specialization, $days, $status, $id]);
        $message = "Doctor updated.";
    } else {
        run("INSERT INTO doctors (doctor_name, department_id, specialization, available_days, status) VALUES (?, ?, ?, ?, ?)",
            "sisss", [$name, $department_id, $specialization, $days, $status]);
        $message = "Doctor added.";
    }
}

$edit = null;
if (isset($_GET["edit"])) {
    $edit = row("SELECT * FROM doctors WHERE doctor_id = ?", "i", [(int) $_GET["edit"]]);
}

$departments = rows("SELECT * FROM departments ORDER BY department_name");
$doctors = rows("SELECT d.*, dep.department_name FROM doctors d JOIN departments dep ON dep.department_id = d.department_id ORDER BY dep.department_name, d.doctor_name");

$title = "Manage Doctors";
include "header.php";
?>

<div class="box">
    <h2><?= $edit ? "Edit Doctor" : "Add Doctor" ?></h2>

    <?php if ($message != "") { ?>
        <div class="success"><?= e($message) ?></div>
    <?php } ?>
    <?php if ($error != "") { ?>
        <div class="error"><?= e($error) ?></div>
    <?php } ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $edit ? $edit["doctor_id"] : 0 ?>">

        <label>Doctor Name</label>
        <input type="text" name="name" value="<?= e($edit["doctor_name"] ?? "") ?>" required>

        <label>Department</label>
        <select name="department_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($departments as $d) { ?>
                <option value="<?= $d["department_id"] ?>" <?= ($edit["department_id"] ?? 0) == $d["department_id"] ? "selected" : "" ?>><?= e($d["department_name"]) ?></option>
            <?php } ?>
        </select>

        <label>Specialization</label>
        <input type="text" name="specialization" value="<?= e($edit["specialization"] ?? "") ?>" required>

        <label>Available Days (example: Mon,Tue,Wed)</label>
        <input type="text" name="days" value="<?= e($edit["available_days"] ?? "") ?>" required>

        <label>Status</label>
        <select name="status">
            <option <?= ($edit["status"] ?? "") == "Available" ? "selected" : "" ?>>Available</option>
            <option <?= ($edit["status"] ?? "") == "Inactive" ? "selected" : "" ?>>Inactive</option>
        </select>

        <button type="submit"><?= $edit ? "Update" : "Add" ?></button>
        <?php if ($edit) { ?>
            <a class="btn btn-gray" href="doctors.php">Cancel</a>
        <?php } ?>
    </form>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>Doctor</th>
            <th>Department</th>
            <th>Specialization</th>
            <th>Days</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php foreach ($doctors as $d) { ?>
            <tr>
                <td><?= e($d["doctor_name"]) ?></td>
                <td><?= e($d["department_name"]) ?></td>
                <td><?= e($d["specialization"]) ?></td>
                <td><?= e($d["available_days"]) ?></td>
                <td><?= e($d["status"]) ?></td>
                <td>
                    <a class="btn" href="doctors.php?edit=<?= $d["doctor_id"] ?>">Edit</a>
                    <a class="btn btn-red" href="doctors.php?delete=<?= $d["doctor_id"] ?>" data-confirm="Delete this doctor? Their schedules and appointments will also be deleted.">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<?php include "footer.php"; ?>
