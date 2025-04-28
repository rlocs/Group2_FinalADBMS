<?php
require '../dbConnection.php';
session_start();

// Ensure the user is a Healthworker
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

// Handle form submission to add a new Household Profile, Medical Information, and Emergency Contact
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_household'])) {
    // Household Details
    $head_name = $_POST['head_name'];
    $purok = $_POST['purok'];
    $nic_number = $_POST['nic_number'];
    $num_members = $_POST['num_members'];

    // Household Member Details
    $member_name = $_POST['member_name'];
    $relation = $_POST['relation'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];

    // Medical Information
    $medical_condition = $_POST['medical_condition'];
    $allergies = $_POST['allergies'];

    // Emergency Contact Information
    $emergency_contact_name = $_POST['emergency_contact_name'];
    $emergency_contact_number = $_POST['emergency_contact_number'];
    $emergency_contact_relation = $_POST['emergency_contact_relation'];

    try {
        // Instantiate the Database class and get the connection
        $database = new Database();
        $conn = $database->getConnection();

        // Start transaction
        $conn->beginTransaction();

        // Insert Household Data
        $sql_household = "CALL add_household(:head_name, :purok, :nic_number, :num_members)";
        $stmt_household = $conn->prepare($sql_household);
        $stmt_household->bindParam(':head_name', $head_name);
        $stmt_household->bindParam(':purok', $purok);
        $stmt_household->bindParam(':nic_number', $nic_number);
        $stmt_household->bindParam(':num_members', $num_members);
        $stmt_household->execute();

        // Get the last inserted household ID
        $household_id = $conn->lastInsertId();
        $result = $stmt_household->fetch(PDO::FETCH_ASSOC);
        $household_id = $result['household_id'];
        $stmt_household->closeCursor();  // <<== VERY IMPORTANT to close the procedure result set
        // Check if household_id is valid
        if (!$household_id) {
            throw new Exception("Failed to insert household data.");
        }

        // Insert Household Member Data
        $sql_member = "CALL add_household_members(:household_id, :member_name, :relation, :age, :sex)";
        $stmt_member = $conn->prepare($sql_member);
        $stmt_member->bindParam(':household_id', $household_id);
        $stmt_member->bindParam(':member_name', $member_name);
        $stmt_member->bindParam(':relation', $relation);
        $stmt_member->bindParam(':age', $age);
        $stmt_member->bindParam(':sex', $sex);
        $stmt_member->execute();
        $stmt_household->closeCursor();

        // Insert Medical Information
        $sql_medical = "CALL add_medical_information(:household_id, :medical_condition, :allergies)";
        $stmt_medical = $conn->prepare($sql_medical);
        $stmt_medical->bindParam(':household_id', $household_id);
        $stmt_medical->bindParam(':medical_condition', $medical_condition);
        $stmt_medical->bindParam(':allergies', $allergies);
        $stmt_medical->execute();
        $stmt_household->closeCursor();

        // Insert Emergency Contact Information
        $sql_emergency = "CALL add_emergency_contact(:household_id, :emergency_contact_name, :emergency_contact_number, :emergency_contact_relation)";
        $stmt_emergency = $conn->prepare($sql_emergency);
        $stmt_emergency->bindParam(':household_id', $household_id);
        $stmt_emergency->bindParam(':emergency_contact_name', $emergency_contact_name);
        $stmt_emergency->bindParam(':emergency_contact_number', $emergency_contact_number);
        $stmt_emergency->bindParam(':emergency_contact_relation', $emergency_contact_relation);
        $stmt_emergency->execute();
        $stmt_household->closeCursor();

        // Commit the transaction
        $conn->commit();

        // Redirect to the household profile page after successful insertion
        header("Location: hhprofile.php");
        exit;

    } catch (PDOException $e) {
        // Rollback the transaction if there is an error
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        // Rollback the transaction if there is an error
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>