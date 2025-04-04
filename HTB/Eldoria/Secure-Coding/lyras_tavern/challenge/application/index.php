<?php
require_once 'server_config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/sample_data.php';

$db = get_db_connection();
add_sample_entries();

// Fetch public journals
$result = $db->query("
    SELECT j.*, u.username
    FROM journals j
    JOIN users u ON j.user_id = u.id
    WHERE j.is_public = 1
    ORDER BY j.created_at DESC
");

$journals = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $journals[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arcane Chronicles - Home</title>
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
        <a href="index.php" class="flex items-center gap-3 text-purple-300 hover:text-purple-200 transition-colors group">
          <i data-lucide="sparkles" class="w-8 h-8 text-purple-400 group-hover:text-purple-300 transition-colors"></i>
          <h1 class="text-3xl font-magical bg-clip-text text-transparent bg-gradient-to-r from-purple-300 to-purple-500">
            Arcane Chronicles
          </h1>
        </a>
        <nav class="mt-4 md:mt-0">
          <ul class="flex gap-6">
            <?php if(is_logged_in()): ?>
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
            <?php else: ?>
              <li>
                <a href="/public/login.php" class="flex items-center gap-2 text-purple-300 hover:text-purple-200 transition-colors font-magical">
                  <i data-lucide="log-in" class="w-5 h-5"></i>
                  <span>Channel Magic</span>
                </a>
              </li>
              <li>
                <a href="/public/register.php" class="flex items-center gap-2 text-purple-300 hover:text-purple-200 transition-colors font-magical">
                  <i data-lucide="user-plus" class="w-5 h-5"></i>
                  <span>Begin Training</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </div>
  </header>

  <main class="flex-grow container mx-auto px-4 py-8 relative z-10">
    <div class="max-w-4xl mx-auto">
      <h2 class="text-3xl font-medieval text-amber-500 mb-8">Tales of the Tarnished</h2>
      <div class="space-y-8" id="journals">
        <?php foreach($journals as $journal): ?>
          <article class="bg-parchment border-2 border-amber-700/50 rounded-lg p-8 shadow-lg animate-fadeIn">
            <h3 class="text-2xl font-medieval text-amber-500 mb-2"><?php echo htmlspecialchars($journal['title']); ?></h3>
            <p class="text-amber-400/80 italic mb-4">By <?php echo htmlspecialchars($journal['username']); ?></p>
            <div class="prose font-medieval mb-4 text-amber-100/90">
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

  <?php require_once "./includes/footer.php" ?>

  <script>
    lucide.createIcons();

    // Initialize particles
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
