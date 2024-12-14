<?php
// Handle POST requests to set bot token and guild ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateSettings'])) {
    setcookie('botToken', $_POST['botToken'], time() + (86400 * 30), '/');
    setcookie('guildID', $_POST['guildID'], time() + (86400 * 30), '/');
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Read cookies
$botToken = $_COOKIE['botToken'] ?? '';
$guildId = $_COOKIE['guildID'] ?? '';

// Handle API actions
if (isset($_GET['action']) && $botToken) {
    header("Content-Type: application/json");

    if ($_GET['action'] === 'fetchChannels' && $guildId) {
        // Fetch channels from Discord API
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://discord.com/api/v10/guilds/$guildId/channels",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bot $botToken",
                "Content-Type: application/json",
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            echo $response;
        } else {
            echo json_encode(["error" => "Failed to fetch channels", "status" => $httpCode]);
        }
        exit();
    }
    echo json_encode(["error" => "Invalid action or missing guild ID."]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Discord Channel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .channel-list {
            list-style-type: none;
            padding: 0;
            margin: 20px 0;
        }
        .channel-list li {
            padding: 10px;
            background: #f0f0f0;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .channel-list li:hover {
            background: #ddd;
        }
        .output {
            margin-top: 20px;
            padding: 10px;
            background: #e0ffe0;
            border: 1px solid #b0f0b0;
            border-radius: 5px;
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Choose a Discord Channel</h1>
    <form method="POST">
        <label for="botToken">Bot Token:</label><br>
        <input type="password" id="botToken" name="botToken" value="<?= htmlspecialchars($botToken) ?>" required><br><br>

        <label for="guildID">Guild ID:</label><br>
        <input type="text" id="guildID" name="guildID" value="<?= htmlspecialchars($guildId) ?>" required><br><br>

        <button type="submit" name="updateSettings">Save Settings</button>
    </form>

    <button id="fetchChannels">Fetch Channels</button>
    <ul class="channel-list" id="channelList"></ul>

    <div class="output" id="output"></div>
</div>

<script>
    const fetchChannelsButton = document.getElementById('fetchChannels');
    const channelList = document.getElementById('channelList');
    const output = document.getElementById('output');

    // Fetch channels from the server
    fetchChannelsButton.addEventListener('click', () => {
        fetch('?action=fetchChannels')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                renderChannelList(data);
            })
            .catch(err => {
                console.error("Error fetching channels:", err);
                alert("An error occurred while fetching channels.");
            });
    });

    // Render the channel list
    function renderChannelList(channels) {
        channelList.innerHTML = ''; // Clear previous channels
        channels.forEach(channel => {
            if (channel.type === 0) { // Only show text channels (type 0)
                const li = document.createElement('li');
                li.textContent = `${channel.name} (${channel.id})`;
                li.addEventListener('click', () => selectChannel(channel.id, channel.name));
                channelList.appendChild(li);
            }
        });
    }

    // Select a channel
    function selectChannel(channelId, channelName) {
        output.textContent = `Selected Channel: ${channelName} (ID: ${channelId})`;
        output.style.display = 'block';
    }
</script>
</body>
</html>
