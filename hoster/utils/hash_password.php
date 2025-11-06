<?php
// Use this script to generate password hashes for new admin users
$password = "Zxcvbnm@123456";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password Hash: " . $hash;
?>