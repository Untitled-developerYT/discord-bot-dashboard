<?php
// Read cookies into variables
$botToken = $_COOKIE['botToken'] ?? '';
$channelId = $_COOKIE['channelID'] ?? '';

// Use the variables in your PHP code



//$botToken = getenv('DISCORD_BOT_TOKEN');
$channelId = getenv('DISCORD_CHANNEL_ID');

if ($botToken && $channelId) {
    echo "Bot Token: $botToken\n";
    echo "Channel ID: $channelId\n";
} else {
    echo "Bot Token or Channel ID is not set.\n";
}

// Check if this is an AJAX request to fetch messages
if (isset($_GET["action"])) {
    header("Content-Type: application/json");

    if ($_GET["action"] === "fetch") {
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
        curl_close($curl);

        echo $response;
        exit();
    } elseif ($_GET["action"] === "send") {
        // Send a message to Discord
        $message = json_encode(["content" => $_POST["message"]]);

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
        curl_close($curl);

        echo $response;
        exit();
    }
}
?>


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

        .chat-container {
            width: 400px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        #messageContainer {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
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
            padding: 10px;
            background-color: #f1f1f1;
        }

        form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }

        form button {
            padding: 10px 15px;
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="chat-container">
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
