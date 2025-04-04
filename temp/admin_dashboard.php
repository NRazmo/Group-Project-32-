<?php
session_start();


require 'connection.php';

$success = '';
$error = '';

// fetch from database
$stmt = $conn->prepare("SELECT userID, fullName FROM Users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update data 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_POST['userID'];
    $transferLimit = floatval($_POST['transferLimit']);
    $accountStatus = $_POST['accountStatus'];
    $loginInterval = intval($_POST['loginInterval']);

    // Update the user's transfer limit, account status, and login interval
    try {
        $stmt = $conn->prepare("UPDATE Users SET accountStatus = :accountStatus, lastLogin = NOW() WHERE userID = :userID");
        $stmt->bindParam(':accountStatus', $accountStatus);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();

        // update the transfer limit
        $stmt = $conn->prepare("UPDATE Users SET transferLimit = :transferLimit WHERE userID = :userID");
        $stmt->bindParam(':transferLimit', $transferLimit);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();

        // update the login interval
        // placehold not created yet but should function similar 
        $stmt = $conn->prepare("UPDATE Users SET loginInterval = :loginInterval WHERE userID = :userID");
        $stmt->bindParam(':loginInterval', $loginInterval);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();

        $success = "User information updated successfully.";
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

        <?php if ($success): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="admin_dashboard.php">
            <label for="userID">Select User:</label>
            <select id="userID" name="userID" required>
                <option value="">-- Select User --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['userID']; ?>"><?php echo $user['fullName']; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="transferLimit">Transfer Limit (£):</label>
            <input type="number" id="transferLimit" name="transferLimit" step="0.01" min="0" required>

            <label for="accountStatus">Account Status:</label>
            <select id="accountStatus" name="accountStatus" required>
                <option value="Active">Active</option>
                <option value="Blocked">Blocked</option>
            </select>

            <label for="loginInterval">Login Interval (minutes):</label>
            <input type="number" id="loginInterval" name="loginInterval" min="1" required>

            <button type="submit">Update User</button>
        </form>

        <div class="button-container">
            <button type="button" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
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
        width: 400px;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    label {
        font-weight: bold;
        display: block;
        margin-top: 15px;
        font-size: 14px;
    }

    select, input {
        width: 100%;
        padding: 12px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }

    .button-container {
        display: flex;
        justify-content: center;
        margin-top: 18px;
    }

    button {
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
    }

    button:hover {
        background: #0056b3;
        color: white;
    }
</style>
