<?php
session_start(); // Start session

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ./Login');
exit;
