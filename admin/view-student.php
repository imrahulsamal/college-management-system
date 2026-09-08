<?php
session_start();
require_once '../config/database.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Student ID check
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Student ID not found.");
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student not found.");
}

$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student | College CMS</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            padding: 40px;
        }

        .card {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
        }

        .row {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 18px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div class="card">

    <h2>Student Details</h2>

    <div class="row">
        <span class="label">Admission No:</span>
        <?= htmlspecialchars($student['admission_no']) ?>
    </div>

    <div class="row">
        <span class="label">First Name:</span>
        <?= htmlspecialchars($student['first_name']) ?>
    </div>

    <div class="row">
        <span class="label">Last Name:</span>
        <?= htmlspecialchars($student['last_name']) ?>
    </div>

    <div class="row">
        <span class="label">Email:</span>
        <?= htmlspecialchars($student['email'] ?? '') ?>
    </div>

    <div class="row">
        <span class="label">Phone:</span>
        <?= htmlspecialchars($student['phone'] ?? '') ?>
    </div>

    <div class="row">
        <span class="label">Gender:</span>
        <?= htmlspecialchars($student['gender'] ?? '') ?>
    </div>

    <div class="row">
        <span class="label">Date of Birth:</span>
        <?= htmlspecialchars($student['date_of_birth'] ?? '') ?>
    </div>

    <div class="row">
        <span class="label">Course:</span>
        <?= htmlspecialchars($student['course'] ?? '') ?>
    </div>

    <div class="row">
        <span class="label">Semester:</span>
        <?= htmlspecialchars($student['semester'] ?? '') ?>
    </div>

    <div class="row">
        <span class="label">Address:</span>
        <?= htmlspecialchars($student['address'] ?? '') ?>
    </div>

    <a href="students.php" class="back-btn">← Back to Students</a>

</div>

</body>
</html>