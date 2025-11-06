<?php
require_once('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        header("Location: ../register.html?error=password_mismatch");
        exit();
    }

    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: ../register.html?error=user_exists");
            exit();
        }

        // Hash password and insert user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (firstname, lastname, username, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$firstname, $lastname, $username, $email, $hashed_password]);

        header("Location: ../index.html?registration=success");
        exit();
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        header("Location: ../register.html?error=server");
        exit();
    }
}
?>
