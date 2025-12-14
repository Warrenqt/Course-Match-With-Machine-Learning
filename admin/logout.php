<?php
require_once '../config.php';

// Clear admin session
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_login_time']);

// Redirect to admin login
header('Location: index.php');
exit;

