<?php
$botToken = getenv('DISCORD_BOT_TOKEN');
$channelId = getenv('DISCORD_CHANNEL_ID');
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Chat</title>
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
	
})

ws.addEventListener("close", () => {
   // ... handle closing ...
})


    </script>
</body>
</html>
