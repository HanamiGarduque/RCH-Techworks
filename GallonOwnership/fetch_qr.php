<?php
require_once '../Database/db_connection.php';
$database = new Database();
$conn = $database->getConnect();

if (isset($_POST['code'])) {
    $code = $_POST['code'];

    // fetch gallon ownership details based on scanned code
    $query = "SELECT 
                go.ownership_id AS `Gallon ID`,
                go.gallon_type AS `Gallon Type`,
                u.name AS `Owner`,
                go.code_value AS `Code Value`,
                go.status AS `Status`,
                go.qr_image AS `QR Image`
              FROM gallon_ownership AS go
              LEFT JOIN users AS u ON go.owner_id = u.user_id 
              WHERE go.code_value = :code_value";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':code_value', $code);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        
        echo "Code Value: " . htmlspecialchars($data['Code Value']) . "<br>";
        echo "Customer: " . htmlspecialchars($data['Owner']) . "<br>";

        echo "Gallon Type: " . htmlspecialchars($data['Gallon Type']) . "<br>";
        echo "Status: " . htmlspecialchars($data['Status']);
    } else {
        echo "No record found for this code.";
    }
}
