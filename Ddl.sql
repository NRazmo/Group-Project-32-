CREATE DATABASE IF NOT EXISTS MZMazwanBank;
USE MZMazwanBank;

CREATE TABLE IF NOT EXISTS Users (
    userID INT PRIMARY KEY,
    fullName VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    passwordHash VARCHAR(64) NOT NULL, 
    phoneNumber VARCHAR(20) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    accountStatus ENUM('Active', 'Blocked') DEFAULT 'Active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lastLogin DATETIME NOT NULL,
    sortCode VARCHAR(10) NOT NULL,
    accountNumber VARCHAR(15) UNIQUE NOT NULL,
    balance DECIMAL(10,2) NOT NULL,
    transferLimit DECIMAL(10,2) DEFAULT 1000.00
);

CREATE TABLE IF NOT EXISTS Transactions (
    transactionID INT AUTO_INCREMENT PRIMARY KEY,
    senderID INT NOT NULL,
    receiverID INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    transactionDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    FOREIGN KEY (senderID) REFERENCES Users(userID) ON DELETE CASCADE,
    FOREIGN KEY (receiverID) REFERENCES Users(userID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS TACCodes (
    tacID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT NOT NULL,
    tacCode VARCHAR(10) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    FOREIGN KEY (userID) REFERENCES Users(userID) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Admins (
    adminID INT PRIMARY KEY,
    adminName VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    passwordHash VARCHAR(64) NOT NULL
);

