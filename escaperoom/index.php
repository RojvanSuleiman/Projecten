<?php
session_start(); // Start PHP session so we can use $_SESSION

require 'config.php'; // Include database connection settings

// If the user is already logged in, redirect to the homepage
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit; // Stop script execution
}

$error = ''; // Initialize variable for possible error messages

// When the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get submitted username and password
    $username = trim($_POST['username']); // Remove spaces before/after
    $password = $_POST['password']; // Get raw password input

    // Prepare a SQL query to check for the user in the database
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username); // Bind the username to the query
    $stmt->execute(); // Execute the query
    $stmt->store_result(); // Store result so we can check if any row exists

    // If user exists
    if ($stmt->num_rows > 0) {
        // Get the user data from the database
        $stmt->bind_result($id, $username, $hash, $role);
        $stmt->fetch(); // Fetch the result into the bound variables

        // Verify that the password matches the hashed version
        if (password_verify($password, $hash)) {
            // Password is correct, log the user in
            session_regenerate_id(true); // Regenerate session ID for security
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Redirect to the home page
            header("Location: home.php");
            exit;
        } else {
            // Password is incorrect
            $error = "Wrong password.";
        }
    } else {
        // No such username found
        $error = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login — Escape Room</title>
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
      background-color: rgba(0, 0, 0, 0.7); /* Dark background for contrast */
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
      background-color: darkblue;
      color: white;
      border: none;
      border-radius: 5px;
    }

    .error {
      color: red; /* Display error messages in red */
    }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Login to Escape Room</h2>

    <!-- Login form -->
    <form method="post">
      <input type="text" name="username" required placeholder="Username"><br>
      <input type="password" name="password" required placeholder="Password"><br>
      <button type="submit">Login</button>
    </form>

    <!-- Show error message if any -->
    <?php if ($error): ?>
      <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <!-- Link to registration -->
    <p>No account yet? <a href="register.php" style="color:lightblue;">Register here</a></p>
  </div>
</body>
</html>
