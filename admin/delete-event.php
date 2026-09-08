<?php

session_start();

require_once "../config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../events.php?deleted=1");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: events.php");
    exit;
}

$eventId = (int) ($_POST["id"] ?? 0);

if ($eventId <= 0) {
    header("Location: events.php");
    exit;
}

$deleteQuery = $conn->prepare("
    DELETE FROM events
    WHERE id = ?
");

$deleteQuery->bind_param("i", $eventId);

if ($deleteQuery->execute()) {
    header("Location: events.php?deleted=1");
    exit;
}

header("Location: events.php?error=delete");
exit;