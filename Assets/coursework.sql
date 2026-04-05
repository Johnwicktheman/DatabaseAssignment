-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 05, 2026 at 09:45 AM
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
-- Database: `coursework`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminaccountlist`
--

CREATE TABLE `adminaccountlist` (
  `AdminAccount_id` int(11) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_records`
--

CREATE TABLE `assessment_records` (
  `assessment_code` int(11) NOT NULL,
  `understand_project` float DEFAULT NULL,
  `health_and_safety` float DEFAULT NULL,
  `connectivity` float DEFAULT NULL,
  `presentation` float DEFAULT NULL,
  `clarity` float DEFAULT NULL,
  `activities` float DEFAULT NULL,
  `project_management` float DEFAULT NULL,
  `time_management` float DEFAULT NULL,
  `internship_score` int(11) DEFAULT NULL,
  `feedback` varchar(3500) DEFAULT NULL,
  `AssessorAccount_IDLecturer` int(11) DEFAULT NULL,
  `AssessorAccount_IDSupervisor` int(11) DEFAULT NULL,
  `student_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `assessoraccountlist`
--

CREATE TABLE `assessoraccountlist` (
  `AssessorAccountID` int(11) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `AdminAccount_id` int(11) DEFAULT NULL,
  `AssessorType` enum('Supervisor','Lecturer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `internship`
--

CREATE TABLE `internship` (
  `internship_code` varchar(50) NOT NULL,
  `name` varchar(500) DEFAULT NULL,
  `month_duration` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `student_profile`
--

CREATE TABLE `student_profile` (
  `student_ID` int(11) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `programme_code` varchar(10) DEFAULT NULL,
  `internship_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminaccountlist`
--
ALTER TABLE `adminaccountlist`
  ADD PRIMARY KEY (`AdminAccount_id`);

--
-- Indexes for table `assessment_records`
--
ALTER TABLE `assessment_records`
  ADD PRIMARY KEY (`assessment_code`),
  ADD KEY `fk_Lecturer` (`AssessorAccount_IDLecturer`),
  ADD KEY `fk_Supervisor` (`AssessorAccount_IDSupervisor`),
  ADD KEY `fk_studentID` (`student_ID`);

--
-- Indexes for table `assessoraccountlist`
--
ALTER TABLE `assessoraccountlist`
  ADD PRIMARY KEY (`AssessorAccountID`),
  ADD KEY `AdminAccount_id` (`AdminAccount_id`);

--
-- Indexes for table `internship`
--
ALTER TABLE `internship`
  ADD PRIMARY KEY (`internship_code`);

--
-- Indexes for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD PRIMARY KEY (`student_ID`),
  ADD KEY `internship_code` (`internship_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminaccountlist`
--
ALTER TABLE `adminaccountlist`
  MODIFY `AdminAccount_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessment_records`
--
ALTER TABLE `assessment_records`
  MODIFY `assessment_code` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessoraccountlist`
--
ALTER TABLE `assessoraccountlist`
  MODIFY `AssessorAccountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profile`
--
ALTER TABLE `student_profile`
  MODIFY `student_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessment_records`
--
ALTER TABLE `assessment_records`
  ADD CONSTRAINT `fk_Lecturer` FOREIGN KEY (`AssessorAccount_IDLecturer`) REFERENCES `assessoraccountlist` (`AssessorAccountID`),
  ADD CONSTRAINT `fk_Supervisor` FOREIGN KEY (`AssessorAccount_IDSupervisor`) REFERENCES `assessoraccountlist` (`AssessorAccountID`),
  ADD CONSTRAINT `fk_studentID` FOREIGN KEY (`student_ID`) REFERENCES `student_profile` (`student_ID`);

--
-- Constraints for table `assessoraccountlist`
--
ALTER TABLE `assessoraccountlist`
  ADD CONSTRAINT `assessoraccountlist_ibfk_1` FOREIGN KEY (`AdminAccount_id`) REFERENCES `adminaccountlist` (`AdminAccount_id`);

--
-- Constraints for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD CONSTRAINT `student_profile_ibfk_3` FOREIGN KEY (`internship_code`) REFERENCES `internship` (`internship_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
