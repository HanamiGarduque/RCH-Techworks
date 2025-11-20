<?php
// Sample address
$address = urlencode($customer['address']);

// URL encode
$encodedAddress = urlencode($address);

// Nominatim API URL
$url = "https://nominatim.openstreetmap.org/search?q={$encodedAddress}&format=json&limit=1";

// Fetch JSON response
$response = file_get_contents($url);

// Convert JSON to PHP array
$data = json_decode($response, true);

if (!empty($data)) {
    $lat = $data[0]['lat'];
    $lng = $data[0]['lon'];

    echo "Address: $address <br>";
    echo "Latitude: $lat <br>";
    echo "Longitude: $lng <br>";

    // Optionally, update your database here
    // Example (PDO):
    /*
    $stmt = $conn->prepare("UPDATE users SET latitude=:lat, longitude=:lng WHERE user_id=:id");
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lng', $lng);
    $stmt->bindValue(':id', 1);
    $stmt->execute();
    */
} else {
    echo "Coordinates not found for this address.";
}
?>
