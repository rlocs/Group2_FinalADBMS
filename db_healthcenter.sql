-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2025 at 02:46 PM
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
-- Database: `db_healthcenter`
--

-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `db_healthcenter`;
USE `db_healthcenter`;

DELIMITER $$

CREATE PROCEDURE GetPatientHistory(IN patientID INT)
BEGIN
    -- Appointments
    SELECT * FROM appointments WHERE patient_id = patientID;

    -- Consultations
    SELECT c.* 
    FROM consultations c
    JOIN appointments a ON c.appointment_id = a.appointment_id
    WHERE a.patient_id = patientID;

    -- Health Metrics
    SELECT * FROM healthmetrics WHERE patient_id = patientID;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE ScheduleAppointment(
    IN p_patient_id INT,
    IN p_worker_id INT,
    IN p_date DATETIME,
    IN p_purpose TEXT
)
BEGIN
    INSERT INTO appointments (patient_id, worker_id, appointment_date, purpose, status)
    VALUES (p_patient_id, p_worker_id, p_date, p_purpose, 'Scheduled');
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE CountPatientsByBarangay()
BEGIN
    SELECT h.barangay, COUNT(p.patient_id) AS total_patients
    FROM patients p
    JOIN households h ON p.household_id = h.household_id
    GROUP BY h.barangay;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE AddHealthMetric(
    IN p_patient_id INT,
    IN p_date DATE,
    IN p_blood_pressure VARCHAR(10),
    IN p_weight DECIMAL(5,2),
    IN p_temperature DECIMAL(4,1),
    IN p_notes TEXT
)
BEGIN
    INSERT INTO healthmetrics (patient_id, checkup_date, blood_pressure, weight, temperature, notes)
    VALUES (p_patient_id, p_date, p_blood_pressure, p_weight, p_temperature, p_notes);
END$$

DELIMITER ;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Healthworker','Nurse','Doctor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'health1', '$2y$10$VI1pS8PxAd00oDHBVrs0kO77IdW04fvietHSCwgKfXt/2oHyBA8aC', 'Healthworker'), -- password: healthpass
(2, 'nurse1', '$2y$10$iR8crVFYoghjgkOB4mW7Qe8KihmhCPSO1c3mUe/c0T.bN4VCYRkIy', 'Nurse'),         -- password: nursepass
(3, 'doctor1', '$2y$10$x4PLjpI/uPnEG2nW/kkkuu0c3wBZwJy9kgoWrJ2fef/TjDDczM/Bm', 'Doctor');        -- password: doctorpass

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `appointment_date` datetime DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `worker_id`, `appointment_date`, `purpose`, `status`) VALUES
(1, 1, 1, '2025-04-15 09:00:00', 'Routine Check-up', 'Scheduled'),
(2, 4, 2, '2025-04-16 10:30:00', 'Vaccination', 'Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `consultation_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `consultation_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`consultation_id`, `appointment_id`, `findings`, `prescription`, `remarks`, `consultation_date`) VALUES
(1, 1, 'Blood pressure slightly high.', 'Amlodipine 5mg once daily', 'Follow-up in 2 weeks', '2025-04-11 20:46:09'),
(2, 2, 'Patient vaccinated for influenza.', 'None', 'Return for next scheduled vaccination', '2025-04-11 20:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `healthmetrics`
--

CREATE TABLE `healthmetrics` (
  `metric_id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `checkup_date` date DEFAULT NULL,
  `blood_pressure` varchar(10) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `healthmetrics`
--

INSERT INTO `healthmetrics` (`metric_id`, `patient_id`, `checkup_date`, `blood_pressure`, `weight`, `temperature`, `notes`) VALUES
(1, 1, '2025-04-10', '140/90', 70.50, 36.8, 'High BP noted'),
(2, 4, '2025-04-08', '120/80', 55.00, 37.0, 'Normal metrics');

-- --------------------------------------------------------

--
-- Table structure for table `healthworkers`
--

CREATE TABLE `healthworkers` (
  `worker_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `healthworkers`
--

INSERT INTO `healthworkers` (`worker_id`, `full_name`, `role`, `contact_number`, `email`) VALUES
(1, 'Dr. Emilio Reyes', 'Doctor', '09998887777', 'emilio.reyes@clinic.com'),
(2, 'Nurse Liza Mendoza', 'Nurse', '09172345678', 'liza.mendoza@clinic.com');

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `household_id` int(11) NOT NULL,
  `household_head` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `total_members` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`household_id`, `household_head`, `address`, `barangay`, `total_members`, `created_at`) VALUES
(1, 'Juan Dela Cruz', '123 Sampaguita St.', 'Barangay Malinis', 4, '2025-04-11 12:46:08'),
(2, 'Maria Santos', '456 Rosal St.', 'Barangay Malinis', 3, '2025-04-11 12:46:08');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_household_head` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `household_id`, `full_name`, `gender`, `date_of_birth`, `contact_number`, `email`, `is_household_head`) VALUES
(1, 1, 'Juan Dela Cruz', 'Male', '1985-06-15', '09171234567', 'juan@example.com', 1),
(2, 1, 'Ana Dela Cruz', 'Female', '1987-10-20', '09181234567', 'ana@example.com', 0),
(3, 1, 'Carlos Dela Cruz', 'Male', '2010-08-05', NULL, NULL, 0),
(4, 2, 'Maria Santos', 'Female', '1990-12-12', '09193456789', 'maria@example.com', 1),
(5, 2, 'Jessa Santos', 'Female', '2015-04-03', NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`consultation_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `healthmetrics`
--
ALTER TABLE `healthmetrics`
  ADD PRIMARY KEY (`metric_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `healthworkers`
--
ALTER TABLE `healthworkers`
  ADD PRIMARY KEY (`worker_id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`household_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `household_id` (`household_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `healthmetrics`
--
ALTER TABLE `healthmetrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `healthworkers`
--
ALTER TABLE `healthworkers`
  MODIFY `worker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `household_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `healthworkers` (`worker_id`);

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`);

--
-- Constraints for table `healthmetrics`
--
ALTER TABLE `healthmetrics`
  ADD CONSTRAINT `healthmetrics_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
