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

DELIMITER $$

CREATE PROCEDURE add_appointment(
    IN patient_name VARCHAR(255),
    IN date DATE,
    IN time TIME,
    IN doctor VARCHAR(255),
    IN reason VARCHAR(255)
)
BEGIN
    INSERT INTO appointments (patient_name, date, time, doctor, reason)
    VALUES (patient_name, date, time, doctor, reason);
END $$

DELIMITER ;
DELIMITER //

CREATE PROCEDURE add_patient (
    IN p_name VARCHAR(255),
    IN p_gender VARCHAR(10),
    IN p_address VARCHAR(255),
    IN p_parents VARCHAR(255),
    IN p_dob DATE,
    IN p_weight DECIMAL(5,2),
    IN p_height DECIMAL(5,2),
    IN p_blood_type VARCHAR(5),
    IN p_reason VARCHAR(255)
)
BEGIN
    -- Insert a new patient into the `patients` table
    INSERT INTO patients (
        name,
        gender,
        address,
        parents,
        dob,
        weight,
        height,
        blood_type,
        reason
    ) VALUES (
        p_name,
        p_gender,
        p_address,
        p_parents,
        p_dob,
        p_weight,
        p_height,
        p_blood_type,
        p_reason
    );
END //

DELIMITER ;

-- Stored Procedure for Adding Household
DELIMITER $$

CREATE PROCEDURE add_household(
    IN p_head_name VARCHAR(255),
    IN p_purok VARCHAR(255),
    IN p_nic_number VARCHAR(20),
    IN p_num_members INT
)
BEGIN
    INSERT INTO households (head_name, purok, nic_number, num_members)
    VALUES (p_head_name, p_purok, p_nic_number, p_num_members);
    SELECT LAST_INSERT_ID() AS household_id;
END $$

DELIMITER ;

-- Stored Procedure for Adding Household Members
DELIMITER $$

CREATE PROCEDURE add_household_members(
    IN p_household_id INT,
    IN p_member_name VARCHAR(255),
    IN p_relation VARCHAR(255),
    IN p_age INT,  -- Changed to INT for Age
    IN p_sex ENUM('Male', 'Female', 'Other')  -- Changed to ENUM for sex
)
BEGIN
    INSERT INTO household_members (household_id, member_name, relation, age, sex)
    VALUES (p_household_id, p_member_name, p_relation, p_age, p_sex);
END $$

DELIMITER ;

-- Stored Procedure for Adding Medical Information
DELIMITER $$

CREATE PROCEDURE add_medical_information(
    IN p_household_id INT,
    IN p_medical_condition TEXT,
    IN p_allergies TEXT
)
BEGIN
    INSERT INTO medical_information (household_id, medical_condition, allergies)
    VALUES (p_household_id, p_medical_condition, p_allergies);
END $$

DELIMITER ;

-- Stored Procedure for Adding Emergency Contact
DELIMITER $$

CREATE PROCEDURE add_emergency_contact(
    IN p_household_id INT,
    IN p_emergency_contact_name VARCHAR(255),
    IN p_emergency_contact_number VARCHAR(15),
    IN p_emergency_contact_relation VARCHAR(100)
)
BEGIN
    INSERT INTO emergency_contacts (household_id, emergency_contact_name, emergency_contact_number, emergency_contact_relation)
    VALUES (p_household_id, p_emergency_contact_name, p_emergency_contact_number, p_emergency_contact_relation);
END $$

DELIMITER $$

--Stored procedure for delete appointment

DELIMITER $$ 
CREATE PROCEDURE delete_appointment (
  IN appt_id INT
)
BEGIN
DELETE FROM appointments WHERE appointment_id = appt_id;
END $$
DELIMITER;

--Stored Procedure for delete patient--

DELIMITER $$
CREATE PROCEDURE delete_patient(
    IN pat_id INT
)
BEGIN
DELETE FROM patients WHERE patient_id = pat_id;
END $$
DELIMITER;

--Stored Procedure for delete household-- 

DELIMITER $$
CREATE PROCEDURE delete_households(
    IN hh_id INT
)
BEGIN 
DELETE FROM households WHERE household_id = hh_id;
END $$
DELIMITER;

--
-- Table structure for table `users`
--

-- Stored Procedure for showing appointment table--

-- Appointment Table View --

DELIMITER $$

CREATE PROCEDURE show_appointment_list()
BEGIN
    SELECT * FROM appointments;
END $$

DELIMITER ;



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

CREATE TABLE appointments (
  appointment_id INT(11) NOT NULL AUTO_INCREMENT,
  patient_name VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  time TIME NOT NULL,
  doctor VARCHAR(255) NOT NULL,
  reason TEXT NOT NULL,
  PRIMARY KEY (appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Dumping data for table `appointments`
--

INSERT INTO appointments (patient_name, date, time, doctor, reason) VALUES
('John Doe', '2025-04-15', '09:00:00', 'Dr. Smith', 'Routine Check-up'),
('Jane Doe', '2025-04-16', '10:30:00', 'Dr. Lee', 'Vaccination'),
('Alice Johnson', '2025-04-17', '11:00:00', 'Dr. Brown', 'Consultation for fever'),
('Bob Martin', '2025-04-18', '08:30:00', 'Dr. Green', 'Follow-up check-up');

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

-- Create Household Table
CREATE TABLE households (
    household_id INT AUTO_INCREMENT PRIMARY KEY,
    head_name VARCHAR(255) NOT NULL,
    purok VARCHAR(255) NOT NULL,
    nic_number VARCHAR(20) NOT NULL,
    num_members INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Medical Information Table (for medical_condition and allergies)
CREATE TABLE medical_information (
    medical_id INT AUTO_INCREMENT PRIMARY KEY,
    household_id INT,
    medical_condition TEXT,
    allergies TEXT,
    FOREIGN KEY (household_id) REFERENCES households(household_id) ON DELETE CASCADE
);

-- Create Household Members Table
CREATE TABLE household_members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    household_id INT,
    member_name VARCHAR(255),
    relation VARCHAR(255),
    age INT,
    sex ENUM('Male', 'Female', 'Other'),
    FOREIGN KEY (household_id) REFERENCES households(household_id) ON DELETE CASCADE
);

-- Create Emergency Contacts Table
CREATE TABLE emergency_contacts (
    emergency_id INT AUTO_INCREMENT PRIMARY KEY,
    household_id INT,
    emergency_contact_name VARCHAR(255),
    emergency_contact_number VARCHAR(15),
    emergency_contact_relation VARCHAR(100),
    FOREIGN KEY (household_id) REFERENCES households(household_id) ON DELETE CASCADE
);

-- Insert Example Data into Household Table
INSERT INTO households (head_name, purok, nic_number, num_members)
VALUES
('John Doe', 'Purok 1', '1234567890', 5);

-- Insert Example Data into Medical Information Table
-- You must ensure that the household_id inserted into 'households' exists before this
INSERT INTO medical_information (household_id, medical_condition, allergies)
VALUES (1, 'Hypertension, Asthma', 'Pollen, Dust');

-- Insert Example Data into Household Members Table
-- Ensure the household_id (1) is present in households before inserting
INSERT INTO household_members (household_id, member_name, relation, age , sex)
VALUES
(1, 'John John', 'Spouse', 22, 'Male');

-- Insert Example Data into Emergency Contacts Table
-- Ensure household_id (1) exists in the households table
INSERT INTO emergency_contacts (household_id, emergency_contact_name, emergency_contact_number, emergency_contact_relation)
VALUES
(1, 'Sarah Doe', '09123456789', 'Sister');


-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `household_id` int(11) DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `parents` VARCHAR(255) DEFAULT NULL,
  `dob` DATE NOT NULL,  -- Date of Birth
  `weight` DECIMAL(5,2) DEFAULT NULL,  -- Weight in kilograms
  `height` DECIMAL(5,2) DEFAULT NULL,  -- Height in meters
  `blood_type` ENUM('A', 'B', 'AB', 'O', 'O+', 'Other') DEFAULT NULL,
  `reason` TEXT DEFAULT NULL,  -- Reason for the visit or notes
  `is_household_head` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `patients`
INSERT INTO `patients` (`household_id`, `name`, `gender`, `address`, `parents`, `dob`, `weight`, `height`, `blood_type`, `reason`, `is_household_head`) VALUES
(1, 'Juan Dela Cruz', 'Male', '123 Street, City', 'Maria Dela Cruz, Juan Dela Cruz Sr.', '1985-06-15', 70.5, 1.75, 'O', 'General checkup', 1),
(1, 'Ana Dela Cruz', 'Female', '456 Avenue, City', 'Jose Dela Cruz, Maria Dela Cruz', '1987-10-20', 55.3, 1.60, 'A', 'Routine exam', 0),
(1, 'Carlos Dela Cruz', 'Male', '789 Road, City', 'Maria Dela Cruz', '2010-08-05', 30.0, 1.40, 'B', 'Vaccination', 0),
(2, 'Maria Santos', 'Female', '101 Park, City', 'Jose Santos, Laura Santos', '1990-12-12', 62.0, 1.65, 'AB', 'Consultation', 1);

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

-- Create activity_logs table for the ActivityLogger class
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `details` JSON DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create backup tables
CREATE TABLE users_backup AS SELECT * FROM users;
CREATE TABLE patients_backup AS SELECT * FROM patients;
CREATE TABLE appointments_backup AS SELECT * FROM appointments;
CREATE TABLE healthworkers_backup AS SELECT * FROM healthworkers;
CREATE TABLE households_backup AS SELECT * FROM households;

-- Create reference tables
CREATE TABLE `appointment_status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO appointment_status (status_name) VALUES
('Scheduled'), ('Completed'), ('Cancelled');

CREATE TABLE `blood_types` (
  `blood_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `blood_type` varchar(5) NOT NULL,
  PRIMARY KEY (`blood_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO blood_types (blood_type) VALUES
('A+'), ('A-'), ('B+'), ('B-'), ('AB+'), ('AB-'), ('O+'), ('O-');

-- Modify users table
ALTER TABLE `users`
ADD COLUMN `email` varchar(100) DEFAULT NULL,
ADD COLUMN `contact_number` varchar(15) DEFAULT NULL,
ADD COLUMN `full_name` varchar(100) DEFAULT NULL,
ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
ADD COLUMN `deleted_at` timestamp NULL DEFAULT NULL;

-- Add security columns to users table
ALTER TABLE `users`
ADD COLUMN `two_factor_secret` varchar(32) DEFAULT NULL,
ADD COLUMN `two_factor_enabled` BOOLEAN DEFAULT FALSE,
ADD COLUMN `password_reset_token` varchar(100) DEFAULT NULL,
ADD COLUMN `password_reset_expires` DATETIME DEFAULT NULL,
ADD COLUMN `failed_login_attempts` INT DEFAULT 0,
ADD COLUMN `last_login_attempt` DATETIME DEFAULT NULL,
ADD COLUMN `account_locked_until` DATETIME DEFAULT NULL,
ADD COLUMN `password_changed_at` DATETIME DEFAULT NULL,
ADD COLUMN `force_password_change` BOOLEAN DEFAULT FALSE,
ADD COLUMN `last_password_reset` DATETIME DEFAULT NULL,
ADD COLUMN `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
ADD COLUMN `api_key` VARCHAR(64) DEFAULT NULL,
ADD COLUMN `api_key_expires` DATETIME DEFAULT NULL;

-- Modify appointments table
ALTER TABLE `appointments`
DROP COLUMN `status`,
ADD COLUMN `status_id` int(11) NOT NULL DEFAULT 1,
ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
ADD COLUMN `deleted_at` timestamp NULL DEFAULT NULL,
ADD CONSTRAINT `appointments_status_fk` FOREIGN KEY (`status_id`)
    REFERENCES `appointment_status` (`status_id`);

-- Modify patients table
ALTER TABLE `patients`
ADD COLUMN `blood_type_id` int(11) DEFAULT NULL,
ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
ADD COLUMN `deleted_at` timestamp NULL DEFAULT NULL,
ADD COLUMN `version` INT DEFAULT 1,
ADD CONSTRAINT `patients_blood_type_fk` FOREIGN KEY (`blood_type_id`)
    REFERENCES `blood_types` (`blood_type_id`);

-- Create security-related tables
CREATE TABLE `password_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `password_history_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `security_audit_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `event_type` enum('login','logout','password_change','failed_login','permission_change','api_access') NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` varchar(255) DEFAULT NULL,
    `details` JSON DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `security_audit_log_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create RBAC tables
CREATE TABLE `permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `description` text DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_permission_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `role_permissions` (
    `role` enum('Healthworker','Nurse','Doctor') NOT NULL,
    `permission_id` int(11) NOT NULL,
    PRIMARY KEY (`role`, `permission_id`),
    KEY `permission_id` (`permission_id`),
    CONSTRAINT `role_permissions_permission_fk` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert initial permissions
INSERT INTO permissions (name, description) VALUES
('view_patient_records', 'Can view patient records'),
('edit_patient_records', 'Can edit patient records'),
('schedule_appointments', 'Can schedule appointments'),
('prescribe_medication', 'Can prescribe medication'),
('view_reports', 'Can view reports'),
('manage_users', 'Can manage user accounts');

-- Assign initial role permissions
INSERT INTO role_permissions (role, permission_id) VALUES
('Doctor', (SELECT id FROM permissions WHERE name = 'view_patient_records')),
('Doctor', (SELECT id FROM permissions WHERE name = 'edit_patient_records')),
('Doctor', (SELECT id FROM permissions WHERE name = 'prescribe_medication')),
('Nurse', (SELECT id FROM permissions WHERE name = 'view_patient_records')),
('Nurse', (SELECT id FROM permissions WHERE name = 'schedule_appointments')),
('Healthworker', (SELECT id FROM permissions WHERE name = 'view_patient_records')),
('Healthworker', (SELECT id FROM permissions WHERE name = 'schedule_appointments'));

-- Add indexes for performance
CREATE INDEX idx_appointment_date ON appointments(appointment_date);
CREATE INDEX idx_patient_name ON patients(full_name);
CREATE INDEX idx_household_barangay ON households(barangay);
CREATE INDEX idx_audit_timestamp ON activity_logs(created_at);
CREATE INDEX idx_patient_household ON patients(household_id, is_household_head);
CREATE INDEX idx_appointment_status_date ON appointments(status_id, appointment_date, deleted_at);
CREATE INDEX idx_patient_metrics ON healthmetrics(patient_id, checkup_date);
CREATE INDEX idx_consultation_date ON consultations(consultation_date, appointment_id);
CREATE INDEX idx_household_location ON households(barangay, address(100));
CREATE INDEX idx_patient_search ON patients(full_name, date_of_birth, deleted_at);
CREATE INDEX idx_concurrent_appointments ON appointments(worker_id, appointment_date, deleted_at);

-- Add triggers for data integrity and auditing
DELIMITER $$

CREATE TRIGGER before_user_update
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.password != OLD.password THEN
        SET NEW.password_changed_at = NOW();

        -- Store password history
        INSERT INTO password_history (user_id, password_hash)
        VALUES (OLD.id, OLD.password);
    END IF;
END$$

CREATE TRIGGER before_patient_delete
BEFORE DELETE ON patients
FOR EACH ROW
BEGIN
    UPDATE patients SET deleted_at = NOW()
    WHERE patient_id = OLD.patient_id;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Use soft delete instead';
END$$

DELIMITER ;

-- Set global transaction isolation level
SET GLOBAL TRANSACTION ISOLATION LEVEL REPEATABLE READ;

-- Enable strict mode
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `changes` JSON,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELIMITER $$

-- Audit trigger for patients
CREATE TRIGGER `after_patient_update` AFTER UPDATE ON `patients`
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, record_id, changes)
    VALUES (
        @current_user_id, -- This should be set in your application
        'UPDATE',
        'patients',
        NEW.patient_id,
        JSON_OBJECT(
            'old_data', JSON_OBJECT(
                'full_name', OLD.full_name,
                'contact_number', OLD.contact_number,
                'email', OLD.email
            ),
            'new_data', JSON_OBJECT(
                'full_name', NEW.full_name,
                'contact_number', NEW.contact_number,
                'email', NEW.email
            )
        )
    );
END$$

DELIMITER ;

CREATE VIEW `view_patient_details` AS
SELECT
    p.patient_id,
    p.full_name,
    p.gender,
    p.date_of_birth,
    p.contact_number,
    p.email,
    bt.blood_type,
    h.household_head,
    h.address,
    h.barangay
FROM patients p
JOIN households h ON p.household_id = h.household_id
LEFT JOIN blood_types bt ON p.blood_type_id = bt.blood_type_id
WHERE p.deleted_at IS NULL;

CREATE VIEW `view_upcoming_appointments` AS
SELECT
    a.appointment_id,
    p.full_name AS patient_name,
    hw.full_name AS health_worker,
    a.appointment_date,
    a.purpose,
    aps.status_name
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN healthworkers hw ON a.worker_id = hw.worker_id
JOIN appointment_status aps ON a.status_id = aps.status_id
WHERE a.appointment_date >= CURDATE()
AND a.deleted_at IS NULL
ORDER BY a.appointment_date;

DELIMITER $$

-- Optimized patient history with pagination
CREATE PROCEDURE GetPatientHistoryPaginated(
    IN p_patient_id INT,
    IN p_page INT,
    IN p_limit INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page - 1) * p_limit;

    -- Get total counts first
    SELECT
        COUNT(DISTINCT a.appointment_id) as total_appointments,
        COUNT(DISTINCT c.consultation_id) as total_consultations,
        COUNT(DISTINCT h.metric_id) as total_metrics
    FROM patients p
    LEFT JOIN appointments a ON p.patient_id = a.patient_id AND a.deleted_at IS NULL
    LEFT JOIN consultations c ON a.appointment_id = c.appointment_id
    LEFT JOIN healthmetrics h ON p.patient_id = h.patient_id
    WHERE p.patient_id = p_patient_id;

    -- Recent appointments with consultations
    SELECT
        a.appointment_id,
        a.appointment_date,
        aps.status_name,
        hw.full_name as health_worker,
        c.findings,
        c.prescription
    FROM appointments a
    FORCE INDEX (idx_appointment_status_date)
    JOIN appointment_status aps ON a.status_id = aps.status_id
    JOIN healthworkers hw ON a.worker_id = hw.worker_id
    LEFT JOIN consultations c ON a.appointment_id = c.appointment_id
    WHERE a.patient_id = p_patient_id
    AND a.deleted_at IS NULL
    ORDER BY a.appointment_date DESC
    LIMIT p_limit OFFSET v_offset;

    -- Health metrics with statistical analysis
    SELECT
        AVG(weight) as avg_weight,
        MAX(weight) as max_weight,
        MIN(weight) as min_weight,
        AVG(SUBSTRING_INDEX(blood_pressure, '/', 1)) as avg_systolic,
        AVG(SUBSTRING_INDEX(blood_pressure, '/', -1)) as avg_diastolic
    FROM healthmetrics
    FORCE INDEX (idx_patient_metrics)
    WHERE patient_id = p_patient_id;
END$$

-- Optimized appointment scheduling with conflict checking
CREATE PROCEDURE ScheduleAppointmentSafe(
    IN p_patient_id INT,
    IN p_worker_id INT,
    IN p_date DATETIME,
    IN p_purpose TEXT,
    OUT p_success BOOLEAN,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_conflict INT;

    START TRANSACTION;

    -- Check for existing appointments in the same time slot (+-30 minutes)
    SELECT COUNT(*) INTO v_conflict
    FROM appointments a
    FORCE INDEX (idx_appointment_status_date)
    WHERE a.worker_id = p_worker_id
    AND a.deleted_at IS NULL
    AND a.appointment_date BETWEEN p_date - INTERVAL 30 MINUTE
                              AND p_date + INTERVAL 30 MINUTE;

    IF v_conflict > 0 THEN
        SET p_success = FALSE;
        SET p_message = 'Time slot conflict detected';
        ROLLBACK;
    ELSE
        INSERT INTO appointments (
            patient_id, worker_id, appointment_date,
            purpose, status_id, created_at
        )
        VALUES (
            p_patient_id, p_worker_id, p_date,
            p_purpose, 1, NOW()
        );

        SET p_success = TRUE;
        SET p_message = 'Appointment scheduled successfully';
        COMMIT;
    END IF;
END$$

-- Advanced analytics procedure
CREATE PROCEDURE GetHealthcenterAnalytics(IN p_date_from DATE, IN p_date_to DATE)
BEGIN
    -- Appointment statistics
    WITH AppointmentStats AS (
        SELECT
            DATE_FORMAT(appointment_date, '%Y-%m') as month,
            COUNT(*) as total_appointments,
            SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status_id = 3 THEN 1 ELSE 0 END) as cancelled
        FROM appointments
        FORCE INDEX (idx_appointment_status_date)
        WHERE appointment_date BETWEEN p_date_from AND p_date_to
        AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(appointment_date, '%Y-%m')
    )
    SELECT
        month,
        total_appointments,
        completed,
        cancelled,
        ROUND((completed/total_appointments) * 100, 2) as completion_rate
    FROM AppointmentStats;

    -- Barangay health metrics
    WITH BarangayMetrics AS (
        SELECT
            h.barangay,
            COUNT(DISTINCT p.patient_id) as total_patients,
            COUNT(DISTINCT a.appointment_id) as total_appointments,
            COUNT(DISTINCT CASE WHEN p.is_household_head = 1 THEN p.household_id END) as total_households
        FROM households h
        FORCE INDEX (idx_household_location)
        LEFT JOIN patients p ON h.household_id = p.household_id
        LEFT JOIN appointments a ON p.patient_id = a.patient_id
        WHERE p.deleted_at IS NULL
        GROUP BY h.barangay
    )
    SELECT
        barangay,
        total_patients,
        total_appointments,
        total_households,
        ROUND(total_appointments/total_patients, 2) as appointments_per_patient
    FROM BarangayMetrics
    ORDER BY total_patients DESC;
END$$

-- Materialized-like view for patient summary (refresh periodically)
CREATE TABLE `patient_summary_cache` (
    `patient_id` int(11) NOT NULL,
    `full_name` varchar(100),
    `age` int,
    `last_appointment_date` datetime,
    `total_appointments` int,
    `avg_blood_pressure` varchar(10),
    `last_updated` timestamp,
    PRIMARY KEY (`patient_id`),
    KEY `idx_last_updated` (`last_updated`)
);

-- Procedure to refresh patient summary
DELIMITER $$
CREATE PROCEDURE RefreshPatientSummary()
BEGIN
    TRUNCATE TABLE patient_summary_cache;

    INSERT INTO patient_summary_cache
    SELECT
        p.patient_id,
        p.full_name,
        TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
        MAX(a.appointment_date) as last_appointment_date,
        COUNT(DISTINCT a.appointment_id) as total_appointments,
        (
            SELECT CONCAT(
                ROUND(AVG(SUBSTRING_INDEX(blood_pressure, '/', 1))), '/',
                ROUND(AVG(SUBSTRING_INDEX(blood_pressure, '/', -1)))
            )
            FROM healthmetrics hm
            WHERE hm.patient_id = p.patient_id
        ) as avg_blood_pressure,
        NOW() as last_updated
    FROM patients p
    LEFT JOIN appointments a ON p.patient_id = a.patient_id
    WHERE p.deleted_at IS NULL
    GROUP BY p.patient_id, p.full_name, p.date_of_birth;
END$$
DELIMITER ;

-- Efficient view for upcoming appointments
CREATE OR REPLACE VIEW `view_upcoming_appointments_optimized` AS
SELECT
    a.appointment_id,
    p.full_name AS patient_name,
    hw.full_name AS health_worker,
    a.appointment_date,
    a.purpose,
    aps.status_name,
    h.barangay
FROM appointments a
FORCE INDEX (idx_appointment_status_date)
JOIN patients p FORCE INDEX (idx_patient_search)
    ON a.patient_id = p.patient_id
JOIN healthworkers hw ON a.worker_id = hw.worker_id
JOIN appointment_status aps ON a.status_id = aps.status_id
JOIN households h FORCE INDEX (idx_household_location)
    ON p.household_id = h.household_id
WHERE a.appointment_date >= CURDATE()
AND a.deleted_at IS NULL
AND p.deleted_at IS NULL;

DELIMITER $$

CREATE PROCEDURE SearchPatients(
    IN p_search_term VARCHAR(100),
    IN p_barangay VARCHAR(100),
    IN p_age_from INT,
    IN p_age_to INT,
    IN p_page INT,
    IN p_limit INT
)
BEGIN
    DECLARE v_offset INT;
    SET v_offset = (p_page - 1) * p_limit;

    WITH FilteredPatients AS (
        SELECT
            p.patient_id,
            p.full_name,
            p.gender,
            p.date_of_birth,
            h.barangay,
            h.address,
            TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age,
            COUNT(*) OVER() as total_count
        FROM patients p
        FORCE INDEX (idx_patient_search)
        JOIN households h FORCE INDEX (idx_household_location)
            ON p.household_id = h.household_id
        WHERE p.deleted_at IS NULL
        AND (
            p.full_name LIKE CONCAT('%', p_search_term, '%')
            OR p.contact_number LIKE CONCAT('%', p_search_term, '%')
            OR p.email LIKE CONCAT('%', p_search_term, '%')
        )
        AND (p_barangay IS NULL OR h.barangay = p_barangay)
        AND (
            p_age_from IS NULL
            OR TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) >= p_age_from
        )
        AND (
            p_age_to IS NULL
            OR TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) <= p_age_to
        )
    )
    SELECT
        fp.*,
        ps.last_appointment_date,
        ps.total_appointments,
        ps.avg_blood_pressure
    FROM FilteredPatients fp
    LEFT JOIN patient_summary_cache ps ON fp.patient_id = ps.patient_id
    ORDER BY fp.full_name
    LIMIT p_limit OFFSET v_offset;
END$$

DELIMITER ;

--
-- Transaction Management and Concurrency Control
--

-- Set global transaction isolation level
SET GLOBAL TRANSACTION ISOLATION LEVEL REPEATABLE READ;

-- Enable strict mode for better data integrity
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

--
-- Transaction-Safe Stored Procedures
--

DELIMITER $$

-- Appointment scheduling with transaction safety
CREATE PROCEDURE ScheduleAppointmentTransactional(
    IN p_patient_id INT UNSIGNED,
    IN p_worker_id INT UNSIGNED,
    IN p_appointment_date DATETIME,
    IN p_purpose VARCHAR(255),
    OUT p_success BOOLEAN,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
        SET p_message = 'Transaction failed';
    END;

    START TRANSACTION;

    -- Lock check for concurrent appointments
    SELECT COUNT(*) INTO @conflict_count
    FROM appointments
    WHERE worker_id = p_worker_id
    AND appointment_date BETWEEN
        p_appointment_date - INTERVAL 30 MINUTE AND
        p_appointment_date + INTERVAL 30 MINUTE
    AND deleted_at IS NULL
    FOR UPDATE;

    IF @conflict_count > 0 THEN
        SET p_success = FALSE;
        SET p_message = 'Time slot conflict detected';
        ROLLBACK;
    ELSE
        INSERT INTO appointments (
            patient_id,
            worker_id,
            appointment_date,
            purpose,
            status_id,
            created_at
        ) VALUES (
            p_patient_id,
            p_worker_id,
            p_appointment_date,
            p_purpose,
            1, -- Scheduled status
            NOW()
        );

        -- Update cache table
        UPDATE patient_summary_cache
        SET
            last_appointment_date = p_appointment_date,
            total_appointments = total_appointments + 1,
            last_updated = NOW()
        WHERE patient_id = p_patient_id;

        SET p_success = TRUE;
        SET p_message = 'Appointment scheduled successfully';
        COMMIT;
    END IF;
END$$

-- Consultation recording with transaction safety
CREATE PROCEDURE RecordConsultationTransactional(
    IN p_appointment_id INT UNSIGNED,
    IN p_chief_complaint TEXT,
    IN p_diagnosis TEXT,
    IN p_treatment_plan TEXT,
    IN p_prescription TEXT,
    IN p_notes TEXT,
    OUT p_success BOOLEAN
)
BEGIN
    DECLARE v_patient_id INT UNSIGNED;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
    END;

    START TRANSACTION;

    -- Lock the appointment record
    SELECT patient_id INTO v_patient_id
    FROM appointments
    WHERE appointment_id = p_appointment_id
    FOR UPDATE;

    INSERT INTO consultations (
        appointment_id,
        chief_complaint,
        diagnosis,
        treatment_plan,
        prescription,
        notes,
        created_at
    ) VALUES (
        p_appointment_id,
        p_chief_complaint,
        p_diagnosis,
        p_treatment_plan,
        p_prescription,
        p_notes,
        NOW()
    );

    -- Update appointment status
    UPDATE appointments
    SET
        status_id = 2, -- Completed
        updated_at = NOW()
    WHERE appointment_id = p_appointment_id;

    SET p_success = TRUE;
    COMMIT;
END$$

-- Health metrics recording with transaction safety
CREATE PROCEDURE RecordHealthMetricsTransactional(
    IN p_patient_id INT UNSIGNED,
    IN p_recorded_by INT UNSIGNED,
    IN p_bp_systolic SMALLINT UNSIGNED,
    IN p_bp_diastolic SMALLINT UNSIGNED,
    IN p_heart_rate SMALLINT UNSIGNED,
    IN p_temperature DECIMAL(4,1),
    IN p_weight_kg DECIMAL(5,2),
    IN p_height_cm SMALLINT UNSIGNED,
    IN p_notes TEXT,
    OUT p_success BOOLEAN
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
    END;

    START TRANSACTION;

    -- Lock patient record
    SELECT 1 FROM patients
    WHERE patient_id = p_patient_id
    FOR UPDATE;

    INSERT INTO healthmetrics (
        patient_id,
        recorded_by,
        checkup_date,
        blood_pressure_systolic,
        blood_pressure_diastolic,
        heart_rate,
        temperature,
        weight_kg,
        height_cm,
        notes
    ) VALUES (
        p_patient_id,
        p_recorded_by,
        CURDATE(),
        p_bp_systolic,
        p_bp_diastolic,
        p_heart_rate,
        p_temperature,
        p_weight_kg,
        p_height_cm,
        p_notes
    );

    -- Update cache with new averages
    UPDATE patient_summary_cache
    SET
        avg_systolic = (
            SELECT AVG(blood_pressure_systolic)
            FROM healthmetrics
            WHERE patient_id = p_patient_id
        ),
        avg_diastolic = (
            SELECT AVG(blood_pressure_diastolic)
            FROM healthmetrics
            WHERE patient_id = p_patient_id
        ),
        last_updated = NOW()
    WHERE patient_id = p_patient_id;

    SET p_success = TRUE;
    COMMIT;
END$$

-- Dead lock prevention procedure
CREATE PROCEDURE UpdatePatientAndHouseholdTransactional(
    IN p_patient_id INT UNSIGNED,
    IN p_household_id INT UNSIGNED,
    IN p_patient_data JSON,
    IN p_household_data JSON,
    OUT p_success BOOLEAN
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_success = FALSE;
    END;

    START TRANSACTION;

    -- Always update tables in the same order to prevent deadlocks
    UPDATE households
    SET
        address = JSON_UNQUOTE(JSON_EXTRACT(p_household_data, '$.address')),
        updated_at = NOW()
    WHERE household_id = p_household_id;

    UPDATE patients
    SET
        first_name = JSON_UNQUOTE(JSON_EXTRACT(p_patient_data, '$.first_name')),
        last_name = JSON_UNQUOTE(JSON_EXTRACT(p_patient_data, '$.last_name')),
        updated_at = NOW()
    WHERE patient_id = p_patient_id;

    SET p_success = TRUE;
    COMMIT;
END$$

-- Optimistic locking example
CREATE PROCEDURE UpdatePatientOptimisticLock(
    IN p_patient_id INT UNSIGNED,
    IN p_version INT,
    IN p_data JSON,
    OUT p_success BOOLEAN
)
BEGIN
    DECLARE current_version INT;

    START TRANSACTION;

    SELECT version INTO current_version
    FROM patients
    WHERE patient_id = p_patient_id;

    IF current_version = p_version THEN
        UPDATE patients
        SET
            first_name = JSON_UNQUOTE(JSON_EXTRACT(p_data, '$.first_name')),
            last_name = JSON_UNQUOTE(JSON_EXTRACT(p_data, '$.last_name')),
            version = version + 1,
            updated_at = NOW()
        WHERE patient_id = p_patient_id;

        SET p_success = TRUE;
    ELSE
        SET p_success = FALSE;
    END IF;

    COMMIT;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Additional Indexes for Transaction Performance
-- -----------------------------------------------------
-- Drop the index if it exists, then recreate it
DROP INDEX IF EXISTS idx_concurrent_appointments ON appointments;
ALTER TABLE appointments ADD INDEX idx_concurrent_appointments (worker_id, appointment_date, deleted_at);
ALTER TABLE healthmetrics ADD INDEX idx_patient_metrics_date (patient_id, checkup_date);

-- -----------------------------------------------------
-- Transaction Monitoring Views
-- -----------------------------------------------------
CREATE OR REPLACE VIEW v_active_transactions AS
SELECT
    trx_id,
    trx_state,
    trx_started,
    trx_requested_lock_id,
    trx_wait_started,
    trx_weight,
    trx_mysql_thread_id,
    trx_tables_in_use,
    trx_tables_locked
FROM information_schema.innodb_trx;

CREATE OR REPLACE VIEW v_lock_waits AS
SELECT
    requesting_trx_id,
    requested_lock_id,
    blocking_trx_id,
    blocking_lock_id
FROM information_schema.innodb_lock_waits;

-- -----------------------------------------------------
-- Deadlock Monitoring Trigger
-- -----------------------------------------------------
DELIMITER $$

CREATE TRIGGER after_deadlock_detected
AFTER INSERT ON audit_logs
FOR EACH ROW
BEGIN
    IF NEW.action = 'DEADLOCK_DETECTED' THEN
        -- Log to separate deadlock table
        INSERT INTO deadlock_incidents (
            timestamp,
            affected_table,
            transaction_id,
            error_message
        ) VALUES (
            NOW(),
            NEW.table_name,
            NEW.record_id,
            NEW.changes
        );
    END IF;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Security Enhancements
-- -----------------------------------------------------

-- Create security schema for security-related tables
CREATE SCHEMA IF NOT EXISTS security;


-- Add security-related indexes
ALTER TABLE `users`
ADD INDEX `idx_email` (`email`),
ADD INDEX `idx_status` (`status`),
ADD INDEX `idx_api_key` (`api_key`);

-- -----------------------------------------------------
-- API Access Control
-- -----------------------------------------------------
CREATE TABLE `api_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `api_key` VARCHAR(64) NOT NULL,
    `name` VARCHAR(50),
    `permissions` JSON,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME,
    `last_used` DATETIME,
    `is_active` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    UNIQUE KEY `unique_api_key` (`api_key`)
) ENGINE=InnoDB;

-- Security Procedures

-- Secure Password Change Procedure
DELIMITER $$

CREATE PROCEDURE ChangeUserPassword(
    IN p_user_id INT,
    IN p_new_password VARCHAR(255),
    IN p_current_password VARCHAR(255)
)
BEGIN
    DECLARE v_current_hash VARCHAR(255);
    DECLARE v_count INT;
    
    -- Get current password hash
    SELECT password INTO v_current_hash
    FROM users
    WHERE id = p_user_id;
    
    -- Verify current password
    IF v_current_hash = p_current_password THEN
        -- Update password
        UPDATE users
        SET
            password = p_new_password,
            password_changed_at = NOW()
        WHERE id = p_user_id;
        
        -- Store in password history if table exists
        SELECT COUNT(*) INTO v_count FROM information_schema.tables 
        WHERE table_schema = 'db_healthcenter' AND table_name = 'password_history';
        
        IF v_count > 0 THEN
            INSERT INTO password_history (user_id, password_hash)
            VALUES (p_user_id, p_current_password);
        END IF;
        
        -- Log the change if table exists
        SELECT COUNT(*) INTO v_count FROM information_schema.tables 
        WHERE table_schema = 'db_healthcenter' AND table_name = 'security_audit_log';
        
        IF v_count > 0 THEN
            INSERT INTO security_audit_log (user_id, event_type, details)
            VALUES (p_user_id, 'password_change', '{"method": "user_initiated"}');
        END IF;
    END IF;
END$$

DELIMITER ;

-- Login Attempt Handler
DELIMITER $$

CREATE PROCEDURE HandleLoginAttempt(
    IN p_username VARCHAR(50),
    IN p_password VARCHAR(255),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent VARCHAR(255)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_failed_attempts INT;
    DECLARE v_stored_password VARCHAR(255);
    DECLARE v_count INT;
    
    -- Get user details
    SELECT id, failed_login_attempts, password 
    INTO v_user_id, v_failed_attempts, v_stored_password
    FROM users
    WHERE username = p_username;
    
    -- Check if user exists
    IF v_user_id IS NOT NULL THEN
        -- Check if account is locked
        SELECT COUNT(*) INTO v_count 
        FROM users 
        WHERE id = v_user_id 
        AND account_locked_until IS NOT NULL 
        AND account_locked_until > NOW();
        
        IF v_count > 0 THEN
            -- Account is locked
            UPDATE users
            SET last_login_attempt = NOW()
            WHERE id = v_user_id;
            
            -- Log failed attempt
            INSERT INTO security_audit_log (user_id, event_type, ip_address, user_agent)
            VALUES (v_user_id, 'failed_login', p_ip_address, p_user_agent);
        ELSE
            -- Verify password
            IF v_stored_password = p_password THEN
                -- Successful login
                UPDATE users
                SET 
                    failed_login_attempts = 0,
                    last_login_attempt = NOW(),
                    account_locked_until = NULL
                WHERE id = v_user_id;
                
                -- Log successful login
                INSERT INTO security_audit_log (user_id, event_type, ip_address, user_agent)
                VALUES (v_user_id, 'login', p_ip_address, p_user_agent);
            ELSE
                -- Failed login
                UPDATE users
                SET
                    failed_login_attempts = failed_login_attempts + 1,
                    last_login_attempt = NOW(),
                    account_locked_until = CASE
                        WHEN failed_login_attempts + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                        ELSE NULL
                    END
                WHERE id = v_user_id;
                
                -- Log failed attempt
                INSERT INTO security_audit_log (user_id, event_type, ip_address, user_agent)
                VALUES (v_user_id, 'failed_login', p_ip_address, p_user_agent);
            END IF;
        END IF;
    END IF;
END$$

DELIMITER ;

DELIMITER $$
-- API Key Management
CREATE PROCEDURE GenerateApiKey(
    IN p_user_id INT,
    IN p_name VARCHAR(50),
    IN p_permissions JSON,
    IN p_validity_days INT,
    OUT p_api_key VARCHAR(64)
)
BEGIN
    SET p_api_key = SHA2(CONCAT(UUID(), RAND()), 256);

    INSERT INTO api_keys (
        user_id,
        api_key,
        name,
        permissions,
        expires_at
    ) VALUES (
        p_user_id,
        p_api_key,
        p_name,
        p_permissions,
        DATE_ADD(NOW(), INTERVAL p_validity_days DAY)
    );
END$$

DELIMITER ;

-- -----------------------------------------------------
-- Initial Security Data - Insert only if not exists
INSERT IGNORE INTO permissions (name, description) VALUES
('view_patient_records', 'Can view patient records'),
('edit_patient_records', 'Can edit patient records'),
('schedule_appointments', 'Can schedule appointments'),
('prescribe_medication', 'Can prescribe medication'),
('view_reports', 'Can view reports'),
('manage_users', 'Can manage user accounts');

-- Role permissions - Insert only if not exists
INSERT IGNORE INTO role_permissions (role, permission_id) VALUES
('Doctor', (SELECT id FROM permissions WHERE name = 'view_patient_records')),
('Doctor', (SELECT id FROM permissions WHERE name = 'edit_patient_records')),
('Doctor', (SELECT id FROM permissions WHERE name = 'prescribe_medication')),
('Nurse', (SELECT id FROM permissions WHERE name = 'view_patient_records')),
('Nurse', (SELECT id FROM permissions WHERE name = 'schedule_appointments')),
('Healthworker', (SELECT id FROM permissions WHERE name = 'view_patient_records')),
('Healthworker', (SELECT id FROM permissions WHERE name = 'schedule_appointments'));

-- 
-- Security Views
-- 
CREATE OR REPLACE VIEW v_user_permissions AS
SELECT
    u.username,
    u.role,
    GROUP_CONCAT(p.name) as permissions
FROM users u
JOIN role_permissions rp ON u.role = rp.role
JOIN permissions p ON rp.permission_id = p.id
GROUP BY u.username, u.role;

CREATE OR REPLACE VIEW v_security_audit AS
SELECT
    sa.event_type,
    sa.ip_address,
    u.username,
    sa.created_at,
    sa.details
FROM security_audit_log sa
JOIN users u ON sa.user_id = u.id;

