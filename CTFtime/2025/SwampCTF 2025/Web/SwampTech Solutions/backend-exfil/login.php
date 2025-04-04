<?php
session_start();
$users = [
    'guest' => 'iambutalowlyguest',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (isset($users[$user]) && $pass === $users[$user]) {
        $user_hash = md5($user);

        $_SESSION['user'] = $user;
        setcookie('user', $user_hash, time() + 3600, '/', '', false, true);

        header('Location: dashboard.php');
        exit;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Clear the error message after it's displayed
?>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
    <div class="container">
        <h1>Login to your dashboard</h1>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required><br><br><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
<!-- TEST USER CREDENTIALS -->
 <!-- guest:iambutalowlyguest -->
</html>