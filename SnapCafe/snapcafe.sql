-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2024 at 05:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `snapcafe`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` char(10) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `Name`, `Email`, `Password`) VALUES
('A001', 'Admin1', 'admin1@gmail.com', '123'),
('A002', 'admin3', 'admin3@gmail.com', '123');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` char(10) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` text NOT NULL,
  `Phone` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `Name`, `Email`, `Password`, `Address`, `Phone`) VALUES
('C001', 'Sherlock', 'sherlockholmes@gmail.com', 'sherlocked', '22/B Bakerstreet, London, UK', 123),
('C002', 'John Watson', 'johnwatson@gmail.com', 'watson123', '21/B Bakerstreet, London, UK', 23),
('C003', 'Mycroft Holmes', 'mycroftholmes@gmail.com', 'mycroft456', '20/B Bakerstreet, London, UK', 24),
('C004', 'Irene Adler', 'ireneadler@gmail.com', 'irene789', '19/B Bakerstreet, London, UK', 25),
('C005', 'Lord Faiyaz', 'Beingyousuf@gg.com', '6969', 'Uttarabashi', 6969);

-- --------------------------------------------------------

--
-- Table structure for table `discount`
--

CREATE TABLE `discount` (
  `CustomerID` char(10) NOT NULL,
  `MonthlyStreak` enum('Yes','No') DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `DiscountRate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discount`
--

INSERT INTO `discount` (`CustomerID`, `MonthlyStreak`, `StartDate`, `EndDate`, `DiscountRate`) VALUES
('C005', 'Yes', '2024-04-29', '2024-05-29', 20);

-- --------------------------------------------------------

--
-- Table structure for table `emailwishlist`
--

CREATE TABLE `emailwishlist` (
  `CustomerID` char(10) NOT NULL,
  `Email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emailwishlist`
--

INSERT INTO `emailwishlist` (`CustomerID`, `Email`) VALUES
('C002', 'johnwatson@gmail.com'),
('C005', 'Beingyousuf@gg.com');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EmployeeID` char(10) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` text NOT NULL,
  `Phone` int(11) NOT NULL,
  `Designation` varchar(255) DEFAULT NULL,
  `Payroll` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `OrderID` char(10) NOT NULL,
  `OrderDate` date NOT NULL,
  `Item` char(11) DEFAULT NULL,
  `Customization` text DEFAULT NULL,
  `Discount` int(11) DEFAULT NULL,
  `FinalCost` decimal(3,2) NOT NULL,
  `OrderType` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`OrderID`, `OrderDate`, `Item`, `Customization`, `Discount`, `FinalCost`, `OrderType`) VALUES
('O001', '2024-04-28', 'Mocha', '', NULL, 7.50, 'Dine In'),
('O005', '2024-04-29', 'Cappuccino', '', NULL, 0.00, 'Dine In'),
('O006', '2024-04-29', 'Mocha', '', NULL, 0.00, 'Dine In'),
('O007', '2024-04-29', 'Latte', '', NULL, 0.00, 'Dine In'),
('O008', '2024-04-29', 'Mocha', '', NULL, 0.00, 'Dine In'),
('O009', '2024-04-29', 'Mocha', '', NULL, 0.00, 'Dine In'),
('O010', '2024-04-29', 'Mocha', '', NULL, 0.00, 'Dine In'),
('O011', '2024-04-29', 'Mocha', '', NULL, 0.00, 'Dine In'),
('O012', '2024-04-29', 'Frappe', '', NULL, 0.00, 'Take Home'),
('O013', '2024-04-29', 'Cappuccino', '', NULL, 0.00, 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `CustomerID` char(10) NOT NULL,
  `OrderID` char(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`CustomerID`, `OrderID`) VALUES
('C001', 'O001'),
('C005', 'O005'),
('C005', 'O006'),
('C005', 'O007'),
('C005', 'O008'),
('C005', 'O009'),
('C005', 'O010'),
('C005', 'O011'),
('C005', 'O012'),
('C005', 'O013');

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `ReservationID` char(10) NOT NULL,
  `ReservationDate` date NOT NULL,
  `Seats` int(11) NOT NULL,
  `CustomerID` char(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`ReservationID`, `ReservationDate`, `Seats`, `CustomerID`) VALUES
('R001', '2024-04-29', 1, 'C001'),
('R002', '2024-04-29', 1, 'C005'),
('R003', '2024-04-29', 1, 'C005'),
('R004', '2024-04-29', 1, 'C005'),
('R005', '2024-04-29', 1, 'C005'),
('R006', '2024-04-29', 1, 'C005'),
('R007', '2024-04-29', 1, 'C005');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `discount`
--
ALTER TABLE `discount`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `emailwishlist`
--
ALTER TABLE `emailwishlist`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeID`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`OrderID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`CustomerID`,`OrderID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`ReservationID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `discount`
--
ALTER TABLE `discount`
  ADD CONSTRAINT `discount_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Constraints for table `emailwishlist`
--
ALTER TABLE `emailwishlist`
  ADD CONSTRAINT `emailwishlist_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`OrderID`) REFERENCES `orderdetails` (`OrderID`);

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
