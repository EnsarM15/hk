<?php
require_once 'config/config.php';

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Set flash message
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to login page
redirect('login.php');
?>