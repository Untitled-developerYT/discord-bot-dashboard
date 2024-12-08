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

if (isset($_GET['action'])) {
    header("Content-Type: application/json");

    if ($botToken && $channelId) {
        if ($_GET['action'] === 'fetch') {
            // Fetch messages from Discord
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://discord.com/api/v10/channels/$channelId/messages",
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
                echo json_encode(["error" => "Failed to fetch messages", "status" => $httpCode]);
            }
            exit();
        } elseif ($_GET['action'] === 'send') {
            // Send a message to Discord
            $message = json_encode(["content" => $_POST['message']]);

            $curl = curl_init();
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

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode === 200 || $httpCode === 201) {
                echo $response;
            } else {
                echo json_encode(["error" => "Failed to send message", "status" => $httpCode]);
            }
            exit();
        }
    } else {
        echo json_encode(["error" => "Bot token or channel ID not set."]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Bot</title>
</head>
<body>
    <h1>Discord Bot Settings</h1>
    <form class="w3-container" method="POST">
        <label for="botToken">Bot Token:</label><br>
        <input class="w3-input" type="text" id="botToken" name="botToken" value="<?= htmlspecialchars($botToken) ?>" required><br><br>
        <label for="channelID">Channel ID:</label><br>
        <input class="w3-input" type="text" id="channelID" name="channelID" value="<?= htmlspecialchars($channelId) ?>" required><br><br>
        <button class="w3-button w3-black" type="submit" name="updateSettings">Save Settings</button>
    </form>

    <h2>Actions</h2>
    <form id="sendMessageForm">
        <input type="text" id="messageInput" placeholder="Type a message..." required>
        <button type="submit">Send Message</button>
    </form>

    <div id="messageContainer"></div>

    <script>
        const messageContainer = document.getElementById("messageContainer");
        const messageForm = document.getElementById("sendMessageForm");
        const messageInput = document.getElementById("messageInput");

        // Fetch messages from Discord
        function fetchMessages() {
            fetch("?action=fetch")
                .then(response => response.json())
                .then(data => {
                    messageContainer.innerHTML = ""; // Clear existing messages
                    data.forEach(message => {
                        const p = document.createElement("p");
                        p.textContent = `${message.author.username}: ${message.content}`;
                        messageContainer.appendChild(p);
                    });
                })
                .catch(error => console.error("Error fetching messages:", error));
        }

        // Send a message to Discord
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
                    fetchMessages(); // Refresh messages
                })
                .catch(error => console.error("Error sending message:", error));
        }

        // Handle form submission
        messageForm.addEventListener("submit", event => {
            event.preventDefault(); // Prevent page reload
            const content = messageInput.value.trim();
            if (content) {
                sendMessage(content); // Send message
                messageInput.value = ""; // Clear input field
            }
        });

        // Fetch messages every 5 seconds
        setInterval(fetchMessages, 5000);
        fetchMessages(); // Initial fetch
    </script>
</body>
</html>
