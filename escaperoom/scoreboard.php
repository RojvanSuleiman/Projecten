<?php
require 'auth_session.php';
require 'config.php';

$sql = "
    SELECT 
        teams.name AS team_name,
        users.username,
        results.time_seconds,
        results.streak,
        results.created_at
    FROM results
    JOIN users ON results.user_id = users.id
    LEFT JOIN teams ON users.team_id = teams.id
    WHERE teams.name IS NOT NULL
    ORDER BY teams.name ASC, results.time_seconds ASC
";
$result = $conn->query($sql);

// Group results by team and limit to top 3 members
$team_scores = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $team = $row['team_name'];
        if (!isset($team_scores[$team])) {
            $team_scores[$team] = [];
        }
        if (count($team_scores[$team]) < 3) {
            $team_scores[$team][] = $row;
        }
    }
}

// Sort by best member time per team
uasort($team_scores, fn($a, $b) => $a[0]['time_seconds'] <=> $b[0]['time_seconds']);

// Determine backlink
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$backLink = 'home.php';
if (strpos($referer, 'victory.php') !== false) {
    $backLink = 'victory.php';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Escape Room Team Rankings</title>
  <style>
    body {
      background: url('kurdistan.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
      padding-bottom: 40px;
    }
    h1 {
      text-align: center;
      padding: 30px 0 10px;
      font-size: 2rem;
    }
    .back-link {
      text-align: center;
      margin-top: -10px;
    }
    .back-link a {
      color: white;
      font-weight: bold;
      text-decoration: underline;
      font-size: 1rem;
    }
    table {
      border-collapse: collapse;
      width: 90%;
      margin: 30px auto;
      background-color: rgba(0, 0, 0, 0.75);
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: center;
    }
    th {
      background-color: rgba(255,255,255,0.1);
    }
    tr.team-row {
      background-color: rgba(255, 215, 0, 0.3);
      font-weight: bold;
      font-size: 1.1rem;
    }
  </style>
</head>
<body>
  <h1>🏅 Top Teams & Members</h1>
  <div class="back-link">
    <a href="<?= $backLink ?>">← Back to <?= basename($backLink, '.php') ?></a>
  </div>
  <table>
    <thead>
      <tr>
        <th>Top 3 Members</th>
        <th>Time (seconds)</th>
        <th>Longest Streak</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $rank = 1;
      foreach ($team_scores as $team => $members):
      ?>
        <tr class="team-row">
          <td colspan="4">🏆 <?= $rank ?>. <?= htmlspecialchars($team) ?></td>
        </tr>
        <?php 
        for ($i = 0; $i < 3; $i++):
            $m = $members[$i] ?? null;
            $trophy = $i === 0 ? '🥇 ' : ($i === 1 ? '🥈 ' : ($i === 2 ? '🥉 ' : ''));
        ?>
        <tr>
          <td><?= $m ? $trophy . htmlspecialchars($m['username']) : '-' ?></td>
          <td><?= $m ? gmdate("H:i:s", $m['time_seconds']) : '-' ?></td>
          <td><?= $m ? $m['streak'] : '-' ?></td>
          <td><?= $m ? $m['created_at'] : '-' ?></td>
        </tr>
        <?php endfor; $rank++; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
