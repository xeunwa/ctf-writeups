<?php
require_once '../server_config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    if (empty($title) || empty($content)) {
        $error = 'Please fill in all fields';
    } else {
        $db = get_db_connection();
        $stmt = $db->prepare("
            INSERT INTO journals (user_id, title, content, is_public)
            VALUES (:user_id, :title, :content, :is_public)
        ");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':content', $content, SQLITE3_TEXT);
        $stmt->bindValue(':is_public', $is_public, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $success = 'Thy tale has been etched in stone!';
        } else {
            $error = 'Failed to record thy tale';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arcane Chronicles - New Spell</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&display=swap" rel="stylesheet" />
  <link href="/static/css/tailwind.min.css" rel="stylesheet" />
  <link href="/static/css/style.css" rel="stylesheet" />

  <script src="https://cdn.jsdelivr.net/npm/@tsparticles/slim@3.3.0/tsparticles.slim.bundle.min.js"></script>
  <script src="/static/js/lucide.min.js"></script>
</head>
<body class="bg-magical">
  <div id="magic-particles"></div>
  <header class="border-b border-purple-500/20 bg-[#1a1a2e]/80 backdrop-blur-lg">
    <div class="container mx-auto px-4 py-6">
      <div class="flex flex-col md:flex-row items-center justify-between">
        <a href="/index.php" class="flex items-center gap-3 text-purple-300 hover:text-purple-200 transition-colors group">
          <i data-lucide="sparkles" class="w-8 h-8 text-purple-400 group-hover:text-purple-300 transition-colors"></i>
          <h1 class="text-3xl font-magical bg-clip-text text-transparent bg-gradient-to-r from-purple-300 to-purple-500">
            Arcane Chronicles
          </h1>
        </a>
        <nav class="mt-4 md:mt-0">
          <ul class="flex gap-6">
            <li>
              <a href="/public/new-journal.php" class="flex items-center gap-2 text-purple-300 hover:text-purple-200 transition-colors font-magical">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
                <span>New Spell</span>
              </a>
            </li>
            <li>
              <a href="/public/my-journals.php" class="flex items-center gap-2 text-purple-300 hover:text-purple-200 transition-colors font-magical">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                <span>Grimoire</span>
              </a>
            </li>
            <li>
              <a href="/public/logout.php" class="flex items-center gap-2 text-purple-300 hover:text-purple-200 transition-colors font-magical">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Dispel Magic</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </header>
  <main class="flex-grow container mx-auto px-4 py-8 relative z-10">
    <div class="max-w-4xl mx-auto">
      <form id="newJournalForm" method="POST" class="bg-parchment border-2 border-amber-700/50 rounded-lg p-8 shadow-lg">
        <h2 class="text-2xl font-medieval text-amber-500 mb-6">Inscribe Thy Tale</h2>
        <?php if($error): ?>
          <div id="error" class="bg-red-900/50 text-red-200 p-4 rounded-lg mb-6 border border-red-700">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
        <?php if($success): ?>
          <div id="success" class="bg-green-900/50 text-green-200 p-4 rounded-lg mb-6 border border-green-700">
            <?php echo htmlspecialchars($success); ?>
          </div>
        <?php endif; ?>
        <div class="mb-6">
          <label for="title" class="block text-amber-500 font-medieval mb-2">Title of Thy Tale:</label>
          <input type="text" id="title" name="title" class="w-full p-3 border border-amber-700/50 rounded-lg bg-black/30 font-medieval text-amber-100" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" />
        </div>
        <div class="mb-6">
          <label for="content" class="block text-amber-500 font-medieval mb-2">Thy Chronicle:</label>
          <textarea id="content" name="content" rows="10" class="w-full p-3 border border-amber-700/50 rounded-lg bg-black/30 font-medieval text-amber-100 resize-y" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
        </div>
        <div class="mb-6">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" id="isPublic" name="is_public" class="w-4 h-4 bg-black/30 border-amber-700" <?php if(isset($_POST['is_public'])) echo 'checked'; ?> />
            <span class="text-amber-500 font-medieval">Share this tale with fellow Tarnished</span>
          </label>
        </div>
        <button type="submit" class="w-full bg-amber-700 hover:bg-amber-600 text-amber-100 py-3 rounded-lg font-medieval transition-colors">
          Inscribe Memory
        </button>
      </form>
    </div>
  </main>
  <?php require_once "../includes/footer.php" ?>
  <script>
    lucide.createIcons();
    tsParticles.load("magic-particles", {
      background: { opacity: 0 },
      fpsLimit: 60,
      particles: {
        color: { value: ["#7b61ff", "#4327e3", "#9d8bff"] },
        links: { enable: true, color: "#7b61ff", opacity: 0.15, distance: 150 },
        move: { enable: true, direction: "none", outModes: { default: "bounce" }, random: false, speed: 0.5, straight: false },
        number: { density: { enable: true, area: 1200 }, value: 30 },
        opacity: { value: 0.3, animation: { enable: true, speed: 0.5, minimumValue: 0.1 } },
        shape: { type: ["circle", "triangle"] },
        size: { value: 4, random: true, animation: { enable: true, speed: 1, minimumValue: 1 } }
      },
      detectRetina: true
    });
  </script>
</body>
</html>
