<?php 
session_start();

if (!isset($_SESSION['adminID'])) {
    header("Location: login.php");
    exit();
}

require 'connection.php';

$success = '';
$error = '';

$stmt = $conn->prepare("SELECT userID, fullName, accountStatus FROM Users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateUser'])) {
    $userID = $_POST['userID'];
    $accountStatus = $_POST['accountStatus'];

    try {
        $stmt = $conn->prepare("UPDATE Users SET accountStatus = :accountStatus WHERE userID = :userID");
        $stmt->bindParam(':accountStatus', $accountStatus);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();

        if ($accountStatus === 'Blocked') {
            $success = "User account has been successfully blocked.";
        } else {
            $success = "User account status updated successfully.";
        }

    } catch (Exception $e) {
        $error = "Error updating user information: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['adminName']); ?></p>

        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="admin_dashboard.php">
            <label for="userID">Select User:</label>
            <select id="userID" name="userID" required>
                <option value="">-- Select User --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['userID']; ?>" <?php echo (isset($_POST['userID']) && $_POST['userID'] == $user['userID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['fullName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="accountStatus">Account Status:</label>
            <select id="accountStatus" name="accountStatus" required>
                <option value="Active" <?php echo (isset($_POST['accountStatus']) && $_POST['accountStatus'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Blocked" <?php echo (isset($_POST['accountStatus']) && $_POST['accountStatus'] == 'Blocked') ? 'selected' : ''; ?>>Blocked</option>
            </select>

            <label for="transferLimit">Fund Transfer Limit (£):</label>
            <input type="number" id="transferLimit" name="transferLimit" step="0.01" min="0" value="<?php echo isset($_POST['transferLimit']) ? $_POST['transferLimit'] : '1000.00'; ?>">

            <label for="loginInterval">Login Interval (days):</label>
            <input type="number" id="loginInterval" name="loginInterval" min="1" value="<?php echo isset($_POST['loginInterval']) ? $_POST['loginInterval'] : '30'; ?>">

            <button type="submit" name="updateUser">Update User</button>
        </form>

        <div class="button-container">
            <button type="button" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>
</body>
</html>

<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EFFFFC;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .admin-dashboard {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
            width: 450px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }

        select {
            width: calc(100% - 24px);
            padding: 12px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        input[type="number"] {
            width: calc(100% - 24px);
            padding: 12px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            background-color: #007bff;
            color: white;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success {
            color: green;
            margin-top: 15px;
            text-align: center;
        }

        .error {
            color: red;
            margin-top: 15px;
            text-align: center;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        small {
            display: block;
            margin-top: 5px;
            color: #777;
            font-size: 0.9em;
        }
    </style>
