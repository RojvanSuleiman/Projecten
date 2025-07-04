<?php
// start the session so we can destroy it
session_start();

// remove all session data (logout)
session_destroy();

// send user back to login page
header("Location: index.php");
exit; // stop the script here
?>
