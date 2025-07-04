<?php
session_start(); // Start the session for storing user data if needed
require 'config.php'; // Include database connection

$message = ''; // Message to show registration errors

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize submitted values
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password']; // No trim to preserve spacing if user wants it

    // Validation: all fields must be filled
    if (empty($firstName) || empty($lastName) || empty($username) || empty($password)) {
        $message = "All fields are required.";
    }
    // Password must be at least 6 characters
    elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        // Hash the password before saving
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Prepare insert query
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $username, $passwordHash);

        // Try to execute
        if ($stmt->execute()) {
            header("Location: index.php"); // Redirect to login after success
            exit;
        } else {
            $message = "This username is already taken."; // If duplicate username
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register — Escape Room</title>
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
      background-color: rgba(0, 0, 0, 0.7);
      padding: 30px;
      border-radius: 10px;
      display: inline-block;
    }
    input {
      padding: 10px;
      margin: 10px;
      width: 90%;
      border-radius: 5px;
      border: none;
    }
    button {
      padding: 10px 20px;
      background-color: darkred;
      color: white;
      border: none;
      border-radius: 5px;
    }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Create an Account</h2>

    <!-- Registration form -->
    <form method="post">
      <input type="text" name="first_name" required placeholder="First Name"><br>
      <input type="text" name="last_name" required placeholder="Last Name"><br>
      <input type="text" name="username" required placeholder="Username"><br>
      <input type="password" name="password" required placeholder="Password (min 6 characters)"><br>
      <button type="submit">Register</button>
    </form>

    <!-- Display message if there's an error -->
    <p><?= htmlspecialchars($message) ?></p>

    <!-- Link to login -->
    <p>Already have an account? <a href="index.php" style="color:lightblue;">Log in here</a></p>
  </div>
</body>
</html>
