-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 24, 2026 at 06:16 AM
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
-- Database: `internship`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminaccountlist`
--

CREATE TABLE `adminaccountlist` (
  `AdminAccount_id` int(11) NOT NULL,
  `password` varchar(30) DEFAULT NULL,
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
  `feedback` varchar(3500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `assessoraccountlist`
--

CREATE TABLE `assessoraccountlist` (
  `AssessorAccountID` int(11) NOT NULL,
  `password` varchar(30) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `AdminAccount_id` int(11) DEFAULT NULL,
  `AssessorType` varchar(50) DEFAULT NULL
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
-- Table structure for table `listassessortype`
--

CREATE TABLE `listassessortype` (
  `AssessorType` varchar(50) NOT NULL,
  `Assessor` int(11) DEFAULT NULL,
  `Supervisor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `student_profile`
--

CREATE TABLE `student_profile` (
  `student_ID` varchar(50) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `programme_code` varchar(10) DEFAULT NULL,
  `AssessorAccountID` int(11) DEFAULT NULL,
  `assessment_code` int(11) DEFAULT NULL,
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
  ADD PRIMARY KEY (`assessment_code`);

--
-- Indexes for table `assessoraccountlist`
--
ALTER TABLE `assessoraccountlist`
  ADD PRIMARY KEY (`AssessorAccountID`),
  ADD KEY `AdminAccount_id` (`AdminAccount_id`),
  ADD KEY `AssessorType` (`AssessorType`);

--
-- Indexes for table `internship`
--
ALTER TABLE `internship`
  ADD PRIMARY KEY (`internship_code`);

--
-- Indexes for table `listassessortype`
--
ALTER TABLE `listassessortype`
  ADD PRIMARY KEY (`AssessorType`);

--
-- Indexes for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD PRIMARY KEY (`student_ID`),
  ADD KEY `AssessorAccountID` (`AssessorAccountID`),
  ADD KEY `assessment_code` (`assessment_code`),
  ADD KEY `internship_code` (`internship_code`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessoraccountlist`
--
ALTER TABLE `assessoraccountlist`
  ADD CONSTRAINT `assessoraccountlist_ibfk_1` FOREIGN KEY (`AdminAccount_id`) REFERENCES `adminaccountlist` (`AdminAccount_id`),
  ADD CONSTRAINT `assessoraccountlist_ibfk_2` FOREIGN KEY (`AssessorType`) REFERENCES `listassessortype` (`AssessorType`);

--
-- Constraints for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD CONSTRAINT `student_profile_ibfk_1` FOREIGN KEY (`AssessorAccountID`) REFERENCES `assessoraccountlist` (`AssessorAccountID`),
  ADD CONSTRAINT `student_profile_ibfk_2` FOREIGN KEY (`assessment_code`) REFERENCES `assessment_records` (`assessment_code`),
  ADD CONSTRAINT `student_profile_ibfk_3` FOREIGN KEY (`internship_code`) REFERENCES `internship` (`internship_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
