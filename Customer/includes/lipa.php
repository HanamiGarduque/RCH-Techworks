<?php
// Customer/includes/lipa.php
// Lipa City only - shared by Sign-up, Order, Profile, and all maps.

// Bounding box for Lipa City, Batangas
// South: ~San Jose border, North: ~Rosario border
// West: ~Malvar border, East: ~San Juan border
define('LIPA_MIN_LAT', 13.8600);
define('LIPA_MAX_LAT', 14.0200);
define('LIPA_MIN_LNG', 121.0800);
define('LIPA_MAX_LNG', 121.2400);
define('LIPA_CENTER_LAT', 13.9411);
define('LIPA_CENTER_LNG', 121.1628);

function isInLipa($lat, $lng) {
    if ($lat === null || $lng === null) return false;
    $lat = (float)$lat; $lng = (float)$lng;
    return $lat >= LIPA_MIN_LAT && $lat <= LIPA_MAX_LAT
        && $lng >= LIPA_MIN_LNG && $lng <= LIPA_MAX_LNG;
}

// Text check: "Lipa", "Lipa City" must appear, and must NOT be another city.
// Use this for typed address when you have no lat/lng yet.
function addressSaysLipa($city, $fullAddress = '') {
    $text = strtolower(trim($city . ' ' . $fullAddress));
    if (strpos($text, 'lipa') === false) return false;
    // Reject common look-alikes / other cities typed with Lipa as barangay
    $outside = ['manila','quezon','makati','calamba','tanauan','sto. tomas','santo tomas','batangas city','rosario','san jose','malvar','mataasnakahoy','balete','padre garcia'];
    // Only reject if city field itself is another city
    $cityLow = strtolower(trim($city));
    foreach ($outside as $o) {
        if ($cityLow !== '' && $cityLow !== 'lipa' && $cityLow !== 'lipa city' && strpos($cityLow, $o) !== false) return false;
    }
    return true;
}
?>