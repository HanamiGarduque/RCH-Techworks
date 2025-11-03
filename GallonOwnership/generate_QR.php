<?php
require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
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

$stmt = $db->prepare("UPDATE gallon_ownership SET code_value = :cv WHERE ownership_id = :oid");
$stmt->bindParam(':cv', $codeValue);
$stmt->bindParam(':oid', $gallonID);
$stmt->execute();

$folder = __DIR__ . '/qr_codes/';
if (!is_dir($folder)) mkdir($folder, 0755, true);

$filePath = $folder . 'QR-' . $codeValue . '.png';
$relativePath = 'qr_codes/' . basename($filePath);      


// --- GoQR.me API using cURL ---
$goqrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=YOURTEXT&size=200x200&ecc=L
" . urlencode($codeValue) . "&size=200x200";

$ch = curl_init($goqrUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$qrImageData = curl_exec($ch);
curl_close($ch);

// --- Fallback to local generator if API failed ---
if (!$qrImageData || strlen($qrImageData) < 50) {
    $options = new QROptions([
        'version'    => 5,
        'scale'      => 5,
        'outputType' => QROutputInterface::GDIMAGE_PNG,
    ]);
    $qrcode = new QRCode($options);
    $base64Data = $qrcode->render($codeValue);
    $base64Data = str_replace('data:image/png;base64,', '', $base64Data);
    $qrImageData = base64_decode($base64Data);
}

// Save QR image
file_put_contents($filePath, $qrImageData);

// Update DB with QR image path
$stmt2 = $db->prepare("UPDATE gallon_ownership SET qr_image = :img WHERE ownership_id = :oid");
$stmt2->bindParam(':img', $relativePath);
$stmt2->bindParam(':oid', $gallonID);
$stmt2->execute();

// Send JSON response
header('Content-Type: application/json');
echo json_encode([
    'success'    => true,
    'code_value' => $codeValue,
    'image'      => $relativePath,
    'source'     => ($qrImageData ? 'GoQR.me API or Local Generator' : 'None'),
]);
exit;
