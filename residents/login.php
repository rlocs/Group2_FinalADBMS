<?php
session_start();
require_once '../dbConnection.php';
// Database connection
$database = new Database();
$conn = $database->getConnection();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT resident_id, name, password FROM residents WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $resident = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resident && password_verify($password, $resident['password'])) {
            $_SESSION['resident_id'] = $resident['resident_id'];
            $_SESSION['resident_name'] = $resident['name'];
            header("Location: appointment.php");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Resident Login for Health Center System. Login to set appointments online.">
    <title>Resident Login - Health Center System</title>

    <!-- Boxicons for icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: rgb(255, 255, 255);
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            padding: 0;
        }

        .square {
            width: 400px;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid #ccc;
            background-color: rgb(203, 202, 209);
            box-shadow: 0 6px 8px rgba(22, 22, 22, 0.1);
            border-radius: 15px;
            flex-direction: column;
            padding: 27.9px;
        }

        .square img {
            width: 116%;
            height: auto;
            border-radius: 10px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
            width: 100%;
        }

        .form-group i {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            color: #444;
        }

        .form-control {
            padding-left: 35px;
        }

        .btn-login {
            width: 100%;
            background-color: rgb(37, 52, 79);
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: rgb(25, 54, 243);
            transform: scale(1.02);
        }

        .login-title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #25344f;
        }

        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .register-link a {
            color: #0040aa;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body style="background: url('sanpedrobg.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="container" style="padding: 20px; border-radius: 15px; background-color: rgba(255, 255, 255, 0.4); display: flex; gap: 20px; justify-content: center; align-items: center;">
        <div class="square">
            <img src="../sanpedro.png" alt="Health Center Logo">
        </div>
        <div class="square">
            <div class="login-title">Resident Login</div>
            <?php if (!empty($errors)): ?>
                <div style="color:red;">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <div class="form-group">
                    <i class='bx bxs-envelope'></i>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                </div>
                <div class="form-group">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            <p class="register-link">Don't have an account? <a href="register.php">Register here</a>.</p>
        </div>
    </div>
</body>

</html>
