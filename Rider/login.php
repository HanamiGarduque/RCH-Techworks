<?php
session_start();
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
        font-weight: 600;
        font-size: 22px;
    }

    input[type="email"], input[type="password"] {
        width: 85%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 10px;
        border: 1px solid #ccc;
        outline: none;
        font-size: 14px;
    }

    button {
        width: 90%;
        background-color: #1E90FF;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 20px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.2s;
        margin-top: 10px;
    }

    button:hover {
        background-color: #0b7dda;
    }

    .error {
        color: red;
        font-size: 14px;
        margin-top: 10px;
        margin-bottom: -5px;
    }

    .header {
        position: absolute;
        top: 25px;
        text-align: center;
        color: white;
        width: 100%;
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
    <h1>💧 RCH Water</h1>
    <p>Clean water delivered fresh to your door.</p>
</div>

<div class="container">
    <h2>Rider Login</h2>

    <!-- Show error only when login fails -->
    <?php if(isset($_SESSION['login_error'])): ?>
        <p class="error"><?= $_SESSION['login_error']; ?></p>
        <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form action="login_backend.php" method="POST">
        <input type="email" name="email" placeholder="Enter your email" required><br>
        <input type="password" name="password" placeholder="Enter your password" required><br>
        <button type="submit">Login</button>
    </form>
</div>

<div class="footer">
    © RCH Water Refilling System
</div>

</body>
</html>
