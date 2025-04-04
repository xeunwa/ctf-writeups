<?php
require_once '../server_config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$db = get_db_connection();
$stmt = $db->prepare("SELECT * FROM journals WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
$result = $stmt->execute();

$journals = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $journals[] = $row;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_journal'])) {
    $journal_id = $_POST['delete_journal'];
    $stmt = $db->prepare("DELETE FROM journals WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $journal_id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        header("Location: /public/my-journals.php");
        exit;
    }
}

// Handle visibility toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_visibility'])) {
    $journal_id = $_POST['toggle_visibility'];
    $stmt = $db->prepare("UPDATE journals SET is_public = NOT is_public WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $journal_id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    if ($stmt->execute()) {
        header("Location: /public/my-journals.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arcane Chronicles - Grimoire</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&display=swap" rel="stylesheet" />
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
      <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-medieval text-amber-500">Thy Chronicles</h2>
        <a href="/public/new-journal.php" class="flex items-center gap-2 bg-amber-700 hover:bg-amber-600 text-amber-100 px-4 py-2 rounded-lg transition-colors font-medieval">
          <i data-lucide="plus-circle" class="w-5 h-5"></i>
          Inscribe Memory
        </a>
      </div>
      <div class="space-y-8" id="journals">
        <?php foreach ($journals as $journal): ?>
          <article class="bg-parchment border-2 border-amber-700/50 rounded-lg p-8 shadow-lg relative animate-fadeIn">
            <div class="flex justify-between items-start mb-4">
              <h3 class="text-2xl font-medieval text-amber-500"><?php echo htmlspecialchars($journal['title']); ?></h3>
              <div class="flex gap-2">
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="toggle_visibility" value="<?php echo $journal['id']; ?>">
                  <button type="submit" class="px-4 py-2 rounded-lg font-medieval text-amber-100 <?php echo $journal['is_public'] ? 'bg-blue-900' : 'bg-slate-800'; ?> hover:opacity-90 transition-opacity">
                    <?php echo $journal['is_public'] ? 'Make Hidden' : 'Share Tale'; ?>
                  </button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Art thou certain this memory should fade?');">
                  <input type="hidden" name="delete_journal" value="<?php echo $journal['id']; ?>">
                  <button type="submit" class="px-4 py-2 rounded-lg font-medieval text-amber-100 bg-red-900 hover:opacity-90 transition-opacity">
                    Forget Tale
                  </button>
                </form>
              </div>
            </div>
            <div class="absolute top-4 right-4">
              <span class="px-3 py-1 rounded-full text-sm font-medieval text-amber-100 <?php echo $journal['is_public'] ? 'bg-blue-900' : 'bg-slate-800'; ?>">
                <?php echo $journal['is_public'] ? 'Shared Tale' : 'Hidden Tale'; ?>
              </span>
            </div>
            <div class="prose font-medieval mb-4 text-amber-100">
              <?php echo nl2br(htmlspecialchars($journal['content'])); ?>
            </div>
            <time class="text-amber-500/60 text-sm">
              <?php echo date('F j, Y', strtotime($journal['created_at'])); ?>
            </time>
          </article>
        <?php endforeach; ?>
      </div>
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
