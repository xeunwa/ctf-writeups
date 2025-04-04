<?php

session_start();

$admin_hash = md5("admin");

if (!isset($_COOKIE['user']) || $_COOKIE['user'] !== $admin_hash) {
    echo "<p class='error'>Access Denied</p>";
    exit;}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles/adminpage.css">
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, Admin!</p>

        <!-- This is for our intern, he doesn't know how to look through directories in linux -->
        <!-- Thankfully he won't be with us much longer -->
        <h2>Check if a File Exists</h2>
        <form method="POST" action="check_file.php">
            <input type="text" name="filename" placeholder="Enter file name" required>
            <button type="submit">Check File</button>
        </form>

        <?php
        if (isset($_SESSION['file_check_result'])) {
            echo "<p class='success'>" . htmlentities($_SESSION['file_check_result']) . "</p>";
            unset($_SESSION['file_check_result']);
        }
        ?>

<h2>API Actions</h2>
<form id="apiForm" method="POST" action="api.php">
    <input type="text" id="userInput" name="userInput" placeholder="User ID" required>
    <input type="text" id="actionInput" name="actionInput" placeholder="Action" required>
    <button type="submit" id="getLogsButton">Fetch User Logs</button>
    <button type="submit" id="updateStatusButton">Update User Status</button>
</form>
        <div id="formContainer">
            <?php include('checkform.php'); ?>
        </div>


    </div>
    <script>
// Adding a submit event listener to the form
document.getElementById("apiForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let userInput = document.getElementById('userInput').value;
    let actionInput = document.getElementById('actionInput').value;
    let actionType = event.submitter.id;

    // Prepare form data to be sent
    let formData = new URLSearchParams({ userInput, actionInput });

    if (actionType === "getLogsButton") {
        fetch('api.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                // Handle success
                console.log("System Logs:", data);
                document.getElementById('userInput').value = '';
                document.getElementById('actionInput').value = '';
            })
            .catch(error => console.error("Error fetching system logs:", error));
    } else if (actionType === "updateStatusButton") {
        fetch('api.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                // Handle success
                console.log("User Status Updated:", data);
                document.getElementById('userInput').value = '';
                document.getElementById('actionInput').value = '';
            })
            .catch(error => console.error("Error updating user status:", error));
    }
});

</script>
</body>
</html>