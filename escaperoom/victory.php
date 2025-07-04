<?php
require 'auth_session.php'; // Make sure the user is logged in
require 'config.php';       // Connect to the database

// Get leaderboard data: username, time in seconds, streak, and team name (if any)
$sql = "
    SELECT u.username, r.time_seconds, r.streak, t.name AS team_name
    FROM results r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN teams t ON u.team_id = t.id
    ORDER BY r.time_seconds ASC, r.streak DESC
";
$res = $conn->query($sql);

// Save each row into the leaderboard array
$leaderboard = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}

// Function to format seconds into MM:SS (e.g. 01:45)
function format_time($seconds) {
    $mins = floor($seconds / 60);
    $secs = $seconds % 60;
    return str_pad($mins, 2, '0', STR_PAD_LEFT) . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"> 
  <title>Victory — Greater Kurdistan</title> 
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body {
      height: 100%;
      overflow: hidden;
      font-family: 'Segoe UI', sans-serif;
      background: url('kurdistan.jpg') no-repeat center center fixed;
      background-size: cover;
      position: relative;
      color: white;
    }
    video#bg-video {
      position: absolute;
      top: 50%;
      left: 2%;
      transform: translateY(-50%);
      width: 23vw;
      height: auto;
      max-height: 80vh;
      object-fit: contain;
      z-index: 1;
    }
    .overlay {
      position: absolute;
      top: 5%;
      left: 60%;
      transform: translateX(-50%);
      width: 80%;
      max-width: 1000px;
      z-index: 2;
      text-align: center;
    }
    .overlay h1 {
      font-size: 3rem;
      margin-bottom: 10px;
      text-shadow: 2px 2px 8px #000;
    }
    .overlay p {
      font-size: 1.2rem;
      margin-bottom: 20px;
    }
    .overlay .buttons a {
      text-decoration: none;
      background: #e63946;
      padding: 12px 24px;
      color: white;
      font-size: 1rem;
      border-radius: 8px;
      display: inline-block;
      margin: 6px;
      transition: background 0.3s, transform 0.3s;
    }
    .overlay .buttons a:hover {
      background: #b22222;
      transform: scale(1.05);
    }
    .leaderboard {
      margin: 30px auto 0;
      background: rgba(0, 0, 0, 0.7);
      border-radius: 10px;
      padding: 20px;
      max-height: 50vh;
      overflow-y: auto;
    }
    .leaderboard h2 {
      color: #FFD700;
      font-size: 2rem;
      margin-bottom: 15px;
      text-shadow: 1px 1px 4px #000;
    }
    .leaderboard table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      color: #fff;
    }
    .leaderboard th, 
    .leaderboard td {
      padding: 10px 8px;
      text-align: left;
      font-size: 1rem;
    }
    .leaderboard thead {
      background: rgba(255, 215, 0, 0.2);
    }
    .leaderboard th {
      font-weight: bold;
      font-size: 1.1rem;
      text-shadow: 1px 1px 3px #000;
    }
    .leaderboard tbody tr {
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    .leaderboard tbody tr:nth-child(odd) {
      background: rgba(255, 255, 255, 0.05);
    }
    .leaderboard tbody tr:first-child {
      background: rgba(255, 215, 0, 0.3);
    }
    .leaderboard tbody tr:first-child .rank,
    .leaderboard tbody tr:first-child .username,
    .leaderboard tbody tr:first-child .streak,
    .leaderboard tbody tr:first-child .time,
    .leaderboard tbody tr:first-child .team {
      font-weight: bold;
    }
    .leaderboard tbody tr:first-child .rank::before {
      content: "🏆 ";
    }
    @media (max-width: 768px) {
      video#bg-video {
        transform: translateY(-50%) scale(0.5);
        height: 140%;
      }
      .overlay h1 { font-size: 2.5rem; }
      .overlay p { font-size: 1rem; }
      .leaderboard h2 { font-size: 1.5rem; }
      .leaderboard th, .leaderboard td {
        font-size: 0.9rem;
        padding: 8px 6px;
      }
    }
  </style>
</head>
<body>
  <!-- Background video -->
  <video id="bg-video" playsinline>
    <source src="represent.mp4" type="video/mp4">
    Your browser does not support the video tag.
  </video>

  <!-- Victory message and actions -->
  <div class="overlay">
    <h1>🏆 Greater Kurdistan Is United</h1>
    <p>You led your people with wisdom and courage.</p>
    <p>We know it's just a game, but you did great! Be proud to be a Kurd from Kurdistan.</p>

    <!-- Buttons to restart, logout, or view team scores -->
    <div class="buttons">
      <a href="home.php">Play Again</a>
      <a href="logout.php">Logout</a>
      <a href="scoreboard.php">Check The Teams Scores</a>
    </div>

    <!-- Show leaderboard table -->
    <div class="leaderboard">
      <h2>🏅 Leaderboard</h2>
      <table>
        <thead>
          <tr>
            <th class="rank">Rank</th>
            <th class="username">Username</th>
            <th class="streak">Longest Streak</th>
            <th class="time">Time (MM:SS)</th>
            <th class="team">Team</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rank = 1;
          foreach ($leaderboard as $entry) {
              $username = htmlspecialchars($entry['username']);
              $streak   = (int)$entry['streak'];
              $time_fmt = format_time((int)$entry['time_seconds']);
              $team     = htmlspecialchars($entry['team_name'] ?? '');

              echo "<tr>
                      <td class=\"rank\">{$rank}</td>
                      <td class=\"username\">{$username}</td>
                      <td class=\"streak\">{$streak}</td>
                      <td class=\"time\">{$time_fmt}</td>
                      <td class=\"team\">{$team}</td>
                    </tr>";
              $rank++;
          }

          // Show message if leaderboard is empty
          if (empty($leaderboard)) {
              echo '<tr><td colspan="5" style="text-align:center; color:#ccc;">No results yet.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Script to unmute and play video on user click -->
  <script>
    const video = document.getElementById('bg-video');
    document.addEventListener('click', () => {
      video.muted = false;
      video.play();
    }, { once: true });
  </script>
</body>
</html>
