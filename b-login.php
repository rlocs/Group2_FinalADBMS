<?php
session_start();
include 'dbConnection.php'; // Make sure this file connects to your db_healthcenter

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if username and password are not empty
    if (!empty($username) && !empty($password)) {
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'Healthworker':
                        header("Location: healthworker/index.php");
                        exit;
                    case 'Nurse':
                        header("Location: nurse/index.php");
                        exit;
                    case 'Doctor':
                        header("Location: doctor/index.php");
                        exit;
                    default:
                        echo "Unknown role.";
                        exit;
                }
            } else {
                echo "<script>alert('Incorrect password.'); window.location='login.php';</script>";
            }
        } else {
            echo "<script>alert('Username not found.'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Please fill in both fields.'); window.location='login.php';</script>";
    }
} else {
    header("Location: login.php");
    exit;
}
?>
