<?php
// Include session authentication to ensure user is logged in
require 'auth_session.php';

// Include database configuration
require 'config.php';

// Only allow access to users with 'admin' role
if ($_SESSION['role'] !== 'admin') {
  // Redirect non-admin users to home page
  header("Location: home.php");
  exit(); // Stop script execution
}

// Initialize error message variable
$error = '';

// Get the 'id' from the URL or set to null if not provided
$id = $_GET['id'] ?? null;

// If no ID or the ID is not numeric, redirect to the results list
if (!$id || !is_numeric($id)) {
  header("Location: list_results.php");
  exit; // Stop execution
}

// Fetch the existing result row for the given ID
$stmt = $conn->prepare("SELECT user_id, time_seconds, streak FROM results WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id); // Bind the ID as integer
$stmt->execute(); // Execute the query
$res = $stmt->get_result(); // Get the result set

// If no matching result found, redirect
if ($res->num_rows === 0) {
  header("Location: list_results.php");
  exit;
}

// Fetch the result row as associative array
$row = $res->fetch_assoc();

// Close the SELECT statement
$stmt->close();

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get updated values from form
  $user_id = $_POST['user_id'];
  $time    = $_POST['time_seconds'];
  $streak  = $_POST['streak'];

  // Prepare UPDATE query to modify the result
  $stmt = $conn->prepare("
    UPDATE results
    SET user_id = ?, time_seconds = ?, streak = ?
    WHERE id = ?
  ");
  // Bind the updated values to the query
  $stmt->bind_param("iiii", $user_id, $time, $streak, $id);

  // Execute the query
  if ($stmt->execute()) {
    // Redirect to list if update was successful
    header("Location: list_results.php");
    exit;
  }

  // If error occurred, store error message
  $error = $stmt->error;

  // Close the statement
  $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"> <!-- Set UTF-8 character encoding -->
  <title>Edit Result</title> <!-- Page title in browser -->
</head>
<body>
  <!-- Display heading with result ID -->
  <h1>Edit Result #<?= $id ?></h1>

  <!-- Show error message if one exists -->
  <?php if($error): ?>
    <p style="color:red"><?= $error ?></p>
  <?php endif; ?>

  <!-- Form to edit result details -->
  <form method="POST">
    <!-- User ID input field -->
    <label>User ID:
      <input name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>" required>
    </label><br>

    <!-- Time in seconds input field -->
    <label>Time (seconds):
      <input name="time_seconds" value="<?= htmlspecialchars($row['time_seconds']) ?>" required>
    </label><br>

    <!-- Streak count input field -->
    <label>Streak:
      <input name="streak" value="<?= htmlspecialchars($row['streak']) ?>" required>
    </label><br>

    <!-- Submit and cancel buttons -->
    <button type="submit">Save</button>
    <a href="list_results.php">Cancel</a>
  </form>
</body>
</html>
