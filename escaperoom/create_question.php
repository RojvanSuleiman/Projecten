<?php
// Include the session check to ensure user is logged in
require 'auth_session.php';

// Include the database configuration
require 'config.php';

// Check if the logged-in user is not an admin
if ($_SESSION['role'] !== 'admin') {
    // If not admin, redirect to index.php
    header("Location: index.php");
    exit(); // Stop script execution
}

$error = ''; // Initialize an empty error message

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the submitted values and trim whitespace
  $q = trim($_POST['question']);     // The question text
  $a = trim($_POST['answer']);       // The answer text
  $h = trim($_POST['hint']);         // Optional hint
  $r = (int)$_POST['roomId'];        // Room ID as integer
  $p = (int)$_POST['puzzle_id'];     // Puzzle ID as integer

  // Validate required fields (hint is optional)
  if (!$q || !$a || !$r || !$p) {
    $error = 'All fields except hint are required.'; // Set error message
  } else {
    // Prepare the SQL statement to insert a new question
    $stmt = $conn->prepare(
      "INSERT INTO questions (question,answer,hint,roomId,puzzle_id)
       VALUES (?,?,?,?,?)"
    );
    // Bind the values to the SQL statement
    $stmt->bind_param('sssii', $q, $a, $h, $r, $p);
    // Execute the SQL statement
    $stmt->execute();
    // Redirect to the list of questions after successful insert
    header('Location: list_questions.php');
    exit; // Stop script execution
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"> <!-- Set character encoding -->
  <title>New Question</title> <!-- Title shown in browser tab -->
</head>
<body>
  <h1>Add Question</h1> <!-- Page heading -->

  <!-- Display error message if any -->
  <?php if($error): ?>
    <p style="color:red"><?= $error ?></p>
  <?php endif; ?>

  <!-- Form to add a new question -->
  <form method="post">
    <p>Room ID: <input name="roomId" type="number" required></p> <!-- Input for room ID -->
    <p>Puzzle ID: <input name="puzzle_id" type="number" required></p> <!-- Input for puzzle ID -->
    <p>Question:<br>
       <textarea name="question" rows="3" cols="50" required></textarea></p> <!-- Textarea for question -->
    <p>Answer: <input name="answer" type="text" required></p> <!-- Input for answer -->
    <p>Hint: <input name="hint" type="text"></p> <!-- Optional input for hint -->
    <p>
      <button type="submit">Create</button> <!-- Submit button -->
      <a href="list_questions.php">Cancel</a> <!-- Link to cancel and go back -->
    </p>
  </form>
</body>
</html>
