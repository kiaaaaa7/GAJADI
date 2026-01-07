<?php
require_once '../includes/config.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
Helper::redirect('/', 'success', 'Anda telah logout.');
?>