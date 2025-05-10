<?php
session_start();
require_once '../dbConnection.php';

// Database connection
$database = new Database();
$conn = $database->getConnection();

if (!isset($_SESSION['resident_id'])) {
    header("Location: login.php");
    exit;
}

$resident_name = $_SESSION['resident_name'];
$errors = [];
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

// Handle AJAX request for available times
if (isset($_GET['action']) && $_GET['action'] === 'get_available_times' && isset($_GET['date'])) {
    $date = $_GET['date'];
    $start_hour = 8;
    $end_hour = 16;
    $interval_minutes = 30;

    // Function to generate time slots
    function generate_time_slots($date, $start_hour, $end_hour, $interval_minutes) {
        $slots = [];
        $start = new DateTime("$date $start_hour:00");
        $end = new DateTime("$date $end_hour:00");
        while ($start < $end) {
            $slots[] = $start->format('H:i');
            $start->modify("+$interval_minutes minutes");
        }
        return $slots;
    }

    // Get booked times for the given date
    $stmt = $conn->prepare("SELECT time FROM appointments WHERE date = :date");
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $booked_times = array_column($appointments, 'time');

    // Generate available times excluding booked times and past times for today
    $available_times = [];
    $now = new DateTime();
    $today = $now->format('Y-m-d');
    $current_time = $now->format('H:i');

    $slots = generate_time_slots($date, $start_hour, $end_hour, $interval_minutes);
    foreach ($slots as $slot) {
        if ($date == $today && $slot <= $current_time) {
            continue;
        }
        if (in_array($slot, $booked_times)) {
            continue;
        }
        $available_times[] = $slot;
    }

    header('Content-Type: application/json');
    echo json_encode($available_times);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datetime = $_POST['time']; // combined date and time from select
    $doctor = trim($_POST['doctor']);
    $reason = trim($_POST['reason']);

    if (empty($datetime)) {
        $errors[] = "Date and Time are required.";
    }
    if (empty($doctor)) {
        $errors[] = "Doctor is required.";
    }
    if (empty($reason)) {
        $errors[] = "Reason is required.";
    }

    if (empty($errors)) {
        // Parse date and time from combined datetime string
        $datetime_parts = explode(' ', $datetime);
        if (count($datetime_parts) == 2) {
            $date = $datetime_parts[0];
            $time = $datetime_parts[1];
        } else {
            $errors[] = "Invalid date and time format.";
        }
    }

    if (empty($errors)) {
        // Check if the selected time is already occupied for the date
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE date = :date AND time = :time");
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':time', $time);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['count'] > 0) {
            $errors[] = "The selected time is already occupied. Please choose a different time.";
        } else {
            // Count existing appointments for the selected date
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE date = :date");
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $queue_number = $row ? $row['count'] + 1 : 1;

            // Insert appointment
            $stmt = $conn->prepare("INSERT INTO appointments (patient_name, date, time, doctor, reason) VALUES (:patient_name, :date, :time, :doctor, :reason)");
            $stmt->bindParam(':patient_name', $resident_name);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':time', $time);
            $stmt->bindParam(':doctor', $doctor);
            $stmt->bindParam(':reason', $reason);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Appointment set successfully. Your queue number for $date is $queue_number.";
                header("Location: appointment.php");
                exit;
            } else {
                $errors[] = "Failed to set appointment. Please try again.";
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
    <meta name="description" content="Set Appointment for Health Center System. Residents can set appointments online.">
    <title>Set Appointment - Health Center System</title>

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
            height: 500px;
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

        .btn-appointment {
            width: 100%;
            background-color: rgb(37, 52, 79);
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-appointment:hover {
            background-color: rgb(25, 54, 243);
            transform: scale(1.02);
        }

        .appointment-title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #25344f;
        }

        .logout-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .logout-link a {
            color: #0040aa;
            text-decoration: none;
        }

        .logout-link a:hover {
            text-decoration: underline;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body style="background: url('sanpedrobg.jpg') no-repeat center center fixed; background-size: cover; min-height: 100vh; margin: 0; display: flex; justify-content: center; align-items: center;">
    <div class="container" style="padding: 30px; max-width: 900px; width: 90%; box-sizing: border-box; display: flex; gap: 40px; justify-content: center; align-items: flex-start; background-color: rgba(255, 255, 255, 0.85); border-radius: 15px;">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="square" style="width: 350px; height: 350px; padding: 20px; box-sizing: border-box; background-color: rgb(203, 202, 209); box-shadow: 0 6px 8px rgba(22, 22, 22, 0.1); border-radius: 15px;">
                <img src="../sanpedro.png" alt="Health Center Logo" style="width: 100%; height: auto; border-radius: 10px;">
            </div>
            <div class="square" style="width: 350px; height: 150px; padding: 20px; box-sizing: border-box; background-color: rgb(203, 202, 209); box-shadow: 0 6px 8px rgba(22, 22, 22, 0.1); border-radius: 15px; display: flex; justify-content: center; align-items: center; text-align: center;">
                <?php if (!empty($success_message)): ?>
                    <?php
                        // Extract the queue number from the success message
                        preg_match('/queue number for [\d-]+ is (\d+)/', $success_message, $matches);
                        $queue_number = $matches[1] ?? '';
                        $message_prefix = preg_replace('/Your queue number for [\d-]+ is \d+/', 'Your queue number for ' . ($matches[0] ?? '') . ' is ', $success_message);
                        $message_prefix = preg_replace('/\d+$/', '', $success_message);
                    ?>
                    <div class="success-message" style="color: green; font-size: 16px;">
                        <?= htmlspecialchars(str_replace($queue_number, '', $success_message)) ?>
                        <span style="font-size: 24px; font-weight: bold;"><?= htmlspecialchars($queue_number) ?></span>
                    </div>
                <?php else: ?>
                    <div>&nbsp;</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="square" style="width: 500px; height: auto; padding: 30px; box-sizing: border-box; background-color: rgb(203, 202, 209); box-shadow: 0 6px 8px rgba(22, 22, 22, 0.1); border-radius: 15px;">
            <div class="appointment-title" style="margin-bottom: 25px;">Set Appointment</div>
            <p style="margin-bottom: 20px;">Welcome, <?= htmlspecialchars($resident_name) ?>! Set your appointment below.</p>

            <?php if (!empty($success_message)): ?>
                <div class="success-message" style="margin-bottom: 20px;"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-message" style="margin-bottom: 20px;">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="appointment.php">
                <div class="form-group" style="margin-bottom: 20px;">
                    <i class='bx bxs-calendar'></i>
                <input type="date" class="form-control" name="date" id="date" value="<?= isset($date) ? htmlspecialchars($date) : '' ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <i class='bx bxs-time'></i>
                <select class="form-control" name="time" id="time" required>
                    <option value="">Select Time</option>
                </select>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const dateInput = document.getElementById('date');
                    const timeSelect = document.getElementById('time');

                    // Function to fetch available times from server for selected date
                    function fetchAvailableTimes(date) {
                        fetch(`appointment.php?action=get_available_times&date=${date}`)
                            .then(response => response.json())
                            .then(data => {
                                timeSelect.innerHTML = '<option value="">Select Time</option>';
                                data.forEach(time => {
                                    const option = document.createElement('option');
                                    option.value = date + ' ' + time;
                                    option.textContent = time;
                                    timeSelect.appendChild(option);
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching available times:', error);
                                timeSelect.innerHTML = '<option value="">No times available</option>';
                            });
                    }

                    // Initialize time options on page load if date is set
                    if (dateInput.value) {
                        fetchAvailableTimes(dateInput.value);
                    }

                    // Update time options when date changes
                    dateInput.addEventListener('change', function() {
                        const selectedDate = dateInput.value;
                        if (selectedDate) {
                            fetchAvailableTimes(selectedDate);
                        } else {
                            timeSelect.innerHTML = '<option value="">Select Time</option>';
                        }
                    });
                });
            </script>
                <div class="form-group" style="margin-bottom: 20px;">
                    <i class='bx bxs-user'></i>
                    <input type="text" class="form-control" name="doctor" placeholder="Doctor" value="<?= isset($doctor) ? htmlspecialchars($doctor) : '' ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <i class='bx bxs-comment-detail'></i>
                    <textarea class="form-control" name="reason" placeholder="Reason" required><?= isset($reason) ? htmlspecialchars($reason) : '' ?></textarea>
                </div>
                <button type="submit" class="btn-appointment" style="width: 100%;">Set Appointment</button>
            </form>
            <p class="logout-link" style="margin-top: 20px;"><a href="logout.php">Logout</a></p>
        </div>
    </div>
</body>

</html>
