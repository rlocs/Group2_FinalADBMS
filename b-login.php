<?php
session_start();
require_once 'dbConnection.php'; // Make sure this contains the Database class

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
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
