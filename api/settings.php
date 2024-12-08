<?php
if(!isset($_COOKIE["token"])) {
    setcookie("token", getenv('DISCORD_BOT_TOKEN'));
  } else {
    echo "Cookie token is set!<br>";
    echo "Value is: " . $_COOKIE["token"];
  }
s
//$botToken = getenv('DISCORD_BOT_TOKEN');
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