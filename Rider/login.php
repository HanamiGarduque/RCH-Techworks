<?php
// Include database connection
require_once '../database/Database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->getConnect();

    $query = "SELECT * FROM riders WHERE username = :username AND password = :password";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['rider_username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCH Water | Rider Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #2196F3;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 360px;
            background-color: white;
            border-radius: 25px;
            padding: 30px 20px;
            box-shadow: 0px 4px 20px rgba(0,0,0,0.2);
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #000;
        }

        input[type="text"], input[type="password"] {
            width: 85%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 14px;
        }

        button {
            background-color: #1E90FF;
            color: white;
            border: none;
            padding: 10px 40px;
            border-radius: 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #0b7dda;
        }

        .social-login {
            display: flex;
            justify-content: center;
            margin-top: 15px;
            gap: 20px;
        }

        .social-login img {
            width: 30px;
            cursor: pointer;
        }

        .header {
            position: absolute;
            top: 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            margin: 0;
            font-weight: 600;
        }

        .footer {
            position: absolute;
            bottom: 10px;
            color: white;
            font-size: 12px;
            text-align: center;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Decorative circles */
        .circle1, .circle2 {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            z-index: -1;
        }

        .circle1 {
            width: 150px;
            height: 150px;
            top: 30px;
            left: 40px;
        }

        .circle2 {
            width: 120px;
            height: 120px;
            bottom: 40px;
            right: 40px;
        }
    </style>
</head>
<body>

<div class="circle1"></div>
<div class="circle2"></div>

<div class="header">
    <h1>💧RCH Water</h1>
    <p>Join us today and enjoy fresh, clean water delivered right at your doorstep.</p>
</div>

<div class="container">
    <h2>Login Account</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Enter your username" required><br>
        <input type="password" name="password" placeholder="Enter your password" required><br>
        <button type="submit">Login</button>
    </form>

    <div class="error">
        <?php if (!empty($error)) echo $error; ?>
    </div>

    <p>OR</p>
    <div class="social-login">
        <img src="https://cdn-icons-png.flaticon.com/512/124/124010.png" alt="Facebook">
        <img src="https://cdn-icons-png.flaticon.com/512/300/300221.png" alt="Google">
    </div>
</div>

<div class="footer">
    © RCH Water Refilling System
</div>

</body>
</html>
