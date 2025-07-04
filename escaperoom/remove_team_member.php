<?php
require 'auth_session.php';
require 'config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$teamId = $_POST['team_id'] ?? null;
$userId = $_POST['user_id'] ?? null;
$removed = false;

if ($teamId && $userId) {
    $stmt = $conn->prepare("UPDATE users SET team_id = NULL WHERE id = ? AND team_id = ?");
    $stmt->bind_param("ii", $userId, $teamId);
    $stmt->execute();
    $removed = $stmt->affected_rows > 0;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Remove Member</title>
  <style>
body {
  background: url('kurdistan.jpg') no-repeat center center fixed;
  background-size: cover;
  font-family: Arial, sans-serif;
  color: white;
  margin: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start; /* Align to top */
  padding-top: 40px; /* space from top */
}

.box {
  background-color: rgba(0, 0, 0, 0.7);
  padding: 30px;
  border-radius: 10px;
  max-width: 400px;
  width: 90%;
}

a {
  color: lightblue;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
  </style>
</head>
<body>
  <div class="box">
    <h2><?= $removed ? 'User removed from team.' : 'No changes made.' ?></h2>
    <p><a href="edit_team.php?id=<?= htmlspecialchars($teamId) ?>">Back to Team</a></p>
  </div>
</body>
</html>
