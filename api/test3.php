<?php
// Your bot token
$botToken = getenv('DISCORD_BOT_TOKEN');

// Endpoint for updating bot presence
$url = "https://discord.com/api/v10/users/@me/settings";

// Data for setting the bot's status and activity
$data = [
    "status" => "dnd", // "dnd" for "Do Not Disturb"
    "custom_status" => [
        "text" => "I'm watching you"
    ]
];

// Function to send the HTTP request
function sendCurlRequest($url, $data, $botToken) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PATCH", // Use PATCH to update settings
        CURLOPT_HTTPHEADER => [
            "Authorization: Bot $botToken",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

// Make the API call
$response = sendCurlRequest($url, $data, $botToken);

// Output the response
if (isset($response['message'])) {
    echo "Error: " . $response['message'];
} else {
    echo "Status and activity updated successfully!";
}