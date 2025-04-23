<?php

date_default_timezone_set('Europe/London');

session_start();

include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['email']) && isset($_POST['password'])) {

        $email = $_POST['email'];
        $password = $_POST['password'];

        try {
            $adminQuery = "SELECT adminID, adminName, passwordHash FROM Admins WHERE email = :email";
            $adminStmt = $conn->prepare($adminQuery);
            $adminStmt->bindParam(':email', $email);
            $adminStmt->execute();
            $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && hash('sha256', $password) === $admin['passwordHash']) {
                $_SESSION['adminID'] = $admin['adminID'];
                $_SESSION['adminName'] = $admin['adminName'];
                header("Location: admin_dashboard.php");
                exit();
            }

            $query = "SELECT userID, fullName, passwordHash, lastLogin, accountStatus FROM Users WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && hash('sha256', $password) === $user['passwordHash']) {
                if ($user['accountStatus'] === 'Blocked') {
                    header("Location: account_blocked.php");
                    exit();
                }

                $_SESSION['userID'] = $user['userID'];
                $_SESSION['fullName'] = $user['fullName'];

                $currentTime = new DateTime();
                $lastLoginTime = new DateTime($user['lastLogin']);
                $interval = $currentTime->diff($lastLoginTime);
                $totalSeconds = ($interval->days * 86400) + ($interval->h * 3600) + ($interval->i * 60) + $interval->s;

                $isLessThan5Minutes = $totalSeconds < 300;
                $isMoreThan30Days = $interval->days >= 30;

                if ($isLessThan5Minutes || $isMoreThan30Days) {
                    $_SESSION['require_tac'] = true;
                    header("Location: login_tac_verify.php");
                    exit();
                } else {
                    $updateLogin = $conn->prepare("UPDATE Users SET lastLogin = NOW() WHERE userID = :userID");
                    $updateLogin->bindParam(':userID', $_SESSION['userID']);
                    $updateLogin->execute();
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

