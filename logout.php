<?php
session_start();
include('connect/connection.php');
// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page or homepage
header("Location: index.php");
exit();
?>