<?php

session_start();

require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$role = $_POST["role"] ?? "";

if ($email === "" || $password === "" || $role === "") {
    $_SESSION["login_error"] = "Please fill all fields.";
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        id, 
        name, 
        email, 
        password, 
        role, 
        status,
        last_login, 
        faculty_id,
        student_id
    FROM users
    WHERE email = ? AND role = ?
    LIMIT 1
");

$stmt->bind_param("ss", $email, $role);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION["login_error"] = "Invalid email, password, or role.";
    header("Location: index.php");
    exit;
}

$user = $result->fetch_assoc();

if ((int)$user["status"] !== 1) {
    $_SESSION["login_error"] = "Your account is inactive.";
    header("Location: index.php");
    exit;
}

if (!password_verify($password, $user["password"])) {
    $_SESSION["login_error"] = "Invalid email, password, or role.";
    header("Location: index.php");
    exit;
}

session_regenerate_id(true);

$_SESSION["last_login"] = $user["last_login"] ?? null;
$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_email"] = $user["email"];
$_SESSION["user_role"] = $user["role"];
$_SESSION["faculty_id"] = $user["faculty_id"] ?? null;
$_SESSION["student_id"] = $user["student_id"] ?? null;

$updateLogin = $conn->prepare("
    UPDATE users
    SET last_login = NOW()
    WHERE id = ?
");

$updateLogin->bind_param("i", $user["id"]);
$updateLogin->execute();

if ($user["role"] === "admin") {

    header("Location: admin/dashboard.php");
    exit;

} elseif ($user["role"] === "faculty") {

    header("Location: faculty/dashboard.php");
    exit;

} elseif ($user["role"] === "student") {

    header("Location: student/dashboard.php");
    exit;

}

exit;
?>