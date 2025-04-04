INSERT INTO Users (userID, fullName, email, passwordHash, phoneNumber, address, lastLogin, sortCode, accountNumber, balance)  
VALUES  
(1, 'John Doe', 'john.doe@icloud.com', SHA2('password123', 256), '07647875328', '17 Main Street', DATE_SUB(NOW(), INTERVAL 3 MINUTE), '037726', '53783027', 1500.00),  
(2, 'Michael Scott', 'michael.scott@gmail.com', SHA2('security429', 256), '07983627846', '99 West Avenue', DATE_SUB(NOW(), INTERVAL 32 DAY), '474936', '93568369', 2456.23),  
(3, 'Pam Halpert', 'pam.h@yahoo.com', SHA2('pamPass', 256), '07374894743', '888 Tree St', DATE_SUB(NOW(), INTERVAL 4 HOUR), '846374', '94783947', 530.56),  
(4, 'Lisa May', 'lisa.may@hotmail.com', SHA2('lissSecure', 256), '07925738263', '69 Road Way', DATE_SUB(NOW(), INTERVAL 9 HOUR), '478372', '47894637', 3250.99);  

INSERT INTO Admins (adminID, adminName, email, passwordHash) VALUES
(1, 'Admin User', 'admin@mzmazwanbank.com', SHA2('adminSecurePass', 256));

INSERT INTO Transactions (transactionID, senderID, receiverID, amount, status) VALUES
(1, 1, 2, 100.50, 'Completed'),
(2, 2, 3, 250.00, 'Pending'),
(3, 3, 4, 75.25, 'Failed'),
(4, 4, 1, 300.00, 'Completed');

INSERT INTO TACCodes (tacID, userID, tacCode, isUsed) VALUES
(1, 1, 'LOL69', 0),
(2, 2, 'XTC87', 0),
(3, 3, 'KMT54', 1),
(4, 4, 'SMH67', 0);

