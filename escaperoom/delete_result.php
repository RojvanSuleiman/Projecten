<?php
// Include session check to ensure user is logged in
require 'auth_session.php';

// Include database configuration
require 'config.php';

// Get 'id' from URL, or null if not set
$id = $_GET['id'] ?? null;

// Check if $id is not null and is a number
if ($id && is_numeric($id)) {
  // Prepare a DELETE SQL statement for the given result ID
  $stmt = $conn->prepare("DELETE FROM results WHERE id = ?");
  // Bind the ID as an integer to the statement
  $stmt->bind_param("i", $id);
  // Execute the delete query
  $stmt->execute();
  // Close the statement to free up resources
  $stmt->close();
}

// Redirect back to the list of results page
header("Location: list_results.php");
exit; // Stop further script execution
