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

<html>
    <script>
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
                                    name: "A user is online",
                                    type: 0 // "Playing" activity type
                                }],
                                status: "dnd", // Set status to "Do Not Disturb"
                                afk: false
                            }
                        }
                    }));
                    console.log("Bot status set successfully.");
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
                    console.log("WebSocket connection closed.");
                });
            })
            .catch(error => {
                console.error("Error fetching token:", error);
            });
    </script>
</html>