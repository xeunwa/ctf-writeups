<?php
require_once '../server_config.php';
require_once '../includes/auth.php';
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login_user($username, $password)) {
        header('Location: ../index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arcane Chronicles - Login</title>
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
      </div>
    </div>
  </header>

  <main class="flex-grow container mx-auto px-4 py-8 relative z-10">
    <div class="max-w-md mx-auto">
      <form id="loginForm" method="POST" class="bg-parchment p-8 border-2 border-amber-700/50 rounded-lg shadow-lg">
        <h2 class="text-2xl font-medieval text-amber-500 mb-6 text-center">Return to the Realm</h2>
        <?php if($error): ?>
          <div id="error" class="bg-red-900/50 text-red-200 p-4 rounded-lg mb-6 border border-red-700">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>
        <div class="mb-6">
          <label for="username" class="block text-amber-500 font-medieval mb-2">Name of Thy Character:</label>
          <input type="text" id="username" name="username" class="w-full p-3 border border-amber-700/50 rounded-lg bg-black/30 font-medieval text-amber-100" required />
        </div>
        <div class="mb-6">
          <label for="password" class="block text-amber-500 font-medieval mb-2">Sacred Password:</label>
          <input type="password" id="password" name="password" class="w-full p-3 border border-amber-700/50 rounded-lg bg-black/30 font-medieval text-amber-100" required />
        </div>
        <button type="submit" class="w-full bg-amber-700 hover:bg-amber-600 text-amber-100 py-3 rounded-lg font-medieval transition-colors">
          Touch Grace
        </button>
        <p class="text-center mt-4 font-medieval text-amber-100">
          Yet to begin thy journey?
          <a href="/public/register.php" class="text-amber-500 hover:text-amber-300 transition-colors">Arise, Tarnished</a>
        </p>
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
