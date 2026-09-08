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
    die("Invalid request method.");
}

$subjectId = (int) ($_POST["id"] ?? 0);

if ($subjectId <= 0) {
    die("Invalid subject ID.");
}

$stmt = $conn->prepare("
    DELETE FROM subjects
    WHERE id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $subjectId);

if (!$stmt->execute()) {
    die("Delete failed: " . $stmt->error);
}

$stmt->close();

header("Location: subjects.php?deleted=1");
exit;