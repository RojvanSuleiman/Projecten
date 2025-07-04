<?php
require 'auth_session.php';
require 'config.php';

$error         = '';
$pickedPuzzle2 = null;
$currentQ2     = null;

// Room 1 must be completed first
if (!isset($_SESSION['room1_done']) || count($_SESSION['room1_done']) < 6) {
    header("Location: room1.php");
    exit;
}

if (!isset($_SESSION['room2_initialized'])) {
    $_SESSION['room2_done'] = [];
    $_SESSION['room2_initialized'] = true;
}

if (isset($_GET['pick2']) && is_numeric($_GET['pick2'])) {
    $pick2 = (int)$_GET['pick2'];
    if (!in_array($pick2, $_SESSION['room2_done'], true)) {
        $stmt = $conn->prepare("SELECT id, question, answer, hint FROM questions WHERE roomId = 2 AND puzzle_id = ? LIMIT 1");
        $stmt->bind_param("i", $pick2);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $pickedPuzzle2 = $pick2;
            $currentQ2     = [
                'q_id'     => (int)$row['id'],
                'question' => $row['question'],
                'answer'   => $row['answer'],
                'hint'     => $row['hint']
            ];
        }
        $stmt->close();
    }
}

// Fix: Retrieve question again from DB to check the correct answer on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['puzzle_id'])) {
    $pid2  = (int)$_POST['puzzle_id'];
    $given = trim($_POST['answer'] ?? '');

    $stmt = $conn->prepare("SELECT answer FROM questions WHERE roomId = 2 AND puzzle_id = ? LIMIT 1");
    $stmt->bind_param("i", $pid2);
    $stmt->execute();
    $res = $stmt->get_result();
    $correct = '';
    if ($res && $res->num_rows === 1) {
        $correct = trim($res->fetch_assoc()['answer']);
    }
    $stmt->close();

    if (strcasecmp($given, $correct) === 0) {
        $_SESSION['current_streak']++;
        if ($_SESSION['current_streak'] > ($_SESSION['longest_streak'] ?? 0)) {
            $_SESSION['longest_streak'] = $_SESSION['current_streak'];
        }
        $_SESSION['room2_done'][] = $pid2;
        $_SESSION['score']++;

        if (count($_SESSION['room2_done']) >= 5) { // Changed from 6 to 5
            $start   = $_SESSION['game_start_time'];
            $finish  = time();
            $elapsed = $finish - $start;
            $longest = $_SESSION['longest_streak'];
            $userId  = $_SESSION['user_id'];

            $stmt = $conn->prepare("INSERT INTO results (user_id, time_seconds, streak) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE time_seconds = VALUES(time_seconds), streak = VALUES(streak), created_at = CURRENT_TIMESTAMP");
            $stmt->bind_param("iii", $userId, $elapsed, $longest);
            $stmt->execute();
            $stmt->close();

            header("Location: victory.php");
            exit;
        } else {
            $pickedPuzzle2 = null;
            $currentQ2     = null;
        }
    } else {
        $_SESSION['current_streak'] = 0;
        $error = "Incorrect—your streak has been reset.";
    }
}


$done2    = $_SESSION['room2_done'];
$mapScore = $_SESSION['score'] ?? 7; // should be 7 when entering Room 2

// ─────────────────────────────────────────────
// Hotspot → puzzle_id for Room 2 rally scene
// ─────────────────────────────────────────────
$hotspots2 = [
  'torch'    => 1,  // Newroz → fire torch
  'lang'     => 2,  // Kurdish language monogram
  'unity'    => 3,  // handshake emblem
  'portrait' => 4,  // Qazi Muhammad portrait
  'ypj'      => 6,  // YPJ flag on mic stand
];


$cityUnlock = [
     0 => 'Start: South Kurdistan (Hewlêr, Slêmanî, Duhok, Helebçe)',
     1 => '+ Kerkûk (🎤 Chopy Fatah & 🎵 Adnan Karim)',
     2 => '+ Xaneqîn (🕊️ Leyla Qasim)',
     3 => '+ Qamişlo & Kobanê (🎤 Ciwan Haco & 🛡️ Mazloum Abdi)',
     4 => '+ Efrîn & Heseke (🎤 Ebdo Mihemed & 🎶 Seid Gabari)',
     5 => '+ Amed & Wan (🕊️ Sheikh Said & 🎤 Hozan Canê)',
     6 => '+ Sêrt & Şirnex (🎤 Rojda & 🎤 Nûdem Durak)',
     7 => '+ Sine & Kirmaşan (🎤 Merziye Feriqi & 🏛️ Karim Sanjabi)',
     8 => '+ Ûrmiyê & Mehabad (🗳️ Ghassemlou & 🏛️ Qazi Muhammad)',
     9 => '+ Riha & Semsûr (Şivan Perwer & Shakiro)',
    10 => '+ Xarpêt & Dêrsim (Demirtaş & Diyar Dersim)',
    11 => '+ Mûş & Agirî (Hozan Dîno & Broye Heskê)',
    12 => '+ Mêrdîn & Êlih (Mem Ararat & Karapetê Xaço)',
    13 => '+ Dîlok & Mereş (Haki Karer & Şêx Seîdê Kurdî)',
    14 => 'Last challenge before liberation',
    15 => 'The Greater Kurdistan is free!'
];

// 8 emojis for puzzles 1..8
$emojiList2 = [
  1 => '🏔️',
  2 => '🌋',
  3 => '🕍',
  4 => '🛤️',
  5 => '⚔️',
  6 => '🛡️',
  7 => '🔥',
  8 => '🗡️'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Escape Room — Room 2</title>
  <style>
    /* RESET & BACKGROUND */
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      background: url('room2.png') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: #fff;
      overflow: hidden;
    }

    /* PARTICLES */
    .particles {
      position: fixed; top:0; left:0;
      width:100%; height:100%;
      z-index:0; overflow:hidden; pointer-events:none;
    }
    .particle {
      position:absolute;
      background:rgba(255,69,0,0.7);
      width:6px; height:6px; border-radius:50%;
      animation:rise2 6s linear infinite;
    }
    @keyframes rise2 {
      0%   { transform:translateY(100vh) scale(0.5); opacity:0; }
      10%  { opacity:1; }
      100% { transform:translateY(-10vh) scale(1); opacity:0; }
    }

   /* 1) Constrain the header to exactly 80×80px */
header {
  width: 180px;
  height: 115px;
  overflow: visible;   /* allow the child to scale down instead of cropping */
}

/* 2) Make the mapContainer fill the header */
header #mapContainer {
  width: 100%;
  height: 100%;
  overflow: hidden;    /* optional, but safe if any stray edges appear */
}

/* 3) Shrink the <img> to always show the full map */
header #mapContainer img {
  width: 100%;
  height: 100%;
  object-fit: contain; /* scale the image down to fit, preserving aspect */
  display: block;
}
    #mapContainer {
      position:relative; width:280px;
      border:4px solid #4caf50; border-radius:12px; overflow:hidden;
      animation:pulseMap2 2s infinite ease-in-out;
    }
    @keyframes pulseMap2 {
      0%,100% { box-shadow:0 0 10px rgba(76,175,80,0.6); }
      50%     { box-shadow:0 0 20px rgba(76,175,80,1); }
    }
     #mapScoreText {
  position: absolute;      /* absolutely position inside header */
  bottom: 5px;             /* sit just above the bottom edge */
  left: 50%;               /* center horizontally */
  transform: translateX(-50%); /* truly center in the container */
  width: 100%;             /* span full width of header */
  text-align: left;      /* center the text itself */
  font-size: 0.9rem;       /* adjust as needed for readability */
  font-weight: bold;
  text-shadow: 1px 1px 2px #000;
  white-space: nowrap;     /* keep it on one line */
  pointer-events: none;    /* non‐interactive */
}

    @keyframes fadeInText2 {
      from { opacity:0; transform:translateX(20px); }
      to   { opacity:1; transform:translateX(0); }
    }

    /* EMOJI GRID */
    .emoji-grid {
      position:absolute; top:50%; left:50%;
      transform:translate(-50%,-50%); z-index:2;
      display:grid; grid-template-columns:repeat(4,1fr);
      gap:20px; width:70%; max-width:900px;
    }
    .emoji-item {
      background:rgba(0,0,0,0.6);
      border:3px solid #fff; border-radius:8px;
      display:flex; align-items:center; justify-content:center;
      font-size:2.5rem; cursor:pointer;
      transition: transform 0.2s, filter 0.2s;
      height:80px; position:relative; user-select:none;
      text-decoration:none; color:inherit;
    }
    .emoji-item:hover {
      transform:scale(1.1);
      filter:brightness(1.2);
    }
    .emoji-item.done {
      filter:grayscale(100%) opacity(0.5);
      cursor:default;
    }
    .emoji-item .badge {
      position:absolute; top:6px; right:6px;
      background:rgba(255,215,0,0.9); color:#000; font-weight:bold;
      padding:2px 6px; border-radius:50%;
      display:none;
    }
    .emoji-item.done .badge {
      display:block;
    }
    .hotspots a {
  position: absolute;
  display: block;
  background: rgba(0,0,0,0);
  cursor: pointer;
}
/* Adjust these so each box sits over its symbol */
#hotspot-torch    { top: 400px; left: 340px; width:  60px; height: 120px; }
#hotspot-lang     { top: 500px; left: 900px; width: 120px; height:  60px; }
#hotspot-unity    { top: 500px; left: 660px; width: 100px; height:  80px; }
#hotspot-portrait { top: 500px; left: 1160px; width:  80px; height: 100px; }
#hotspot-ypj      { top: 460px; left: 1380px; width:  80px; height: 120px; }


    /* QUESTION MODAL */
    .question-modal {
      position:fixed; top:0; left:0;
      width:100%; height:100%;
      background:rgba(0,0,0,0.8); z-index:10;
      display:flex; align-items:center; justify-content:center;
    }
    .question-box {
      background:rgba(0,0,0,0.9);
      padding:30px; border-radius:12px; width:80%; max-width:600px;
      box-shadow:0 0 20px rgba(0,0,0,0.8); position:relative;
    }
    .question-box h2 {
      font-size:1.8rem; margin-bottom:10px; color:#4caf50;
      text-shadow:1px 1px 3px #000;
    }
    .question-box p {
      font-size:1.1rem; margin-bottom:8px;
    }
    .question-box input[type="text"] {
      width:100%; padding:12px; margin-top:10px;
      border-radius:5px; border:none; font-size:1rem; outline:none;
    }
    .question-box button {
      margin-top:16px; padding:10px 20px;
      border:none; border-radius:6px;
      background:#4caf50; color:#fff; font-size:1rem;
      cursor:pointer; transition: background 0.3s;
    }
    .question-box button:hover {
      background:#357a38;
    }
    .question-box .close-btn {
      position:absolute; top:12px; right:12px;
      background:transparent; border:none; font-size:1.2rem; color:#fff;
      cursor:pointer;
    }
    .question-box .error {
      color:#ff4444; margin-top:12px; font-weight:bold;
    }
    .question-box .hint {
      margin-top:16px; font-size:0.95rem; color:#ccc; opacity:0.9;
      font-style:italic;
    }

    /* TIMER BAR */
    .timer-container {
      position:relative;
      width:100%; height:24px;
      background:rgba(0,0,0,0.6);
      border:2px solid #4caf50;
      border-radius:12px;
      margin-bottom:20px;
      overflow:hidden;
    }
    .timer-bar {
      position:absolute; top:0; left:0;
      height:100%; background:#e63946;
      width:100%; transform-origin:left;
    }
    .timer-text {
      position:relative; z-index:2;
      font-weight:bold; color:#fff;
      text-align:center; line-height:24px; font-size:1rem;
    }

    /* FOOTER (FACT + FLAG) */
    footer {
      position:fixed; bottom:20px; left:50%;
      transform:translateX(-50%); text-align:center; z-index:5;
      pointer-events:none;
    }
    .fact {
      font-style:italic; color:#4caf50; animation:fadeInFact 2s ease-out;
    }
    @keyframes fadeInFact {
      from { opacity:0; transform:translateY(10px); }
      to   { opacity:1; transform:translateY(0);   }
    }
    .flag {
      margin-top:10px; width:80px; border:2px solid #fff;
      border-radius:6px; animation:waveFlag2 3s infinite ease-in-out;
    }
    @keyframes waveFlag2 {
      0%,100% { transform:rotate(0deg); }
      50%     { transform:rotate(3deg); }
    }
  </style>
</head>
<body>

  <!-- PARTICLES SECTION -->
  <div class="particles" id="particles"></div>

  <!-- HEADER: MAP + CITY -->
  <header>
    <div id="mapContainer">
      <img src="maps/map_<?php echo $mapScore; ?>.png"
           alt="Progress Map" style="width:100%; display:block;">
    </div>
    <div id="mapScoreText"><?php echo $cityUnlock[$mapScore] ?? ''; ?></div>
  </header>

  <!-- EMOJI GRID -->
  <?php if ($pickedPuzzle2 === null): ?>
  <div class="hotspots">
    <?php foreach ($hotspots2 as $id => $pid): ?>
      <a
        id="hotspot-<?php echo $id; ?>"
        href="room2.php?pick2=<?php echo $pid; ?>"
      ></a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>


  <!-- QUESTION MODAL -->
  <?php if ($pickedPuzzle2 !== null && $currentQ2 !== null): ?>
    <div class="question-modal">
      <div class="question-box">
        <button class="close-btn" onclick="window.location='room2.php';">&times;</button>

        <!-- Timer Bar -->
        <div class="timer-container">
          <div class="timer-bar" id="timerBar2"></div>
          <div class="timer-text" id="timerText2">01:00</div>
        </div>

        <h2>Puzzle #<?php echo $pickedPuzzle2; ?> Question</h2>
        <p><?php echo htmlspecialchars($currentQ2['question']); ?></p>

        <form method="POST">
          <input type="hidden" name="puzzle_id" value="<?php echo $pickedPuzzle2; ?>">
          <input type="text" name="answer" placeholder="Type your answer…" required autocomplete="off">
          <button type="submit">Submit</button>
        </form>

        <?php if (!empty($error)): ?>
          <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="hint">
          <strong>Hint:</strong> <?php echo htmlspecialchars($currentQ2['hint']); ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- JAVASCRIPT: Particles + Timer -->
  <script>
    // Particles
    const container2 = document.getElementById('particles');
    for (let i = 0; i < 60; i++) {
      const p = document.createElement('div');
      p.classList.add('particle');
      const size = Math.random() * 6 + 2;
      p.style.width  = size + 'px';
      p.style.height = size + 'px';
      p.style.left = (Math.random() * 100) + 'vw';
      p.style.animationDuration = (Math.random() * 6 + 4) + 's';
      p.style.animationDelay = (Math.random() * 4) + 's';
      container2.appendChild(p);
    }

    // Timer logic for Room 2
    <?php if ($pickedPuzzle2 !== null && $currentQ2 !== null): ?>
      let totalSeconds2 = 60;
      const timerBar2  = document.getElementById('timerBar2');
      const timerText2 = document.getElementById('timerText2');

      const countdown2 = setInterval(() => {
        totalSeconds2--;
        const minutes2 = Math.floor(totalSeconds2 / 60);
        const seconds2 = totalSeconds2 % 60;
        timerText2.textContent =
          String(minutes2).padStart(2, '0') + ':' + String(seconds2).padStart(2, '0');

        const pct2 = (totalSeconds2 / 60) * 100;
        timerBar2.style.width = pct2 + '%';

        if (totalSeconds2 <= 0) {
          clearInterval(countdown2);
          window.location = 'loss.html';
        }
      }, 1000);
    <?php endif; ?>
  </script>
<!-- Music toggle and selector -->
<div id="musicBox" style="position: fixed; bottom: 20px; right: 20px; z-index: 999;">
  <button onclick="toggleMusicMenu()" style="font-size: 1.6rem; background: none; border: none; color: white; cursor: pointer;">🎵</button>
  <div id="musicMenu" style="display: none; margin-top: 6px; background-color: rgba(0,0,0,0.7); padding: 8px; border-radius: 8px;">
    <p style="color: white; font-weight: bold; margin: 0;">🎶 Vibe with Kurdish music:</p>
    <select id="musicSelect" onchange="playMusic()" style="margin-top: 5px;">
      <option value="">Silent/Nothing</option>
      <option value="music/Şivan Perwer Kine em.mp3">Şivan Perwer – Kîne Em</option>
      <option value="music/Aziz Waisi - Gul.mp3">Aziz Waisi – Gul</option>
      <option value="music/Naser Razzazi - Berzî Berzî.mp3">Naser Razzazi – Berzî Berzî</option>
      <option value="music/Rojda - Ax Lê Gidyê.mp3">Rojda – Ax Lê Gidyê</option>
    </select>
  </div>
</div>

<audio id="musicPlayer" controls style="display: none;"></audio>
<script>
  function toggleMusicMenu() {
    const menu = document.getElementById('musicMenu');
    menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
  }

  const musicPlayer = document.getElementById('musicPlayer');
  const select = document.getElementById('musicSelect');
  const savedSrc = sessionStorage.getItem('musicSrc');
  const savedTime = sessionStorage.getItem('musicTime');

  if (savedSrc) {
    musicPlayer.src = savedSrc;
    musicPlayer.currentTime = parseFloat(savedTime) || 0;
    musicPlayer.play();
    select.value = savedSrc;
  }

  function playMusic() {
    const src = select.value;
    if (src) {
      musicPlayer.src = src;
      musicPlayer.play();
      sessionStorage.setItem('musicSrc', src);
    } else {
      musicPlayer.pause();
      musicPlayer.currentTime = 0;
      sessionStorage.removeItem('musicSrc');
      sessionStorage.removeItem('musicTime');
    }
  }

  setInterval(() => {
    if (!musicPlayer.paused && musicPlayer.src) {
      sessionStorage.setItem('musicTime', musicPlayer.currentTime);
    }
  }, 1000);
</script>


</body>
</html>
