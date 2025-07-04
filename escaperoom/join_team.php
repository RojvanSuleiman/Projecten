<?php
// Ensure user is logged in before accessing this page
require 'auth_session.php';

// Include database connection settings
require 'config.php';

// Get the currently logged-in user's ID
$userId = $_SESSION['user_id'];

// Initialize error/success messages
$error = '';
$success = '';

// Step 1: Fetch the current team ID of the user
$currentTeamId = null;
$stmt = $conn->prepare("SELECT team_id FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($currentTeamId);
$stmt->fetch();
$stmt->close();

// Step 2: Fetch list of teams, excluding the one user is already in
$teams = [];
$result = $conn->query("
    SELECT DISTINCT t.id, t.name 
    FROM teams t
    WHERE t.id != " . intval($currentTeamId) . "
    ORDER BY t.name
");
while ($row = $result->fetch_assoc()) {
    $teams[$row['id']] = $row['name']; // Store available team info
}

// Step 3: Handle form submission when user wants to join a team
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamId = (int)$_POST['team_id']; // Sanitize and cast input to integer

    // Check for invalid or redundant team selection
    if ($teamId === $currentTeamId) {
        $error = "You are already in this team.";
    } elseif (!isset($teams[$teamId])) {
        $error = "Invalid team selection.";
    } else {
        // Update user's team_id to the selected team
        $stmt = $conn->prepare("UPDATE users SET team_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $teamId, $userId);
        if ($stmt->execute()) {
            $success = "You successfully joined the new team.";
            $currentTeamId = $teamId;

            // Refresh the teams list, now excluding the new team
            $teams = [];
            $result = $conn->query("
                SELECT DISTINCT id, name 
                FROM teams 
                WHERE id != " . intval($currentTeamId) . "
                ORDER BY name
            ");
            while ($row = $result->fetch_assoc()) {
                $teams[$row['id']] = $row['name'];
            }
        } else {
            $error = "Failed to join the team.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"> <!-- Character encoding -->
  <title>Join a Team</title> <!-- Browser tab title -->
  <style>
    body {
      background: url('kurdistan.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
      text-align: center;
      padding: 50px;
    }

    .form-box {
      background-color: rgba(0, 0, 0, 0.7); /* Dark transparent box */
      padding: 30px;
      border-radius: 10px;
      display: inline-block;
    }

    select, button, a {
      padding: 10px;
      margin: 10px;
      width: 90%;
      border-radius: 5px;
      border: none;
      display: inline-block;
      text-align: center;
    }

    button {
      background-color: darkgreen;
      color: white;
    }

    a {
      text-decoration: none;
      font-weight: bold;
    }

    .start-link {
      background-color: goldenrod;
      color: black;
    }

    .create-link {
      background-color: teal;
      color: white;
    }

    .error { color: red; }
    .success { color: lightgreen; }
  </style>
</head>
<body>

  <div class="form-box">
    <h2>Join a Team</h2>

    <!-- If there are available teams to join -->
    <?php if (!empty($teams)): ?>
      <form method="post">
        <select name="team_id" required>
          <option value="">-- Select a Team --</option>
          <?php foreach ($teams as $id => $name): ?>
            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
          <?php endforeach; ?>
        </select><br>
        <button type="submit">Join Team</button>
      </form>
    <?php else: ?>
      <!-- If no teams are available except current one -->
      <p style="color:#ccc;">You are already in the only available team.</p>
    <?php endif; ?>

    <!-- Display error or success messages -->
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <!-- Links to create a new team or start the game -->
    <a href="create_team.php" class="create-link">➕ Create Your Own Team</a>
    <a href="room1.php" class="start-link">🎮 Start the Game</a>
  </div>
</body>
</html>
