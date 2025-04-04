<?php
session_start();
include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        try {
            $query = "SELECT * FROM Users WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && hash('sha256', $password) === $user['passwordHash']) {
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['fullName'] = $user['fullName'];
                if ($user['fullName'] === 'John Doe' || $user['fullName'] === 'Michael Scott') {
                    header("Location: tac_verify.php");
                    exit();
                } else {
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                echo "<p style='color: red;'>Invalid email or password.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Please enter both email and password.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - MZMazwan Banking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>MZMazwan Banking</h1>
        <a href="login.php"> 
            <img src="Assets/icons8-banking-100.png" alt="Bank" width="70" height="70">
        </a>
    </header>

    <main>
        <div class="login-box">
            <form action="login.php" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit">Login</button>
            </form>
        </div>
    </main>
</body>
</html>
