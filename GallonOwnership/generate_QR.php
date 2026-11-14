<?php
// FIX: No vendor/autoload.php needed anymore.
// Old file died here because C:\xampp\htdocs\RCH-Techworks-4\vendor\autoload.php does not exist.
// QR is now made 100% via free API (GoQR + QuickChart fallback), no composer library required.
header('Content-Type: application/json');

require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

$input = json_decode(file_get_contents('php://input'), true);
$gallonID = intval($input['gallonID'] ?? 0);

if ($gallonID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$codeValue = 'GAL-' . date('Ymd') . str_pad($gallonID, 3, '0', STR_PAD_LEFT);

// --- 1) Try GoQR.me API (FIX: old URL had data=YOURTEXT + a newline inside it) ---
$qrImageData = null;
$goqrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($codeValue) . "&size=300x300&ecc=M&margin=10";

$ch = curl_init($goqrUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // XAMPP local often fails SSL verify
$qrImageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Reject HTML error pages / tiny responses (not a real PNG)
if (!$qrImageData || $httpCode !== 200 || strlen($qrImageData) < 500 || substr($qrImageData, 0, 8) !== "\x89PNG\r\n\x1a\n") {
    $qrImageData = null;
}

// --- 2) Fallback: QuickChart QR API (if GoQR is down / blocked by VPN) ---
if (!$qrImageData) {
    $quickUrl = "https://quickchart.io/qr?text=" . urlencode($codeValue) . "&size=300&margin=2";
    $ch2 = curl_init($quickUrl);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $qrImageData = curl_exec($ch2);
    $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    if (!$qrImageData || $httpCode2 !== 200 || strlen($qrImageData) < 500 || substr($qrImageData, 0, 8) !== "\x89PNG\r\n\x1a\n") {
        $qrImageData = null;
    }
}

// --- 3) If BOTH APIs failed, do NOT save an empty file / do NOT update DB ---
if (!$qrImageData) {
    echo json_encode(['success' => false, 'message' => 'Could not generate QR image. Check internet / turn VPN OFF, then try again. Code was NOT saved.']);
    exit;
}

// --- 4) Only now: save code + image to DB ---
$stmt = $db->prepare("UPDATE gallon_ownership SET code_value = :cv WHERE ownership_id = :oid");
$stmt->bindParam(':cv', $codeValue);
$stmt->bindParam(':oid', $gallonID);
$stmt->execute();

$folder = __DIR__ . '/qr_codes/';
if (!is_dir($folder)) mkdir($folder, 0755, true);

$filePath = $folder . 'QR-' . $codeValue . '.png';
$relativePath = 'qr_codes/' . basename($filePath);

file_put_contents($filePath, $qrImageData);

// Update DB with QR image path
$stmt2 = $db->prepare("UPDATE gallon_ownership SET qr_image = :img WHERE ownership_id = :oid");
$stmt2->bindParam(':img', $relativePath);
$stmt2->bindParam(':oid', $gallonID);
$stmt2->execute();

echo json_encode([
    'success'    => true,
    'code_value' => $codeValue,
    'image'      => $relativePath,
    'source'     => 'QR API',
]);
exit;