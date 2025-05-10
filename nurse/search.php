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
    if ($type === 'appointment') {
        if ($searchTerm) {
            $stmt = $conn->prepare("SELECT * FROM appointments WHERE patient_name LIKE CONCAT('%', :search_term, '%') ORDER BY appointment_id ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':search_term', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT * FROM appointments ORDER BY appointment_id ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($results);
        exit;
    } elseif ($type === 'patient') {
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
    } elseif ($type === 'household') {
        if ($searchTerm) {
            $stmt = $conn->prepare("SELECT h.household_id, h.head_name, h.purok, h.nic_number, h.num_members, h.created_at, mi.medical_condition, mi.allergies, ec.emergency_contact_name, ec.emergency_contact_number, ec.emergency_contact_relation
                FROM households h
                LEFT JOIN medical_information mi ON h.household_id = mi.household_id
                LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
                WHERE h.head_name LIKE CONCAT('%', :search_term, '%')
                ORDER BY h.created_at DESC
                LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':search_term', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT h.household_id, h.head_name, h.purok, h.nic_number, h.num_members, h.created_at, mi.medical_condition, mi.allergies, ec.emergency_contact_name, ec.emergency_contact_number, ec.emergency_contact_relation
                FROM households h
                LEFT JOIN medical_information mi ON h.household_id = mi.household_id
                LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
                ORDER BY h.created_at DESC
                LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($results);
        exit;
    } elseif ($type === 'household_members' && isset($_GET['household_id'])) {
        $household_id = $_GET['household_id'];
        $stmt = $conn->prepare("SELECT member_id, member_name, relation, age, sex FROM household_members WHERE household_id = :household_id");
        $stmt->bindParam(':household_id', $household_id, PDO::PARAM_INT);
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($members);
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
