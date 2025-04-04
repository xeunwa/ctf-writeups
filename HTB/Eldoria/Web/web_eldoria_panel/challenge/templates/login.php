<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eldoria - Login</title>
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
            margin-bottom: 1rem;
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
            width: 100%;
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
            width: 3rem;
            height: 3rem;
            color: var(--accent);
        }
    </style>
</head>
<body class="retro-gradient flex items-center justify-center">
    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1475274047050-1d0c0975c63e?auto=format&fit=crop&q=80')"></div>
    <div class="scanline absolute inset-0 pointer-events-none"></div>

    <div class="retro-panel p-8 pixel-corners w-full max-w-md relative">
        <div class="flex items-center justify-center mb-8">
            <h1 class="text-2xl" style="color: var(--accent)">ELDORIA</h1>
        </div>

        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-sm mb-2" style="color: var(--accent)">USERNAME</label>
                <input id="username" type="text" class="retro-input" placeholder="ENTER USERNAME" required>
            </div>

            <div>
                <label class="block text-sm mb-2" style="color: var(--accent)">PASSWORD</label>
                <input id="password" type="password" class="retro-input" placeholder="ENTER PASSWORD" required>
            </div>

            <div id="error" class="text-red-500 text-sm text-center hidden"></div>

            <button id="submitBtn" type="submit" class="retro-button">
                <i data-lucide="shield" class="icon"></i>
                LOGIN
            </button>

            <div class="text-center mt-4 text-sm">
                <a href="/register" style="color: var(--accent)" class="hover:opacity-80">
                    CREATE NEW ACCOUNT
                </a>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
          e.preventDefault();
          const [username, password] = this.querySelectorAll('input');
          const error = document.getElementById('error');
          if (username.value.length < 3 || password.value.length < 6) {
            error.textContent = 'Please check your input';
            error.classList.remove('hidden');
            return;
          }
          const res = await fetch('/api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: username.value, password: password.value })
          });
          const data = await res.json();
          if (data.login_status === 'success') {
            window.location.href = '/dashboard';
          } else {
            error.textContent = data.message || 'Login failed';
            error.classList.remove('hidden');
          }
        });
      </script>
</body>
</html>
