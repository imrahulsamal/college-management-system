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
    header("Location: departments.php");
    exit;
}

$id = (int) ($_POST["id"] ?? 0);

if ($id <= 0) {
    header("Location: departments.php");
    exit;
}

$stmt = $conn->prepare("
    DELETE FROM departments
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: departments.php?deleted=1");
exit;