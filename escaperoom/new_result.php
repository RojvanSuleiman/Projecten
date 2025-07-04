<?php
require 'auth_session.php'; // Ensure user is logged in
require 'config.php';       // Connect to the database

// Only allow access to admins
if ($_SESSION['role'] !== 'admin') {
  header("Location: home.php");
  exit();
}

$error = ''; // For storing error messages

// Fetch all users to populate the dropdown
$userStmt = $conn->prepare("SELECT id, username FROM users ORDER BY username");
$userStmt->execute();
$users = $userStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$userStmt->close();

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get and sanitize inputs
  $user_id = (int)$_POST['user_id'];
  $time    = (int)$_POST['time_seconds'];
  $streak  = (int)$_POST['streak'];

  // Insert the result into the database
  $stmt = $conn->prepare("
    INSERT INTO results (user_id, time_seconds, streak)
    VALUES (?, ?, ?)
  ");
  $stmt->bind_param("iii", $user_id, $time, $streak);

  // If successful, redirect to results list
  if ($stmt->execute()) {
    header("Location: list_results.php");
    exit;
  }

  // If failed, show error message
  $error = $stmt->error;
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> <!-- Encoding -->
  <title>New Result</title> <!-- Page title -->
</head>
<body>
  <h1>New Result</h1>

  <!-- Show error if any -->
  <?php if ($error): ?>
    <p style="color: red;">Error: <?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <!-- Form to add a new result -->
  <form method="POST">
    <label>
      User:
      <!-- Dropdown to select user -->
      <select name="user_id" required>
        <option value="">-- pick a user --</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>">
            <?= htmlspecialchars($u['username']) ?> (ID <?= $u['id'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <br><br>

    <label>
      Time (seconds):
      <input type="number" name="time_seconds" min="0" required>
    </label>
    <br><br>

    <label>
      Streak:
      <input type="number" name="streak" min="0" required>
    </label>
    <br><br>

    <button type="submit">Create Result</button>
    <a href="list_results.php" style="margin-left:1em;">Cancel</a>
  </form>
</body>
</html>
