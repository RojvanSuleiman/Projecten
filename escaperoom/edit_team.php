<?php
// Include session authentication to ensure the user is logged in
require 'auth_session.php';

// Include database configuration for DB connection
require 'config.php';

// Restrict access: only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit(); // Stop the script if not admin
}

// Get the 'id' from the URL or set to null if missing
$id = $_GET['id'] ?? null;

// Initialize error and success message variables
$error = '';
$success = '';

// Initialize array to hold team members
$members = [];

// Check if a valid team ID is provided
if ($id) {

    // If form is submitted (POST method)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']); // Get submitted team name

        // Make sure the team name is not empty
        if ($name !== '') {
            // Update the team name in the database
            $stmt = $conn->prepare("UPDATE teams SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            if ($stmt->execute()) {
                $success = "Team updated."; // Success message
            } else {
                $error = "Update failed."; // Error message if query fails
            }
        } else {
            $error = "Team name cannot be empty."; // Validation error
        }
    }

    // Fetch the current team name for display in the input field
    $stmt = $conn->prepare("SELECT name FROM teams WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($teamName);
    $stmt->fetch(); // Fetch result into $teamName
    $stmt->close();

    // Fetch members of this team
    $stmt = $conn->prepare("SELECT users.id, users.username FROM users WHERE users.team_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $members[] = $row; // Store each member in the $members array
    }
    $stmt->close();

} else {
    // If no valid team ID was provided, redirect back
    header("Location: view_teams.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"> <!-- Character encoding -->
  <title>Edit Team</title> <!-- Page title -->
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
      background-color: rgba(0, 0, 0, 0.7); /* Dark transparent background */
      padding: 30px;
      border-radius: 10px;
      display: inline-block;
    }

    input, button {
      padding: 10px;
      margin: 10px;
      width: 90%;
      border-radius: 5px;
      border: none;
    }

    button {
      background-color: darkblue;
      color: white;
    }

    .error { color: red; }
    .success { color: lightgreen; }

    table {
      margin-top: 20px;
      width: 100%;
      color: white;
      border-collapse: collapse;
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
    }

    ul.admin-links {
      list-style: none;
      padding: 0;
      width: 20%;
      max-width: 600px;
      background-color: rgba(0, 0, 0, 0.7);
      border-radius: 10px;
      padding: 20px;
    }

    ul.admin-links li {
      margin: 10px 0;
    }

    ul.admin-links a {
      color: lightblue;
      text-decoration: none;
      font-size: 1.1rem;
      display: block;
      background: rgba(255,255,255,0.1);
      padding: 10px;
      border-radius: 6px;
      transition: background 0.3s;
    }

    ul.admin-links a:hover {
      background: rgba(255,255,255,0.3);
    }
  </style>
</head>
<body>

  <!-- Navigation links for admin -->
  <ul class="admin-links">
    <li><a href="create_team.php">Go To Create Team</a></li>
    <li><a href="view_teams.php">Back To List Teams</a></li>  
  </ul>

  <!-- Edit form container -->
  <div class="form-box">
    <h2>Edit Team</h2>

    <!-- Form to update team name -->
    <form method="post">
      <input type="text" name="name" value="<?= htmlspecialchars($teamName) ?>" required><br>
      <button type="submit">Update</button>
    </form>

    <!-- Display error or success messages -->
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <!-- Show team members if there are any -->
    <?php if (!empty($members)): ?>
      <h3>Team Members</h3>
      <table>
        <tr><th>Username</th><th>Action</th></tr>
        <?php foreach ($members as $member): ?>
          <tr>
            <td><?= htmlspecialchars($member['username']) ?></td>
            <td>
              <!-- Form to remove a user from the team -->
              <form action="remove_team_member.php" method="post" style="margin:0">
                <input type="hidden" name="user_id" value="<?= $member['id'] ?>">
                <input type="hidden" name="team_id" value="<?= $id ?>">
                <button type="submit">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
