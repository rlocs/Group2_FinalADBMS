<?php
session_start();
require_once '../dbConnection.php'; // Adjust path if needed


// Handle profile edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $nic = $_POST['nic'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];

    try {
        $database = new Database();
        $conn = $database->getConnection();

        $sql = "CALL update_user_profile(:user_id, :name, :nic, :email, :gender, :dob, :address)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':nic', $nic);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully.";
            header("Location: profile.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update profile.";
            header("Location: profile.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: profile.php");
        exit;
    }
}
?>