<?php
$container = $app->getContainer();

$dsn = sprintf("sqlite:%s", $GLOBALS['settings']['db']['database']);
$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$container->set('db', $pdo);

$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
if (!$stmt->fetch()) {
    require __DIR__ . '/seed.php';
}

$stmt = $pdo->prepare("SELECT value FROM app_settings WHERE key = ?");
$stmt->execute(['template_path']);
$templatePathFromDB = $stmt->fetchColumn();
if ($templatePathFromDB) {
    $GLOBALS['settings']['templatesPath'] = $templatePathFromDB;
}
