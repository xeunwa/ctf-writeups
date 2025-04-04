
<?php

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT,
    is_admin INTEGER DEFAULT 0,
    api_key TEXT,
    status TEXT DEFAULT 'Ready for adventure!'
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS config (
    id INTEGER PRIMARY KEY,
    announcement TEXT
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS quests (
    id TEXT PRIMARY KEY,
    title TEXT,
    description TEXT,
    reward TEXT,
    difficulty TEXT,
    status TEXT DEFAULT 'Available',
    image TEXT,
    reward_image TEXT
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS app_settings (
    key TEXT PRIMARY KEY,
    value TEXT
);
");

$stmt = $pdo->query("SELECT COUNT(*) FROM users");
if ($stmt->fetchColumn() == 0) {
    $adminApiKey = bin2hex(random_bytes(16));
    $heroApiKey  = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin, api_key) VALUES (?, ?, ?, ?)");
    $adminPassword = bin2hex(random_bytes(32));
    $stmt->execute(['admin', $adminPassword, 1, $adminApiKey]);
    $stmt->execute(['hero', 'heropass', 0, $heroApiKey]);
}

$stmt = $pdo->query("SELECT COUNT(*) FROM config");
if ($stmt->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO config (id, announcement) VALUES (1, ?)");
    $stmt->execute(['Welcome to Eldoria!']);
}

$stmt = $pdo->query("SELECT COUNT(*) FROM quests");
if ($stmt->fetchColumn() == 0) {
    $stmt = $pdo->prepare("INSERT INTO quests (id, title, description, reward, difficulty, status, image, reward_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $quests = [
        [
            'q1',
            'The Lost Grimoire',
            'Recover the ancient spellbook from the Haunted Library. Navigate through enchanted bookshelves and evade ghostly librarians.',
            'Mystic Tome of Knowledge',
            'Medium',
            'Available',
            '/images/grimore.png',
            '/images/tome.png'
        ],
        [
            'q2',
            "Dragon's Challenge",
            "Defeat the shadow dragon in the Twilight Valley before it terrorizes nearby villages.",
            'Dragon Scale Armor',
            'Hard',
            'Available',
            '/images/dragon.png',
            '/images/scale.png'
        ],
        [
            'q7',
            'Sunken Secrets',
            'Explore the ruins beneath the lake and retrieve the lost relic of the sunken kingdom.',
            'Trident of Tides',
            'Medium',
            'Available',
            '/images/sunken.png',
            '/images/trident.png'
        ],
        [
            'q8',
            'The Cursed Bazaar',
            'Uncover the dark magic behind the cursed marketplace where fortunes are stolen.',
            'Coin of Fate',
            'Easy',
            'Available',
            '/images/cursed.png',
            '/images/coin.png'
        ]
    ];

    foreach ($quests as $quest) {
        $stmt->execute($quest);
    }
}
$stmt = $pdo->prepare("INSERT OR IGNORE INTO app_settings (key, value) VALUES (?, ?)");
$stmt->execute(['template_path', __DIR__ . '/../templates']);
$stmt->execute(['database_driver', 'sqlite']);
$stmt->execute(['database_name', $GLOBALS['settings']['db']['database']]);
