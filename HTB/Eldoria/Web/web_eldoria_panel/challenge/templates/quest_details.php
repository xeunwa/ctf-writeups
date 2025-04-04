<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eldoria - Quest Details</title>
  <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
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
    .retro-border {
      border: 1px solid rgba(255, 215, 0, 0.3);
      background: rgba(10, 10, 15, 0.6);
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
    .quest-image {
      height: 300px;
      width: 100%;
      object-fit: cover;
      border-radius: 4px;
      margin-bottom: 1rem;
      border: 1px solid var(--accent);
    }
    .quest-image-overlay {
      position: relative;
      overflow: hidden;
      border-radius: 4px;
      margin-bottom: 1rem;
    }
    .quest-image-overlay::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(0deg, rgba(10, 10, 15, 0.7) 0%, rgba(10, 10, 15, 0) 50%);
      pointer-events: none;
    }
    .reward-image {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 4px;
      border: 1px solid var(--accent);
      margin-bottom: 0.5rem;
    }
  </style>
</head>
<body class="retro-gradient">
  <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1475274047050-1d0c0975c63e?auto=format&fit=crop&q=80')"></div>
  <div class="scanline absolute inset-0 pointer-events-none"></div>

  <div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-8">
      <button onclick="window.history.back()" class="retro-button px-4 py-2 mr-4">
        <i data-lucide="arrow-left" class="icon"></i>
      </button>
      <h1 class="text-2xl" style="color: var(--accent)">
        QUEST DETAILS
      </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <div class="retro-panel p-6 pixel-corners">
          <div class="bg-black/50 p-6 retro-border">
            <div class="quest-image-overlay">
              <img id="questImage" class="quest-image" alt="Quest Image">
            </div>
            <div class="flex justify-between items-start mb-6">
              <h2 id="questTitle" class="text-2xl font-bold" style="color: var(--accent)"></h2>
              <span id="questDifficulty" class="px-3 py-1 text-sm border-2 border-current"></span>
            </div>
            <div class="space-y-6">
              <div class="prose text-white">
                <p id="questDescription" class="text-lg mb-4"></p>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div class="retro-border p-4 space-y-2">
                  <div class="flex items-center" style="color: var(--accent)">
                    <i data-lucide="clock" class="icon"></i>
                    <span class="font-bold">Duration</span>
                  </div>
                  <p id="questDuration" class="text-sm"></p>
                </div>
                <div class="retro-border p-4 space-y-2">
                  <div class="flex items-center" style="color: var(--accent)">
                    <i data-lucide="target" class="icon"></i>
                    <span class="font-bold">Required Level</span>
                  </div>
                  <p id="requiredLevel" class="text-sm"></p>
                </div>
              </div>
              <div class="retro-border p-4 space-y-4">
                <div class="flex items-center" style="color: var(--accent)">
                  <i data-lucide="trophy" class="icon"></i>
                  <span class="font-bold">Rewards</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <img id="rewardImage" class="reward-image" alt="Reward Image">
                    <div class="flex items-center space-x-3">
                      <i data-lucide="gem" class="icon"></i>
                      <span id="questReward" class="text-sm"></span>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <i data-lucide="flame" class="icon"></i>
                    <span id="magicPowerReward" class="text-sm"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-1 space-y-6">
        <div class="retro-panel p-6 pixel-corners">
          <h3 class="text-lg mb-4 flex items-center" style="color: var(--accent)">
            <i data-lucide="shield" class="icon"></i>
            <span>COMBAT ANALYSIS</span>
          </h3>
          <div class="space-y-4">
            <div class="retro-border p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm">Survival Chance</span>
                <span id="survivalChance" class="text-sm"></span>
              </div>
              <div class="retro-progress">
                <div id="survivalBar" class="retro-progress-bar"></div>
              </div>
            </div>
            <div id="warningBox" class="bg-red-500/20 border-2 border-red-500 p-4 rounded-lg hidden">
              <div class="flex items-center text-red-400 mb-2">
                <i data-lucide="alert-triangle" class="icon"></i>
                <span class="font-bold">WARNING</span>
              </div>
              <p class="text-sm">Your current level is too low for this quest. Level up to increase your chances of survival.</p>
            </div>
            <div class="retro-border p-4">
              <div class="flex items-center" style="color: var(--accent)">
                <i data-lucide="swords" class="icon"></i>
                <span class="font-bold">Combat Stats</span>
              </div>
              <div class="space-y-2 mt-3">
                <div class="flex justify-between text-sm">
                  <span>Your Level</span>
                  <span id="playerLevel"></span>
                </div>
                <div class="flex justify-between text-sm">
                  <span>Magic Power</span>
                  <span id="playerMagicPower"></span>
                </div>
                <div class="flex justify-between text-sm">
                  <span>Quests Completed</span>
                  <span id="playerQuestsCompleted"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();

    const params = new URLSearchParams(window.location.search);
    const questId = params.get('id');
    if (!questId) {
      window.location.href = '/dashboard';
    }

    function getQuestDuration(difficulty) {
      switch (difficulty) {
        case 'Easy': return '1-2 hours';
        case 'Medium': return '2-4 hours';
        case 'Hard': return '4-8 hours';
        case 'Legendary': return '8-12 hours';
        default: return 'Unknown';
      }
    }

    function getRequiredLevel(difficulty) {
      return Math.ceil(50 * (
        difficulty === 'Easy' ? 0.2 :
        difficulty === 'Medium' ? 0.4 :
        difficulty === 'Hard' ? 0.6 :
        0.8
      ));
    }

    function calculateSurvivalChance(difficulty, playerLevel) {
      const difficultyModifier = {
        'Easy': 0.8,
        'Medium': 0.6,
        'Hard': 0.4,
        'Legendary': 0.2
      }[difficulty] || 0.5;
      const chance = (playerLevel / 50) * difficultyModifier * 100;
      return Math.min(Math.max(Math.round(chance), 5), 95);
    }

    fetch('/api/quest?id=' + questId)
      .then(res => res.json())
      .then(quest => {
        if (quest.error) {
          window.location.href = '/dashboard';
          return;
        }

        document.getElementById('questTitle').textContent = quest.title;
        document.getElementById('questDescription').textContent = quest.description;
        document.getElementById('questReward').textContent = quest.reward;
        document.getElementById('questDuration').textContent = getQuestDuration(quest.difficulty);
        document.getElementById('requiredLevel').textContent = 'Level ' + getRequiredLevel(quest.difficulty) + '+';

        const magicReward = quest.difficulty === 'Legendary' ? '500' :
                            quest.difficulty === 'Hard' ? '300' :
                            quest.difficulty === 'Medium' ? '200' : '100';
        document.getElementById('magicPowerReward').textContent = '+' + magicReward + ' Magic Power';

        const difficultyEl = document.getElementById('questDifficulty');
        difficultyEl.textContent = quest.difficulty;
        difficultyEl.className = `px-3 py-1 text-sm border-2 border-current quest-difficulty-${quest.difficulty.toLowerCase()}`;

        if (quest.image) {
          document.getElementById('questImage').src = quest.image;
          document.getElementById('questImage').alt = quest.title;
        } else {
          document.getElementById('questImage').src = 'https://via.placeholder.com/800x300?text=Quest+Image';
        }

        if (quest.reward_image) {
          document.getElementById('rewardImage').src = quest.reward_image;
          document.getElementById('rewardImage').alt = quest.reward;
        } else {
          document.getElementById('rewardImage').src = 'https://via.placeholder.com/400x120?text=Reward+Image';
        }
      });

    fetch('/api/user')
      .then(res => res.json())
      .then(user => {
        if (user.error) {
          window.location.href = '/login';
        } else {
          document.getElementById('playerLevel').textContent = user.level || 1;
          document.getElementById('playerMagicPower').textContent = (user.magicPower || 50) + '%';
          document.getElementById('playerQuestsCompleted').textContent = user.questsCompleted || 0;
          const survivalChance = calculateSurvivalChance(quest.difficulty || 'Medium', user.level || 1);
          document.getElementById('survivalChance').textContent = survivalChance + '%';
          document.getElementById('survivalBar').style.width = survivalChance + '%';
          if ((user.level || 1) < getRequiredLevel(quest.difficulty || 'Medium')) {
            document.getElementById('warningBox').classList.remove('hidden');
          }
        }
      });
  </script>
</body>
</html>
