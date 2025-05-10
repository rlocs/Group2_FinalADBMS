<?php
session_start();
require_once '../dbConnection.php'; // Adjust path if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_account'])) {
    error_log("Create account POST request received.");
    $name = $_POST['name'];
    $nic = $_POST['nic'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $role = $_POST['role'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Securely hash the password

    try {
        $database = new Database();
        $conn = $database->getConnection();

        // Check if the username already exists
        $checkSql = "SELECT user_id FROM users WHERE username = :username";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "Username already taken. Please choose another.";
            header("Location: profile.php"); // or wherever you need to redirect
            exit;
        }

        // Insert new user if username is unique
        $insertSql = "INSERT INTO users (name, nic, email, gender, dob, address, role, username, password)
                      VALUES (:name, :nic, :email, :gender, :dob, :address, :role, :username, :password)";

        $stmt = $conn->prepare($insertSql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':nic', $nic);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Staff account created successfully.";
            header("Location: profile.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to create staff account.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
    }
}

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