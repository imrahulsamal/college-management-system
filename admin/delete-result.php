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


/* ONLY ALLOW POST REQUEST */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: results.php");
    exit;
}


/* GET RESULT ID */

$resultId = (int) ($_POST["id"] ?? 0);

if ($resultId <= 0) {
    header("Location: results.php");
    exit;
}


/* DELETE RESULT */

$stmt = $conn->prepare("
    DELETE FROM results
    WHERE id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $resultId);

if (!$stmt->execute()) {
    die("Delete failed: " . $stmt->error);
}

$stmt->close();


/* REDIRECT BACK */

header("Location: results.php?deleted=1");
exit;