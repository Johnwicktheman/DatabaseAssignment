-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 21, 2026 at 02:38 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `courseworkdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminaccountlist`
--

CREATE TABLE `adminaccountlist` (
  `AdminAccount_id` int(11) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Username` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `adminaccountlist`
--

INSERT INTO `adminaccountlist` (`AdminAccount_id`, `Password`, `Username`) VALUES
(1, '12345', 'HueWeiFeng'),
(2, '12345', 'JamesBond'),
(3, '12345', 'ValiantTai');

-- --------------------------------------------------------

--
-- Table structure for table `assesoraccountlist`
--

CREATE TABLE `assesoraccountlist` (
  `AssessorAccountID` int(11) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `AdminAccountID` int(11) NOT NULL,
  `AssesorType` enum('Lecturer','Supervisor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `assesoraccountlist`
--

INSERT INTO `assesoraccountlist` (`AssessorAccountID`, `Password`, `Username`, `AdminAccountID`, `AssesorType`) VALUES
(3, '2132', 'dsad', 1, 'Lecturer'),
(4, '12345', 'JackBlack', 1, 'Lecturer'),
(6, '12345', 'JamesBagewl', 1, 'Lecturer'),
(7, '12345', 'HueWeiFengLect', 1, 'Lecturer');

-- --------------------------------------------------------

--
-- Table structure for table `assessmentrecords`
--

CREATE TABLE `assessmentrecords` (
  `AssessmentCode` int(11) NOT NULL,
  `StudentID` int(11) NOT NULL,
  `AssesorType` enum('Lecturer','Supervisor') NOT NULL,
  `Feedback` varchar(300) DEFAULT NULL,
  `understand_project` float DEFAULT NULL,
  `health_and_safety` float DEFAULT NULL,
  `connectivity` float DEFAULT NULL,
  `presentation` float DEFAULT NULL,
  `clarity` float DEFAULT NULL,
  `activities` float DEFAULT NULL,
  `project_management` float DEFAULT NULL,
  `time_management` float DEFAULT NULL,
  `Internship_Score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `assessmentrecords`
--

INSERT INTO `assessmentrecords` (`AssessmentCode`, `StudentID`, `AssesorType`, `Feedback`, `understand_project`, `health_and_safety`, `connectivity`, `presentation`, `clarity`, `activities`, `project_management`, `time_management`, `Internship_Score`) VALUES
(1, 1, 'Lecturer', 'chicken jockeyyy', 1, 9, 4, 2, 1, 2, 3, 4, 26),
(2, 12, 'Lecturer', '1', 1, 1, 1, 1, 10, 1, 1, 1, 17);

-- --------------------------------------------------------

--
-- Table structure for table `companynamelist`
--

CREATE TABLE `companynamelist` (
  `CompanyInt` int(11) NOT NULL,
  `CompanyName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `companynamelist`
--

INSERT INTO `companynamelist` (`CompanyInt`, `CompanyName`) VALUES
(1, 'Google');

-- --------------------------------------------------------

--
-- Table structure for table `internship`
--

CREATE TABLE `internship` (
  `InternshipCode` varchar(100) NOT NULL,
  `CompanyINT` int(11) NOT NULL,
  `Role` varchar(100) NOT NULL,
  `Months_duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `internship`
--

INSERT INTO `internship` (`InternshipCode`, `CompanyINT`, `Role`, `Months_duration`) VALUES
('MPU3302', 1, 'Tech Engineer', 1);

-- --------------------------------------------------------

--
-- Table structure for table `studentaccountlist`
--

CREATE TABLE `studentaccountlist` (
  `StudentAccountID` int(11) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `AdminAccountID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `studentaccountlist`
--

INSERT INTO `studentaccountlist` (`StudentAccountID`, `Password`, `Username`, `AdminAccountID`) VALUES
(1, '123456', 'MichealJackson2', 1),
(12, '12345', 'JackBlack21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `studentprofile`
--

CREATE TABLE `studentprofile` (
  `StudentProfileID` int(11) NOT NULL,
  `StudentAccountID` int(11) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `ProgrammeCode` varchar(100) NOT NULL,
  `YearOfStudy` int(11) NOT NULL,
  `InternshipCode` varchar(100) NOT NULL,
  `AssesorAccountIDLect` int(11) DEFAULT NULL,
  `AssesorAccountIDSuper` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `studentprofile`
--

INSERT INTO `studentprofile` (`StudentProfileID`, `StudentAccountID`, `FirstName`, `LastName`, `ProgrammeCode`, `YearOfStudy`, `InternshipCode`, `AssesorAccountIDLect`, `AssesorAccountIDSuper`) VALUES
(3, 1, 'Jicken', 'Jockey', 'dsadsa', 1, 'MPU3302', 7, NULL),
(5, 12, 'James', 'Bonded', 'sada', 1, 'MPU3302', 7, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminaccountlist`
--
ALTER TABLE `adminaccountlist`
  ADD PRIMARY KEY (`AdminAccount_id`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `assesoraccountlist`
--
ALTER TABLE `assesoraccountlist`
  ADD PRIMARY KEY (`AssessorAccountID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `AdminAccountID` (`AdminAccountID`);

--
-- Indexes for table `assessmentrecords`
--
ALTER TABLE `assessmentrecords`
  ADD PRIMARY KEY (`AssessmentCode`),
  ADD UNIQUE KEY `unique_student_assessor` (`StudentID`,`AssesorType`);

--
-- Indexes for table `companynamelist`
--
ALTER TABLE `companynamelist`
  ADD PRIMARY KEY (`CompanyInt`);

--
-- Indexes for table `internship`
--
ALTER TABLE `internship`
  ADD PRIMARY KEY (`InternshipCode`),
  ADD UNIQUE KEY `InternshipCode` (`InternshipCode`),
  ADD UNIQUE KEY `CompanyName` (`CompanyINT`);

--
-- Indexes for table `studentaccountlist`
--
ALTER TABLE `studentaccountlist`
  ADD PRIMARY KEY (`StudentAccountID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `AdminAccountID` (`AdminAccountID`);

--
-- Indexes for table `studentprofile`
--
ALTER TABLE `studentprofile`
  ADD PRIMARY KEY (`StudentProfileID`),
  ADD KEY `StudentAccountID` (`StudentAccountID`),
  ADD KEY `studentprofile_ibfk_3` (`AssesorAccountIDLect`),
  ADD KEY `AssesorAccountIDSuper` (`AssesorAccountIDSuper`),
  ADD KEY `studentprofile_ibfk_5` (`InternshipCode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminaccountlist`
--
ALTER TABLE `adminaccountlist`
  MODIFY `AdminAccount_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assesoraccountlist`
--
ALTER TABLE `assesoraccountlist`
  MODIFY `AssessorAccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `assessmentrecords`
--
ALTER TABLE `assessmentrecords`
  MODIFY `AssessmentCode` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `companynamelist`
--
ALTER TABLE `companynamelist`
  MODIFY `CompanyInt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `studentaccountlist`
--
ALTER TABLE `studentaccountlist`
  MODIFY `StudentAccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `studentprofile`
--
ALTER TABLE `studentprofile`
  MODIFY `StudentProfileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assesoraccountlist`
--
ALTER TABLE `assesoraccountlist`
  ADD CONSTRAINT `assesoraccountlist_ibfk_1` FOREIGN KEY (`AdminAccountID`) REFERENCES `adminaccountlist` (`AdminAccount_id`);

--
-- Constraints for table `assessmentrecords`
--
ALTER TABLE `assessmentrecords`
  ADD CONSTRAINT `assessmentrecords_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `studentprofile` (`StudentAccountID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `internship`
--
ALTER TABLE `internship`
  ADD CONSTRAINT `internship_ibfk_1` FOREIGN KEY (`CompanyINT`) REFERENCES `companynamelist` (`CompanyInt`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `studentaccountlist`
--
ALTER TABLE `studentaccountlist`
  ADD CONSTRAINT `studentaccountlist_ibfk_1` FOREIGN KEY (`AdminAccountID`) REFERENCES `adminaccountlist` (`AdminAccount_id`);

--
-- Constraints for table `studentprofile`
--
ALTER TABLE `studentprofile`
  ADD CONSTRAINT `studentprofile_ibfk_1` FOREIGN KEY (`StudentAccountID`) REFERENCES `studentaccountlist` (`StudentAccountID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `studentprofile_ibfk_3` FOREIGN KEY (`AssesorAccountIDLect`) REFERENCES `assesoraccountlist` (`AssessorAccountID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `studentprofile_ibfk_4` FOREIGN KEY (`AssesorAccountIDSuper`) REFERENCES `assesoraccountlist` (`AssessorAccountID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `studentprofile_ibfk_5` FOREIGN KEY (`InternshipCode`) REFERENCES `internship` (`InternshipCode`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
