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










    <div class="container">
        <div class="tabs">
            <div class="tab active" data-tab="settings">Settings</div>
            <div class="tab" data-tab="console">Console</div>
        </div>

        <div class="tab-content active" id="settings-tab">
            <h2>Bot Settings</h2>
            <div class="form-group">
                <label for="botToken">Bot Token</label>
                <input type="text" id="botToken" placeholder="Enter bot token">
            </div>
            <div class="form-group">
                <label for="channelID">Channel ID</label>
                <input type="text" id="channelID" placeholder="Enter channel ID">
            </div>
            <button class="btn" id="saveSettings">Save Settings</button>
        </div>

        <div class="tab-content" id="console-tab">
            <h2>Bot Console</h2>
            <div class="chat-container">
        <div id="messageContainer">
            <!-- Messages will be dynamically added here -->
        </div>
        <form id="sendMessageForm">
            <input type="text" id="messageInput" placeholder="Type a message..." required>
            <button type="submit">Send</button>
        </form>
        </div>
        </div>
    </div>

    <script>
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

        // Connect to Discord Gateway
        fetch("?action=getSettings")
            .then(response => response.json())
            .then(data => {
                const token = data.botToken;
                if (!token) {
                    document.getElementById("consoleOutput").textContent = "Error: No bot token set.";
                    return;
                }

                
    </script>
</body>
</html>
