-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2026 at 05:29 PM
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
-- Database: `library_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `RoleID` int(50) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `CourseID` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`RoleID`, `Name`, `Email`, `Password`, `CourseID`) VALUES
(9, 'Yujin', 'jinyu3097@gmail.com', '123', 123),
(10, 'Pasion', 'manubayeugene35@gmail.com', '123', 123),
(11, 'Russel', 'burat@gmail.com', '123', 0),
(12, 'Ash', 'tite@gmail.com', '123', 0),
(13, 'Mica', 'tite1@gmail.com', '123', 123),
(14, 'Pia De Belen', 'debelenpiaangellie4@gmail.com', 'PiaDeBelen', 2024100588),
(15, 'AllyaDeBelen', 'allyatherese@gmail.com', 'allyathererse', 2024100589),
(16, 'Allya De Belen', 'allyatheresedebelen@gmail.com', 'allyatherese', 2024100589),
(17, 'Nina Castillo', 'NinaMariaeCastillo@gmail.com', 'NinaCas', 2024100580),
(18, 'BunBun Bigalo', 'BunBunBigalo@gmail.com', 'CuteBunBun', 2024100589),
(19, 'Leonora D. De Belen', 'LeonoraDeBelen@gmail.com', 'LDB', 0),
(20, 'Neil De Belen', 'NeilDeBelen@gmail.com', 'ndb', 2024100581),
(21, 'Diana Jav', 'DianaJav@gmail.com', 'daj', 0),
(22, 'Irish A. Javier', 'IrishJavier@gmail.com', 'rishi', 0),
(23, 'Trixia Mae A. Javier', 'TrixiaJavier@gmail.com', 'trixie', 2024100509),
(24, 'Pia Angellie De Belen', 'PiaDeBelen@gmail.com', 'pia', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `CourseID` (`CourseID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `RoleID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
