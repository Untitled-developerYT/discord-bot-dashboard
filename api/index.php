<?php
// Handle settings update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateSettings'])) {
    setcookie('botToken', $_POST['botToken'], time() + (86400 * 30), '/');
    setcookie('channelID', $_POST['channelID'], time() + (86400 * 30), '/');
    setcookie('guildID', $_POST['guildID'], time() + (86400 * 30), '/');
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Read cookies into variables
$botToken = $_COOKIE['botToken'] ?? '';
$channelId = $_COOKIE['channelID'] ?? '';
$guildId = $_COOKIE['guildID'] ?? '';

// Handle API actions
if (isset($_GET['action'])) {
    header("Content-Type: application/json");

    if ($botToken && $channelId) {
        $curl = curl_init();
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
        
        
        if ($_GET['action'] === 'fetch') {
            // Fetch messages from Discord
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://discord.com/api/v10/channels/$channelId/messages",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bot $botToken",
                    "Content-Type: application/json",
                ],
            ]);
        } elseif ($_GET['action'] === 'send') {
            // Send a message to Discord
            $message = json_encode(["content" => $_POST['message']]);
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://discord.com/api/v10/channels/$channelId/messages",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $message,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bot $botToken",
                    "Content-Type: application/json",
                ],
            ]);
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (in_array($httpCode, [200, 201])) {
            echo $response;
        } else {
            echo json_encode(["error" => "Failed to process request", "status" => $httpCode]);
        }
    } else {
        echo json_encode(["error" => "Bot token or channel ID not set."]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('../assets/background.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            display: flex;
            height: 100vh;
            position: relative;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
						.column {
            flex-direction: column;
        }
						.row {
            flex-direction: row;
        }
        #messageContainer {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column-reverse;
            background-color: #fafafa;
        }
        p {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            background-color: #e0e0e0;
            word-wrap: break-word;
        }
        p strong {
            color: #007bff;
        }
        form {
            display: flex;
            flex-shrink: 0;
            padding: 10px;
            gap: 10px; /* Add spacing between items */
            align-items: center;
            justify-content: space-between;
            background-color: #f1f1f1;
            box-sizing: border-box;
        }
        form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 5px;
        }
        form button {
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            white-space: nowrap;
        }
        form button:hover {
            background-color: #0056b3;
        }
        label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
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
        @media (max-width: 800px) {
            form {
                flex-direction: column; /* Switch to vertical layout */
                align-items: stretch; /* Make inputs take full width */
            }

            form input,
            form button {
                width: 100%; /* Full width for both input and button */
            }
        
        }
    </style>
</head>
<body>
<div class="container row">
<div class="container column">
    <ul class="channel-list" id="channelList"></ul>
</div>


<div class="container column">

<form method="POST">
        <label for="botToken">Bot Token:</label><br>
        <input type="password" id="botToken" name="botToken" value="<?= htmlspecialchars($botToken) ?>" required><br><br>
        <label for="channelID">Channel ID:</label><br>
        <input type="text" id="channelID" name="channelID" value="<?= htmlspecialchars($channelId) ?>" required><br><br>
        <label for="guildID">Guild ID:</label><br>
        <input type="text" id="guildID" name="guildID" value="<?= htmlspecialchars($guildId) ?>" required><br><br>
        <button type="submit" name="updateSettings">Save Settings</button>
   </form>

        <div id="messageContainer">

            <!-- Messages will be dynamically added here -->
        </div>
        <form id="sendMessageForm">
            <input type="text" id="messageInput" placeholder="Type a message..." required>
            <button type="submit">Send</button>
        </form>
</div>
</div>

    <script>
        const messageContainer = document.getElementById("messageContainer");
        const messageForm = document.getElementById("sendMessageForm");
        const messageInput = document.getElementById("messageInput");
        const fetchChannelsButton = document.getElementById('fetchChannels');
        const channelList = document.getElementById('channelList');
        const output = document.getElementById('output');

        // Function to fetch messages
        function fetchMessages() {
            fetch("?action=fetch")
                .then(response => response.json())
                .then(data => {
                    messageContainer.innerHTML = ""; // Clear existing messages
                    data.forEach(message => {
                        const p = document.createElement("p");
                        p.innerHTML = `<strong>${message.author.username}:</strong> ${message.content}`;
                        messageContainer.appendChild(p);
                    });
                })
                .catch(error => console.error("Error fetching messages:", error));
        }

        // Function to send a message
        function sendMessage(content) {
            const formData = new FormData();
            formData.append("message", content);

            fetch("?action=send", {
                method: "POST",
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    console.log("Message sent:", data);
                    fetchMessages(); // Refresh messages after sending
                })
                .catch(error => console.error("Error sending message:", error));
        }

        // Event listener for form submission
        messageForm.addEventListener("submit", event => {
            event.preventDefault(); // Prevent form from reloading the page
            const content = messageInput.value.trim();
            if (content) {
                sendMessage(content); // Send the message
                messageInput.value = ""; // Clear the input
            }
        });

        // Fetch messages every 5 seconds
        setInterval(fetchMessages, 5000);
        fetchMessages(); // Initial fetch



//-----------------------------------------------------------------test
async function sendData(channelId) {
  // Construct a FormData instance
  const formData = new FormData();

  // Add a text field
  formData.append("botToken", "<?= htmlspecialchars($botToken) ?>");
  formData.append("channelID", channelId);
  formData.append("guildID", "<?= htmlspecialchars($guildId) ?>");
  formData.append("updateSettings", "");

  try {
    const response = await fetch("./", {
      method: "POST",
      // Set the FormData instance as the request body
      body: formData,
    });
    console.log(await response.json());
  } catch (e) {
    console.error(e);
  }
  fetchMessages();
}




// channels---------------------------------------------------------------------------------------------------------------
function fetchChannels() {
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
            });
    };

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
        sendData(channelId);
    }


    setInterval(fetchChannels, 60000);
    fetchChannels(); // Initial fetch




    </script>
</body>
</html>
