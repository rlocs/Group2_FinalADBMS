<?php
require '../dbConnection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = $_GET['search'] ?? '';
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = 10;
    $offset = ($page - 1) * $records_per_page;

    try {
        $database = new Database();
        $conn = $database->getConnection();

        if ($search) {
            $stmt = $conn->prepare("SELECT * FROM patients WHERE name LIKE CONCAT('%', :search_term, '%') ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':search_term', $search);
            $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("SELECT * FROM patients ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
        }

        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($patients);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error fetching patients: ' . $e->getMessage()]);
        exit;
    }
}

// No create, update, or delete operations for doctors in this file.
?>