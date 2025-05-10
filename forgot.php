<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f9f9f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            width: 400px;
            padding: 30px;
            background: rgb(203, 202, 209);
            border-radius: 15px;
            box-shadow: 0 6px 8px rgba(22, 22, 22, 0.1);
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
        .btn-submit {
            width: 100%;
            background-color: rgb(37, 52, 79);
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background-color: rgb(25, 54, 243);
            transform: scale(1.02);
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #25344f;
            text-align: center;
        }
        .forgot-password {
            text-align: right;
            width: 100%;
            margin-top: -10px;
            margin-bottom: 15px;
        }
        .forgot-password a {
            font-size: 14px;
            color: #0040aa;
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="title">Forgot Password</div>
        <form action="b-login.php" method="POST">
            <div class="form-group" style="position: relative; margin-bottom: 20px;">
                <i class='bx bxs-user'></i>
                <input type="text" class="form-control" name="username" placeholder="Username" required />
            </div>
            <div class="form-group" style="position: relative; margin-bottom: 20px;">
                <i class='bx bxs-calendar'></i>
                <input type="date" class="form-control" name="dob" placeholder="Date of Birth" required />
            </div>
            <div class="form-group" style="position: relative; margin-bottom: 20px;">
                <i class='bx bxs-lock-alt'></i>
                <input type="password" class="form-control" name="password" placeholder="New Password" minlength="8" maxlength="16" required />
            </div>
            <div class="form-group" style="position: relative; margin-bottom: 20px;">
                <i class='bx bxs-lock-alt'></i>
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm New Password" minlength="8" maxlength="16" required />
            </div>
            <div class="forgot-password">
                <a href="login.php">Back to Login</a>
            </div>
            <button type="submit" class="btn-submit">Reset Password</button>
        </form>
    </div>
</body>

</html>