<?php
require 'auth_session.php'; // Ensure the user is logged in
require 'config.php';       // Connect to the database

// Only allow admin access
if ($_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Fetch all results with associated usernames, newest first
$stmt = $conn->prepare("
    SELECT r.id, r.time_seconds, r.streak, r.created_at, u.username
    FROM results r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Results</title>
  <style>
    body {
      background: url('kurdistan.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
      text-align: center;
      padding: 40px;
    }
    table {
      margin: 0 auto;
      border-collapse: collapse;
      background-color: rgba(0, 0, 0, 0.7);
      color: white;
      width: 90%;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
    }
    a {
      color: lightblue;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    h1 {
      margin-bottom: 20px;
    }
    .new-result {
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

  <h1>All Results</h1>

  <!-- Link to add a new result manually (if needed) -->
  <p class="new-result"><a href="new_result.php">+ New Result</a></p>

  <!-- Results table -->
  <table>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Time (s)</th>
      <th>Streak</th>
      <th>Created At</th>
      <th>Actions</th>
    </tr>
    <?php foreach($results as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td> <!-- Result ID -->
      <td><?= htmlspecialchars($r['username']) ?></td> <!-- Username -->
      <td><?= $r['time_seconds'] ?></td> <!-- Time taken in seconds -->
      <td><?= $r['streak'] ?></td> <!-- Correct answers in a row -->
      <td><?= $r['created_at'] ?></td> <!-- Timestamp -->
      <td>
        <a href="edit_result.php?id=<?= $r['id'] ?>">Edit</a> |
        <a href="delete_result.php?id=<?= $r['id'] ?>" onclick="return confirm('Delete this result?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>

</body>
</html>
