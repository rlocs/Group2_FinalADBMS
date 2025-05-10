<?php
session_start();
require_once '../dbConnection.php';

// Database connection
$database = new Database();
$conn = $database->getConnection();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT resident_id FROM residents WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email is already registered.";
        } else {
            // Insert new resident
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO residents (name, email, phone, address, password) VALUES (:name, :email, :phone, :address, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':password', $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['resident_id'] = $conn->lastInsertId();
                $_SESSION['resident_name'] = $name;
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Failed to register. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Resident Registration for Health Center System. Create your account to set appointments online.">
    <title>Resident Registration - Health Center System</title>

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

        .btn-register {
            width: 100%;
            background-color: rgb(37, 52, 79);
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background-color: rgb(25, 54, 243);
            transform: scale(1.02);
        }

        .register-title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #25344f;
        }

        .login-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .login-link a {
            color: #0040aa;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body style="background: url('sanpedrobg.jpg') no-repeat center center fixed; background-size: cover;">
    <div class="container" style="display: flex; gap: 20px; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; background-color: rgba(255, 255, 255, 0); border-radius: 15px; box-sizing: border-box;">
        <form method="post" action="register.php" style="display: flex; gap: 20px;">
            <div class="square" style="width: 400px; height: 400px; padding: 20px; box-sizing: border-box;">
                <div class="register-title">Resident Registration</div>
                <?php if (!empty($errors)): ?>
                    <div style="color:red;">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <i class='bx bxs-user'></i>
                    <input type="text" class="form-control" name="name" placeholder="Name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                </div>
                <div class="form-group">
                    <i class='bx bxs-envelope'></i>
                    <input type="email" class="form-control" name="email" placeholder="Email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                </div>
                <div class="form-group">
                    <i class='bx bxs-phone'></i>
                    <input type="text" class="form-control" name="phone" placeholder="Phone" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>">
                </div>
            </div>
            <div class="square" style="width: 400px; height: 400px; padding: 20px; box-sizing: border-box;">
                <div class="form-group">
                    <i class='bx bxs-home'></i>
                    <textarea class="form-control" name="address" placeholder="Address"><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                </div>
                <div class="form-group">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <i class='bx bxs-lock-alt'></i>
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn-register">Register</button>
                <p class="login-link">Already have an account? <a href="login.php">Login here</a>.</p>
            </div>
        </form>
    </div>
</body>

</html>
