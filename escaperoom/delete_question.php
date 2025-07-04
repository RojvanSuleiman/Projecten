<?php
// Include session check to make sure the user is logged in
require 'auth_session.php';

// Include database connection settings
require 'config.php';

// Check if the logged-in user is not an admin
if ($_SESSION['role'] !== 'admin') {
    // Redirect non-admin users to index page
    header("Location: index.php");
    exit(); // Stop further execution
}
?>

<?php
// If 'id' is not provided in the URL, redirect to list_questions.php
if (!isset($_GET['id'])) header('Location:list_questions.php');

// Get the ID from the URL and cast it to an integer
$id = (int)$_GET['id'];

// Prepare a DELETE statement to remove the question with that ID
$stmt = $conn->prepare("DELETE FROM questions WHERE id=?");

// Bind the ID to the SQL query as an integer
$stmt->bind_param('i', $id);

// Execute the query
$stmt->execute();

// Redirect back to the list of questions after deletion
header('Location: list_questions.php');
exit; // Stop further execution
?>
