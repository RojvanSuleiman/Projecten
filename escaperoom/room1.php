<?php
require 'auth_session.php'; // make sure the user is logged in
require 'config.php';       // connect to the database

/*
  questions table columns: id, question, answer, hint, roomId, puzzle_id.
  For roomId=1, puzzle_id must be 1..6 (unique).
*/

// ─────────────────────────────────────────────
// Hotspot → puzzle_id mapping and total count
// ─────────────────────────────────────────────
$hotspots = [
  'rojava'  => 2,  // Rojava book
  'seyid'   => 6,  // Seyid Riza portrait
  'ferheng' => 5,  // Ferheng (white) book
  'hewler'  => 1,  // Erbil/Hewler picture
  'zagros'  => 3,  // Blue book (Zagros)
  'compass' => 4,  // Compass
];
$totalRoom1 = count($hotspots);  // 6 puzzles

// ─────────────────────────────────────────────
// Load room 1 data the first time user visits
// ─────────────────────────────────────────────
if (!isset($_SESSION['room1_initialized'])) {
    // set the start time once when user enters room 1
    if (!isset($_SESSION['game_start_time'])) {
        $_SESSION['game_start_time'] = time(); // save current time
        $_SESSION['current_streak']  = 0;      // streak starts at 0
        $_SESSION['longest_streak']  = 0;      // best streak
        $_SESSION['score']           = 0;      // points for correct answers
    }

    // get all $totalRoom1 questions from database (room 1 only)
    $sql = "
      SELECT id, question, answer, hint, puzzle_id
      FROM questions
      WHERE roomId = 1
      ORDER BY puzzle_id ASC
      LIMIT {$totalRoom1}
    ";
    $result = $conn->query($sql);
    $rows   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $puzzles = [];
    foreach ($rows as $r) {
        $pid = (int)$r['puzzle_id'];
        $puzzles[$pid] = [
          'q_id'     => (int)$r['id'],
          'question' => $r['question'],
          'answer'   => $r['answer'],
          'hint'     => $r['hint']
        ];
    }

    $_SESSION['room1_puzzles']     = $puzzles;
    $_SESSION['room1_done']        = [];
    $_SESSION['room1_initialized'] = true;
}

// ─────────────────────────────────────────────
// Logic for picking a puzzle
// ─────────────────────────────────────────────
$error        = '';
$pickedPuzzle = null;
$currentQ     = null;

if (isset($_GET['pick']) && is_numeric($_GET['pick'])) {
    $pick = (int)$_GET['pick'];
    if (!in_array($pick, $_SESSION['room1_done'], true)) {
        $currentQ     = $_SESSION['room1_puzzles'][$pick] ?? null;
        if ($currentQ) {
            $pickedPuzzle = $pick;
        }
    }
}

// ─────────────────────────────────────────────
// Logic for answering a puzzle
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['puzzle_id'])) {
    $pid     = (int)$_POST['puzzle_id'];
    $given   = trim($_POST['answer'] ?? '');
    $correct = $_SESSION['room1_puzzles'][$pid]['answer'] ?? '';

    if (strcasecmp($given, $correct) === 0) {
        $_SESSION['score']++;
        $_SESSION['current_streak']++;
        if ($_SESSION['current_streak'] > ($_SESSION['longest_streak'] ?? 0)) {
            $_SESSION['longest_streak'] = $_SESSION['current_streak'];
        }
        $_SESSION['room1_done'][] = $pid;

        // all solved? move to room 2
        if (count($_SESSION['room1_done']) >= $totalRoom1) {
            header("Location: room2.php");
            exit;
        } else {
            $pickedPuzzle = null;
            $currentQ     = null;
        }
    } else {
        $_SESSION['current_streak'] = 0;
        $error = "Incorrect—your streak has been reset.";
    }
}

// ─────────────────────────────────────────────
// Prepare for display
// ─────────────────────────────────────────────
$donePuzzles = $_SESSION['room1_done'];
$mapScore    = $_SESSION['score'] ?? 0;
$allAnswered = count($donePuzzles) >= $totalRoom1;

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
     9 => '+ Riha & Semsûr (🎤 Şivan Perwer & Shakiro)',
    10 => '+ Xarpêt & Dêrsim (🎤 Demirtaş & Diyar Dersim)',
    11 => '+ Mûş & Agirî (🎤 Hozan Dîno & Broye Heskê)',
    12 => '+ Mêrdîn & Êlih (🎤 Mem Ararat & Karapetê Xaço)',
    13 => '+ Dîlok & Mereş (🎤 Haki Karer & Şêx Seîdê Kurdî)',
    14 => 'Last challenge before liberation',
    15 => 'The Greater Kurdistan is free!'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Escape Room — Room 1</title>
  <style>
    /* HOTSPOTS  */
    .hotspots a {
      position: absolute;
      display: block;
      background: rgba(0,0,0,0);
    
    }
    #hotspot-rojava  { top: 580px; left: 700px; width:  70px; height: 120px; }
    #hotspot-seyid   { top:  80px; left: 900px; width:  90px; height: 130px; }
    #hotspot-ferheng { top: 410px; left: 520px; width:  80px; height: 110px; }
    #hotspot-hewler  { top: 110px; left: 450px; width: 200px; height: 150px; }
    #hotspot-zagros  { top: 415px; left: 660px; width:  90px; height: 110px; }
    #hotspot-compass { top: 395px; left: 850px; width:  60px; height: 120px; }

    /* RESET & BACKGROUND */
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      background: url('room1.png') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: #fff;
      overflow: hidden;
    }

    /* PARTICLES */
    .particles { position: fixed; top:0; left:0; width:100%; height:100%; z-index:0; overflow:hidden; pointer-events:none; }
    .particle { position:absolute; background:rgba(255,215,0,0.7); width:6px; height:6px; border-radius:50%; animation:floatUp 6s linear infinite; }
    @keyframes floatUp { 0%   { transform:translateY(100vh) scale(0.5); opacity:0; } 10% { opacity:1; } 100% { transform:translateY(-10vh) scale(1); opacity:0; } }

    /* HEADER (MAP + CITY) */
    header {
      width: 180px;
      height: 136px;
      overflow: visible;
    }
    header #mapContainer {
      width: 100%; height: 100%; overflow: hidden;
    }
    header #mapContainer img {
      width: 100%; height: 100%; object-fit: contain; display: block;
    }
    #mapContainer {
      position:relative; width:280px; border:4px solid #FFD700; border-radius:12px; overflow:hidden; animation:pulseMap 2s infinite ease-in-out;
    }
    @keyframes pulseMap { 0%,100% { box-shadow:0 0 10px rgba(255,215,0,0.6); } 50% { box-shadow:0 0 20px rgba(255,215,0,1); } }
    #mapScoreText {
      position:absolute; bottom:5px; left:50%; transform:translateX(-50%); width:100%; text-align:left;
      font-size:0.9rem; font-weight:bold; text-shadow:1px 1px 2px #000; white-space:nowrap; pointer-events:none;
    }

    /* QUESTION MODAL */
    .question-modal { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:10; display:flex; align-items:center; justify-content:center; }
    .question-box { background:rgba(0,0,0,0.9); padding:30px; border-radius:12px; width:80%; max-width:600px; box-shadow:0 0 20px rgba(0,0,0,0.8); position:relative; }
    .question-box h2 { font-size:1.8rem; margin-bottom:10px; color:#FFD700; text-shadow:1px 1px 3px #000; }
    .question-box p { font-size:1.1rem; margin-bottom:8px; }
    .question-box input[type="text"] { width:100%; padding:12px; margin-top:10px; border-radius:5px; border:none; font-size:1rem; outline:none; }
    .question-box button { margin-top:16px; padding:10px 20px; border:none; border-radius:6px; background:#e63946; color:#fff; font-size:1rem; cursor:pointer; transition: background 0.3s; }
    .question-box button:hover { background:#b22222; }
    .question-box .close-btn { position:absolute; top:12px; right:12px; background:transparent; border:none; font-size:1.2rem; color:#fff; cursor:pointer; }
    .question-box .error { color:#ff4444; margin-top:12px; font-weight:bold; }
    .question-box .hint { margin-top:16px; font-size:0.95rem; color:#ccc; opacity:0.9; font-style:italic; }

    /* TIMER BAR */
    .timer-container { position:relative; width:100%; height:24px; background:rgba(0,0,0,0.6); border:2px solid #FFD700; border-radius:12px; margin-bottom:20px; overflow:hidden; }
    .timer-bar { position:absolute; top:0; left:0; height:100%; background:#e63946; width:100%; transform-origin:left; }
    .timer-text { position:relative; z-index:2; font-weight:bold; color:#fff; text-align:center; line-height:24px; font-size:1rem; }

    /* FOOTER (PROVERB + FLAG) */
    footer { position:fixed; bottom:20px; left:50%; transform:translateX(-50%); text-align:center; z-index:5; pointer-events:none; }
    .proverb { font-style:italic; color:#FFD700; animation:fadeInProverb 2s ease-out; }
    @keyframes fadeInProverb { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    .flag { margin-top:10px; width:80px; border:2px solid #fff; border-radius:6px; animation:waveFlag 3s infinite ease-in-out; }
    @keyframes waveFlag { 0%,100% { transform:rotate(0deg); } 50% { transform:rotate(3deg); } }
  </style>
</head>
<body>

  <!-- PARTICLES LAYER -->
  <div class="particles" id="particles"></div>

  <!-- HEADER: Map + City Unlock -->
  <header>
    <div id="mapContainer">
      <img src="maps/map_<?php echo $mapScore; ?>.png" alt="Progress Map">
    </div>
    <div id="mapScoreText"><?php echo $cityUnlock[$mapScore] ?? ''; ?></div>
  </header>

  <!-- HOTSPOTS -->
  <?php if ($pickedPuzzle === null): ?>
    <div class="hotspots">
      <?php foreach ($hotspots as $id => $pid): ?>
        <a id="hotspot-<?php echo $id; ?>" href="room1.php?pick=<?php echo $pid; ?>"></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- QUESTION MODAL -->
  <?php if ($pickedPuzzle !== null && $currentQ !== null): ?>
    <div class="question-modal">
      <div class="question-box">
        <button class="close-btn" onclick="window.location='room1.php';">&times;</button>

        <!-- Timer Bar -->
        <div class="timer-container">
          <div class="timer-bar" id="timerBar"></div>
          <div class="timer-text" id="timerText">01:00</div>
        </div>

        <h2>Puzzle #<?php echo $pickedPuzzle; ?> Question</h2>
        <p><?php echo htmlspecialchars($currentQ['question']); ?></p>

        <form method="POST">
          <input type="hidden" name="puzzle_id" value="<?php echo $pickedPuzzle; ?>">
          <input type="text" name="answer" placeholder="Type your answer…" required autocomplete="off">
          <button type="submit">Submit</button>
        </form>

        <?php if (!empty($error)): ?>
          <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="hint">
          <strong>Hint:</strong> <?php echo htmlspecialchars($currentQ['hint']); ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Music toggle and selector -->
  <div id="musicBox" style="position: fixed; bottom: 20px; right: 20px; z-index: 999;">
    <button onclick="toggleMusicMenu()" style="font-size: 1.6rem; background: none; border: none; color: white; cursor: pointer;">🎵</button>
    <div id="musicMenu" style="display: none; margin-top: 6px; background-color: rgba(0,0,0,0.7); padding: 8px; border-radius: 8px;">
      <p style="color: white; font-weight: bold; margin: 0;">🎶 Vibe with Kurdish music:</p>
      <select id="musicSelect" onchange="playMusic()" style="margin-top: 5px;">
        <option value="">Silent/Nothing</option>
        <option value="Şivan Perwer Kine em.mp3">Şivan Perwer – Kîne Em</option>
        <option value="Aziz Waisi - Gul.mp3">Aziz Waisi – Gul</option>
        <option value="Naser Razzazi - Berzî Berzî.mp3">Naser Razzazi – Berzî Berzî</option>
        <option value="Rojda - Ax Lê Gidyê.mp3">Rojda – Ax Lê Gidyê</option>
      </select>
    </div>
  </div>

  <audio id="musicPlayer" controls style="display: none;">YOUR BROWSER DOES NOT SUPPORT AUDIO ELEMENT</audio>
  <script>
    function toggleMusicMenu() {
      const menu = document.getElementById('musicMenu');
      menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
    }

    const musicPlayer = document.getElementById('musicPlayer');
    const select      = document.getElementById('musicSelect');
    const savedSrc    = sessionStorage.getItem('musicSrc');
    const savedTime   = sessionStorage.getItem('musicTime');

    if (savedSrc) {
      musicPlayer.src         = savedSrc;
      musicPlayer.currentTime = parseFloat(savedTime) || 0;
      musicPlayer.play();
      select.value            = savedSrc;
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

    // Particles + Timer JS unchanged
    const container = document.getElementById('particles');
    for (let i = 0; i < 60; i++) {
      const p = document.createElement('div');
      p.classList.add('particle');
      const size = Math.random() * 6 + 2;
      p.style.width = size + 'px';
      p.style.height = size + 'px';
      p.style.left = (Math.random() * 100) + 'vw';
      p.style.animationDuration = (Math.random() * 6 + 4) + 's';
      p.style.animationDelay    = (Math.random() * 4) + 's';
      container.appendChild(p);
    }

    <?php if ($pickedPuzzle !== null && $currentQ !== null): ?>
    let totalSeconds = 60;
    const timerBar   = document.getElementById('timerBar');
    const timerText  = document.getElementById('timerText');
    const countdown  = setInterval(() => {
      totalSeconds--;
      const m = Math.floor(totalSeconds / 60);
      const s = totalSeconds % 60;
      timerText.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
      timerBar.style.width   = (totalSeconds/60*100) + '%';
      if (totalSeconds <= 0) { clearInterval(countdown); window.location = 'loss.html'; }
    }, 1000);
    <?php endif; ?>
  </script>
  <?php if ($allAnswered): ?>
  <div id="room2Ready" style="
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.85);
    padding: 30px;
    border: 3px solid #FFD700;
    border-radius: 12px;
    text-align: center;
    z-index: 9999;
  ">
    <h2 style="color: #FFD700; margin-bottom: 10px;">✅ All Room 1 Questions Answered!</h2>
    <p style="color: white; margin-bottom: 20px;">Click below to continue to Room 2.</p>
    <a href="room2.php" style="
      display: inline-block;
      padding: 10px 20px;
      background: #e63946;
      color: white;
      text-decoration: none;
      font-weight: bold;
      border-radius: 8px;
      font-size: 1.2rem;
      transition: background 0.3s;
    " onmouseover="this.style.background='#b22222'" onmouseout="this.style.background='#e63946'">
      ➤ Enter Room 2
    </a>
  </div>
<?php endif; ?>

</body>
</html>
