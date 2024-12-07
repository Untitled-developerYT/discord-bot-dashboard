<?php
$botToken = getenv('DISCORD_BOT_TOKEN');
$channelId = getenv('DISCORD_CHANNEL_ID');
// Check if this is an AJAX request to fetch messages
if (isset($_GET["action"]) && $_GET["action"] === "fetch") {
    // Fetch messages from Discord API
    header("Content-Type: application/json");

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://discord.com/api/v10/channels/$channelId/messages",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Authorization: Bot $botToken", // Replace with your bot token
        ],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);

    echo $response;
	exit(); // Stop further execution for AJAX requests
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Discord Messages</title>
        <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f8;
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
            margin-right: 5px;
        }

        .message-right {
            align-self: flex-end;
            background-color: #007bff;
            color: white;
        }

        .message-left {
            align-self: flex-start;
            background-color: #e0e0e0;
            color: #333;
        }

        .chat-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: green; /* Default to green for "online" */
        }

        .status-offline {
            background-color: red;
        }

        .status-idle {
            background-color: yellow;
        }

    </style>

</head>
<body>
    <h1>Discord Channel Messages</h1>
    <div id="messageContainer">
        <!-- Messages will be dynamically inserted here -->
    </div>

    <script>
        // Function to fetch and update messages
       function fetchMessages() {
    const container = document.getElementById("messageContainer");

    // Calculate if the user is near the bottom
    const isAtBottom = Math.abs(container.scrollHeight - container.scrollTop - container.clientHeight) < 50;

    fetch('?action=fetch') // AJAX request to this PHP file
        .then(response => response.json())
        .then(data => {
            container.innerHTML = ''; // Clear existing messages

            // Add messages to the container
            data.forEach(message => {
                const messageElement = document.createElement("p");
                messageElement.innerHTML = `<strong>${message.author.username}:</strong> ${message.content}`;
                container.appendChild(messageElement);
            });

            // Auto-scroll only if the user is already near the bottom
            if (isAtBottom) {
                container.scrollTop = container.scrollHeight;
            }
        })
        //.catch(error => console.error('Error fetching messages:', error));
}

// Fetch messages every 5 seconds
setInterval(fetchMessages, 5000);

// Initial fetch
fetchMessages();
  let ws = new WebSocket("wss://gateway.discord.gg/?v=6&encoding=json"),
    interval = 0,
    token = "<?php echo $botToken;?>"
;

ws.addEventListener("open", () => {
	ws.send(JSON.stringify({
		op: 2,
		d: {
			token,
			intents: 512,
			properties: {
				$os: "linux",
				$browser: "chrome",
				$device: "chrome",
			},
			"presence": {
				"activities": [{
					"name": "I'm watching you",
					"type": 1
				}],
				"status": "dnd",
				"since": 91879201,
				"afk": false
			},
        },
    }));
})

ws.addEventListener("message", function incoming(data) {
   let payload = JSON.parse(data.data);

   const { t, event, op, d } = payload;

   // Setup heartbeats to keep the connection alive
   switch (op) {
      case 10:
         setInterval(() => {
            ws.send(JSON.stringify({ op: 1, d: null }));
         }, d.heartbeat_interval);
         break;
   }

   // Event type
	switch (t) {
		case "MESSAGE_CREATE":
		const para = document.createElement("p");
		const node = document.createTextNode(`[${d.author.username}]: ${d.content} ${d.channel_id}`);
		para.appendChild(node);
		const element = document.getElementById("messageContainer");
		element.appendChild(para);
		
	}
})

ws.addEventListener("close", () => {
   // ... handle closing ...
})


    </script>
</body>
</html>
