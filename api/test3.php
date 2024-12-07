<?php



// Handle AJAX request to retrieve the token
if (isset($_GET['action']) && $_GET['action'] === 'getToken') {
    // Replace this with your actual bot token
    $botToken = getenv('DISCORD_BOT_TOKEN');
    header("Content-Type: application/json");
    echo json_encode(['token' => $botToken]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Bot Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .status-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <h1>Set Bot Status</h1>
        <button id="setStatus">Set Status to "Do Not Disturb"</button>
        <p id="statusMessage"></p>
    </div>

    <script>
        document.getElementById("setStatus").addEventListener("click", () => {
            // Fetch the bot token securely from the PHP endpoint
            fetch("?action=getToken")
                .then(response => response.json())
                .then(data => {
                    const token = data.token;

                    // Connect to the Discord Gateway WebSocket
                    const ws = new WebSocket("wss://gateway.discord.gg/?v=10&encoding=json");

                    ws.addEventListener("open", () => {
                        // Send the Identify payload with the token
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
                                        type: 0 // "Playing" activity type
                                    }],
                                    status: "dnd", // Set status to "Do Not Disturb"
                                    afk: false
                                }
                            }
                        }));
                        document.getElementById("statusMessage").innerText = "Bot status set!";
                    });

                    ws.addEventListener("message", event => {
                        const payload = JSON.parse(event.data);
                        if (payload.op === 10) {
                            // Start heartbeat
                            setInterval(() => {
                                ws.send(JSON.stringify({ op: 1, d: null }));
                            }, payload.d.heartbeat_interval);
                        }
                    });

                    ws.addEventListener("close", () => {
                        document.getElementById("statusMessage").innerText = "WebSocket connection closed.";
                    });
                })
                .catch(error => {
                    console.error("Error fetching token:", error);
                    document.getElementById("statusMessage").innerText = "Failed to set bot status.";
                });
        });
    </script>
</body>
</html>
