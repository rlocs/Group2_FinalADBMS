<?php
require '../dbConnection.php';
session_start();

$type = $_GET['type'] ?? '';
$searchTerm = $_GET['search'] ?? '';
$records_per_page = 4;
$limit = $records_per_page;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$database = new Database();
$conn = $database->getConnection();

header('Content-Type: application/json');

try {
    if ($type === 'patient') {
        if ($searchTerm) {
            $stmt = $conn->prepare("SELECT * FROM patients WHERE name LIKE CONCAT('%', :search_term, '%') ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':search_term', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT * FROM patients ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($results);
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type parameter']);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
