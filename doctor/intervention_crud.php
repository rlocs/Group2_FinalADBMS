<?php
require '../dbConnection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $conn = $database->getConnection();

    if (isset($_POST['create'])) {
        $patient_id = $_POST['patient_id'];
        $doctor = $_SESSION['username'];
        $reason = $_POST['reason'];
        $intervention = $_POST['intervention'];

        try {
            $sql = "INSERT INTO interventions (patient_id, doctor, reason, intervention, created_at) VALUES (:patient_id, :doctor, :reason, :intervention, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
            $stmt->bindParam(':doctor', $doctor);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':intervention', $intervention);

            if ($stmt->execute()) {
                header("Location: patient.php");
                exit;
            } else {
                echo "Error: Unable to add intervention.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['update'])) {
        $intervention_id = $_POST['intervention_id'];
        $reason = $_POST['reason'];
        $intervention = $_POST['intervention'];

        try {
            $sql = "UPDATE interventions SET reason = :reason, intervention = :intervention WHERE intervention_id = :intervention_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':intervention_id', $intervention_id, PDO::PARAM_INT);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':intervention', $intervention);

            if ($stmt->execute()) {
                header("Location: patient.php");
                exit;
            } else {
                echo "Error: Unable to update intervention.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
} else if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $searchTerm = $_GET['search'] ?? '';
    $database = new Database();
    $conn = $database->getConnection();

    try {
        $sql = "SELECT i.intervention_id, p.name AS patient_name, i.doctor, i.reason, i.intervention 
                FROM interventions i 
                JOIN patients p ON i.patient_id = p.patient_id 
                WHERE p.name LIKE CONCAT('%', :search_term, '%') 
                   OR i.doctor LIKE CONCAT('%', :search_term, '%') 
                   OR i.reason LIKE CONCAT('%', :search_term, '%') 
                   OR i.intervention LIKE CONCAT('%', :search_term, '%')
                ORDER BY i.created_at DESC
                LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':search_term', $searchTerm);
        $stmt->execute();
        $interventions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($interventions);
        exit;
    } catch (PDOException $e) {
        echo json_encode([]);
        exit;
    }
} else {
    echo "Invalid request method.";
}
?>
