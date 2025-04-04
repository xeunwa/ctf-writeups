<?php
// check_file.php - Check if a file exists

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filename = $_POST['filename'] ?? '';
    $file_path = './' . $filename;  // Assuming files are stored in a directory named 'uploads'

    if (file_exists($file_path)) {
        $_SESSION['file_check_result'] = "File '$filename' exists.";
    } else {
        $_SESSION['file_check_result'] = "File '$filename' does not exist.";
    }

    header('Location: adminpage.php');
    exit;
}
?>