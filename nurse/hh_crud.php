<?php
require '../dbConnection.php';
session_start();


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_household'])) {
    $household_id = $_POST['household_id'];
    $head_name = $_POST['head_name'];
    $purok = $_POST['purok'];
    $nic_number = $_POST['nic_number'];
    $num_members = $_POST['num_members'];

    // Household Member Details
    $member_name = $_POST['member_name'];
    $relation = $_POST['relation'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];

    $medical_condition = $_POST['medical_condition'];
    $allergies = $_POST['allergies'];
    $emergency_contact_name = $_POST['emergency_contact_name'];
    $emergency_contact_number = $_POST['emergency_contact_number'];
    $emergency_contact_relation = $_POST['emergency_contact_relation'];

    try {
        $database = new Database();
        $conn = $database->getConnection();

        // Start transaction
        $conn->beginTransaction();

        // Update Household Profile
        $sql = "CALL UpdateHouseholdProfile(:household_id, :head_name, :purok, :nic_number, :num_members, :medical_condition, :allergies, :emergency_contact_name, :emergency_contact_number, :emergency_contact_relation)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':household_id', $household_id, PDO::PARAM_INT);
        $stmt->bindParam(':head_name', $head_name);
        $stmt->bindParam(':purok', $purok);
        $stmt->bindParam(':nic_number', $nic_number);
        $stmt->bindParam(':num_members', $num_members);
        $stmt->bindParam(':medical_condition', $medical_condition);
        $stmt->bindParam(':allergies', $allergies);
        $stmt->bindParam(':emergency_contact_name', $emergency_contact_name);
        $stmt->bindParam(':emergency_contact_number', $emergency_contact_number);
        $stmt->bindParam(':emergency_contact_relation', $emergency_contact_relation);

        $stmt->execute();
        $stmt->closeCursor();

        // Delete existing household members before inserting updated ones
        $delete_sql = "DELETE FROM household_members WHERE household_id = :household_id";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bindParam(':household_id', $household_id, PDO::PARAM_INT);
        $delete_stmt->execute();

        // Insert updated household members
        $sql_member = "CALL add_household_members(:household_id, :member_name, :relation, :age, :sex)";
        $stmt_member = $conn->prepare($sql_member);
        for ($i = 0; $i < count($member_name); $i++) {
            $stmt_member->bindValue(':household_id', $household_id);
            $stmt_member->bindValue(':member_name', $member_name[$i]);
            $stmt_member->bindValue(':relation', $relation[$i]);
            $stmt_member->bindValue(':age', $age[$i]);
            $stmt_member->bindValue(':sex', $sex[$i]);
            $stmt_member->execute();
            $stmt_member->closeCursor();
        }

        // Commit transaction
        $conn->commit();

        header("Location: hhprofile.php");
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
