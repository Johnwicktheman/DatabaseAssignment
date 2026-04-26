-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 26, 2026 at 07:20 AM
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
(4, '12345', 'ValiantTai'),
(5, '12345', 'Admin');

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
(12, '12345', 'DrTanLecturer', 1, 'Lecturer'),
(13, '12345', 'DrTanSupervisor', 1, 'Supervisor'),
(14, '12345', 'MrSupervisor', 1, 'Supervisor'),
(15, '12345', 'MrLecturer', 1, 'Lecturer');

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
(19, 24, 'Lecturer', 'From what I see, Valiant has been very attentive and passionate under the care of Google. Keep it up Valiant!', 9, 9, 9, 9, 9, 9, 9, 9, 90),
(20, 24, 'Supervisor', 'Valiant has been very pleasant to work with! We hope to see him again as a full-time worker!', 9, 9, 9, 9, 9, 9, 9, 9, 90),
(21, 25, 'Lecturer', 'I don\'t understand how he can even get a job under Meta! I\'ve only heard complaints from him ever since he got accepted from this internship...', 1, 2, 1, 1, 1, 1, 1.5, 3, 15),
(22, 27, 'Lecturer', 'Never attended a single meeting, never written a single email, but still does abit of work, i\'ll give him that.', 1, 1, 1, 3, 4, 6, 2, 3, 28),
(23, 27, 'Supervisor', 'I don\'t understand how he even manage to land a job from google... He does work for sure, but he has to write emails and confirm before git pushing into our main repo right?', 1, 1, 1, 2, 3, 4, 5, 6, 32);

-- --------------------------------------------------------

--
-- Table structure for table `companynamelist`
--

CREATE TABLE `companynamelist` (
  `CompanyInt` int(11) NOT NULL,
  `CompanyName` varchar(100) NOT NULL,
  `CompanyAddress` varchar(100) DEFAULT NULL,
  `CompanyType` enum('Technology','Finance','Healthcare','Engineering','Media','Other') DEFAULT NULL,
  `ContactNumber` varchar(100) DEFAULT NULL,
  `EmailContact` varchar(100) DEFAULT NULL,
  `PicturePath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `companynamelist`
--

INSERT INTO `companynamelist` (`CompanyInt`, `CompanyName`, `CompanyAddress`, `CompanyType`, `ContactNumber`, `EmailContact`, `PicturePath`) VALUES
(1, 'Google', 'Silicon Valley', 'Technology', '+018293201', 'google@gmail.com', 'images/1777149579_google.png'),
(11, 'Amazon', 'Amazon Inc Washington\r\n', 'Engineering', '0123456789', 'amazon@gmail.com', 'images/1777149653_amazon.png'),
(12, 'Meta', 'Menlo Park', 'Other', '024681234', 'meta@gmail.com', 'images/1777149700_meta.png');

-- --------------------------------------------------------

--
-- Table structure for table `internship`
--

CREATE TABLE `internship` (
  `InternshipID` int(11) NOT NULL,
  `StudentAccountID` int(11) NOT NULL,
  `CompanyINT` int(11) DEFAULT NULL,
  `Role` varchar(100) NOT NULL,
  `Months_duration` int(11) NOT NULL,
  `Description` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `internship`
--

INSERT INTO `internship` (`InternshipID`, `StudentAccountID`, `CompanyINT`, `Role`, `Months_duration`, `Description`) VALUES
(18, 24, 1, 'Software Intern', 6, 'Worked as Software Intern in Google company. Made a website for the company'),
(19, 25, 11, 'Data Engineer', 12, 'Worked as a Data Engineer in Amazon. Handled tasks given by their supervisor, does work well.'),
(20, 26, 12, 'Fullstack developer', 12, 'Worked under Meta. Attended to all the meeting, ambitious about career, passionate about work.'),
(21, 27, 1, 'Data Scientist', 10, 'Does work as a data scientist under google, very good');

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
(24, '12345', '20705238', 1),
(25, '12345', '20715078', 1),
(26, '12345', '20711677', 1),
(27, '12345', '20710858', 1);

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
  `AssesorAccountIDLect` int(11) DEFAULT NULL,
  `AssesorAccountIDSuper` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `studentprofile`
--

INSERT INTO `studentprofile` (`StudentProfileID`, `StudentAccountID`, `FirstName`, `LastName`, `ProgrammeCode`, `YearOfStudy`, `AssesorAccountIDLect`, `AssesorAccountIDSuper`) VALUES
(14, 24, 'Valiant', 'Tai', 'CS101', 1, 12, 13),
(15, 25, 'Faysal', 'Muhammad', 'CS102', 2, 15, 14),
(16, 26, 'Wei Feng', 'Hue', 'CS103', 3, 12, 13),
(17, 27, 'Phae Pyo', 'Min', 'CS102', 4, 15, 14);

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
  ADD PRIMARY KEY (`InternshipID`),
  ADD KEY `internship_ibfk_2` (`StudentAccountID`),
  ADD KEY `internship_ibfk_1` (`CompanyINT`);

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
  ADD KEY `AssesorAccountIDSuper` (`AssesorAccountIDSuper`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminaccountlist`
--
ALTER TABLE `adminaccountlist`
  MODIFY `AdminAccount_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assesoraccountlist`
--
ALTER TABLE `assesoraccountlist`
  MODIFY `AssessorAccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `assessmentrecords`
--
ALTER TABLE `assessmentrecords`
  MODIFY `AssessmentCode` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `companynamelist`
--
ALTER TABLE `companynamelist`
  MODIFY `CompanyInt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `internship`
--
ALTER TABLE `internship`
  MODIFY `InternshipID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `studentaccountlist`
--
ALTER TABLE `studentaccountlist`
  MODIFY `StudentAccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `studentprofile`
--
ALTER TABLE `studentprofile`
  MODIFY `StudentProfileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  ADD CONSTRAINT `internship_ibfk_1` FOREIGN KEY (`CompanyINT`) REFERENCES `companynamelist` (`CompanyInt`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `internship_ibfk_2` FOREIGN KEY (`StudentAccountID`) REFERENCES `studentprofile` (`StudentAccountID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `studentprofile_ibfk_4` FOREIGN KEY (`AssesorAccountIDSuper`) REFERENCES `assesoraccountlist` (`AssessorAccountID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
