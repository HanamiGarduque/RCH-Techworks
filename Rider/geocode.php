<?php
// Make sure you already have the customer address from database
// Example: $customer['address'] = "Purok 4 Mojon-Tampoy, San Jose, Batangas";

// Address from DB
$address = $customer['address'];

// URL encode ONCE
$encodedAddress = urlencode($address);

// Nominatim API URL
$url = "https://nominatim.openstreetmap.org/search?q={$encodedAddress}&format=json&limit=1";

// Set User Agent (Nominatim requires it)
$opts = [
    "http" => [
        "header" => "User-Agent: PHP"
    ]
];
$context = stream_context_create($opts);

// Fetch API response
$response = file_get_contents($url, false, $context);

// Convert JSON to array
$data = json_decode($response, true);

if (!empty($data)) {

    $lat = $data[0]['lat'];
    $lng = $data[0]['lon'];

    echo "Address: $address <br>";
    echo "Latitude: $lat <br>";
    echo "Longitude: $lng <br>";

    // OPTIONAL: Update users table with coordinates
   
    $stmt = $conn->prepare("
        UPDATE users 
        SET latitude = :lat, longitude = :lng 
        WHERE user_id = :id
    ");
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lng', $lng);
    $stmt->bindValue(':id', $customer['user_id']); // dynamic user
    $stmt->execute();
    

} else {
    echo "Coordinates not found for this address.";
}
?>
