<?php
$dsn = "mysql:host=localhost;port=3307;dbname=MZMazwanBank;charset=utf8mb4";
$username = "root"; 
$password = ""; 

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
