<?php
require 'auth_session.php'; // Make sure user is logged in
require 'config.php';       // Connect to database

// Only allow admin users to access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php"); // Redirect non-admins to home
    exit();
}

// Check if question ID is provided in the URL
if (!isset($_GET['id'])) {
    header('Location: list_questions.php'); // Redirect if ID is missing
}

// Sanitize and store the question ID
$id = (int)$_GET['id'];

// Fetch the question from the database
$stmt = $conn->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

// If question not found, stop the script
if ($res->num_rows === 0) {
    exit('Not found');
}

// Store question data in $Q to prefill the form later
$Q = $res->fetch_assoc();

$error = ''; // Variable to hold any error messages

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get values from form input and trim whitespace
    $q = trim($_POST['question']);   // The question text
    $a = trim($_POST['answer']);     // The correct answer
    $h = trim($_POST['hint']);       // Hint for the question
    $r = (int)$_POST['roomId'];      // Room ID (1 or 2)
    $p = (int)$_POST['puzzle_id'];   // Puzzle ID inside that room

    // Check if required fields are empty
    if (!$q || !$a || !$r || !$p) {
        $error = 'All fields except hint are required.';
    } else {
        // Update the question in the database
        $u = $conn->prepare("
            UPDATE questions
            SET roomId = ?, puzzle_id = ?, question = ?, answer = ?, hint = ?
            WHERE id = ?
        ");
        $u->bind_param('iisssi', $r, $p, $q, $a, $h, $id);
        $u->execute();

        // After updating, go back to the question list
        header('Location: list_questions.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Question</title>
</head>
<body>
  <h1>Edit Question #<?= $id ?></h1>

  <!-- Show error message if any -->
  <?php if ($error): ?>
    <p style="color:red"><?= $error ?></p>
  <?php endif; ?>

  <!-- Form to edit the question -->
  <form method="post">
    <p>Room ID:
      <input name="roomId" type="number"
             value="<?= $Q['roomId'] ?>" required>
    </p>
    <p>Puzzle ID:
      <input name="puzzle_id" type="number"
             value="<?= $Q['puzzle_id'] ?>" required>
    </p>
    <p>Question:<br>
      <textarea name="question" rows="3" cols="50" required><?= htmlspecialchars($Q['question']) ?></textarea>
    </p>
    <p>Answer:
      <input name="answer" type="text"
             value="<?= htmlspecialchars($Q['answer']) ?>" required>
    </p>
    <p>Hint:
      <input name="hint" type="text"
             value="<?= htmlspecialchars($Q['hint']) ?>">
    </p>
    <p>
      <button type="submit">Save</button>
      <a href="list_questions.php">Cancel</a>
    </p>
  </form>
</body>
</html>
