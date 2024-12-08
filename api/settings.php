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

    elseif ($_GET["action"] === "fetch" && isset($botToken) && isset($channelID)) {
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
    } elseif ($_GET["action"] === "send" && isset($botToken) && isset($channelID)) {
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

        .form {
            display: flex;
            padding: 10px;
            background-color: #f1f1f1;
        }
        .form label {
            display: block;
            margin-bottom: 5px;
        }

        .form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" data-tab="settings">Settings</div>
            <div class="tab" data-tab="console">Console</div>
        </div>

        <div class="tab-content active" id="settings-tab">
            <h2>Bot Settings</h2>
            <div class="form">
                <label for="botToken">Bot Token</label>
                <input type="text" id="botToken" placeholder="Enter bot token">
            </div>
            <div class="form">
                <label for="channelID">Channel ID</label>
                <input type="text" id="channelID" placeholder="Enter channel ID">
            </div>
            <button class="btn" id="saveSettings">Save Settings</button>
        </div>

        <div class="tab-content" id="console-tab">
            <h2>Bot Console</h2>
        <div id="messageContainer">
            <!-- Messages will be dynamically added here -->
        </div>
        <form id="sendMessageForm">
            <input type="text" id="input" placeholder="Type a message..." required>
            <button class="btn" type="submit">Send</button>
        </form>
        </div>
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




        // Tab functionality
        document.querySelectorAll(".tab").forEach(tab => {
            tab.addEventListener("click", () => {
                document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
                document.querySelectorAll(".tab-content").forEach(tc => tc.classList.remove("active"));

                tab.classList.add("active");
                document.getElementById(tab.dataset.tab + "-tab").classList.add("active");
            });
        });

        // Load settings from cookies
        fetch("?action=getSettings")
            .then(response => response.json())
            .then(data => {
                document.getElementById("botToken").value = data.botToken || '';
                document.getElementById("channelID").value = data.channelID || '';
            })
            .catch(console.error);

        // Save settings to cookies
        document.getElementById("saveSettings").addEventListener("click", () => {
            const botToken = document.getElementById("botToken").value;
            const channelID = document.getElementById("channelID").value;

            fetch("?action=saveSettings", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `botToken=${encodeURIComponent(botToken)}&channelID=${encodeURIComponent(channelID)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) alert("Settings saved successfully!");
                })
                .catch(console.error);
        });
    </script>
</body>
</html>
