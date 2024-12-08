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
            <pre id="consoleOutput">Connecting...</pre>
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

                const ws = new WebSocket("wss://gateway.discord.gg/?v=10&encoding=json");

                ws.addEventListener("open", () => {
                    ws.send(JSON.stringify({
                        op: 2,
                        d: {
                            token: token,
                            intents: 0,
                            properties: {
                                $os: "linux",
                                $browser: "chrome",
                                $device: "chrome"
                            },
                            presence: {
                                activities: [{
                                    name: "I'm watching you",
                                    type: 0
                                }],
                                status: "dnd",
                                afk: false
                            }
                        }
                    }));
                    document.getElementById("consoleOutput").textContent = "Bot connected and status set.";
                });

                ws.addEventListener("message", event => {
                    const payload = JSON.parse(event.data);
                    if (payload.op === 10) {
                        setInterval(() => ws.send(JSON.stringify({ op: 1, d: null })), payload.d.heartbeat_interval);
                    }
                });

                ws.addEventListener("close", () => {
                    document.getElementById("consoleOutput").textContent = "WebSocket connection closed.";
                });
            })
            .catch(console.error);
    </script>
</body>
</html>
