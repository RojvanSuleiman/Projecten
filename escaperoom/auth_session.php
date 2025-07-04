<?php
// start the session so we can check if someone is logged in
session_start();

// if user_id is not set then the person is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // send them back to login page
    exit; // stop everything else
}
?>
