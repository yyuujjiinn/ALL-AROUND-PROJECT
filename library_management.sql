-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 02:15 PM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(50) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `ContactNumber` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `AuthorID` int(50) NOT NULL,
  `AuthorName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `BookID` int(50) NOT NULL,
  `BookTitle` varchar(100) NOT NULL,
  `AuthorID` int(50) NOT NULL,
  `CategoryID` int(50) NOT NULL,
  `PublisherId` int(50) NOT NULL,
  `Quantity` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow`
--

CREATE TABLE `borrow` (
  `BorrowID` int(50) NOT NULL,
  `UserID` int(50) NOT NULL,
  `BookID` int(50) NOT NULL,
  `AdminID` int(50) NOT NULL,
  `BorrowDate` varchar(15) NOT NULL,
  `DueDate` varchar(15) NOT NULL,
  `Returndate` varchar(15) NOT NULL,
  `Status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `CategoryID` int(50) NOT NULL,
  `CategoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `CourseID` int(50) NOT NULL,
  `CourseName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `FineID` int(50) NOT NULL,
  `BorrowID` int(50) NOT NULL,
  `Amount` int(20) NOT NULL,
  `PaidStatus` varchar(10) NOT NULL,
  `DateIssued` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `MaterialID` int(50) NOT NULL,
  `MaterialName` varchar(100) NOT NULL,
  `MaterialType` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int(11) NOT NULL,
  `UserID` int(50) NOT NULL,
  `Message` varchar(100) NOT NULL,
  `DateCreated` varchar(15) NOT NULL,
  `Status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publisher`
--

CREATE TABLE `publisher` (
  `PublisherID` int(50) NOT NULL,
  `PublisherName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` varchar(50) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(20) NOT NULL,
  `CourseID` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`AuthorID`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`BookID`),
  ADD KEY `AuthorID` (`AuthorID`),
  ADD KEY `CategoryID` (`CategoryID`),
  ADD KEY `PublisherId` (`PublisherId`);

--
-- Indexes for table `borrow`
--
ALTER TABLE `borrow`
  ADD PRIMARY KEY (`BorrowID`),
  ADD KEY `StudentID` (`UserID`),
  ADD KEY `BookID` (`BookID`),
  ADD KEY `LibrarianID` (`AdminID`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`CourseID`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD PRIMARY KEY (`FineID`),
  ADD KEY `BorrowID` (`BorrowID`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`MaterialID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `StudentID` (`UserID`);

--
-- Indexes for table `publisher`
--
ALTER TABLE `publisher`
  ADD PRIMARY KEY (`PublisherID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD KEY `CourseID` (`CourseID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
