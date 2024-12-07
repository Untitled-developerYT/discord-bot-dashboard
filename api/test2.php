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