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
<body>
    <h1>Bot Settings</h1>
    <form id="settingsForm">
        <label for="botToken">Bot Token:</label>
        <input type="text" id="botToken" name="botToken" placeholder="Enter bot token"><br><br>
        <label for="channelID">Channel ID:</label>
        <input type="text" id="channelID" name="channelID" placeholder="Enter channel ID"><br><br>
        <button type="button" id="saveSettings">Save</button>
    </form>
    <script>
        // Load settings from cookies
        fetch('?action=getSettings')
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

            fetch('?action=saveSettings', {
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
    </script>
</body>
</html>
