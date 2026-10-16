<?php
session_start();
session_destroy();

// Start a fresh session so we can carry a message to login.php
session_start();
$_SESSION['successMessage'] = "You have been logged out successfully.";

header("Location: ../../login.php");
exit();
?>