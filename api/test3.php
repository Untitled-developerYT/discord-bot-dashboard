<?php
// Your bot token
$botToken = getenv('DISCORD_BOT_TOKEN');


// Gateway URL
$gatewayUrl = "wss://gateway.discord.gg/?v=10&encoding=json";

// JSON payload to set the bot's presence
$presencePayload = json_encode([
    "op" => 2, // Identify opcode
    "d" => [
        "token" => $botToken,
        "intents" => 0, // No events, just set status
        "properties" => [
            "\$os" => "linux",
            "\$browser" => "custom",
            "\$device" => "custom",
        ],
        "presence" => [
            "status" => "dnd", // Set status to Do Not Disturb
            "activities" => [
                [
                    "name" => "I'm watching you", // Custom activity text
                    "type" => 0, // "Playing" activity type
                ]
            ],
            "afk" => false,
        ]
    ]
]);

// Function to send WebSocket messages
function sendWebSocketMessage($url, $payload) {
    // Open WebSocket connection
    $connection = fsockopen("ssl://" . parse_url($url, PHP_URL_HOST), 443);

    if (!$connection) {
        die("Failed to connect to WebSocket");
    }

    $headers = "GET " . parse_url($url, PHP_URL_PATH) . " HTTP/1.1\r\n" .
               "Host: " . parse_url($url, PHP_URL_HOST) . "\r\n" .
               "Upgrade: websocket\r\n" .
               "Connection: Upgrade\r\n" .
               "Sec-WebSocket-Key: " . base64_encode(random_bytes(16)) . "\r\n" .
               "Sec-WebSocket-Version: 13\r\n\r\n";

    fwrite($connection, $headers);

    // Wait for handshake response
    while (!feof($connection)) {
        $response = fgets($connection);
        if (strpos($response, "\r\n\r\n") !== false) {
            break;
        }
    }

    // Send payload
    $frame = chr(129) . chr(strlen($payload)) . $payload; // WebSocket frame format
    fwrite($connection, $frame);

    // Read server response
    while (!feof($connection)) {
        $serverResponse = fread($connection, 1024);
        echo "Server Response: " . $serverResponse . "\n";
        break; // Only read the first response
    }

    fclose($connection);
}

// Send the presence payload to the WebSocket
sendWebSocketMessage($gatewayUrl, $presencePayload);

echo "Status and activity sent!";
