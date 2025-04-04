<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eldoria - Admin Panel</title>
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
    body {
      font-family: 'Press Start 2P', system-ui, -apple-system, sans-serif;
      background-color: var(--background);
      color: var(--text);
      min-height: 100vh;
    }
    .retro-panel {
      background: rgba(26, 26, 46, 0.9);
      border: 2px solid var(--accent);
      padding: 2rem;
      max-width: 600px;
      margin: 2rem auto;
    }
    .retro-input {
      width: 100%;
      padding: 0.5rem;
      background: rgba(10,10,15,0.8);
      border: 1px solid var(--accent);
      color: var(--text);
      margin-bottom: 1rem;
    }
    .retro-button {
      padding: 0.75rem 1.5rem;
      border: 1px solid var(--accent);
      background: transparent;
      color: var(--accent);
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="retro-panel">
    <h1>Admin Panel</h1>
    <h2>Update Announcement</h2>
    <form id="announcementForm">
      <textarea id="announcement" class="retro-input" rows="4" placeholder="Enter new announcement"></textarea>
      <button type="submit" class="retro-button">Update</button>
    </form>
    <div id="result" class="mt-4"></div>
  </div>
  <script>
    document.getElementById('announcementForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      const announcement = document.getElementById('announcement').value;
      const res = await fetch('/api/admin/updateAnnouncement', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ announcement })
      });
      const data = await res.json();
      document.getElementById('result').textContent = data.message || 'Announcement updated!';
    });
  </script>
</body>
</html>
