<?php
// Start session
session_start();

$admin_hash = md5("admin");

// Check if the user cookie matches the admin hash
if (!isset($_COOKIE['user']) || $_COOKIE['user'] !== $admin_hash) {
    echo "<p class='error'>Access Denied</p>";
    exit;}

// Ensure that data is received and processed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $userInput = isset($_POST['userInput']) ? $_POST['userInput'] : '';
    $actionInput = isset($_POST['actionInput']) ? $_POST['actionInput'] : '';

    // Action processing based on the input
    if ($userInput && $actionInput) {
        if ($actionInput === 'Get System Logs') {
            echo json_encode(['status' => 'success', 'message' => "System logs for user $userInput retrieved."]);
        } elseif ($actionInput === 'Update User Status') {
            echo json_encode(['status' => 'success', 'message' => "User status for $userInput updated."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing user input or action input.']);
    }
}
?>