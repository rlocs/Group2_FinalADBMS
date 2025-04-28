<?php
require '../dbConnection.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

// appoint_add
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $parents = $_POST['parents'];
    $dob = $_POST['dob'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $blood_type = $_POST['blood_type'];
    $reason = $_POST['reason'];

    try {
        // Instantiate the Database class and get the connection
        $database = new Database();
        $conn = $database->getConnection();
        
        // Call the stored procedure for adding a patient
        $sql = "CALL add_patient(:name, :gender, :address, :parents, :dob, :weight, :height, :blood_type, :reason)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':parents', $parents);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':height', $height);
        $stmt->bindParam(':blood_type', $blood_type);
        $stmt->bindParam(':reason', $reason);

        if ($stmt->execute()) {
            header("Location: patient.php");
            exit;
        } else {
            echo "Error: Unable to add patient.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// your work here
?>
