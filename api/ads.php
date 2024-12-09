<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotating Ad System</title>
    <style>
        .ad-container {
            width: 100%;
            max-width: 400px; /* Adjust as needed */
            height: 300px; /* Adjust as needed */
            border: 1px solid #ddd;
            overflow: hidden;
            position: relative;
            background-color: #f4f4f4;
        }

        .ad-container iframe,
        .ad-container img,
        .ad-container video {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures media scales properly */
        }
    </style>
</head>
<body>
    <div class="ad-container">
        <div id="adRotator"></div>
    </div>

    <script>
        // Define the ads: an array of objects with type and source
        const ads = [
            { type: 'image', src: '../assets/background.jpg' },
            { type: 'video', src: 'https://www.w3schools.com/html/mov_bbb.mp4' },
            { type: 'iframe', src: 'https://example.com' },
            { type: 'image', src: 'https://via.placeholder.com/400x300?text=Ad+2' },
        ];

        let currentAdIndex = 0; // Start with the first ad
        const adRotator = document.getElementById("adRotator");

        // Function to display the current ad
        function displayAd() {
            const ad = ads[currentAdIndex];
            adRotator.innerHTML = ""; // Clear the previous ad

            if (ad.type === "image") {
                const img = document.createElement("img");
                img.src = ad.src;
                img.alt = "Advertisement";
                adRotator.appendChild(img);
            } else if (ad.type === "video") {
                const video = document.createElement("video");
                video.src = ad.src;
                video.autoplay = true;
                video.muted = true;
                video.loop = true;
                adRotator.appendChild(video);
            } else if (ad.type === "iframe") {
                const iframe = document.createElement("iframe");
                iframe.src = ad.src;
                iframe.frameBorder = "0";
                iframe.allowFullscreen = true;
                adRotator.appendChild(iframe);
            }

            // Move to the next ad, looping back to the start if needed
            currentAdIndex = (currentAdIndex + 1) % ads.length;
        }

        // Start rotating ads every 5 seconds
        displayAd(); // Display the first ad immediately
        setInterval(displayAd, 5000); // Change ads every 5 seconds
    </script>
</body>
</html>
