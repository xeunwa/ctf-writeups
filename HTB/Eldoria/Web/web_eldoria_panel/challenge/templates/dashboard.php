<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eldoria - Dashboard</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- DOMPurify -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.2/purify.min.js" crossorigin="anonymous"></script>
  <style>
    :root {
      --primary: #4a1c7c;
      --secondary: #2c1810;
      --accent: #ffd700;
      --background: #0a0a0f;
      --surface: #1a1a2e;
      --text: #e2e8f0;
      --border: #2a2a4a;
    }
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }
    @keyframes scanline {
      0% { transform: translateY(-100%); }
      100% { transform: translateY(100%); }
    }
    @keyframes glow {
      0% { filter: drop-shadow(0 0 2px var(--accent)); }
      50% { filter: drop-shadow(0 0 8px var(--accent)); }
      100% { filter: drop-shadow(0 0 2px var(--accent)); }
    }
    body {
      font-family: 'Press Start 2P', system-ui, -apple-system, sans-serif;
      background-color: var(--background);
      color: var(--text);
      line-height: 1.7;
      letter-spacing: 0.5px;
      min-height: 100vh;
    }
    .retro-gradient {
      background: linear-gradient(135deg, var(--background) 0%, var(--surface) 100%);
    }
    .floating {
      animation: float 3s ease-in-out infinite;
    }
    .glowing {
      animation: glow 2s ease-in-out infinite;
    }
    .scanline {
      background: linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.1) 10%,
        rgba(255, 255, 255, 0) 100%
      );
      animation: scanline 8s linear infinite;
    }
    .retro-panel {
      background: rgba(26, 26, 46, 0.9);
      border: 2px solid var(--accent);
      box-shadow: 0 0 20px rgba(255, 215, 0, 0.1);
      backdrop-filter: blur(10px);
    }
    .pixel-corners {
      clip-path: polygon(
        0 4px,
        4px 4px,
        4px 0,
        calc(100% - 4px) 0,
        calc(100% - 4px) 4px,
        100% 4px,
        100% calc(100% - 4px),
        calc(100% - 4px) calc(100% - 4px),
        calc(100% - 4px) 100%,
        4px 100%,
        4px calc(100% - 4px),
        0 calc(100% - 4px)
      );
    }
    .retro-input {
      background: rgba(10, 10, 15, 0.8);
      border: 1px solid var(--accent);
      color: var(--text);
      font-family: 'Press Start 2P', monospace;
      font-size: 10px;
      width: 100%;
      padding: 0.5rem 1rem;
    }
    .retro-input::placeholder {
      color: rgba(255, 215, 0, 0.3);
    }
    .retro-button {
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
      border: 1px solid var(--accent);
      font-family: 'Press Start 2P', monospace;
      font-size: 10px;
      text-transform: uppercase;
      color: var(--accent);
      background: transparent;
      padding: 0.75rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .retro-button:hover {
      background: rgba(255, 215, 0, 0.1);
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
    }
    .retro-button:active {
      transform: scale(0.98);
    }
    .retro-border {
      border: 1px solid rgba(255, 215, 0, 0.3);
      background: rgba(10, 10, 15, 0.6);
    }
    .retro-progress {
      width: 100%;
      height: 12px;
      background: rgba(10, 10, 15, 0.8);
      border: 1px solid var(--accent);
      padding: 2px;
    }
    .retro-progress-bar {
      height: 100%;
      background: var(--accent);
      transition: width 0.3s ease;
      box-shadow: 0 0 10px var(--accent);
    }
    .nav-button {
      padding: 0.75rem 1rem;
      font-size: 10px;
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
      color: var(--accent);
    }
    .nav-button:hover {
      text-shadow: 0 0 8px var(--accent);
    }
    .status-panel {
      display: flex;
      align-items: center;
      padding: 0.25rem 0.75rem;
      font-size: 8px;
    }
    .icon {
      width: 1rem;
      height: 1rem;
      margin-right: 0.5rem;
      color: var(--accent);
    }
    .large-icon {
      width: 1.5rem;
      height: 1.5rem;
      color: var(--accent);
    }
    .quest-difficulty-easy { color: #4ade80; }
    .quest-difficulty-medium { color: #facc15; }
    .quest-difficulty-hard { color: #fb923c; }
    .quest-difficulty-legendary { color: #ef4444; }
  </style>
</head>
<body class="retro-gradient">
  <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1475274047050-1d0c0975c63e?auto=format&fit=crop&q=80')"></div>
  <div class="scanline absolute inset-0 pointer-events-none"></div>

  <nav class="relative border-b border-[#ffd700]/30 bg-[#0a0a2c]/95 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center space-x-3">
          <i data-lucide="scroll" class="large-icon floating"></i>
          <span class="text-base" style="color: var(--accent)">ELDORIA</span>
        </div>
        <div class="flex items-center space-x-3">
          <a href="/claim_quest" class="nav-button">
            <i data-lucide="users" class="icon"></i>
            CLAIM QUEST
          </a>
          <div class="h-4 w-px bg-[#ffd700]/20"></div>
          <div class="status-panel">
            <i data-lucide="crown" class="icon glowing"></i>
            <span id="userLevel">LVL 1</span>
          </div>
          <div class="status-panel">
            <i data-lucide="star" class="icon"></i>
            <span id="userRank">NOVICE</span>
          </div>
          <div class="status-panel">
            <i data-lucide="shield" class="icon"></i>
            <span id="userRole">USER</span>
          </div>
          <div class="h-4 w-px bg-[#ffd700]/20"></div>
          <button id="logoutBtn" class="nav-button">
            <i data-lucide="log-out" class="icon"></i>
            LOGOUT
          </button>
        </div>
      </div>
    </div>
  </nav>

  <main class="relative container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <div class="lg:col-span-8 space-y-8">
        <section class="retro-panel p-6 pixel-corners">
          <div class="flex items-center mb-6">
            <i data-lucide="shield" class="large-icon mr-3 glowing"></i>
            <h2 class="text-xl" style="color: var(--accent)">MAGICAL CREDENTIALS</h2>
          </div>
          <div class="bg-black/50 p-4 retro-border mb-6">
            <p class="text-sm mb-2" style="color: var(--accent)">DRAGON'S HEART API KEY</p>
            <p class="font-mono" style="color: var(--accent)" id="apiKey">Loading...</p>
          </div>
          <div>
            <h3 class="text-lg mb-4 flex items-center">
              <i data-lucide="sparkles" class="icon glowing"></i>
              <span>HERO STATUS</span>
            </h3>
            <div class="bg-black/50 p-4 retro-border mb-4" style="color: var(--accent)" id="heroStatus">
              Loading status…
            </div>
            <div class="flex space-x-4">
              <input type="text" id="statusInput" placeholder="CAST STATUS..." class="retro-input flex-1" maxlength="100">
              <button id="updateStatusBtn" class="retro-button">
                <i data-lucide="wand" class="icon"></i>
                ENCHANT
              </button>
            </div>
          </div>
        </section>
        <section class="retro-panel p-6 pixel-corners">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
              <i data-lucide="scroll-text" class="large-icon mr-3 glowing"></i>
              <h2 class="text-xl" style="color: var(--accent)">AVAILABLE QUESTS</h2>
            </div>
            <button id="newQuestBtn" class="retro-button px-4 py-2 text-sm hidden">
              <i data-lucide="plus" class="icon"></i>
              NEW QUEST
            </button>
          </div>
          <div id="questsList" class="space-y-4">
          </div>
        </section>
      </div>
      <div class="lg:col-span-4 space-y-8">
        <section class="retro-panel p-6 pixel-corners">
          <h3 class="text-lg mb-4 flex items-center">
            <i data-lucide="flame" class="icon glowing"></i>
            <span>MAGIC POWER</span>
          </h3>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between text-sm mb-2">
                <span>POWER LEVEL</span>
                <span id="magicPower" style="color: var(--accent)">Loading...</span>
              </div>
              <div class="retro-progress">
                <div class="retro-progress-bar" style="width: 0%"></div>
              </div>
            </div>
            <div class="retro-border p-4">
              <div class="flex justify-between text-sm">
                <span>QUESTS COMPLETED</span>
                <span id="questsCompleted" style="color: var(--accent)">0</span>
              </div>
            </div>
          </div>
        </section>
        <section class="retro-panel p-6 pixel-corners">
          <h3 class="text-lg mb-4 flex items-center">
            <i data-lucide="gem" class="icon glowing"></i>
            <span>ARTIFACTS</span>
          </h3>
          <div id="artifactsList" class="space-y-3">
          </div>
        </section>
      </div>
    </div>
    <footer class="mt-8 retro-panel p-4 pixel-corners text-center">
      <p class="flex items-center justify-center text-xs">
        <i data-lucide="sparkles" class="icon glowing"></i>
        ONLY THE CHOSEN ONES MAY WIELD THE POWER OF THE DRAGON'S HEART
      </p>
    </footer>
  </main>

  <script>
    lucide.createIcons();

    fetch('/api/user')
      .then(res => res.json())
      .then(user => {
        if (user.error) {
          window.location.href = '/login';
        } else {
          document.getElementById('apiKey').textContent = user.api_key;
          document.getElementById('userLevel').textContent = 'LVL ' + (user.level || 1);
          document.getElementById('userRank').textContent = user.rank || 'NOVICE';
          document.getElementById('userRole').textContent = (user.is_admin ? 'ADMIN' : 'USER');
          document.getElementById('magicPower').textContent = (user.magicPower || 50) + '%';
          document.getElementById('questsCompleted').textContent = user.questsCompleted || 0;
          
          const cleanStatus = DOMPurify.sanitize(user.status || 'Ready for adventure!', {
            USE_PROFILES: { html: true },
            ALLOWED_TAGS: ['a', 'b', 'i', 'em', 'strong', 'span', 'br'],
            FORBID_TAGS: ['svg', 'math'],
            FORBID_CONTENTS: ['']
          });

          document.getElementById('heroStatus').innerHTML = cleanStatus;

          const progressBar = document.querySelector('.retro-progress-bar');
          if (progressBar) {
            progressBar.style.width = (user.magicPower || 50) + '%';
          }
        }
      });

    document.getElementById('updateStatusBtn').addEventListener('click', async () => {
      const status = document.getElementById('statusInput').value.trim();
      if (!status) return;
      const res = await fetch('/api/updateStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
      });
      const data = await res.json();
      document.getElementById('heroStatus').innerHTML = data.newStatus
      DOMPurify.sanitize(data.newStatus, {
        ALLOWED_TAGS: ['a', 'b', 'i', 'em', 'strong', 'span', 'br'],
        FORBID_TAGS: ['svg', 'math'],
        FORBID_CONTENTS: ['']
      });
      document.getElementById('statusInput').value = '';
    });

    fetch('/api/quests')
      .then(res => res.json())
      .then(quests => {
        const questsList = document.getElementById('questsList');
        questsList.innerHTML = '';
        quests.forEach(quest => {
          const div = document.createElement('div');
          div.className = 'bg-black/50 p-4 retro-border';
          div.innerHTML = `
            <div class="flex justify-between items-center mb-2">
              <h3 style="color: var(--accent)" class="font-bold">${quest.title}</h3>
              <span class="px-2 py-1 text-xs">${quest.difficulty}</span>
            </div>
            <p class="text-sm mb-3">${quest.description}</p>
            <div class="flex justify-end">
              <button class="retro-button text-sm" onclick="window.location.href='/quest_details?id=${quest.id}'">VIEW QUEST</button>
            </div>
          `;
          questsList.appendChild(div);
        });
      });

    fetch('/api/user')
      .then(res => res.json())
      .then(user => {
        const artifactsList = document.getElementById('artifactsList');
        artifactsList.innerHTML = '';
        if (user.artifacts && Array.isArray(user.artifacts)) {
          user.artifacts.forEach(artifact => {
            const div = document.createElement('div');
            div.className = 'retro-border p-3 cursor-pointer hover:bg-[#ffd700]/10 transition-colors';
            div.innerHTML = `
              <i data-lucide="sword" class="icon inline-block"></i>
              <span class="text-sm">${artifact}</span>
            `;
            artifactsList.appendChild(div);
          });
        } else {
          artifactsList.innerHTML = '<p class="text-sm">No artifacts found.</p>';
        }
      });

    document.getElementById('logoutBtn').addEventListener('click', async () => {
      await fetch('/api/logout', { method: 'POST', headers: { 'Content-Type': 'application/json' } });
      window.location.href = '/';
    });

    setInterval(async () => {
      const res = await fetch('/api/user');
      const user = await res.json();
      if (!user.error) {
        document.getElementById('userLevel').textContent = 'LVL ' + (user.level || 1);
        document.getElementById('userRank').textContent = user.rank || 'NOVICE';
        document.getElementById('magicPower').textContent = (user.magicPower || 50) + '%';
        document.getElementById('questsCompleted').textContent = user.questsCompleted || 0;
        const progressBar = document.querySelector('.retro-progress-bar');
        if (progressBar) {
          progressBar.style.width = (user.magicPower || 50) + '%';
        }
      }
    }, 10000);

    lucide.createIcons();
  </script>
</body>
</html>
