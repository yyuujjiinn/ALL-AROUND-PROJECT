-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2026 at 09:38 PM
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
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `AuthorID` int(50) NOT NULL,
  `AuthorName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`AuthorID`, `AuthorName`) VALUES
(1, 'Rizal'),
(2, 'Jose Rizal'),
(3, 'JK Rowling');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `BookID` int(50) NOT NULL,
  `BookTitle` varchar(100) NOT NULL,
  `CategoryID` int(50) NOT NULL,
  `PublisherId` int(50) NOT NULL,
  `Quantity` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`BookID`, `BookTitle`, `CategoryID`, `PublisherId`, `Quantity`) VALUES
(3, 'El Filibusterismo', 2, 2, 5),
(4, 'Harry Potter', 3, 3, 7);

-- --------------------------------------------------------

--
-- Table structure for table `book_archive`
--

CREATE TABLE `book_archive` (
  `ArchiveID` int(11) NOT NULL,
  `BookID` int(50) NOT NULL,
  `BookTitle` varchar(100) NOT NULL,
  `CategoryID` int(50) NOT NULL,
  `PublisherId` int(50) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `DeletedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_archive`
--

INSERT INTO `book_archive` (`ArchiveID`, `BookID`, `BookTitle`, `CategoryID`, `PublisherId`, `Quantity`, `DeletedAt`) VALUES
(8, 2, 'Noli', 1, 1, 2, '2026-03-31 01:38:14');

-- --------------------------------------------------------

--
-- Table structure for table `book_authors`
--

CREATE TABLE `book_authors` (
  `BookID` int(50) NOT NULL,
  `AuthorID` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_authors`
--

INSERT INTO `book_authors` (`BookID`, `AuthorID`) VALUES
(3, 2),
(4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `book_copies`
--

CREATE TABLE `book_copies` (
  `Status` varchar(100) NOT NULL,
  `BookID` int(11) NOT NULL,
  `AuthorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow`
--

CREATE TABLE `borrow` (
  `BorrowID` int(11) NOT NULL,
  `UserID` int(50) NOT NULL,
  `BookID` int(50) NOT NULL,
  `RequestDate` date NOT NULL DEFAULT curdate(),
  `BorrowDate` date DEFAULT NULL,
  `DueDate` date DEFAULT NULL,
  `Returndate` date DEFAULT NULL,
  `Status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow`
--

INSERT INTO `borrow` (`BorrowID`, `UserID`, `BookID`, `RequestDate`, `BorrowDate`, `DueDate`, `Returndate`, `Status`) VALUES
(1, 16, 2, '2026-04-02', '2026-03-30', '2026-02-20', '2026-03-30', 'Returned'),
(2, 17, 2, '2026-04-02', '2026-03-30', '2026-02-20', '2026-03-30', 'Returned'),
(3, 20, 3, '2026-04-02', '2026-03-30', '2026-04-06', NULL, 'Borrowed'),
(4, 21, 3, '2026-04-02', '2026-03-31', '2026-04-07', '2026-03-31', 'Returned'),
(5, 1, 5, '2026-04-02', '2026-03-26', '2026-03-29', NULL, 'Borrowed'),
(6, 22, 3, '2026-04-02', '2026-03-31', '2026-04-07', NULL, 'Borrowed'),
(7, 9, 3, '2026-04-02', '2026-04-02', '2026-04-09', NULL, 'Borrowed'),
(8, 11, 3, '2026-04-02', '2026-04-02', '2026-04-09', '2026-04-03', 'Returned'),
(9, 11, 4, '2026-04-02', NULL, NULL, NULL, 'Rejected'),
(10, 24, 3, '2026-04-02', '2026-04-02', '2026-04-09', '2026-04-03', 'Returned'),
(11, 24, 4, '2026-04-02', NULL, NULL, NULL, 'Rejected');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `CategoryID` int(50) NOT NULL,
  `CategoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`CategoryID`, `CategoryName`) VALUES
(1, 'History'),
(2, 'Novel'),
(3, 'Story Book');

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
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `FineID` int(11) NOT NULL,
  `BorrowID` int(11) NOT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fines`
--

INSERT INTO `fines` (`FineID`, `BorrowID`, `Type`, `Amount`, `Status`) VALUES
(1, 2, 'Overdue', 189.79, 'Unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `MaterialID` int(11) NOT NULL,
  `MaterialName` varchar(255) NOT NULL,
  `Quantity` int(11) DEFAULT 0,
  `CategoryName` varchar(100) NOT NULL,
  `Category` varchar(100) DEFAULT 'General'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`MaterialID`, `MaterialName`, `Quantity`, `CategoryName`, `Category`) VALUES
(1, 'Journal of IT', 5, 'Journal', 'General'),
(2, 'AI Research 2024', 2, 'Research', 'General'),
(3, 'Thesis on Robotics', 1, 'Research', 'General'),
(4, 'National Geographic', 10, 'Magazine', 'General'),
(5, 'PC World', 8, 'Magazine', 'General'),
(6, 'Journal of IT Research', 5, 'Journal', 'Journal'),
(7, 'Study on AI Ethics', 2, '', 'Research'),
(8, 'Gadget Magazine', 10, 'Magazine', 'Magazine'),
(9, 'Manila Times', 15, '', 'Newspaper');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Message` text NOT NULL,
  `Status` varchar(50) DEFAULT 'Unread',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`ID`, `UserID`, `Message`, `Status`, `CreatedAt`) VALUES
(1, 9, 'Hello', 'Read', '2026-03-25 10:52:00'),
(2, 9, 'he', 'Read', '2026-03-25 10:56:13'),
(3, 9, 'tt', 'Read', '2026-03-25 11:03:44'),
(4, 9, '123', 'Read', '2026-03-25 11:07:43'),
(5, 9, 'Burat', 'Read', '2026-03-25 12:12:12'),
(6, 16, 'You successfully borrowed a book. Please return it by 2026-04-06', 'Read', '2026-03-29 23:33:40'),
(7, 17, 'You successfully borrowed a book. Please return it by 2026-04-06', 'Read', '2026-03-30 01:24:54'),
(8, 20, 'You successfully borrowed a book. Please return it by 2026-04-06', 'Unread', '2026-03-30 11:38:09'),
(9, 21, 'You successfully borrowed a book. Please return it by 2026-04-07', 'Unread', '2026-03-31 01:40:10'),
(11, 9, 'You successfully borrowed a book. Please return it by 2026-04-09', 'Unread', '2026-04-02 12:21:44'),
(12, 11, 'Your borrow request for the book has been submitted and is pending approval.', 'Read', '2026-04-02 14:53:14'),
(13, 11, 'Your borrow request has been approved!', 'Read', '2026-04-02 15:23:45'),
(14, 11, 'You successfully borrowed the book. Please return it by 2026-04-09', 'Read', '2026-04-02 16:00:29'),
(15, 11, 'Your borrow request for the book has been submitted and is pending approval.', 'Read', '2026-04-02 16:11:22'),
(16, 11, 'Your borrow request has been rejected.', 'Read', '2026-04-02 16:11:35'),
(17, 24, 'Your borrow request for the book has been submitted and is pending approval.', 'Read', '2026-04-02 19:26:38'),
(18, 24, 'Your borrow request has been approved!', 'Read', '2026-04-02 19:27:17'),
(19, 24, 'Your borrow request for the book has been submitted and is pending approval.', 'Read', '2026-04-02 19:27:53'),
(20, 24, 'You successfully borrowed the book. Please return it by 2026-04-09', 'Read', '2026-04-02 19:27:56'),
(21, 24, 'Your borrow request has been rejected.', 'Read', '2026-04-02 19:28:08');

-- --------------------------------------------------------

--
-- Table structure for table `publisher`
--

CREATE TABLE `publisher` (
  `PublisherID` int(50) NOT NULL,
  `PublisherName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publisher`
--

INSERT INTO `publisher` (`PublisherID`, `PublisherName`) VALUES
(1, 'Liwayway'),
(2, 'Eliazha Pasion'),
(3, 'PIa De Belen');

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

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `RoleID` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `AdminID` int(11) NOT NULL,
  `VisitorID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `FacultyID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`RoleID`, `StudentID`, `AdminID`, `VisitorID`, `StaffID`, `FacultyID`) VALUES
(9, 9, 0, 0, 0, 0),
(10, 0, 10, 0, 0, 0),
(11, 0, 0, 11, 0, 0),
(12, 0, 0, 0, 12, 0),
(13, 0, 0, 0, 0, 13),
(14, 14, 0, 0, 0, 0),
(15, 15, 0, 0, 0, 0),
(16, 16, 0, 0, 0, 0),
(17, 17, 0, 0, 0, 0),
(18, 18, 0, 0, 0, 0),
(19, 0, 19, 0, 0, 0),
(20, 20, 0, 0, 0, 0),
(21, 0, 21, 0, 0, 0),
(22, 0, 0, 0, 22, 0),
(23, 0, 0, 0, 0, 23),
(24, 0, 0, 24, 0, 0);

--
-- Indexes for dumped tables
--

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
  ADD KEY `CategoryID` (`CategoryID`),
  ADD KEY `PublisherId` (`PublisherId`);

--
-- Indexes for table `book_archive`
--
ALTER TABLE `book_archive`
  ADD PRIMARY KEY (`ArchiveID`);

--
-- Indexes for table `book_authors`
--
ALTER TABLE `book_authors`
  ADD PRIMARY KEY (`BookID`,`AuthorID`),
  ADD KEY `AuthorID` (`AuthorID`);

--
-- Indexes for table `book_copies`
--
ALTER TABLE `book_copies`
  ADD PRIMARY KEY (`Status`);

--
-- Indexes for table `borrow`
--
ALTER TABLE `borrow`
  ADD PRIMARY KEY (`BorrowID`),
  ADD KEY `StudentID` (`UserID`),
  ADD KEY `BookID` (`BookID`);

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
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`FineID`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`MaterialID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `publisher`
--
ALTER TABLE `publisher`
  ADD PRIMARY KEY (`PublisherID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`RoleID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `CourseID` (`CourseID`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`RoleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `AuthorID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `BookID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `book_archive`
--
ALTER TABLE `book_archive`
  MODIFY `ArchiveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `borrow`
--
ALTER TABLE `borrow`
  MODIFY `BorrowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `CategoryID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `FineID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `MaterialID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `publisher`
--
ALTER TABLE `publisher`
  MODIFY `PublisherID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `RoleID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `RoleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_authors`
--
ALTER TABLE `book_authors`
  ADD CONSTRAINT `book_authors_ibfk_1` FOREIGN KEY (`BookID`) REFERENCES `books` (`BookID`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_authors_ibfk_2` FOREIGN KEY (`AuthorID`) REFERENCES `authors` (`AuthorID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
