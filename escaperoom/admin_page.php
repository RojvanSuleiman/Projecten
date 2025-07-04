<?php
// this is for if the user is logged in it autherises 
require 'auth_session.php';

// this is for the file config, so from config.php it connects to the database 
require 'config.php';

// this is to check if the logged in user is a player or an admin
if ($_SESSION['role'] !== 'admin') {
    // If it's not an admin, redirect the user to the home page
    header("Location: home.php");
    exit(); // Stop executing the rest of the script
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> <!-- Set character encoding to UTF-8 -->
  <title>Admin Tools</title> <!-- Title of the page shown in the browser tab -->
  <style>
    /* Style the body with background image and general layout */
    body {
      background: url('kurdistan.jpg') no-repeat center center fixed; /* Set background image */
      background-size: cover; /* Make image cover the full background */
      font-family: Arial, sans-serif; /* Set font */
      color: white; /* Set text color */
      display: flex; /* Use flexbox layout */
      flex-direction: column; /* Arrange items vertically */
      align-items: center; /* Center items horizontally */
      padding-top: 50px; /* Add spacing at the top */
    }

    /* Style the main header */
    h1 {
      font-size: 2rem; /* Font size */
      margin-bottom: 20px; /* Space below the header */
      color: gold; /* Gold color for the title */
    }

    /* Style the unordered list containing the admin links */
    ul.admin-links {
      list-style: none; /* Remove bullet points */
      padding: 0; /* Remove default padding */
      width: 80%; /* Take up 80% of the page width */
      max-width: 600px; /* Max width */
      background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
      border-radius: 10px; /* Rounded corners */
      padding: 20px; /* Inner padding */
    }

    /* Style each list item inside the admin links list */
    ul.admin-links li {
      margin: 10px 0; /* Space above and below each item */
    }

    /* Style the anchor tags (links) inside list items */
    ul.admin-links a {
      color: lightblue; /* Link text color */
      text-decoration: none; /* Remove underline */
      font-size: 1.1rem; /* Font size */
      display: block; /* Make the link take full width */
      background: rgba(255,255,255,0.1); /* Slight white background */
      padding: 10px; /* Inner spacing */
      border-radius: 6px; /* Rounded corners */
      transition: background 0.3s; /* Smooth hover effect */
    }

    /* Change background on hover */
    ul.admin-links a:hover {
      background: rgba(255,255,255,0.3); /* More visible white background */
    }

    /* Style the back button container */
    .back {
      margin-top: 30px; /* Space above the back button */
    }

    /* Style the back button itself */
    .back a {
      color: white; /* Text color */
      background: red; /* Button background */
      padding: 10px 20px; /* Padding inside the button */
      text-decoration: none; /* Remove underline */
      border-radius: 6px; /* Rounded corners */
    }

    /* Change background of back button on hover */
    .back a:hover {
      background: darkred; /* Darker red on hover */
    }
  </style>
</head>
<body>

  <!-- Page title -->
  <h1>🔒 Admin Tools</h1>

  <!-- List of admin-only tool links -->
  <ul class="admin-links">
    <li><a href="create_team.php">Create Team</a></li> <!-- Link to create a team -->
    <li><a href="list_questions.php">List Questions</a></li> <!-- Link to view, edit, and add questions -->
    <li><a href="list_results.php">List Results</a></li> <!-- Link to view game results -->
    <li><a href="view_teams.php">List Teams</a></li> <!-- Link to view all teams -->
    <li><a href="scoreboard.php">Full Team Scoreboard</a></li> <!-- Link to scoreboard -->
  </ul>

  <!-- Back to home link -->
  <div class="back">
    <a href="home.php">⬅️ Back to Home</a> <!-- Back button -->
  </div>

</body>
</html>
