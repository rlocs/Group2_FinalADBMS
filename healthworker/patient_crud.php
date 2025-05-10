<?php
require '../dbConnection.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update']) && !isset($_POST['delete'])) {
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $parents = $_POST['parents'];
    $dob = $_POST['dob'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $blood_type = $_POST['blood_type'];
    $reason = $_POST['reason'];

    // Validate weight and height
    if (!is_numeric($weight) || $weight <= 0) {
        echo "Error: Weight must be a positive number.";
        exit;
    }
    if (!is_numeric($height) || $height <= 0) {
        echo "Error: Height must be a positive number.";
        exit;
    }

    try {
        $database = new Database();
        $conn = $database->getConnection();

        $sql = "INSERT INTO patients (name, gender, address, parents, dob, weight, height, blood_type, reason) VALUES (:name, :gender, :address, :parents, :dob, :weight, :height, :blood_type, :reason)";
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

//Delete Patient
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $patient_id = $_POST['delete_id'];

    try {
        $database = new Database();
        $conn = $database->getConnection();

        $sql = "DELETE FROM patients WHERE patient_id = :patient_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header("Location: patient.php");
            exit;
        } else {
            echo "Error: Unable to delete patient.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    error_log("Update Patient POST data: " . print_r($_POST, true));
    $patient_id = $_POST['patient_id'];
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $parents = $_POST['parents'];
    $dob = $_POST['dob'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $blood_type = $_POST['blood_type'];
    $reason = $_POST['reason'];

    // Validate weight and height
    if (!is_numeric($weight) || $weight <= 0) {
        echo "Error: Weight must be a positive number.";
        exit;
    }
    if (!is_numeric($height) || $height <= 0) {
        echo "Error: Height must be a positive number.";
        exit;
    }

    try {
        $database = new Database();
        $conn = $database->getConnection();

        $sql = "UPDATE patients SET name = :name, gender = :gender, address = :address, parents = :parents, dob = :dob, weight = :weight, height = :height, blood_type = :blood_type, reason = :reason WHERE patient_id = :patient_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
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
            $errorInfo = $stmt->errorInfo();
            error_log("Update Patient error: " . print_r($errorInfo, true));
            echo "Error: Unable to update patient.";
        }
    } catch (PDOException $e) {
        error_log("PDOException in Update Patient: " . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
}
?>
