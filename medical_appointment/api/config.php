<?php
session_start();
date_default_timezone_set("Asia/Manila");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli("localhost", "root", "", "medical_appointment");
    $conn->set_charset("utf8mb4");
} catch (Exception $ex) {
    die("Cannot connect to the database. Start MySQL in XAMPP and import database.sql first.");
}

function run($sql, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($types != "") {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

function rows($sql, $types = "", $params = []) {
    return run($sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

function row($sql, $types = "", $params = []) {
    $result = rows($sql, $types, $params);
    return $result ? $result[0] : null;
}

function e($text) {
    return htmlspecialchars($text ?? "");
}

function css($text) {
    return strtolower(str_replace(" ", "", $text));
}

function needLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function needAdmin() {
    if (!isset($_SESSION["admin_id"])) {
        header("Location: login.php");
        exit;
    }
}

require_once __DIR__ . "/functions.php";
