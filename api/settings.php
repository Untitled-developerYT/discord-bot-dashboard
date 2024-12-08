<?php
if(!isset($_COOKIE["token"])) {
    setcookie("token", getenv('DISCORD_BOT_TOKEN'));
  } else {
    echo "Cookie token is set!<br>";
    echo "Value is: " . $_COOKIE["token"];
  }

//$botToken = getenv('DISCORD_BOT_TOKEN');
$channelId = getenv('DISCORD_CHANNEL_ID');

?>