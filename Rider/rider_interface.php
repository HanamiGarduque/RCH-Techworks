<?php
session_start();
require_once '../Database/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Map Interface</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f5f6fa;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        header {
            background-color: #0077b6;
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
        }

        #map {
            flex-grow: 1;
            width: 100%;
        }

        footer {
            background-color: #0077b6;
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>🚚 Rider Delivery Route</header>

    <!-- Map container -->
    <div id="map"></div>

    <footer>© 2025 RCH Delivery System</footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        // Initialize map
        var map = L.map('map').setView([14.5995, 120.9842], 13); // Manila default coordinates

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add a marker (example: delivery destination)
        var marker = L.marker([14.5995, 120.9842]).addTo(map)
            .bindPopup("<b>Delivery Location</b><br>Metro Manila")
            .openPopup();

        // Example of adding another location dynamically
        // L.marker([14.5547, 121.0244]).addTo(map).bindPopup("Makati Delivery Point");
    </script>
</body>
</html>
