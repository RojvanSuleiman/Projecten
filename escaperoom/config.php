<?php
// settings to connect with the database
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'escaperoom';

// connect to the MySQL database with those settings
$conn = new mysqli($host, $user, $pass, $db);

// if something goes wrong, show error and stop
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
