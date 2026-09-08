<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: attendance.php");
    exit;
}

$attendanceId = (int) ($_POST["id"] ?? 0);

if ($attendanceId <= 0) {
    header("Location: attendance.php");
    exit;
}

$stmt = $conn->prepare("
    DELETE FROM attendance
    WHERE id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $attendanceId);

if (!$stmt->execute()) {
    die("Delete failed: " . $stmt->error);
}

$stmt->close();

header("Location: attendance.php?deleted=1");
exit;