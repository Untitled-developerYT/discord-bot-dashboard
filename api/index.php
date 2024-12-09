<?php
// Handle settings update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateSettings'])) {
    setcookie('botToken', $_POST['botToken'], time() + (86400 * 30), '/');
    setcookie('channelID', $_POST['channelID'], time() + (86400 * 30), '/');
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Read cookies into variables
$botToken = $_COOKIE['botToken'] ?? '';
$channelId = $_COOKIE['channelID'] ?? '';

// Handle API actions
if (isset($_GET['action'])) {
    header("Content-Type: application/json");

    if ($botToken && $channelId) {
        $curl = curl_init();

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
            flex-direction: column;
            height: 100vh;
            width: 800px;
            max-width: 100%;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #messageContainer {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
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
        @media (max-width: 600px) {
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

<div class="container">

<form method="POST">
        <label for="botToken">Bot Token:</label><br>
        <input type="password" id="botToken" name="botToken" value="<?= htmlspecialchars($botToken) ?>" required><br><br>
        <label for="channelID">Channel ID:</label><br>
        <input type="text" id="channelID" name="channelID" value="<?= htmlspecialchars($channelId) ?>" required><br><br>
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
    <script>
        const messageContainer = document.getElementById("messageContainer");
        const messageForm = document.getElementById("sendMessageForm");
        const messageInput = document.getElementById("messageInput");

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
    </script>
</body>
</html>
