<?php
// Include session check to make sure the user is logged in
require 'auth_session.php';

// Include database configuration for DB connection
require 'config.php';

// Only allow access to admins
if ($_SESSION['role'] !== 'admin') {
    // Redirect non-admin users to home page
    header("Location: home.php");
    exit(); // Stop further script execution
}

// Get 'id' from the URL, or set to null if not present
$id = $_GET['id'] ?? null;

// If ID exists, delete the team with that ID
if ($id) {
    // Prepare a DELETE SQL statement for the teams table
    $stmt = $conn->prepare("DELETE FROM teams WHERE id = ?");
    // Bind the ID as an integer
    $stmt->bind_param("i", $id);
    // Execute the delete query
    $stmt->execute();
}

// After deletion, redirect back to the teams list
header("Location: view_teams.php");
exit(); // End the script
?>
