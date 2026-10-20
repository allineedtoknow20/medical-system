<?php
include "../api/config.php";
needAdmin();

$message = "";
$error = "";

if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];
    $doctors = row("SELECT COUNT(*) AS total FROM doctors WHERE department_id = ?", "i", [$id]);
    if ($doctors["total"] > 0) {
        $error = "You cannot delete a department that still has doctors.";
    } else {
        run("DELETE FROM departments WHERE department_id = ?", "i", [$id]);
        $message = "Department deleted.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int) $_POST["id"];
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $same = row("SELECT department_id FROM departments WHERE department_name = ? AND department_id != ?", "si", [$name, $id]);

    if ($name == "") {
        $error = "Department name is required.";
    } elseif ($same) {
        $error = "That department already exists.";
    } elseif ($id > 0) {
        run("UPDATE departments SET department_name = ?, description = ? WHERE department_id = ?", "ssi", [$name, $description, $id]);
        $message = "Department updated.";
    } else {
        run("INSERT INTO departments (department_name, description) VALUES (?, ?)", "ss", [$name, $description]);
        $message = "Department added.";
    }
}

$edit = null;
if (isset($_GET["edit"])) {
    $edit = row("SELECT * FROM departments WHERE department_id = ?", "i", [(int) $_GET["edit"]]);
}

$departments = rows("SELECT * FROM departments ORDER BY department_name");

$title = "Manage Departments";
include "header.php";
?>

<div class="box">
    <h2><?= $edit ? "Edit Department" : "Add Department" ?></h2>

    <?php if ($message != "") { ?>
        <div class="success"><?= e($message) ?></div>
    <?php } ?>
    <?php if ($error != "") { ?>
        <div class="error"><?= e($error) ?></div>
    <?php } ?>

    <form method="post">
        <input type="hidden" name="id" value="<?= $edit ? $edit["department_id"] : 0 ?>">

        <label>Department Name</label>
        <input type="text" name="name" value="<?= e($edit["department_name"] ?? "") ?>" required>

        <label>Description</label>
        <input type="text" name="description" value="<?= e($edit["description"] ?? "") ?>">

        <button type="submit"><?= $edit ? "Update" : "Add" ?></button>
        <?php if ($edit) { ?>
            <a class="btn btn-gray" href="departments.php">Cancel</a>
        <?php } ?>
    </form>
</div>

<div class="table-wrap">
    <table>
        <tr>
            <th>Department</th>
            <th>Description</th>
            <th></th>
        </tr>
        <?php foreach ($departments as $d) { ?>
            <tr>
                <td><?= e($d["department_name"]) ?></td>
                <td><?= e($d["description"]) ?></td>
                <td>
                    <a class="btn" href="departments.php?edit=<?= $d["department_id"] ?>">Edit</a>
                    <a class="btn btn-red" href="departments.php?delete=<?= $d["department_id"] ?>" data-confirm="Delete this department?">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<?php include "footer.php"; ?>
