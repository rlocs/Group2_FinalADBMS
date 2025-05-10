<?php
require_once '../dbConnection.php';
session_start();
// Check if user is logged in and is a Healthworker
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../login.php");
    exit;
}

// Example user details
$username = $_SESSION['username'];

try {
    if (!isset($conn)) {
        $database = new Database();
        $conn = $database->getConnection();
    }

    // Household Profiles count using stored procedure returning result set
    $stmtHouseholds = $conn->prepare("CALL get_total_households_rs()");
    $stmtHouseholds->execute();
    $resultHouseholds = $stmtHouseholds->fetch(PDO::FETCH_ASSOC);
    $stmtHouseholds->closeCursor();
    $total_households = $resultHouseholds ? $resultHouseholds['total_households'] : 0;

    // Total Patients count using stored procedure returning result set
    $stmtPatients = $conn->prepare("CALL get_total_patients_rs()");
    $stmtPatients->execute();
    $resultPatients = $stmtPatients->fetch(PDO::FETCH_ASSOC);
    $stmtPatients->closeCursor();
    $total_patients = $resultPatients ? $resultPatients['total_patients'] : 0;

    // Today's Appointments count using stored procedure returning result set
    $stmtToday = $conn->prepare("CALL get_today_appointment_count_rs()");
    $stmtToday->execute();
    $resultToday = $stmtToday->fetch(PDO::FETCH_ASSOC);
    $stmtToday->closeCursor();
    $total_today_appointments = $resultToday ? $resultToday['total_today'] : 0;

    // Tomorrow's Appointments count using stored procedure returning result set
    $stmtTomorrow = $conn->prepare("CALL get_tomorrow_appointment_count_rs()");
    $stmtTomorrow->execute();
    $resultTomorrow = $stmtTomorrow->fetch(PDO::FETCH_ASSOC);
    $stmtTomorrow->closeCursor();
    $total_tomorrow_appointments = $resultTomorrow ? $resultTomorrow['total_tomorrow'] : 0;

} catch (PDOException $e) {
    $total_households = 0;
    $total_patients = 0;
    $total_today_appointments = 0;
    $total_tomorrow_appointments = 0;
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Healthworker Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <!-- Left side nav -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'patient.php' ? 'active' : ''; ?>" href="patient.php">Patients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'barangay.php' ? 'active' : ''; ?>" href="barangay.php">Brgy. Map</a>
                </li>

                <!-- More dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle 
                        <?php echo in_array(basename($_SERVER['PHP_SELF']), ['faqs.php', 'aboutus.php']) ? 'active' : ''; ?>" 
                        href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        More
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="faqs.php">FAQs</a></li>
                        <li><a class="dropdown-item" href="aboutus.php">About Us</a></li>
                    </ul>
                </li>
            </ul>

                <!-- Right side profile and logout -->
                <!-- USER INFO WRAPPER -->
<div class="user-info">
    <!-- DATE -->
    <div class="user-date">
        <p class="label">Today's Date</p>
        <p class="value">
            <?php 
                date_default_timezone_set('Asia/Manila');
                echo date('Y-m-d');
            ?>
        </p>
    </div>

    <!-- USERNAME -->
    <div class="user-name">
        <a href="profile.php">
            <strong><?php echo htmlspecialchars($username); ?></strong>
        </a>
    </div>

    <!-- LOGOUT -->
    <form method="POST" action="">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</div>

            </div>
        </div>
    </nav>


    <div class="container">   
            <!-- Welcome Section -->
                <div class="filter-container welcome-container mb-4">
                    <h3>Welcome!</h3>
                    <h1><?php echo htmlspecialchars($username); ?>.</h1>
                    <p>Thanks for joining us. We are always striving to provide you with the best service.<br>
                        You can view your daily schedule and manage your patient appointments!</p>
                </div>
        </div>
    </div>

    <tr>
                    <td colspan="4">
                        <table border="0" width="85%"">
                            <tr>
                                <td width="50%">  
                                          
                                    <center>
                                    <table class="filter-containers" style="border: none;" border="0">

                                        <tr>
                                            <td colspan="4">
                                                <p style="font-size: 25px; font-weight: 600; padding-left: 12px;">Status</p>
                                            </td>
                                            
                                        </tr>
                                        
                                        <tr>
                                        <td style="width: 20%;">
                                                <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                            <div>
                                                <div class="h1-dashboard"><?php echo $total_tomorrow_appointments; ?></div><br>
                                                <div class="h3-dashboard">Tomorrow Appointment</div>
                                            </div>
                                            <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/doctors-hover.svg');background-position: center;"></div>
                                        </div>
                                    </td>
                                
                                    <td style="width: 20%;">
                                        <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                            <div>
                                                <div class="h1-dashboard"><?php echo $total_households; ?></div><br>
                                                <div class="h3-dashboard">Households</div>
                                            </div>
                                            <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/book-hover.svg');background-position: center;"></div>
                                        </div>
                                    </td>
                                    <!-- Patients -->
                                    <td style="width: 20%;">
                                        <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                            <div>
                                                <div class="h1-dashboard"><?php echo $total_patients; ?></div><br>
                                                <div class="h3-dashboard">Patients</div>
                                            </div>
                                            <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/patients-hover.svg');background-position: center;"></div>
                                        </div>
                                    </td>

                                            <!-- New Booking -->
                                            <td style="width: 20%;">
                                                <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                                    <div>
                                                        <div class="h1-dashboard"><?php echo $total_today_appointments; ?></div><br>
                                                        <div class="h3-dashboard">New Appointment</div>
                                                    </div>
                                                    <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/doctors-hover.svg'); background-position: center;"></div>
                                                </div>
                                            </td>

                                            

                                        <tr>
                                                <td colspan="4">
                                                    <table width="100%" border="0" class="dashbord-tables">
                                                    <tr>
                                                    <td colspan="2">
                                                    <!-- Appointments Section -->
                                                    <div class="container-fluid px-5 mt-4">
                                                            <div class="row">
                                                            <!-- All Appointments Today -->
                                                            <div class="col-md-6">
                                                                <p style="font-size:23px;font-weight:700;color:var(--primarycolor);">
                                                                    All Appointments For <?php echo date("l"); ?>
                                                                </p>
                                                                <p style="font-size:15px;font-weight:500;color:#212529e3;">
                                                                Welcome! Every appointment is a chance to make a positive difference.
                                                                Here's quick access to today's appointments.<br>
                                                                </p>

                                                                <div class="table-responsive mt-3">
                                                                    <div class="table-container">
                                                                        <h4 class="fw-bold appointment-heading" style="margin-left: 10px;">Today</h4>
                                                                        <table class="table table-bordered table-hover bg-white">
                                                                            <thead class="table-light">
                                                                                <tr>
                                                                                    <th>Number</th>
                                                                                    <th>Patient</th>
                                                                                    <th>Time</th>
                                                                                    <th>Doctor</th>
                                                                                    <th>Reason</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="appointmentTableBody">
                                                                                <?php
                                                                                // Fetch appointments for today and tomorrow using stored procedure
                                                                                try {
                                                                                    $stmt = $conn->prepare("CALL get_appointments_for_days()");
                                                                                    $stmt->execute();
                                                                                    $appointmentsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                    $stmt->closeCursor();

                                                                                    $todayAppointments = [];
                                                                                    $tomorrowAppointments = [];
                                                                                    $counterToday = 1;
                                                                                    $counterTomorrow = 1;

                                                                                    foreach ($appointmentsData as $row) {
                                                                                        if ($row['day_type'] === 'today') {
                                                                                            $todayAppointments[] = $row;
                                                                                        } elseif ($row['day_type'] === 'tomorrow') {
                                                                                            $tomorrowAppointments[] = $row;
                                                                                        }
                                                                                    }
                                                                                } catch (PDOException $e) {
                                                                                    $todayAppointments = [];
                                                                                    $tomorrowAppointments = [];
                                                                                }

                                                                                foreach ($todayAppointments as $a): ?>
                                                                                <tr>
                                                                                    <td><?= $counterToday++ ?></td>
                                                                                    <td><?= htmlspecialchars($a['patient_name']) ?></td>
                                                                                    <td><?= htmlspecialchars(date('H:i', strtotime($a['time']))) ?></td>
                                                                                    <td><?= htmlspecialchars($a['doctor']) ?></td>
                                                                                    <td><?= htmlspecialchars($a['reason']) ?></td>
                                                                                </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Upcoming Appointments -->
                                                            <div class="col-md-6">
                                                                <p style="text-align:right; font-size:23px; font-weight:700; color:var(--primarycolor);">
                                                                    Upcoming Appointments For <?php echo date("l", strtotime("+1 day")); ?>
                                                                </p>
                                                                <p style="text-align:right; font-size:15px; font-weight:500; color:#212529e3;">
                                                                    Here's quick access to sessions scheduled over the next 7 days.<br>
                                                                    Stay prepared and keep making a difference in your community.
                                                                </p>

                                                                <div class="table-responsive mt-3">
                                                                    <div class="table-container">
                                                                        <h4 class="fw-bold appointment-heading" style="margin-left: 10px;">Tomorrow</h4>
                                                                        <table class="table table-bordered table-hover bg-white">
                                                                            <thead class="table-light">
                                                                                <tr>
                                                                                    <th>Number</th>
                                                                                    <th>Patient</th>
                                                                                    <th>Time</th>
                                                                                    <th>Doctor</th>
                                                                                    <th>Reason</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="upcomingAppointmentTableBody">
                                                                                <?php
                                                                                foreach ($tomorrowAppointments as $a): ?>
                                                                                <tr>
                                                                                    <td><?= $counterTomorrow++ ?></td>
                                                                                    <td><?= htmlspecialchars($a['patient_name']) ?></td>
                                                                                    <td><?= htmlspecialchars(date('H:i', strtotime($a['time']))) ?></td>
                                                                                    <td><?= htmlspecialchars($a['doctor']) ?></td>
                                                                                    <td><?= htmlspecialchars($a['reason']) ?></td>
                                                                                </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    </td>
                                                </td>
                                            </tr>
                                            <tr>
                                        </table>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                <div style="width: 85%; margin: 0 auto; display: flex; justify-content: space-between; gap: 20px;">
                    <?php include 'chart.php'; ?>
                </div>
            </td>
        </tr>
    </table>


<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.11/dist/sweetalert2.all.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // AJAX polling to refresh appointment tables every 30 seconds
    function refreshAppointments() {
        $.ajax({
            url: 'healthworker/index.php',
            type: 'POST',
            data: { ajax_refresh: 1 },
            success: function(response) {
                // Parse the response HTML and update the table bodies
                var parser = new DOMParser();
                var doc = parser.parseFromString(response, 'text/html');

                var todayRows = doc.querySelectorAll('#appointmentTableBody tr');
                var tomorrowRows = doc.querySelectorAll('#upcomingAppointmentTableBody tr');

                $('#appointmentTableBody').html('');
                $('#upcomingAppointmentTableBody').html('');

                todayRows.forEach(function(row) {
                    $('#appointmentTableBody').append(row.outerHTML);
                });
                tomorrowRows.forEach(function(row) {
                    $('#upcomingAppointmentTableBody').append(row.outerHTML);
                });
            },
            error: function() {
                console.error('Failed to refresh appointments.');
            }
        });
    }

    $(document).ready(function() {
        setInterval(refreshAppointments, 30000); // Refresh every 30 seconds
    });
</script>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Fetch data for patient age distribution chart
});
</script>

<?php include 'chart.php'; ?>

</body>
</html>
