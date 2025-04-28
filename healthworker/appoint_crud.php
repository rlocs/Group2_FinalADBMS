<?php
require '../dbConnection.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            header("Location: appointment.php");
            exit;
        } else {
            echo "Error: Unable to add appointment.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// your work here
?>
