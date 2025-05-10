<?php
require '../dbConnection.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $patient_name = $_POST['patient_name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $doctor = $_POST['doctor'];
    $reason = $_POST['reason'];


    try {
        // Instantiate the Database class and get the connection
        $database = new Database();
        $conn = $database->getConnection();
        // Call the stored procedure for adding an appointment
        $sql = "CALL add_appointment(:patient_name, :date, :time, :doctor, :reason)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':patient_name', $patient_name);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':doctor', $doctor);
        $stmt->bindParam(':reason', $reason);

if ($stmt->execute()) {
    header("Location: appointment.php?success=add");
    exit;
} else {
            echo "Error: Unable to add appointment.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $appointment_id = $_POST['delete_id'];

    try {
        $database = new Database();
        $conn = $database->getConnection();

        $sql = "CALL delete_appointment(:appointment_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header("Location: appointment.php");
            exit;
        } else {
            echo "Error: Unable to delete appointment.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $appointment_id = $_POST['appointment_id'];
    $patient_name = $_POST['patient_name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $doctor = $_POST['doctor'];
    $reason = $_POST['reason'];

    // Debugging: Log POST data
    error_log("Update Appointment POST data: " . print_r($_POST, true));

    try {
        $database = new Database();
        $conn = $database->getConnection();

        $sql = "CALL UpdateAppointment(:appointment_id, :patient_name, :date, :time, :doctor, :reason)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':appointment_id', $appointment_id, PDO::PARAM_INT);
        $stmt->bindParam(':patient_name', $patient_name);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->bindParam(':doctor', $doctor);
        $stmt->bindParam(':reason', $reason);

        if ($stmt->execute()) {
            header("Location: appointment.php");
            exit;
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("Update Appointment error: " . print_r($errorInfo, true));
            echo "Error: Unable to update appointment.";
        }
    } catch (PDOException $e) {
        error_log("PDOException in Update Appointment: " . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}
?>