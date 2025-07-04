<?php
// Show all errors (for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
require 'auth_session.php';

// Connect to the database
require 'config.php';

// Initialize empty variables for error and success messages
$error = '';
$success = '';

// Get user role and ID from session
$role = $_SESSION['role'];
$loggedInUserId = $_SESSION['user_id'];

// ADMIN: Fetch all users and their team status
$users = [];
if ($role === 'admin') {
    $res = $conn->query("
        SELECT u.id, u.username, t.name AS team_name
        FROM users u
        LEFT JOIN teams t ON u.id = t.user_id
    ");
    while ($row = $res->fetch_assoc()) {
        $users[] = $row; // Save each user's data in the array
    }
}

// PLAYER: Fetch current user's team ID
$currentTeam = null;
if ($role === 'player') {
    $stmt = $conn->prepare("SELECT team_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $loggedInUserId);
    $stmt->execute();
    $stmt->bind_result($currentTeamId);
    if ($stmt->fetch()) {
        $currentTeam = $currentTeamId; // Save user's current team ID
    }
    $stmt->close();
}

// When the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); // Get the submitted team name

    if ($role === 'admin') {
        // Admin selects a user to assign the team
        $assignedUserId = ($_POST['assigned_user'] !== '') ? (int)$_POST['assigned_user'] : $loggedInUserId;

        // Check if the selected user already has a team
        $check = $conn->prepare("SELECT id FROM teams WHERE user_id = ?");
        $check->bind_param("i", $assignedUserId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // User already has a team
            $error = "That user already has a team.";
        } else {
            // Create the team for the selected user
            $stmt = $conn->prepare("INSERT INTO teams (user_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $assignedUserId, $name);
            if ($stmt->execute()) {
                $success = "Team created successfully.";
            } else {
                $error = "Failed to create team: " . $stmt->error;
            }
        }
    } elseif ($role === 'player') {
        // PLAYER: Remove old team link (if any)
        $stmt = $conn->prepare("UPDATE users SET team_id = NULL WHERE id = ?");
        $stmt->bind_param("i", $loggedInUserId);
        $stmt->execute();
        $stmt->close();

        // Create a new team with player as owner
        $stmt = $conn->prepare("INSERT INTO teams (user_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $loggedInUserId, $name);
        if ($stmt->execute()) {
            // Get the new team ID
            $newTeamId = $stmt->insert_id;

            // Link the user to the new team
            $update = $conn->prepare("UPDATE users SET team_id = ? WHERE id = ?");
            $update->bind_param("ii", $newTeamId, $loggedInUserId);
            $update->execute();
            $update->close();

            $success = "Team created successfully.";
        } else {
            $error = "Failed to create team: " . $stmt->error;
        }
    } else {
        // Invalid role
        $error = "You are not allowed to create a team.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"> <!-- Set character encoding -->
  <title>Create Team</title> <!-- Page title -->
  <style>
    body {
      background: url('kurdistan.jpg') no-repeat center center fixed; /* Background image */
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
      text-align: center;
      padding: 50px;
    }
    .form-box {
      background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent box */
      padding: 30px;
      border-radius: 10px;
      display: inline-block;
    }
    input, select, button, a {
      padding: 10px;
      margin: 10px;
      width: 90%;
      border-radius: 5px;
      border: none;
      display: inline-block;
      text-align: center;
    }
    button {
      background-color: darkblue; /* Button color */
      color: white;
    }
    a.start-link {
      background-color: goldenrod; /* Start button color */
      color: black;
      text-decoration: none;
      font-weight: bold;
    }
    .error { color: red; }           /* Error message color */
    .success { color: lightgreen; }  /* Success message color */
    .notice { color: orange; }       /* Optional info message color */
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Create Team</h2> <!-- Form title -->

    <!-- Form for creating team -->
    <form method="post">
      <input type="text" name="name" placeholder="Team Name" required><br> <!-- Team name input -->

      <!-- Admin dropdown to assign team to user -->
      <?php if ($role === 'admin'): ?>
        <select name="assigned_user" required>
          <option value="">-- Choose a user --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $u['team_name'] ? 'disabled' : '' ?>>
              <?= htmlspecialchars($u['username']) ?>
              <?= $u['team_name'] ? '(Already in team: ' . htmlspecialchars($u['team_name']) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select><br>
      <?php endif; ?>

      <button type="submit">Create</button> <!-- Submit button -->
    </form>

    <!-- Show error message if exists -->
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

    <!-- Show success message if exists -->
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <!-- Link to start the game -->
    <a href="room1.php" class="start-link">🎮 Start the Game</a>
  </div>
</body>
</html>
