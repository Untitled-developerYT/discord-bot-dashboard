<?php
// Handle AJAX requests to retrieve and update token/channel settings
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'getSettings') {
        // Read user settings from cookies
        $botToken = $_COOKIE['botToken'] ?? '';
        $channelID = $_COOKIE['channelID'] ?? '';
        header("Content-Type: application/json");
        echo json_encode(['botToken' => $botToken, 'channelID' => $channelID]);
        exit();
    } elseif ($_GET['action'] === 'saveSettings') {
        // Save user settings to cookies
        $botToken = $_POST['botToken'] ?? '';
        $channelID = $_POST['channelID'] ?? '';
        setcookie("botToken", $botToken, time() + (86400 * 30), "/"); // Save for 30 days
        setcookie("channelID", $channelID, time() + (86400 * 30), "/");
        echo json_encode(['success' => true]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bot Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f8;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .tabs {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .tab {
            cursor: pointer;
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 10px 10px 0 0;
            background-color: #f9f9f9;
        }

        .tab.active {
            background-color: #ffffff;
            border-bottom: 1px solid white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .hidden {
            display: none;
        }
 

  