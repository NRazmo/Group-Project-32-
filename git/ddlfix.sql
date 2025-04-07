DROP TABLE IF EXISTS TACCodes;
DROP TABLE IF EXISTS Transactions;

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
    isUsed BOOLEAN DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES Users(userID) ON DELETE CASCADE
);
