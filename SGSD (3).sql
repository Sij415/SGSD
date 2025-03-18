-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 18, 2025 at 07:36 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `SGSD`
--

-- --------------------------------------------------------

--
-- Table structure for table `Customers`
--

CREATE TABLE `Customers` (
  `Customer_ID` int(11) NOT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `First_Name` varchar(255) DEFAULT NULL,
  `Last_Name` varchar(255) DEFAULT NULL,
  `Contact_Number` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Customers`
--

INSERT INTO `Customers` (`Customer_ID`, `Product_ID`, `First_Name`, `Last_Name`, `Contact_Number`) VALUES
(1, 1, 'Joh', 'Doe', '1234567890'),
(2, 2, 'Jane', 'Smith', '2345678901'),
(3, 3, 'Jim', 'Brown', '3456789012'),
(4, 4, 'Jack', 'White', '4567890123'),
(5, 5, 'Jill', 'Green', '5678901234');

-- --------------------------------------------------------

--
-- Table structure for table `IP_Cooldown`
--

CREATE TABLE `IP_Cooldown` (
  `ID` int(11) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `IP_Address` varchar(45) NOT NULL,
  `Attempts` int(11) DEFAULT 0,
  `Last_Attempt` datetime NOT NULL,
  `Locked_Until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Logs`
--

CREATE TABLE `Logs` (
  `Log_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `User_Full_Name` varchar(255) DEFAULT NULL,
  `Order_ID` int(11) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Time` time DEFAULT NULL,
  `Activity` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Logs`
--

INSERT INTO `Logs` (`Log_ID`, `User_ID`, `User_Full_Name`, `Order_ID`, `Date`, `Time`, `Activity`) VALUES
(1, 1, NULL, NULL, '2025-03-03', '21:30:08', 'Logged into the system from IP: ::1'),
(2, 1, NULL, NULL, '2025-03-04', '05:00:15', 'Logged into the system from IP: ::1'),
(3, 1, 'Roel San Gabriel', NULL, '2025-03-04', '05:42:05', 'Logged into the system from IP: ::1'),
(4, 1, 'Roel San Gabriel', NULL, '2025-03-04', '16:15:31', 'Logged into the system from IP: ::1'),
(5, NULL, 'Unknown User', NULL, '2025-03-04', '16:18:01', 'Email does not exist of IP: ::1'),
(6, NULL, 'Unknown User', NULL, '2025-03-04', '16:27:19', 'Email does not exist of IP: ::1'),
(7, 9, 'Sij Hernandez', NULL, '2025-03-04', '16:40:28', 'Invalid password of IP: ::1'),
(8, 1, 'Roel San Gabriel', NULL, '2025-03-04', '16:40:51', 'Logged into the system from IP: ::1'),
(9, 1, 'Roel San Gabriel', NULL, '2025-03-04', '16:41:33', 'Logged into the system from IP: ::1'),
(10, 1, 'Roel San Gabriel', NULL, '2025-03-18', '13:23:56', 'Logged into the system from IP: ::1');

-- --------------------------------------------------------

--
-- Table structure for table `Orders`
--

CREATE TABLE `Orders` (
  `Order_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `Order_Type` varchar(20) DEFAULT NULL,
  `Amount` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total_price` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Orders`
--

INSERT INTO `Orders` (`Order_ID`, `User_ID`, `Product_ID`, `Status`, `Order_Type`, `Amount`, `quantity`, `total_price`) VALUES
(21, 1, 1, 'To Pick Up', 'Outbound', 0, 1, 0),
(22, 2, 2, 'Completed', 'Sale', 0, 1, 0),
(23, 3, 3, 'Shipped', 'Purchase', 0, 1, 0),
(24, 4, 1, 'Cancelled', 'Sale', 0, 1, 0),
(25, 5, 4, 'Processing', 'Purchase', 0, 1, 0),
(26, 1, 5, 'Pending', 'Sale', 0, 1, 0),
(27, 2, 3, 'Completed', 'Purchase', 0, 1, 0),
(28, 3, 2, 'Shipped', 'Sale', 0, 1, 0),
(29, 4, 4, 'Cancelled', 'Purchase', 0, 1, 0),
(30, 5, 1, 'Processing', 'Sale', 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `Products`
--

CREATE TABLE `Products` (
  `Product_ID` int(11) NOT NULL,
  `Product_Name` varchar(255) DEFAULT NULL,
  `Product_Type` varchar(255) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Products`
--

INSERT INTO `Products` (`Product_ID`, `Product_Name`, `Product_Type`, `Price`) VALUES
(1, 'Coca-Cola', 'Soft Drink', 1.50),
(2, 'Peps', 'Soft Drink', 12.00),
(3, 'Sprite', 'Soft Drink', 1.30),
(4, 'Fanta Orange', 'Soft Drink', 1.40),
(5, 'Mountain Dew', 'Soft Drink', 1.60),
(6, 'Dr Pepper', 'Soft Drink', 1.70),
(7, '7-Up', 'Soft Drink', 1.30),
(8, 'Ginger Ale', 'Soft Drink', 1.50),
(9, 'Root Beer', 'Soft Drink', 1.60),
(10, 'Club Soda', 'Soft Drink', 1.20),
(11, '12', '12', 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `Settings`
--

CREATE TABLE `Settings` (
  `Setting_Key` varchar(50) NOT NULL,
  `Value` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Settings`
--

INSERT INTO `Settings` (`Setting_Key`, `Value`) VALUES
('AdminSignUpEnabled', 0),
('MaxSignUps', 0),
('SignUpEnabled', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Stocks`
--

CREATE TABLE `Stocks` (
  `Stock_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Old_Stock` int(11) DEFAULT NULL,
  `Threshold` int(11) DEFAULT NULL,
  `New_Stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Stocks`
--

INSERT INTO `Stocks` (`Stock_ID`, `User_ID`, `Product_ID`, `Old_Stock`, `Threshold`, `New_Stock`) VALUES
(9, 1, 1, 100, 200, 10),
(10, 2, 2, 50, 100, NULL),
(11, 3, 3, 75, 150, NULL),
(12, 4, 4, 120, 250, NULL),
(13, 5, 5, 90, 180, NULL),
(14, 6, 6, 110, 220, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Transactions`
--

CREATE TABLE `Transactions` (
  `Transaction_ID` int(11) NOT NULL,
  `Order_ID` int(11) DEFAULT NULL,
  `Customer_ID` int(11) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Transactions`
--

INSERT INTO `Transactions` (`Transaction_ID`, `Order_ID`, `Customer_ID`, `Date`, `Time`) VALUES
(31, 21, 1, '2025-01-18', '10:00:00'),
(32, 22, 2, '2025-01-18', '12:30:00'),
(33, 23, 3, '2025-01-18', '14:45:00'),
(34, 24, 4, '2025-01-19', '09:15:00'),
(35, 25, 5, '2025-01-19', '11:00:00'),
(36, 26, 1, '2025-01-20', '13:20:00'),
(37, 27, 2, '2025-01-20', '15:40:00'),
(38, 28, 3, '2025-01-21', '08:25:00'),
(39, 29, 4, '2025-01-21', '16:50:00'),
(40, 30, 5, '2025-01-22', '07:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `User_ID` int(11) NOT NULL,
  `Role` varchar(50) NOT NULL,
  `First_Name` varchar(255) NOT NULL,
  `Last_Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password_hash` varchar(255) NOT NULL,
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `account_activation_hash` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`User_ID`, `Role`, `First_Name`, `Last_Name`, `Email`, `Password_hash`, `reset_token_hash`, `reset_token_expires_at`, `account_activation_hash`) VALUES
(1, 'admin', 'Roel', 'San Gabriel', 'sangabrielsoftdrinksdelivery@gmail.com', '$2y$10$MTB2StolM/Qm7hnzt9ZVuuEV46ae/HdL0.ip6phxF2dsPxwgSn17O', '7f42203e93d1b877a9e3ed5ccd7b2c24edf9666ab5849f59d8c9f843bd73b9a5', '2025-01-24 14:08:13', NULL),
(2, 'admin', 'John', 'Doe', 'john.doe@example.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', NULL, NULL, NULL),
(3, 'admin', 'Jane', 'Smith', 'jane.smith@example.com', '4682ac63f25bf51e92fc3b6e3033d29c4f6ccee77ae5a6543e4aff9698aa6b49', NULL, NULL, NULL),
(4, 'admin', 'Alice', 'Brown', 'alice.brown@example.com', '571eeecee49f17206833749f9cd3415b936317dd3d19e74e2218507134a4e2a0', NULL, NULL, NULL),
(5, 'admin', 'Bob', 'Johnson', 'bob.johnson@example.com', '8841360cd79d864383cdc8497c489f2218e13cf8130ab5f2cf89ce582c8e8e61', NULL, NULL, NULL),
(6, 'admin', 'Emily', 'Davis', 'emily.davis@example.com', '25c0041fc0e6e6ca09ba7b7c1c2f042bf613f3eefd3e2841bcaec65d98d1b4f6', NULL, NULL, NULL),
(7, 'admin', 'Michael', 'Wilson', 'michael.wilson@example.com', '2b31ee34a56c997fc947f4d9abac9752cb0b206937bde8cb0ee0c9153f1da68f', NULL, NULL, NULL),
(8, 'admin', 'as', 'asas', 'christendomsun.macapinlac.cics@ust.edu.ph', '$2y$10$1Mp1MFNyFh62ND/VnvSs.O5Fx/YU9lUaeLWgizJfiU8XxrSAspnUe', NULL, NULL, NULL),
(9, 'driver', 'Sij', 'Hernandez', 'seanirvin.hernandez.cics@ust.edu.ph', '$2y$10$oQ5eWwTIGwheHXX.CDknxu1jVPz72vytYYiVRPO9djKqVuZX2ADwe', 'cec8ffae6ad104a597d46b87db95208bb8bedc2f1cebe6dc046362a8580addde', '2025-01-24 13:58:46', NULL),
(18, 'admin', 'Sij', 'Hernandez', 'seanirvincv@gmail.com', '$2y$10$dV5Kb5KRdg3RzXgYoAXK0.267dM0kzonEMbFHdjSeDGkkynwQWNOi', NULL, NULL, '2165c6a644786c7c6c8f6f0c609f1d23e7e9f0a1995fae06fad4b6c3c2355b8c'),
(19, 'admin', 'Sij', 'Hernandez', 'seanirvin9@gmail.com', '$2y$10$TcPDicTr0Aw/xqmcxTbSz.TR9isY3MwjZeYQShfaACfOBPRfwuEZC', NULL, NULL, '406575f22b178483a0151007f17549b56d8a9b39ec6eb699278367859ec54fb2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Customers`
--
ALTER TABLE `Customers`
  ADD PRIMARY KEY (`Customer_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `IP_Cooldown`
--
ALTER TABLE `IP_Cooldown`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `FK_Email` (`Email`);

--
-- Indexes for table `Logs`
--
ALTER TABLE `Logs`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Order_ID` (`Order_ID`);

--
-- Indexes for table `Orders`
--
ALTER TABLE `Orders`
  ADD PRIMARY KEY (`Order_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `Products`
--
ALTER TABLE `Products`
  ADD PRIMARY KEY (`Product_ID`);

--
-- Indexes for table `Settings`
--
ALTER TABLE `Settings`
  ADD PRIMARY KEY (`Setting_Key`);

--
-- Indexes for table `Stocks`
--
ALTER TABLE `Stocks`
  ADD PRIMARY KEY (`Stock_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Product_ID` (`Product_ID`);

--
-- Indexes for table `Transactions`
--
ALTER TABLE `Transactions`
  ADD PRIMARY KEY (`Transaction_ID`),
  ADD KEY `Order_ID` (`Order_ID`),
  ADD KEY `Customer_ID` (`Customer_ID`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `reset_token_hash` (`reset_token_hash`),
  ADD UNIQUE KEY `account_activation_hash` (`account_activation_hash`),
  ADD KEY `fk_role` (`Role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Customers`
--
ALTER TABLE `Customers`
  MODIFY `Customer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `IP_Cooldown`
--
ALTER TABLE `IP_Cooldown`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `Logs`
--
ALTER TABLE `Logs`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Orders`
--
ALTER TABLE `Orders`
  MODIFY `Order_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `Products`
--
ALTER TABLE `Products`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `Stocks`
--
ALTER TABLE `Stocks`
  MODIFY `Stock_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `Transactions`
--
ALTER TABLE `Transactions`
  MODIFY `Transaction_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Customers`
--
ALTER TABLE `Customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `Products` (`Product_ID`);

--
-- Constraints for table `IP_Cooldown`
--
ALTER TABLE `IP_Cooldown`
  ADD CONSTRAINT `FK_Email` FOREIGN KEY (`Email`) REFERENCES `Users` (`Email`);

--
-- Constraints for table `Logs`
--
ALTER TABLE `Logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `Users` (`User_ID`),
  ADD CONSTRAINT `logs_ibfk_2` FOREIGN KEY (`Order_ID`) REFERENCES `Orders` (`Order_ID`);

--
-- Constraints for table `Orders`
--
ALTER TABLE `Orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `Users` (`User_ID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `Products` (`Product_ID`);

--
-- Constraints for table `Stocks`
--
ALTER TABLE `Stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `Users` (`User_ID`),
  ADD CONSTRAINT `stocks_ibfk_2` FOREIGN KEY (`Product_ID`) REFERENCES `Products` (`Product_ID`);

--
-- Constraints for table `Transactions`
--
ALTER TABLE `Transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`Order_ID`) REFERENCES `Orders` (`Order_ID`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`Customer_ID`) REFERENCES `Customers` (`Customer_ID`);

--
-- Constraints for table `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`Role`) REFERENCES `Roles` (`Role`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
