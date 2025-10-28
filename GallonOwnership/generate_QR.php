<?php
require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
require_once '../Database/db_connection.php';

$database = new Database();
$db = $database->getConnect();

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$gallonID = intval($input['gallonID'] ?? 0);

if ($gallonID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// Generate code value
$codeValue = 'GAL-' . date('Ymd') . str_pad($gallonID, 3, '0', STR_PAD_LEFT);


$stmt = $db->prepare("UPDATE gallon_ownership SET code_value = :cv WHERE ownership_id = :oid");
$stmt->bindParam(':cv', $codeValue);
$stmt->bindParam(':oid', $gallonID);
$ok = $stmt->execute();

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'Failed to update code value']);
    exit;
}

$options = new QROptions([
    'version'    => 5,
    'scale'      => 5,
'outputType' => QROutputInterface::GDIMAGE_PNG,
]);

$qrcode = new QRCode($options);

// Generate base64 PNG data
$base64Data = $qrcode->render($codeValue);

// Remove header if present
$base64Data = str_replace('data:image/png;base64,', '', $base64Data);

// Convert base64 → binary PNG
$binaryData = base64_decode($base64Data);

// Ensure folder exists
$folder = __DIR__ . '/qr_codes/';
if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}

$filePath = $folder . 'QR-'. $codeValue . '.png';

// ✅ Save actual PNG file
file_put_contents($filePath, $binaryData);

// Relative path for front-end use
$relativePath = 'qr_codes/' . basename($filePath);



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
]);
exit;
?>