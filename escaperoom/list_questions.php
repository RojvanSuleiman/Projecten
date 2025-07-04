<?php
// Include session check to ensure user is logged in
require 'auth_session.php';

// Display logged-in user's name and role
echo "Logged in as: " . $_SESSION['username'] . " (Role: " . $_SESSION['role'] . ")";

// Include database connection settings
require 'config.php';

// Restrict access to only admins
if ($_SESSION['role'] !== 'admin') {
    // Redirect non-admin users to home page
    header("Location: home.php");
    exit(); // Stop further execution
}

// Fetch all questions from the database, ordered by roomId and puzzle_id
$stmt = $conn->query("SELECT * FROM questions ORDER BY roomId, puzzle_id");
$questions = $stmt->fetch_all(MYSQLI_ASSOC); // Store result as associative array
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"> <!-- Set UTF-8 encoding -->
  <title>All Questions</title> <!-- Browser tab title -->
  <style>
    body {
      background-image: url('kurdistan.jpg'); /* Set background image */
      background-size: cover; /* Cover the entire screen */
      font-family: Arial, sans-serif; /* Font styling */
      color: white; /* Text color */
    }

    h1 {
      text-align: center;
      padding-top: 20px;
    }

    table {
      border-collapse: collapse; /* Remove space between table borders */
      width: 90%;
      margin: 20px auto; /* Center table */
      background-color: rgba(0, 0, 0, 0.6); /* Semi-transparent dark background */
    }

    th, td {
      padding: 8px;
      border: 1px solid #ccc; /* Light border */
      text-align: center;
    }

    a {
      color: #00c3ff; /* Bright blue link color */
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .new-question {
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <h1>Questions</h1>

  <!-- Link to create a new question -->
  <div class="new-question">
    <a href="create_question.php">+ New Question</a>
  </div>

  <!-- Display questions in a table -->
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Room</th>
        <th>Puzzle ID</th>
        <th>Question</th>
        <th>Answer</th>
        <th>Hint</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($questions as $q): ?>
      <tr>
        <td><?= $q['id'] ?></td> <!-- Question ID -->
        <td><?= $q['roomId'] ?></td> <!-- Room number -->
        <td><?= $q['puzzle_id'] ?></td> <!-- Puzzle ID -->
        <td><?= htmlspecialchars($q['question']) ?></td> <!-- Escaped question text -->
        <td><?= htmlspecialchars($q['answer']) ?></td> <!-- Escaped answer text -->
        <td><?= htmlspecialchars($q['hint']) ?></td> <!-- Escaped hint text -->
        <td>
          <!-- Edit and Delete actions -->
          <a href="update_question.php?id=<?= $q['id'] ?>">Edit</a> |
          <a href="delete_question.php?id=<?= $q['id'] ?>" onclick="return confirm('Delete this question?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
