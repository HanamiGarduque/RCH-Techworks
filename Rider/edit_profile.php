<?php
session_start();
require_once '../Database/db_connection.php';

// Example session ID; replace with your actual session variable
$rider_id = $_SESSION['rider_id'] ?? 1;

// Fetch current info
$query = "SELECT phone, email FROM riders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $rider_id);
$stmt->execute();
$result = $stmt->get_result();
$rider = $result->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $updateQuery = "UPDATE riders SET phone = ?, email = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $phone, $email, $rider_id);
    $updateStmt->execute();

    header("Location: account.php?updated=true");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #f5f5f5;
    }

    .header {
      background: linear-gradient(180deg, #1E90FF 0%, #2196F3 100%);
      color: white;
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 20px;
      font-weight: 600;
    }

    .back-btn {
      cursor: pointer;
      font-size: 22px;
    }

    .card {
      background: white;
      margin: 20px;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }

    h3 {
      margin-top: 0;
      font-weight: 600;
    }

    .input-group {
      display: flex;
      flex-direction: column;
      margin-bottom: 15px;
    }

    label {
      font-size: 14px;
      color: #555;
      margin-bottom: 5px;
    }

    input {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      font-family: 'Poppins', sans-serif;
    }

    .btn-save {
      display: block;
      width: 90%;
      margin: 20px auto;
      padding: 12px;
      text-align: center;
      background: #d9d9d9;
      border-radius: 10px;
      border: none;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn-save:hover {
      background: #4CAF50;
      color: white;
    }

    .icon {
      margin-right: 8px;
    }
  </style>
</head>
<body>

  <div class="header">
    <span class="back-btn" onclick="window.location.href='account.php'">←</span>
    <span>Account</span>
    <span>⚙️</span>
  </div>

  <form method="POST" class="card">
    <h3>Account Information</h3>

    <div class="input-group">
      <label for="phone">Phone Number</label>
      <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($rider['phone']); ?>" required>
    </div>

    <div class="input-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($rider['email']); ?>" required>
    </div>

    <button type="submit" class="btn-save">
      <span class="icon">✏️</span> Save Changes
    </button>
  </form>

</body>
</html>
