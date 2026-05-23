<?php
$hash = '$2y$10$0DNUbwP8qfBnOodjiKVPSuHMX/ssBZUhgY18u3mFXAEAV6rtzSNVG';
if (password_verify('admin', $hash)) echo "Password is admin\n";
if (password_verify('admin123', $hash)) echo "Password is admin123\n";
if (password_verify('password', $hash)) echo "Password is password\n";
?>
