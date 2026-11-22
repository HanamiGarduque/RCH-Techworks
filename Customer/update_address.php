<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']); exit;
}
try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) { throw new Exception("Invalid JSON received"); }
    if (!isset($data['user_id']) || empty($data['user_id'])) { throw new Exception("User ID is required"); }
    $userId = intval($data['user_id']);
    $addressData = isset($data['address']) && is_array($data['address']) ? $data['address'] : $data;
    $requiredFields = ['house', 'street', 'barangay', 'city', 'province', 'postal'];
    foreach ($requiredFields as $field) {
        if (!isset($addressData[$field]) || trim($addressData[$field]) === '') { throw new Exception(ucfirst($field) . " is required"); }
    }
    // ===== LIPA ONLY CHECK =====
    $city = strtolower(trim($addressData['city']));
    $province = strtolower(trim($addressData['province']));
    $postal = trim($addressData['postal']);
    $cityOk = in_array($city, ['lipa city', 'lipa'], true);
    $provOk = in_array($province, ['batangas'], true);
    if (!$cityOk || !$provOk) {
        echo json_encode(['success' => false, 'message' => 'We only deliver in Lipa City']); exit;
    }
    if ($postal !== '' && $postal !== '4217') {
        echo json_encode(['success' => false, 'message' => 'We only deliver in Lipa City']); exit;
    }
    $fullAddress = sprintf("%s, %s, %s, %s, %s %s",
        trim($addressData['house']), trim($addressData['street']), trim($addressData['barangay']),
        trim($addressData['city']), trim($addressData['province']), trim($addressData['postal']));
    require_once '../Database/db_connection.php';
    $database = new Database(); $db = $database->getConnect();
    $check = $db->prepare("SELECT user_id FROM users WHERE user_id = :id");
    $check->bindParam(':id', $userId, PDO::PARAM_INT); $check->execute();
    if ($check->rowCount() === 0) { throw new Exception("User not found"); }
    $update = $db->prepare("UPDATE users SET address = :address, updated_at = NOW() WHERE user_id = :id");
    $update->bindParam(':address', $fullAddress, PDO::PARAM_STR);
    $update->bindParam(':id', $userId, PDO::PARAM_INT);
    if ($update->execute()) {
        echo json_encode(["success" => true, "message" => "Address updated successfully", "address" => $fullAddress]);
    } else { throw new Exception("Failed to update address"); }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
