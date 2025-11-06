<?php
$password = 'your_password_here'; // Password you want to check
$hash = 'hash_from_database'; // Hash from your database

if (password_verify($password, $hash)) {
    echo "Password is valid!";
} else {
    echo "Invalid password.";
}
?>
