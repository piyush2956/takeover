<?php
$password = 'Hrry7357';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password Hash: " . $hash;
?>
