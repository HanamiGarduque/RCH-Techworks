<?php
require_once '../Database/db_connection.php';
$database = new Database();
$conn = $database->getConnect();

header('Content-Type: application/json'); // ensure JSON response

if (isset($_POST['code'])) {
    $code = $_POST['code'];

    $query = "SELECT 
                go.ownership_id AS `Gallon ID`,
                go.gallon_type AS `Gallon Type`,
                u.name AS `Owner`,
                go.code_value AS `Code Value`,
                go.status AS `Status`
              FROM gallon_ownership AS go
              LEFT JOIN users AS u ON go.owner_id = u.user_id 
              WHERE go.code_value = :code_value";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':code_value', $code);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'code' => $data['Code Value'],
            'owner' => $data['Owner'] ?? 'Unknown',
            'type' => $data['Gallon Type'] ?? 'Unknown',
            'status' => $data['Status'] ?? 'Unknown'
        ]);
    } else {
        echo json_encode(['error' => 'No record found for this code.']);
    }
} else {
    echo json_encode(['error' => 'No code provided.']);
}
