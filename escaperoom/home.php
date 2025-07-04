<?php
// Include session check to make sure the user is logged in
require 'auth_session.php'; // This prevents access to guests or logged-out users
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" /> <!-- Character encoding -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/> <!-- Responsive scaling -->
  <title>Rise of Kurdistan</title> <!-- Title shown in browser tab -->
  <link rel="stylesheet" href="style.css" /> <!-- External CSS file -->

  <!-- Inline styles -->
  <style>
    .team-buttons {
      position: absolute;
      left: 30px;
      top: 35%;
      display: flex;
      flex-direction: column;
      gap: 15px;
      z-index: 10;
    }

    .team-buttons a {
      text-decoration: none;
      padding: 10px 18px;
      font-size: 1rem;
      border-radius: 6px;
      font-weight: bold;
      text-align: center;
    }

    .create-btn {
      background-color: yellow;
      color: black;
    }

    .join-btn {
      background-color: limegreen;
      color: white;
    }

    .admin-button {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 1000;
      background-color: red;
      color: white;
      padding: 10px 16px;
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
    }
  </style>
</head>
<body>

  <!-- Section with Join Team and Scoreboard buttons -->
  <div class="team-buttons">
    <a href="join_team.php" class="join-btn">🔗 Change or Join a Team</a>
    <a href="scoreboard.php" class="join-btn">Check Out the Top teams!</a>
  </div>

  <!-- Admin-only button that links to admin tools -->
  <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="admin_page.php" class="admin-button">🔒 Admin Tools</a>
  <?php endif; ?>

  <!-- Main hero section with background and story intro -->
  <div class="hero">
    <div class="overlay">
      <div class="content">
        <h1>🗳️ You Have Been Elected President of Kurdistan</h1>
        <p>
          After centuries of struggle, the people have chosen you to lead them toward peace, unity, and freedom.
          As the newly elected President of Kurdistan, your vision will guide the nation through challenges that will test your wisdom and strength.
        </p>
        <p>
          You will face a series of questions. If you answer correctly, you will be hailed as the hero of a united, peaceful Greater Kurdistan — free from war and oppression.
        </p>
        <p><strong>Click on Start to begin the Journey</strong></p>

        <!-- Start button -->
        <div class="difficulty-buttons">
          <a href="room1.php" class="difficulty-button">Start</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Background video that starts playing when user clicks -->
  <video id="introVideo" playsinline class="corner-video">
    <source src="kurd.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <script>
    // Unmute and play the video once when user clicks anywhere
    const video = document.getElementById('introVideo');
    document.addEventListener('click', () => {
      video.muted = false;
      video.play();
    }, { once: true });

    // Hide video after it finishes
    video.addEventListener('ended', () => {
      video.style.display = 'none';
    });
  </script>

  <!-- Logout link in bottom left corner -->
  <a href="logout.php" style="
    position: fixed;
    bottom: 10px;
    left: 10px;
    background: rgba(255,255,255,0.1);
    color: white;
    padding: 6px 10px;
    font-size: 0.75rem;
    border-radius: 5px;
    text-decoration: none;
    z-index: 1000;
  ">Logout</a>

</body>
</html>
