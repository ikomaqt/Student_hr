-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 02:59 AM
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
-- Database: `health_record`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `guardian_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `guardian_name` varchar(100) NOT NULL,
  `guardian_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guardians`
--

INSERT INTO `guardians` (`guardian_id`, `student_id`, `guardian_name`, `guardian_date`) VALUES
(1, 1, 'sample', '2025-04-21');

-- --------------------------------------------------------

--
-- Table structure for table `immunization_record`
--

CREATE TABLE `immunization_record` (
  `immunization_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `vaccine_name` varchar(100) NOT NULL,
  `dose` varchar(50) NOT NULL,
  `date_given` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `immunization_record`
--

INSERT INTO `immunization_record` (`immunization_id`, `student_id`, `vaccine_name`, `dose`, `date_given`) VALUES
(1, 1, 'sample', '1', '2025-04-06');

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `medical_history_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `allergies` enum('Yes','No') DEFAULT 'No',
  `allergies_details` text DEFAULT NULL,
  `asthma` enum('Yes','No') DEFAULT 'No',
  `asthma_details` text DEFAULT NULL,
  `chicken_pox` enum('Yes','No') DEFAULT 'No',
  `chicken_pox_details` text DEFAULT NULL,
  `diabetes` enum('Yes','No') DEFAULT 'No',
  `diabetes_details` text DEFAULT NULL,
  `epilepsy` enum('Yes','No') DEFAULT 'No',
  `epilepsy_details` text DEFAULT NULL,
  `heart_disorder` enum('Yes','No') DEFAULT 'No',
  `heart_disorder_details` text DEFAULT NULL,
  `kidney_disease` enum('Yes','No') DEFAULT 'No',
  `kidney_disease_details` text DEFAULT NULL,
  `tuberculosis` enum('Yes','No') DEFAULT 'No',
  `tuberculosis_details` text DEFAULT NULL,
  `mumps` enum('Yes','No') DEFAULT 'No',
  `mumps_details` text DEFAULT NULL,
  `other_medical_history` enum('Yes','No') DEFAULT 'No',
  `other_medical_history_details` text DEFAULT NULL,
  `date_of_confinement` date DEFAULT NULL,
  `confinement_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_history`
--

INSERT INTO `medical_history` (`medical_history_id`, `student_id`, `allergies`, `allergies_details`, `asthma`, `asthma_details`, `chicken_pox`, `chicken_pox_details`, `diabetes`, `diabetes_details`, `epilepsy`, `epilepsy_details`, `heart_disorder`, `heart_disorder_details`, `kidney_disease`, `kidney_disease_details`, `tuberculosis`, `tuberculosis_details`, `mumps`, `mumps_details`, `other_medical_history`, `other_medical_history_details`, `date_of_confinement`, `confinement_details`) VALUES
(1, 1, 'Yes', 'sample', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2025-03-30', 'sample');

-- --------------------------------------------------------

--
-- Table structure for table `menstruation_history`
--

CREATE TABLE `menstruation_history` (
  `menstruation_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `menarche` varchar(50) DEFAULT NULL,
  `last_menstrual_period` date DEFAULT NULL,
  `ob_score` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menstruation_history`
--

INSERT INTO `menstruation_history` (`menstruation_id`, `student_id`, `menarche`, `last_menstrual_period`, `ob_score`) VALUES
(1, 1, '', '2025-04-06', '');

-- --------------------------------------------------------

--
-- Table structure for table `physical_examination`
--

CREATE TABLE `physical_examination` (
  `physical_exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `height` varchar(50) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `bmi` varchar(50) DEFAULT NULL,
  `blood_pressure` varchar(50) DEFAULT NULL,
  `pulse_rate` varchar(50) DEFAULT NULL,
  `respiratory_rate` varchar(50) DEFAULT NULL,
  `examined_by` varchar(100) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `physical_examination`
--

INSERT INTO `physical_examination` (`physical_exam_id`, `student_id`, `height`, `weight`, `bmi`, `blood_pressure`, `pulse_rate`, `respiratory_rate`, `examined_by`, `exam_date`, `remarks`) VALUES
(1, 1, '163', '43', '22', '107/59', '67', '23', 'Jeraldine Pascual', '2025-04-21', 'Normal');

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `section_name`) VALUES
(1, '7 - St. Michael'),
(2, '7 - St. Raphael'),
(3, '7 - St. Ramiel'),
(4, '7 - St. Zadkiel'),
(5, '8 - St. Gabriel'),
(6, '8 - St. Chamuel'),
(7, '8 - St. Haniel'),
(8, '9 - St. Jophiel'),
(9, '9 - St. Azrael'),
(10, '10 - St. Ariel'),
(11, '10 - St. Jeremiel'),
(12, '11 - Naphtali (STEM A)'),
(13, '11 - Joseph (STEM B)'),
(14, '11 - Asher (STEM C)'),
(15, '11 - Simeon (STEM D)'),
(16, '11 - Dan (HUMSS A)'),
(17, '11 - Benjamin (HUMSS B)'),
(18, '11 - Issachar (HUMSS C)'),
(19, '11 - Levi (ABM)'),
(20, '11 - Gad (ICT)'),
(21, '11 - Reuben (HE)'),
(22, '12 - John (STEM A)'),
(23, '12 - Mathew (STEM B)'),
(24, '12 - Jude (STEM C)'),
(25, '12 - James (HUMSS A)'),
(26, '12 - Philip (HUMSS B)'),
(27, '12 - Peter (ABM)'),
(28, '12 - Andrew (ICT)'),
(29, '12 - Thomas (HE)');

-- --------------------------------------------------------

--
-- Table structure for table `smoking_history`
--

CREATE TABLE `smoking_history` (
  `smoking_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `smokes` enum('Yes','No') NOT NULL,
  `age_of_onset` int(11) DEFAULT 0,
  `sticks_per_day` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `smoking_history`
--

INSERT INTO `smoking_history` (`smoking_id`, `student_id`, `smokes`, `age_of_onset`, `sticks_per_day`) VALUES
(2, 1, 'No', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `special_needs`
--

CREATE TABLE `special_needs` (
  `special_need_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `has_special_needs` enum('Yes','No') DEFAULT 'No',
  `physical_limitations` enum('Yes','No') DEFAULT 'No',
  `physical_limitations_specify` text DEFAULT '',
  `emotional_disorder` enum('Yes','No') DEFAULT 'No',
  `emotional_disorder_specify` text DEFAULT '',
  `natural_conditions` enum('Yes','No') DEFAULT 'No',
  `natural_conditions_specify` text DEFAULT '',
  `attention_conditions` enum('Yes','No') DEFAULT 'No',
  `attention_conditions_specify` text DEFAULT '',
  `medical_conditions` enum('Yes','No') DEFAULT 'No',
  `medical_conditions_specify` text DEFAULT '',
  `receiving_treatment` enum('Yes','No') DEFAULT 'No',
  `contact_permission` enum('Yes','No') DEFAULT 'No',
  `health_professional_name` varchar(100) DEFAULT '',
  `health_professional_address` text DEFAULT '',
  `health_professional_office_number` varchar(15) DEFAULT '',
  `health_professional_mobile_number` varchar(15) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `special_needs`
--

INSERT INTO `special_needs` (`special_need_id`, `student_id`, `has_special_needs`, `physical_limitations`, `physical_limitations_specify`, `emotional_disorder`, `emotional_disorder_specify`, `natural_conditions`, `natural_conditions_specify`, `attention_conditions`, `attention_conditions_specify`, `medical_conditions`, `medical_conditions_specify`, `receiving_treatment`, `contact_permission`, `health_professional_name`, `health_professional_address`, `health_professional_office_number`, `health_professional_mobile_number`) VALUES
(1, 1, 'Yes', '', '', 'Yes', 'sample', '', '', '', '', '', '', 'Yes', 'Yes', 'sample', 'sample', '35252525', '34242423');

-- --------------------------------------------------------

--
-- Table structure for table `student_info`
--

CREATE TABLE `student_info` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `lrn` bigint(20) NOT NULL,
  `date_of_birth` date NOT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_phone` varchar(15) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_phone` varchar(15) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_relationship` varchar(50) DEFAULT NULL,
  `emergency_contact_phone` varchar(15) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_info`
--

INSERT INTO `student_info` (`student_id`, `name`, `sex`, `lrn`, `date_of_birth`, `nationality`, `address`, `father_name`, `father_phone`, `mother_name`, `mother_phone`, `emergency_contact_name`, `emergency_contact_relationship`, `emergency_contact_phone`, `section_id`) VALUES
(1, 'Jaja  Jacinto', 'Female', 1234567890, '2025-04-09', 'Filipino', 'Quezon', '', '', '', '', '', '', '', 17);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `has_record` tinyint(1) DEFAULT 0,
  `lrn` varchar(20) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `first_name`, `middle_name`, `last_name`, `password`, `created_at`, `has_record`, `lrn`, `section_id`) VALUES
(1, 'jasminejacinto27@gmail.com', 'Jaja', '', 'Jacinto', '$2y$10$KBLpWabjJObrd2AFlipJs.iPF6K9WqsAOUhgvopjWVFCk5SyKPFES', '2025-04-10 02:48:29', 1, '1234567890', 17),
(5, 'marieperona0@gmail.com', 'Marie', '', 'Perona', '$2y$10$rz6laI9U5gEUQRfsT7KqveNU..Z79dq5Qq/edgttORe96P5MbsSui', '2025-04-14 01:11:14', 0, '12345678', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`guardian_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `immunization_record`
--
ALTER TABLE `immunization_record`
  ADD PRIMARY KEY (`immunization_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`medical_history_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `menstruation_history`
--
ALTER TABLE `menstruation_history`
  ADD PRIMARY KEY (`menstruation_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `physical_examination`
--
ALTER TABLE `physical_examination`
  ADD PRIMARY KEY (`physical_exam_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `smoking_history`
--
ALTER TABLE `smoking_history`
  ADD PRIMARY KEY (`smoking_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `special_needs`
--
ALTER TABLE `special_needs`
  ADD PRIMARY KEY (`special_need_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_info`
--
ALTER TABLE `student_info`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `lrn` (`lrn`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `lrn` (`lrn`),
  ADD KEY `fk_section` (`section_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `guardian_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `immunization_record`
--
ALTER TABLE `immunization_record`
  MODIFY `immunization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `medical_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menstruation_history`
--
ALTER TABLE `menstruation_history`
  MODIFY `menstruation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `physical_examination`
--
ALTER TABLE `physical_examination`
  MODIFY `physical_exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `smoking_history`
--
ALTER TABLE `smoking_history`
  MODIFY `smoking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `special_needs`
--
ALTER TABLE `special_needs`
  MODIFY `special_need_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_info`
--
ALTER TABLE `student_info`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `immunization_record`
--
ALTER TABLE `immunization_record`
  ADD CONSTRAINT `immunization_record_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `menstruation_history`
--
ALTER TABLE `menstruation_history`
  ADD CONSTRAINT `menstruation_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `physical_examination`
--
ALTER TABLE `physical_examination`
  ADD CONSTRAINT `physical_examination_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`student_id`);

--
-- Constraints for table `smoking_history`
--
ALTER TABLE `smoking_history`
  ADD CONSTRAINT `smoking_history_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `special_needs`
--
ALTER TABLE `special_needs`
  ADD CONSTRAINT `special_needs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_info` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_section` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
