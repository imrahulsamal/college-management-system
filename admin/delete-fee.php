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
    header("Location: fees.php");
    exit;
}


/* GET FEE ID */

$feeId = (int) ($_POST["id"] ?? 0);

if ($feeId <= 0) {
    header("Location: fees.php");
    exit;
}


/* DELETE FEE RECORD */

$stmt = $conn->prepare("
    DELETE FROM fees
    WHERE id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $feeId);

if (!$stmt->execute()) {
    die("Delete failed: " . $stmt->error);
}

$stmt->close();


/* REDIRECT BACK */

header("Location: fees.php?deleted=1");
exit;