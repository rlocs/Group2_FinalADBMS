-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 09:51 AM
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
-- Database: `renew_healthcenter`
--
CREATE DATABASE IF NOT EXISTS `renew_healthcenter`;
USE `renew_healthcenter`;

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=root@localhost PROCEDURE add_appointment (
IN patient_name VARCHAR(255),
IN date DATE,
IN time TIME,
IN doctor VARCHAR(255),
IN reason VARCHAR(255)
)
BEGIN
INSERT INTO appointments (patient_name, date, time, doctor, reason)
VALUES (patient_name, date, time, doctor, reason);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_today_appointment_count`(OUT total_today INT)
BEGIN
    SELECT COUNT(*) INTO total_today FROM appointments WHERE date = CURDATE();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_tomorrow_appointment_count`(OUT total_tomorrow INT)
BEGIN
    SELECT COUNT(*) INTO total_tomorrow FROM appointments WHERE date = DATE_ADD(CURDATE(), INTERVAL 1 DAY);
END$$


CREATE DEFINER=`root`@`localhost` PROCEDURE `add_emergency_contact` (IN `p_household_id` INT, IN `p_emergency_contact_name` VARCHAR(255), IN `p_emergency_contact_number` VARCHAR(15), IN `p_emergency_contact_relation` VARCHAR(100))   BEGIN
    INSERT INTO emergency_contacts (household_id, emergency_contact_name, emergency_contact_number, emergency_contact_relation)
    VALUES (p_household_id, p_emergency_contact_name, p_emergency_contact_number, p_emergency_contact_relation);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_household` (IN `p_head_name` VARCHAR(255), IN `p_purok` VARCHAR(255), IN `p_nic_number` VARCHAR(20), IN `p_num_members` INT)   BEGIN
    INSERT INTO households (head_name, purok, nic_number, num_members)
    VALUES (p_head_name, p_purok, p_nic_number, p_num_members);
    
    SELECT LAST_INSERT_ID() AS household_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_household_members` (IN `p_household_id` INT, IN `p_member_name` VARCHAR(255), IN `p_relation` VARCHAR(255), IN `p_age` INT, IN `p_sex` ENUM('Male','Female','Other'))   BEGIN
    INSERT INTO household_members (household_id, member_name, relation, age, sex)
    VALUES (p_household_id, p_member_name, p_relation, p_age, p_sex);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_intervention` (IN `p_patient_id` INT, IN `p_doctor` VARCHAR(255), IN `p_reason` TEXT, IN `p_intervention` TEXT)   BEGIN
  INSERT INTO interventions (patient_id, doctor, reason, intervention)
  VALUES (p_patient_id, p_doctor, p_reason, p_intervention);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_medical_information` (IN `p_household_id` INT, IN `p_medical_condition` TEXT, IN `p_allergies` TEXT)   BEGIN
    INSERT INTO medical_information (household_id, medical_condition, allergies)
    VALUES (p_household_id, p_medical_condition, p_allergies);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_patient` (IN `p_name` VARCHAR(255), IN `p_gender` VARCHAR(10), IN `p_address` VARCHAR(255), IN `p_parents` VARCHAR(255), IN `p_dob` DATE, IN `p_weight` DECIMAL(5,2), IN `p_height` DECIMAL(5,2), IN `p_blood_type` VARCHAR(5), IN `p_reason` VARCHAR(255))   BEGIN
    INSERT INTO patients (
        name, gender, address, parents, dob, weight, height, blood_type, reason
    ) VALUES (
        p_name, p_gender, p_address, p_parents, p_dob, p_weight, p_height, p_blood_type, p_reason
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CountPatientsByBarangay` ()   BEGIN
    SELECT h.barangay, COUNT(p.patient_id) AS total_patients
    FROM patients p
    JOIN households h ON p.household_id = h.household_id
    GROUP BY h.barangay;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `create_user` (IN `p_name` VARCHAR(255), IN `p_nic` VARCHAR(20), IN `p_email` VARCHAR(255), IN `p_gender` ENUM('Male','Female','Other'), IN `p_dob` DATE, IN `p_address` TEXT, IN `p_role` ENUM('Healthworker','Nurse','Doctor'), IN `p_username` VARCHAR(50), IN `p_password` VARCHAR(255))   BEGIN
  IF EXISTS (SELECT 1 FROM users WHERE username = p_username) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already exists.';
  ELSE
    INSERT INTO users (
      name, nic, email, gender, dob, address, role, username, password
    ) VALUES (
      p_name, p_nic, p_email, p_gender, p_dob, p_address, p_role, p_username, p_password
    );
  END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_appointment` (IN `appt_id` INT)   BEGIN
    DELETE FROM appointments WHERE appointment_id = appt_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_households` (IN `hh_id` INT)   BEGIN
    DELETE FROM households WHERE household_id = hh_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_patient` (IN `pat_id` INT)   BEGIN
    DELETE FROM patients WHERE patient_id = pat_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_appointments_for_days` ()   BEGIN
    SELECT 'today' AS day_type, appointment_id, patient_name, date, time, doctor, reason
    FROM appointments
    WHERE date = CURDATE()
    UNION ALL
    SELECT 'tomorrow' AS day_type, appointment_id, patient_name, date, time, doctor, reason
    FROM appointments
    WHERE date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    ORDER BY date, time;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_appointment_counts_by_date` ()   BEGIN
  SELECT date, COUNT(*) AS appointment_count
  FROM appointments
  WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY date
  ORDER BY date;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_interventions_by_patient` (IN `p_patient_id` INT)   BEGIN
  SELECT i.intervention_id, p.name AS patient_name, i.doctor, i.reason, i.intervention, i.created_at
  FROM interventions i
  JOIN patients p ON i.patient_id = p.patient_id
  WHERE i.patient_id = p_patient_id
  ORDER BY i.created_at DESC;
END$$

CREATE PROCEDURE get_patient_counts_by_age_range()
BEGIN
SELECT
DATE(created_at) AS date,
COUNT(*) AS patient_count
FROM patients
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
GROUP BY DATE(created_at)
ORDER BY DATE(created_at);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_appointments` (IN `search_term` VARCHAR(255))   BEGIN
    SELECT * FROM appointments
    WHERE patient_name LIKE CONCAT('%', search_term, '%');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_households` (IN `search_term` VARCHAR(255))   BEGIN
    SELECT * FROM households
    WHERE head_name LIKE CONCAT('%', search_term, '%');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_patients` (IN `search_term` VARCHAR(255))   BEGIN
    SELECT * FROM patients
    WHERE name LIKE CONCAT('%', search_term, '%');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `show_appointment_list` ()   BEGIN
    SELECT * FROM appointments;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateAppointment` (IN `p_appointment_id` INT, IN `p_patient_name` VARCHAR(255), IN `p_date` DATE, IN `p_time` TIME, IN `p_doctor` VARCHAR(255), IN `p_reason` TEXT)   BEGIN
    UPDATE appointments
    SET
        patient_name = p_patient_name,
        date = p_date,
        time = p_time,
        doctor = p_doctor,
        reason = p_reason
    WHERE appointment_id = p_appointment_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateHouseholdProfile` (IN `p_household_id` INT, IN `p_head_name` VARCHAR(255), IN `p_purok` VARCHAR(255), IN `p_nic_number` VARCHAR(20), IN `p_num_members` INT, IN `p_medical_condition` TEXT, IN `p_allergies` TEXT, IN `p_emergency_contact_name` VARCHAR(255), IN `p_emergency_contact_number` VARCHAR(15), IN `p_emergency_contact_relation` VARCHAR(100))   BEGIN
    -- Update Household Table
    UPDATE households
    SET head_name = p_head_name, 
        purok = p_purok, 
        nic_number = p_nic_number, 
        num_members = p_num_members
    WHERE household_id = p_household_id;

    -- Update Medical Information Table
    UPDATE medical_information
    SET medical_condition = p_medical_condition,
        allergies = p_allergies
    WHERE household_id = p_household_id;

    -- Update Emergency Contacts Table
    UPDATE emergency_contacts
    SET emergency_contact_name = p_emergency_contact_name, 
        emergency_contact_number = p_emergency_contact_number, 
        emergency_contact_relation = p_emergency_contact_relation
    WHERE household_id = p_household_id;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdatePatient` (IN `p_id` INT, IN `p_name` VARCHAR(100), IN `p_gender` ENUM('Male','Female','Other'), IN `p_address` VARCHAR(255), IN `p_parents` VARCHAR(255), IN `p_dob` DATE, IN `p_weight` DECIMAL(5,2), IN `p_height` DECIMAL(5,2), IN `p_blood_type` ENUM('A','B','AB','O','O+','Other'), IN `p_reason` TEXT)   BEGIN
    UPDATE patients
    SET 
        name = p_name,
        gender = p_gender,
        address = p_address,
        parents = p_parents,
        dob = p_dob,
        weight = p_weight,
        height = p_height,
        blood_type = p_blood_type,
        reason = p_reason
    WHERE patient_id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_user_profile` (IN `p_user_id` INT, IN `p_name` VARCHAR(255), IN `p_nic` VARCHAR(20), IN `p_email` VARCHAR(255), IN `p_gender` ENUM('Male','Female','Other'), IN `p_dob` DATE, IN `p_address` TEXT)   BEGIN
    UPDATE users
    SET name = p_name,
        nic = p_nic,
        email = p_email,
        gender = p_gender,
        dob = p_dob,
        address = p_address
    WHERE user_id = p_user_id;
END$$


CREATE DEFINER=`root`@`localhost` PROCEDURE `get_total_households_count`(OUT total_households INT)
BEGIN
    SELECT COUNT(*) INTO total_households FROM households;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_total_patients_count`(OUT total_patients INT)
BEGIN
    SELECT COUNT(*) INTO total_patients FROM patients;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_total_households_rs`()
BEGIN
    SELECT COUNT(*) AS total_households FROM households;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_total_patients_rs`()
BEGIN
    SELECT COUNT(*) AS total_patients FROM patients;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_today_appointment_count_rs`()
BEGIN
    SELECT COUNT(*) AS total_today FROM appointments WHERE date = CURDATE();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_tomorrow_appointment_count_rs`()
BEGIN
    SELECT COUNT(*) AS total_tomorrow FROM appointments WHERE date = DATE_ADD(CURDATE(), INTERVAL 1 DAY);
END$$


CREATE DEFINER=`root`@`localhost` PROCEDURE `get_patient_age_distribution_sp`()
BEGIN
    SELECT
        CASE
            WHEN dob IS NULL OR dob = '0000-00-00' THEN 'Unknown'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 0 AND 9 THEN '0-9'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 10 AND 19 THEN '10-19'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 20 AND 29 THEN '20-29'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 30 AND 39 THEN '30-39'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 40 AND 49 THEN '40-49'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 50 AND 59 THEN '50-59'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= 60 THEN '60+'
            ELSE 'Unknown'
        END AS age_range,
        COUNT(*) AS patient_count
    FROM patients
    GROUP BY age_range
    ORDER BY age_range;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_medical_conditions_prevalence_sp`()
BEGIN
    SELECT condition_name, COUNT(*) AS condition_count FROM (
        SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(medical_condition, ',', numbers.n), ',', -1)) AS condition_name
        FROM medical_information
        JOIN (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
        ) numbers ON CHAR_LENGTH(medical_condition) - CHAR_LENGTH(REPLACE(medical_condition, ',', '')) >= numbers.n - 1
        WHERE medical_condition IS NOT NULL AND medical_condition != ''
    ) AS conditions
    WHERE condition_name != '' AND LOWER(condition_name) != 'non'
    GROUP BY condition_name
    ORDER BY condition_count DESC
    LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_appointments_paginated` (
    IN `search_term` VARCHAR(255),
    IN `limit_val` INT,
    IN `offset_val` INT
)
BEGIN
    IF search_term IS NOT NULL AND search_term != '' THEN
        SELECT * FROM appointments
        WHERE patient_name LIKE CONCAT('%', search_term, '%')
        ORDER BY appointment_id ASC
        LIMIT limit_val OFFSET offset_val;
    ELSE
        SELECT * FROM appointments
        ORDER BY appointment_id ASC
        LIMIT limit_val OFFSET offset_val;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_all_appointments_paginated` (
    IN `limit_val` INT,
    IN `offset_val` INT
)
BEGIN
    SELECT * FROM appointments
    ORDER BY appointment_id ASC
    LIMIT limit_val OFFSET offset_val;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_appointments_count` (
    IN `search_term` VARCHAR(255)
)
BEGIN
    IF search_term IS NOT NULL AND search_term != '' THEN
        SELECT COUNT(*) FROM appointments
        WHERE patient_name LIKE CONCAT('%', search_term, '%');
    ELSE
        SELECT COUNT(*) FROM appointments;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_households_count` (
    IN `search_term` VARCHAR(255)
)
BEGIN
    IF search_term IS NOT NULL AND search_term != '' THEN
        SELECT COUNT(*) FROM households
        WHERE head_name LIKE CONCAT('%', search_term, '%');
    ELSE
        SELECT COUNT(*) FROM households;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_households_paginated` (
    IN `search_term` VARCHAR(255),
    IN `limit_val` INT,
    IN `offset_val` INT
)
BEGIN
    IF search_term IS NOT NULL AND search_term != '' THEN
        SELECT 
            h.*, 
            mi.medical_condition, 
            mi.allergies, 
            ec.emergency_contact_name, 
            ec.emergency_contact_number, 
            ec.emergency_contact_relation
        FROM households h
        LEFT JOIN medical_information mi ON h.household_id = mi.household_id
        LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
        WHERE h.head_name LIKE CONCAT('%', search_term, '%')
        ORDER BY h.created_at DESC
        LIMIT limit_val OFFSET offset_val;
    ELSE
        SELECT 
            h.*, 
            mi.medical_condition, 
            mi.allergies, 
            ec.emergency_contact_name, 
            ec.emergency_contact_number, 
            ec.emergency_contact_relation
        FROM households h
        LEFT JOIN medical_information mi ON h.household_id = mi.household_id
        LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
        ORDER BY h.created_at DESC
        LIMIT limit_val OFFSET offset_val;
    END IF;
END$$

CREATE PROCEDURE get_all_households_paginated (
    IN limit_val INT,
    IN offset_val INT
)
BEGIN
    SELECT 
        h.*, 
        mi.medical_condition, 
        mi.allergies, 
        ec.emergency_contact_name, 
        ec.emergency_contact_number, 
        ec.emergency_contact_relation
    FROM households h
    LEFT JOIN medical_information mi ON h.household_id = mi.household_id
    LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
    ORDER BY h.created_at DESC
    LIMIT limit_val OFFSET offset_val;
END$$


CREATE DEFINER=`root`@`localhost` PROCEDURE `search_patients_count` (
    IN `search_term` VARCHAR(255)
)
BEGIN
    IF search_term IS NOT NULL AND search_term != '' THEN
        SELECT COUNT(*) AS total_patients FROM patients
        WHERE name LIKE CONCAT('%', search_term, '%');
    ELSE
        SELECT COUNT(*) AS total_patients FROM patients;
    END IF;
END$$

CREATE PROCEDURE `get_households_paginated_search` (
    IN search_term VARCHAR(255),
    IN limit_val INT,
    IN offset_val INT
)
BEGIN
    IF search_term IS NOT NULL AND search_term != '' THEN
        SELECT 
            h.household_id, 
            h.head_name, 
            h.purok, 
            h.nic_number, 
            h.num_members, 
            h.created_at, 
            mi.medical_condition, 
            mi.allergies, 
            ec.emergency_contact_name, 
            ec.emergency_contact_number, 
            ec.emergency_contact_relation
        FROM households h
        LEFT JOIN medical_information mi ON h.household_id = mi.household_id
        LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
        WHERE h.head_name LIKE CONCAT('%', search_term, '%')
        ORDER BY h.created_at DESC
        LIMIT limit_val OFFSET offset_val;
    ELSE
        SELECT 
            h.household_id, 
            h.head_name, 
            h.purok, 
            h.nic_number, 
            h.num_members, 
            h.created_at, 
            mi.medical_condition, 
            mi.allergies, 
            ec.emergency_contact_name, 
            ec.emergency_contact_number, 
            ec.emergency_contact_relation
        FROM households h
        LEFT JOIN medical_information mi ON h.household_id = mi.household_id
        LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
        ORDER BY h.created_at DESC
        LIMIT limit_val OFFSET offset_val;
    END IF;
END$$

CREATE PROCEDURE get_household_members(IN p_household_id INT)
BEGIN
  SELECT member_id, household_id, member_name, relation, age, sex
  FROM household_members
  WHERE household_id = p_household_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Procedures
--


-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `doctor` varchar(255) NOT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE IF NOT EXISTS residents (
  resident_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO residents (name, email, phone, address, password) VALUES (
  'John Doe',
  'johndoe@example.com',
  '123-456-7890',
  '123 Main St, Barangay San Pedro, Sto. Tomas, Batangas',
  '$2y$10$BJVTjSrGou9CbMjv0AMRWuqb99OLMLTHS3uqso6odmaarv2ltVxBq' -- Example hashed password
);
--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_name`, `date`, `time`, `doctor`, `reason`) VALUES
(927, 'John Doe', '2025-04-15', '09:00:00', 'Dr. Smith', 'Routine Check-up'),
(928, 'Jane Doe', '2025-04-16', '10:30:00', 'Dr. Lee', 'Vaccination'),
(929, 'Alice Johnson', '2025-04-17', '11:00:00', 'Dr. Brown', 'Consultation for fever'),
(930, 'Bob Martin', '2025-04-18', '08:30:00', 'Dr. Green', 'Follow-up check-up'),
(931, 'fgdfgd', '2003-02-16', '12:22:00', 'Dr.Harley', 'Checkup'),
(932, 'ralph lauren bautista', '2025-05-07', '15:13:00', 'Dr.Harley', 'Flu'),
(933, 'dasdasda', '2025-05-08', '07:18:00', 'Dr.Harley', 'Emergency');

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `emergency_id` int(11) NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_number` varchar(15) DEFAULT NULL,
  `emergency_contact_relation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergency_contacts`
--

INSERT INTO `emergency_contacts` (`emergency_id`, `household_id`, `emergency_contact_name`, `emergency_contact_number`, `emergency_contact_relation`) VALUES
(927, 927, 'Sarah Doe', '09123456789', 'Sister');

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `household_id` int(11) NOT NULL,
  `head_name` varchar(255) NOT NULL,
  `purok` varchar(255) NOT NULL,
  `nic_number` varchar(20) NOT NULL,
  `num_members` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`household_id`, `head_name`, `purok`, `nic_number`, `num_members`, `created_at`) VALUES
(927, 'John Doe', 'Purok 1', '1234567890', 5, '2025-05-07 06:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `household_members`
--

CREATE TABLE `household_members` (
  `member_id` int(11) NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sex` enum('Male','Female','Other') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `household_members`
--

INSERT INTO `household_members` (`member_id`, `household_id`, `member_name`, `relation`, `age`, `sex`) VALUES
(927, 927, 'John John', 'Spouse', 22, 'Male');

-- --------------------------------------------------------

--
-- Table structure for table `interventions`
--

CREATE TABLE `interventions` (
  `intervention_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `intervention` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interventions`
--

INSERT INTO `interventions` (`intervention_id`, `patient_id`, `doctor`, `reason`, `intervention`, `created_at`) VALUES
(927, 927, 'Dr. Jane Doe', 'Post-surgery follow-up', 'Reviewed recovery and adjusted medication.', '2025-05-07 06:07:20'),
(928, 928, 'Dr. Alan Smith', 'Routine Checkup', 'Performed general physical exam and recommended exercise.', '2025-05-07 06:07:20'),
(929, 929, 'Dr. Maria Lopez', 'High blood pressure', 'Monitored blood pressure and prescribed medication.', '2025-05-07 06:07:20'),
(930, 930, 'Dr. John Kim', 'Diabetes Management', 'Adjusted insulin dosage and advised dietary changes.', '2025-05-07 06:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `medical_information`
--

CREATE TABLE `medical_information` (
  `medical_id` int(11) NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `medical_condition` text DEFAULT NULL,
  `allergies` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_information`
--

INSERT INTO `medical_information` (`medical_id`, `household_id`, `medical_condition`, `allergies`) VALUES
(927, 927, 'Hypertension, Asthma', 'Pollen, Dust');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `household_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `parents` varchar(255) DEFAULT NULL,
  `dob` date NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `blood_type` enum('A','B','AB','O','O+','Other') DEFAULT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `household_id`, `name`, `gender`, `address`, `parents`, `dob`, `weight`, `height`, `blood_type`, `reason`) VALUES
(927, 1, 'Juan Dela Cruz', 'Male', '123 Street, City', 'Maria Dela Cruz, Juan Dela Cruz Sr.', '1985-06-15', 70.50, 1.75, 'O', 'General checkup'),
(928, 2, 'Ana Dela Cruz', 'Female', '456 Avenue, City', 'Jose Dela Cruz, Maria Dela Cruz', '1987-10-20', 55.30, 1.60, 'A', 'Routine exam'),
(929, 3, 'Carlos Dela Cruz', 'Male', '789 Road, City', 'Maria Dela Cruz', '2010-08-05', 30.00, 1.40, 'B', 'Vaccination'),
(930, 4, 'Maria Santos', 'Female', '101 Park, City', 'Jose Santos, Laura Santos', '1990-12-12', 62.00, 1.65, 'AB', 'Consultation'),
(931, NULL, 'Ralph Lauren Bautista', 'Male', 'sd', 'sd', '2022-06-23', 54.00, 145.00, 'O', 'wala lang');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `nic` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `dob` date NOT NULL,
  `address` text NOT NULL,
  `role` enum('Healthworker','Nurse','Doctor') NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `nic`, `email`, `gender`, `dob`, `address`, `role`, `username`, `password`) VALUES
(927, 'Health Worker One', 'HW123456', 'health1@example.com', 'Male', '1990-01-01', '123 Health St, Wellness City', 'Healthworker', 'health1', '$2y$10$e3OWiba9X/T/DrrIGJjNYOrKA0PaiuLAcH6Wp.p3LcI6sCoBDGadG'),
(928, 'Nurse One', 'NR654321', 'nurse1@example.com', 'Female', '1988-05-12', '456 Nurse Ave, Caretown', 'Nurse', 'nurse1', '$2y$10$iR8crVFYoghjgkOB4mW7Qe8KihmhCPSO1c3mUe/c0T.bN4VCYRkIy'),
(929, 'Doctor One', 'DR112233', 'doctor1@example.com', 'Other', '1985-03-30', '789 Doctor Blvd, Healville', 'Doctor', 'doctor1', '$2y$10$x4PLjpI/uPnEG2nW/kkkuu0c3wBZwJy9kgoWrJ2fef/TjDDczM/Bm');

-- Add medication-related tables and procedures

-- Create medications table
CREATE TABLE IF NOT EXISTS `medications` (
    `medication_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `generic_name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `unit` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create medication_inventory table
CREATE TABLE IF NOT EXISTS `medication_inventory` (
    `inventory_id` INT AUTO_INCREMENT PRIMARY KEY,
    `medication_id` INT NOT NULL,
    `batch_number` VARCHAR(100),
    `quantity` INT NOT NULL DEFAULT 0,
    `expiry_date` DATE,
    `supplier` VARCHAR(255),
    `date_received` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`medication_id`) REFERENCES `medications`(`medication_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create medication_transactions table
CREATE TABLE IF NOT EXISTS `medication_transactions` (
    `transaction_id` INT AUTO_INCREMENT PRIMARY KEY,
    `medication_id` INT NOT NULL,
    `inventory_id` INT NOT NULL,
    `transaction_type` ENUM('IN', 'OUT') NOT NULL,
    `quantity` INT NOT NULL,
    `patient_id` INT,
    `notes` TEXT,
    `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_by` VARCHAR(100),
    FOREIGN KEY (`medication_id`) REFERENCES `medications`(`medication_id`),
    FOREIGN KEY (`inventory_id`) REFERENCES `medication_inventory`(`inventory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add medication-related stored procedures
DELIMITER $$

-- Procedure to add a new medication
CREATE PROCEDURE IF NOT EXISTS `add_medication` (
    IN p_name VARCHAR(255),
    IN p_generic_name VARCHAR(255),
    IN p_category VARCHAR(100),
    IN p_unit VARCHAR(50),
    IN p_description TEXT
)
BEGIN
    INSERT INTO medications (name, generic_name, category, unit, description)
    VALUES (p_name, p_generic_name, p_category, p_unit, p_description);
    SELECT LAST_INSERT_ID() AS medication_id;
END$$

-- Procedure to add inventory
CREATE PROCEDURE IF NOT EXISTS `add_inventory` (
    IN p_medication_id INT,
    IN p_batch_number VARCHAR(100),
    IN p_quantity INT,
    IN p_expiry_date DATE,
    IN p_supplier VARCHAR(255),
    IN p_date_received DATE
)
BEGIN
    INSERT INTO medication_inventory (medication_id, batch_number, quantity, expiry_date, supplier, date_received)
    VALUES (p_medication_id, p_batch_number, p_quantity, p_expiry_date, p_supplier, p_date_received);
    
    INSERT INTO medication_transactions (medication_id, inventory_id, transaction_type, quantity, notes, created_by)
    VALUES (p_medication_id, LAST_INSERT_ID(), 'IN', p_quantity, CONCAT('Initial stock - Batch: ', p_batch_number), USER());
END$$

-- Procedure to dispense medication
CREATE PROCEDURE IF NOT EXISTS `dispense_medication` (
    IN p_inventory_id INT,
    IN p_quantity INT,
    IN p_patient_id INT,
    IN p_notes TEXT,
    IN p_created_by VARCHAR(100)
)
BEGIN
    DECLARE v_medication_id INT;
    DECLARE v_current_quantity INT;
    
    -- Get medication_id and current quantity
    SELECT medication_id, quantity INTO v_medication_id, v_current_quantity
    FROM medication_inventory WHERE inventory_id = p_inventory_id;
    
    -- Check if there's enough stock
    IF v_current_quantity >= p_quantity THEN
        -- Update inventory
        UPDATE medication_inventory 
        SET quantity = quantity - p_quantity
        WHERE inventory_id = p_inventory_id;
        
        -- Record transaction
        INSERT INTO medication_transactions (
            medication_id, inventory_id, transaction_type, 
            quantity, patient_id, notes, created_by
        ) VALUES (
            v_medication_id, p_inventory_id, 'OUT', 
            p_quantity, p_patient_id, p_notes, p_created_by
        );
        
        SELECT 'Success' AS result;
    ELSE
        SELECT CONCAT('Insufficient stock. Available: ', v_current_quantity) AS result;
    END IF;
END$$

-- Procedure to get medication inventory summary
CREATE PROCEDURE IF NOT EXISTS `get_medication_inventory` ()
BEGIN
    SELECT m.medication_id, m.name, m.generic_name, m.category, m.unit,
           SUM(mi.quantity) AS total_quantity,
           MIN(mi.expiry_date) AS nearest_expiry
    FROM medications m
    LEFT JOIN medication_inventory mi ON m.medication_id = mi.medication_id
    GROUP BY m.medication_id
    ORDER BY m.name;
END$$

-- Procedure to get expiring medications
CREATE PROCEDURE IF NOT EXISTS `get_expiring_medications` (IN days_threshold INT)
BEGIN
    SELECT m.name, m.generic_name, mi.batch_number, mi.quantity, 
           mi.expiry_date, DATEDIFF(mi.expiry_date, CURDATE()) AS days_remaining
    FROM medication_inventory mi
    JOIN medications m ON mi.medication_id = m.medication_id
    WHERE mi.expiry_date IS NOT NULL 
      AND mi.expiry_date <= DATE_ADD(CURDATE(), INTERVAL days_threshold DAY)
      AND mi.quantity > 0
    ORDER BY mi.expiry_date;
END$$

DELIMITER ;

-- --------------------------------------------------------
--
-- Table structure for table `residents`
--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`emergency_id`),
  ADD KEY `household_id` (`household_id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`household_id`);

--
-- Indexes for table `household_members`
--
ALTER TABLE `household_members`
  ADD PRIMARY KEY (`member_id`),
  ADD KEY `household_id` (`household_id`);

--
-- Indexes for table `interventions`
--
ALTER TABLE `interventions`
  ADD PRIMARY KEY (`intervention_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `medical_information`
--
ALTER TABLE `medical_information`
  ADD PRIMARY KEY (`medical_id`),
  ADD KEY `household_id` (`household_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=934;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `emergency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=928;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `household_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=928;

--
-- AUTO_INCREMENT for table `household_members`
--
ALTER TABLE `household_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=928;

--
-- AUTO_INCREMENT for table `interventions`
--
ALTER TABLE `interventions`
  MODIFY `intervention_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=927;

--
-- AUTO_INCREMENT for table `medical_information`
--
ALTER TABLE `medical_information`
  MODIFY `medical_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=928;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=932;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=930;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE CASCADE;

--
-- Constraints for table `household_members`
--
ALTER TABLE `household_members`
  ADD CONSTRAINT `household_members_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE CASCADE;

--
-- Constraints for table `interventions`
--
ALTER TABLE `interventions`
  ADD CONSTRAINT `interventions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_information`
--
ALTER TABLE `medical_information`
  ADD CONSTRAINT `medical_information_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE CASCADE;
COMMIT;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

