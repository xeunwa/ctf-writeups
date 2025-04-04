<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as SlimResponse;
use HeadlessChromium\BrowserFactory;

$authMiddleware = function (Request $request, $handler) {
	if (!isset($_SESSION['user'])) {
		$response = new SlimResponse();
		return $response->withHeader('Location', '/')->withStatus(302);
	}
	return $handler->handle($request);
};

function isAdmin(Request $request) {
	return (isset($_SESSION['user'])
		&& $_SESSION['user']['is_admin'] == 1
		&& isset($_COOKIE['adminCookie'])
		&& $_COOKIE['adminCookie'] === 'true');
}

$apiKeyMiddleware = function (Request $request, $handler) use ($app) {
	if (!isset($_SESSION['user'])) {
		$apiKey = $request->getHeaderLine('X-API-Key');
		if ($apiKey) {
			$pdo = $app->getContainer()->get('db');
			$stmt = $pdo->prepare("SELECT * FROM users WHERE api_key = ?");
			$stmt->execute([$apiKey]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($user) {
				$_SESSION['user'] = [
					'id'              => $user['id'],
					'username'        => $user['username'],
					'is_admin'        => $user['is_admin'],
					'api_key'         => $user['api_key'],
					'level'           => 1,
					'rank'            => 'NOVICE',
					'magicPower'      => 50,
					'questsCompleted' => 0,
					'artifacts'       => ["Ancient Scroll of Wisdom", "Dragon's Heart Shard"]
				];
			}
		}
	}
	return $handler->handle($request);
};

$adminApiKeyMiddleware = function (Request $request, $handler) use ($app) {
	if (!isset($_SESSION['user'])) {
		$apiKey = $request->getHeaderLine('X-API-Key');
		if ($apiKey) {
			$pdo = $app->getContainer()->get('db');
			$stmt = $pdo->prepare("SELECT * FROM users WHERE api_key = ?");
			$stmt->execute([$apiKey]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($user && $user['is_admin'] === 1) {
				$_SESSION['user'] = [
					'id'              => $user['id'],
					'username'        => $user['username'],
					'is_admin'        => $user['is_admin'],
					'api_key'         => $user['api_key'],
					'level'           => 1,
					'rank'            => 'NOVICE',
					'magicPower'      => 50,
					'questsCompleted' => 0,
					'artifacts'       => ["Ancient Scroll of Wisdom", "Dragon's Heart Shard"]
				];
			}
		}
	}
	return $handler->handle($request);
};

// ------------------------
// Static Page Routes
// ------------------------

function render($filePath) {
    if (!file_exists($filePath)) {
        return "Error: File not found.";
    }
    $phpCode = file_get_contents($filePath);
    ob_start();
    eval("?>" . $phpCode);
    return ob_get_clean();
}

$app->get('/', function (Request $request, Response $response, $args) {
    $html = render($GLOBALS['settings']['templatesPath'] . '/login.php');
    $response->getBody()->write($html);
    return $response;
});

$app->get('/register', function (Request $request, Response $response, $args) {
    $html = render($GLOBALS['settings']['templatesPath'] . '/register.php');
    $response->getBody()->write($html);
    return $response;
});

$app->get('/dashboard', function (Request $request, Response $response, $args) {
    $html = render($GLOBALS['settings']['templatesPath'] . '/dashboard.php');
    $response->getBody()->write($html);
    return $response;
})->add($authMiddleware);

$app->get('/quest_details', function (Request $request, Response $response, $args) {
    $html = render($GLOBALS['settings']['templatesPath'] . '/quest_details.php');
    $response->getBody()->write($html);
    return $response;
})->add($authMiddleware);

$app->get('/claim_quest', function (Request $request, Response $response, $args) {
    $html = render($GLOBALS['settings']['templatesPath'] . '/claim_quest.php');
    $response->getBody()->write($html);
    return $response;
})->add($authMiddleware);

$app->get('/admin', function (Request $request, Response $response, $args) {
    if (!isAdmin($request)) {
        $response->getBody()->write("Access Denied");
        return $response->withStatus(403);
    }
    
    $html = render($GLOBALS['settings']['templatesPath'] . '/admin.php');
    $response->getBody()->write($html);
    return $response;
})->add($authMiddleware);


// ------------------------
// API Endpoints Group (/api)
// ------------------------
// GET /api/user
$app->get('/api/user', function (Request $request, Response $response, $args) {
	if (isset($_SESSION['user'])) {
		$payload = json_encode($_SESSION['user']);
	} else {
		$payload = json_encode(['error' => 'Not authenticated']);
	}
	$response->getBody()->write($payload);
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);

// POST /api/logout
$app->post('/api/logout', function (Request $request, Response $response, $args) {
	$_SESSION = [];

	session_destroy();

	$payload = json_encode(['status' => 'logged out']);
	$response->getBody()->write($payload);
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);


// POST /api/login
$app->post('/api/login', function (Request $request, Response $response, $args) {
	$data = json_decode($request->getBody()->getContents(), true);
	$pdo = $this->get('db');
	$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
	$stmt->execute([$data['username']]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($user && $user['password'] === $data['password']) {
		if (empty($user['api_key'])) {
			$apiKey = bin2hex(random_bytes(16));
			$stmtUpdate = $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?");
			$stmtUpdate->execute([$apiKey, $user['id']]);
			$user['api_key'] = $apiKey;
		}
		$_SESSION['user'] = [
			'id'              => $user['id'],
			'username'        => $user['username'],
			'is_admin'        => $user['is_admin'],
			'api_key'         => $user['api_key'],
			'status'          => $user['status'] ?? 'Ready for adventure!',
			'level'           => 1,
			'rank'            => 'NOVICE',
			'magicPower'      => 50,
			'questsCompleted' => 0,
			'artifacts'       => ["Ancient Scroll of Wisdom", "Dragon's Heart Shard"]
		];
		$result = [
			'login_status'   => 'success',
			'username' => $user['username'],
			'api_key'  => $user['api_key'],
			'status'   => $user['status'] ?? 'Ready for adventure!',
			'level'           => 1,
			'rank'            => 'NOVICE',
			'magicPower'      => 50,
			'questsCompleted' => 0,
			'artifacts'       => ["Ancient Scroll of Wisdom", "Dragon's Heart Shard"]
		];
	} else {
		$result = ['status' => 'error', 'message' => 'Invalid credentials'];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);

// POST /api/register
$app->post('/api/register', function (Request $request, Response $response, $args) {
	$data = json_decode($request->getBody()->getContents(), true);
	$pdo = $this->get('db');
	$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
	$stmt->execute([$data['username']]);
	if ($stmt->fetch(PDO::FETCH_ASSOC)) {
		$result = ['status' => 'error', 'message' => 'Username already taken'];
	} else {
		$apiKey = bin2hex(random_bytes(16));
		$stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin, api_key) VALUES (?, ?, ?, ?)");
		$is_admin = 0;
		$stmt->execute([$data['username'], $data['password'], $is_admin, $apiKey]);
		$result = [
			'status'   => 'registered',
			'username' => $data['username'],
			'api_key'  => $apiKey,
			'level'           => 1,
			'rank'            => 'NOVICE',
			'magicPower'      => 50,
			'questsCompleted' => 0,
			'artifacts'       => ["Ancient Scroll of Wisdom", "Dragon's Heart Shard"]
		];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);

// POST /api/updateStatus
$app->post('/api/updateStatus', function (Request $request, Response $response, $args) {
	$data = json_decode($request->getBody()->getContents(), true);
	$newStatus = $data['status'] ?? '';
	if (!isset($_SESSION['user'])) {
		$result = ['status' => 'error', 'message' => 'Not authenticated'];
	} else {
		$_SESSION['user']['status'] = $newStatus;
		$pdo = $this->get('db');
		$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
		$stmt->execute([$newStatus, $_SESSION['user']['id']]);
		$result = ['status' => 'updated', 'newStatus' => $newStatus];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);

// GET /api/announcement
$app->get('/api/announcement', function (Request $request, Response $response, $args) {
	$pdo = $this->get('db');
	$stmt = $pdo->query("SELECT announcement FROM config WHERE id = 1");
	$announcement = $stmt->fetchColumn();
	$result = ['announcement' => $announcement];
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);

// GET /api/quests
$app->get('/api/quests', function (Request $request, Response $response, $args) {
	$pdo = $this->get('db');
	$stmt = $pdo->query("SELECT * FROM quests");
	$quests = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$response->getBody()->write(json_encode($quests));
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);

// GET /api/quest
$app->get('/api/quest', function (Request $request, Response $response, $args) {
	$params = $request->getQueryParams();
	if (empty($params['id'])) {
		$result = ['error' => 'No quest id provided'];
	} else {
		$pdo = $this->get('db');
		$stmt = $pdo->prepare("SELECT * FROM quests WHERE id = ?");
		$stmt->execute([$params['id']]);
		$quest = $stmt->fetch(PDO::FETCH_ASSOC);
		$result = $quest ? $quest : ['error' => 'Quest not found'];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($apiKeyMiddleware);

// POST /api/claimQuest

$app->post('/api/claimQuest', function (Request $request, Response $response, $args) {
	$data = json_decode($request->getBody()->getContents(), true);

	if (empty($data['questId'])) {
		$result = ['status' => 'error', 'message' => 'No quest id provided'];
	} else {
		$pdo = $this->get('db');
		$stmt = $pdo->prepare("UPDATE quests SET status = 'Claimed' WHERE id = ?");
		$stmt->execute([$data['questId']]);
		$result = ['status' => 'success', 'message' => 'Quest claimed'];
	}

	$response->getBody()->write(json_encode($result));
	$response = $response->withHeader('Content-Type', 'application/json');

	if (function_exists('fastcgi_finish_request')) {
		fastcgi_finish_request();
	} else {
		ignore_user_abort(true);
		if (ob_get_level() > 0) {
			ob_end_flush();
		}
		flush();
	}

	if (!empty($data['questUrl'])) {
        $validatedUrl = filter_var($data['questUrl'], FILTER_VALIDATE_URL);
        if ($validatedUrl === false) {
            error_log('Invalid questUrl provided: ' . $data['questUrl']);
        } else {
            $safeQuestUrl = escapeshellarg($validatedUrl);
            $cmd = "nohup python3 " . escapeshellarg(__DIR__ . "/bot/run_bot.py") . " " . $safeQuestUrl . " > /dev/null 2>&1 &";
            exec($cmd);
        }
    }
	
	return $response;
})->add($apiKeyMiddleware);

$app->get('/api/admin/appSettings', function (Request $request, Response $response, $args) {
	$pdo = $this->get('db');
	$stmt = $pdo->query("SELECT key, value FROM app_settings");
	$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
	$response->getBody()->write(json_encode(['settings' => $settings]));
	return $response->withHeader('Content-Type', 'application/json');
})->add($adminApiKeyMiddleware);

// POST /api/admin/appSettings
$app->post('/api/admin/appSettings', function (Request $request, Response $response, $args) {
	$data = json_decode($request->getBody()->getContents(), true);
	if (empty($data) || !is_array($data)) {
		$result = ['status' => 'error', 'message' => 'No settings provided'];
	} else {
		$pdo = $this->get('db');
		$stmt = $pdo->prepare("INSERT INTO app_settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value");
		foreach ($data as $key => $value) {
			$stmt->execute([$key, $value]);
		}
		if (isset($data['template_path'])) {
			$GLOBALS['settings']['templatesPath'] = $data['template_path'];
		}
		$result = ['status' => 'success', 'message' => 'Settings updated'];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($adminApiKeyMiddleware);

// POST /api/admin/cleanDatabase
$app->post('/api/admin/cleanDatabase', function (Request $request, Response $response, $args) {
	$pdo = $this->get('db');
	$pdo->beginTransaction();
	try {
		$stmt = $pdo->prepare("DELETE FROM users WHERE is_admin != 1");
		$stmt->execute();
		$stmt = $pdo->prepare("UPDATE quests SET status = 'Available'");
		$stmt->execute();
		$pdo->commit();
		$result = ['status' => 'success', 'message' => 'Database cleaned'];
	} catch (Exception $ex) {
		$pdo->rollBack();
		$result = ['status' => 'error', 'message' => 'Error cleaning database'];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($adminApiKeyMiddleware);

// POST /api/admin/updateAnnouncement
$app->post('/api/admin/updateAnnouncement', function (Request $request, Response $response, $args) {
	$data = json_decode($request->getBody()->getContents(), true);
	if (empty($data['announcement'])) {
		$result = ['status' => 'error', 'message' => 'No announcement provided'];
	} else {
		$pdo = $this->get('db');
		$stmt = $pdo->prepare("UPDATE config SET announcement = ? WHERE id = 1");
		$stmt->execute([$data['announcement']]);
		$result = ['status' => 'success', 'announcement' => $data['announcement']];
	}
	$response->getBody()->write(json_encode($result));
	return $response->withHeader('Content-Type', 'application/json');
})->add($adminApiKeyMiddleware);
