<?php
session_start();
// Check if user is logged in and is a Healthworker
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

// Example user details
$username = $_SESSION['username'];

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
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointment.php' ? 'active' : ''; ?>" href="appointment.php">Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'patient.php' ? 'active' : ''; ?>" href="patient.php">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hhprofile.php' ? 'active' : ''; ?>" href="hhprofile.php">Household Profiles</a>
                    </li>
                </ul>




                <!-- Right side profile and logout -->
                <div class="user-info">
                    <div>
                        <div>
                            <a href="profile.php" style="text-decoration: none;font-size: 1.7rem; color: black;">
                                <strong><?php echo htmlspecialchars($username); ?></strong>
                            </a>
                        </div>
                    </div>
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
                                                    <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                                                        Today's Date
                                                    </p>
                                                    <p class="heading-sub12" style="padding: 0;margin: 0;">
                                                        <?php 
                                                    date_default_timezone_set('Asia/Manila');
                            
                                                    $today = date('Y-m-d');
                                                    echo $today; ?>
                                                    </p>
                                            </td>
                                            
                                        </tr>
                                        
                                        <tr>
                                            <!-- Doctors -->
                                            <td style="width: 20%;">
                                                <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                                    <div>
                                                        <div class="h1-dashboard">1</div><br>
                                                        <div class="h3-dashboard">additional</div>
                                                    </div>
                                                    <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/doctors-hover.svg');background-position: center;"></div>
                                                </div>
                                            </td>
                                            <!-- Doctors -->
                                            <td style="width: 20%;">
                                                <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                                    <div>
                                                        <div class="h1-dashboard">1</div><br>
                                                        <div class="h3-dashboard">Doctors</div>
                                                    </div>
                                                    <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/doctors-hover.svg');background-position: center;"></div>
                                                </div>
                                            </td>

                                            <!-- Patients -->
                                            <td style="width: 20%;">
                                                <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                                    <div>
                                                        <div class="h1-dashboard">5</div><br>
                                                        <div class="h3-dashboard">Patients</div>
                                                    </div>
                                                    <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/patients-hover.svg');background-position: center;"></div>
                                                </div>
                                            </td>

                                            <!-- New Booking -->
                                            <td style="width: 20%;">
                                                <div class="dashboard-items" style="padding: 20px; margin: auto; width: 95%; display: flex; align-items: center; justify-content: space-between;">
                                                    <div>
                                                        <div class="h1-dashboard">8</div><br>
                                                        <div class="h3-dashboard">New Booking</div>
                                                    </div>
                                                    <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/book-hover.svg'); background-position: center;"></div>
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
                                                                    Here's quick access to today's appointments.<br>
                                                                    More details are available in the @Appointment section.
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
                                                                                    $appointments = [
                                                                                        ["number" => 1, "patient_name" => "John Doe", "time" => "10:00", "doctor" => "Dr. Smith", "reason" => "Checkup"],
                                                                                        ["number" => 2, "patient_name" => "Jane Roe", "time" => "14:00", "doctor" => "Dr. Lee", "reason" => "Flu"]
                                                                                ];
                                                                                foreach ($appointments as $a): ?>
                                                                                <tr>
                                                                                    <td><?= $a['number'] ?></td>
                                                                                    <td><?= htmlspecialchars($a['patient_name']) ?></td>
                                                                                    <td><?= $a['time'] ?></td>
                                                                                    <td><?= htmlspecialchars($a['doctor']) ?></td>
                                                                                    <td><?= $a['reason'] ?></td>
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
                                                                    Manage them in the @Schedule section.
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
                                                                                $upcomingAppointments = [
                                                                                    ["number" => 1, "patient_name" => "John Doe", "time" => "10:00", "doctor" => "Dr. Smith", "reason" => "Checkup"],
                                                                                    ["number" => 2, "patient_name" => "Jane Roe", "time" => "14:00", "doctor" => "Dr. Lee", "reason" => "Flu"]
                                                                                ];
                                                                                foreach ($upcomingAppointments as $a): ?>
                                                                                <tr>
                                                                                    <td><?= $a['number'] ?></td>
                                                                                    <td><?= htmlspecialchars($a['patient_name']) ?></td>
                                                                                    <td><?= $a['time'] ?></td>
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
                                    </center>
                                </td>       
                            </tr>
                        </table>
                    </td>


<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.11/dist/sweetalert2.all.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
