<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eldoria - Claim Quest</title>
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

      .retro-button:disabled {
          opacity: 0.5;
          cursor: not-allowed;
      }

      .retro-border {
          border: 1px solid rgba(255, 215, 0, 0.3);
          background: rgba(10, 10, 15, 0.6);
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

      select.retro-input {
          appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23ffd700'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 1rem center;
          background-size: 1em;
          padding-right: 2.5rem;
      }
  </style>
</head>
<body class="retro-gradient">
  <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1475274047050-1d0c0975c63e?auto=format&fit=crop&q=80')"></div>
  <div class="scanline absolute inset-0 pointer-events-none"></div>

  <div class="container mx-auto px-4 py-8 max-w-3xl">
      <div class="flex items-center mb-8">
          <button onclick="window.history.back()" class="retro-button px-4 py-2 mr-4">
              <i data-lucide="arrow-left" class="icon"></i>
          </button>
          <h1 class="text-2xl" style="color: var(--accent);display: flex;">
              <i data-lucide="scroll-text" class="large-icon mr-3 glowing"></i>
              CLAIM QUEST
          </h1>
      </div>

      <div class="retro-panel p-8 pixel-corners">
          <div class="bg-black/50 p-6 retro-border mb-6">
              <div class="space-y-6">
                  <div>
                      <label class="block text-sm mb-3 font-bold" style="color: var(--accent)">QUEST ID</label>
                      <input type="text" id="questId" placeholder="ENTER QUEST ID..." class="retro-input">
                      <p class="text-xs text-gray-400 mt-2">Enter the ID of the quest you want to claim</p>
                  </div>

                  <div>
                      <label class="block text-sm mb-3 font-bold" style="color: var(--accent)">GUILD URL</label>
                      <input type="text" id="questUrl" placeholder="ENTER GUILD URL..." class="retro-input">
                      <p class="text-xs text-gray-400 mt-2">Provide the URL where we can access your guild</p>
                  </div>

                  <div>
                      <label class="block text-sm mb-3 font-bold" style="color: var(--accent)">COMPANIONS</label>
                      <div class="flex items-center space-x-4">
                          <button id="decreaseCompanions" class="retro-button w-12 h-12 text-xl">–</button>
                          <div class="retro-border px-6 py-3 flex items-center min-w-[120px] justify-center">
                              <i data-lucide="users" class="icon"></i>
                              <span id="companionsCount" class="text-lg">0</span>
                          </div>
                          <button id="increaseCompanions" class="retro-button w-12 h-12 text-xl">+</button>
                      </div>
                      <div class="flex items-center mt-3 text-sm" style="color: var(--accent)">
                          <i data-lucide="alert-triangle" class="icon"></i>
                          <span>Each companion increases survival chance by 10%</span>
                      </div>
                  </div>
              </div>
          </div>

          <div class="flex justify-end">
              <button id="claimButton" class="retro-button px-8 py-3 min-w-[160px]" disabled>
                  CLAIM QUEST
              </button>
          </div>
      </div>
  </div>

  <script>
      lucide.createIcons();

      // Handle companions count
      const increaseBtn = document.getElementById('increaseCompanions');
      const decreaseBtn = document.getElementById('decreaseCompanions');
      const companionsCountElem = document.getElementById('companionsCount');
      let companionsCount = 0;

      increaseBtn.addEventListener('click', () => {
          companionsCount++;
          companionsCountElem.textContent = companionsCount;
          checkFormValidity();
      });

      decreaseBtn.addEventListener('click', () => {
          if (companionsCount > 0) {
              companionsCount--;
              companionsCountElem.textContent = companionsCount;
          }
          checkFormValidity();
      });

      function checkFormValidity() {
          const questId = document.getElementById('questId').value.trim();
          const questUrl = document.getElementById('questUrl').value.trim();
          const claimButton = document.getElementById('claimButton');
          if (questId && questUrl) {
              claimButton.disabled = false;
          } else {
              claimButton.disabled = true;
          }
      }

      document.getElementById('questId').addEventListener('input', checkFormValidity);
      document.getElementById('questUrl').addEventListener('input', checkFormValidity);

      document.getElementById('claimButton').addEventListener('click', async () => {
          const questId = document.getElementById('questId').value.trim();
          const questUrl = document.getElementById('questUrl').value.trim();
          if (!questId || !questUrl) {
              console.error('Please fill in all fields.');
              return;
          }
          const res = await fetch('/api/claimQuest', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ questId, questUrl, companions: companionsCount })
          });
          const data = await res.json();
          const messageEl = document.createElement('p');
          messageEl.className = "text-center text-sm mt-4";
          messageEl.textContent = data.message;
          document.querySelector('.retro-panel').appendChild(messageEl);
          if (data.status === 'success') {
              setTimeout(() => {
                  window.location.href = '/dashboard';
              }, 2000);
          }
      });
  </script>
</body>
</html>
