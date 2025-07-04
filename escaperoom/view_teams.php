<?php
require 'auth_session.php'; // Make sure the user is logged in
require 'config.php';       // Connect to the database

// Check the user's role
if ($_SESSION['role'] === 'admin') {
    // Admin sees all teams with their creators
    $stmt = $conn->query("SELECT teams.*, users.username FROM teams JOIN users ON teams.user_id = users.id ORDER BY teams.id");
} else {
    // Normal user only sees their own team
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT teams.*, users.username FROM teams JOIN users ON teams.user_id = users.id WHERE user_id = ?");
    $stmt->bind_param("i", $userId); // bind user id
    $stmt->execute();
    $stmt = $stmt->get_result(); // get results
}

// Fetch all teams into array
$teams = $stmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Teams</title>
  <style>
    body {
      background: url('kurdistan.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
    }
    h1 {
      text-align: center;
    }
    table {
      border-collapse: collapse;
      width: 90%;
      margin: 20px auto;
      background-color: rgba(0, 0, 0, 0.6);
    }
    th, td {
      padding: 8px;
      border: 1px solid #ccc;
      text-align: center;
    }
    a {
      color: #00c3ff;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <h1>Teams</h1>

  <table>
    <thead>
      <tr>
        <th>ID</th> <!-- team id -->
        <th>Team Name</th> <!-- name of the team -->
        <th>Team Creator</th> <!-- username of the person who created the team -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <th>Actions</th> <!-- only for admins: edit/delete -->
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($teams as $team): ?>
        <tr>
          <td><?= $team['id'] ?></td>
          <td><?= htmlspecialchars($team['name']) ?></td>
          <td><?= htmlspecialchars($team['username']) ?></td>
          <?php if ($_SESSION['role'] === 'admin'): ?>
          <td>
            <a href="edit_team.php?id=<?= $team['id'] ?>">Edit</a> |
            <a href="delete_team.php?id=<?= $team['id'] ?>" onclick="return confirm('Delete this team?')">Delete</a>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
