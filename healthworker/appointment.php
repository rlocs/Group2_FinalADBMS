<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$search = $_GET['search'] ?? '';
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
    <title>Appointment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/appointment.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <!-- Left side nav -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.php" onclick="showSection('dashboard')">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="appointment.php" onclick="showSection('appointments')">Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="patient.php" onclick="showSection('patients')">Patients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hhprofile.php" onclick="showSection('households')">Household Profiles</a>
                </li>
            </ul>

            <!-- Right side profile and logout -->
            <div class="user-info">
                <div>
                    <div>
                        <a href="profile.php" style="text-decoration: none; font-size: 1.7rem; color: black;">
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

<!-- Content Section -->
<div class="container top-gap-after-navbar">
    <div class="d-flex justify-content-between align-items-center mb-2" style="margin: 0 26px;">
        <!-- Add Patient Button -->
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-custom-add"data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
            <i class="bi bi-plus-circle"></i>
            </button>
            <span class="ms-2">Add Appointment</span>
        </div>

        <!-- Search Form -->
        <form method="get" class="d-flex" style="max-width: 350px;">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by Name" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-search">Search</button>
            </div>
        </form>
    </div>
</div>

    <!-- Appointment Table -->
<div class="table-responsive mt-4">
<div class="table-container">
<h4 class="fw-bold appointment-heading"style="margin-left: 10px;">Appointment List</h4>
    <table class="table table-bordered table-hover bg-white">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="appointmentTableBody">
                <?php
                // Example dummy data (replace with data from view.php)
                $appointments = [
                    ["id" => 1, "patient_name" => "John Doe", "date" => "2025-04-15", "time" => "10:00", "doctor" => "Dr. Smith", "reason" => "Checkup"],
                    ["id" => 2, "patient_name" => "Jane Roe", "date" => "2025-04-16", "time" => "14:00", "doctor" => "Dr. Lee", "reason" => "Flu"]
                ];

                foreach ($appointments as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td><?= htmlspecialchars($a['patient_name']) ?></td>
                    <td><?= $a['date'] ?></td>
                    <td><?= $a['time'] ?></td>
                    <td><?= htmlspecialchars($a['doctor']) ?></td>
                    <td><?= $a['reason'] ?></td>
                    <td>
                        <!-- Edit Button -->
                        <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $a['id'] ?>">Edit</button>

                        <!-- Delete Form -->
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this appointment?');">
                            <input type="hidden" name="delete_id" value="<?= $a['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
                        </form>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $a['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $a['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <form method="post" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel<?= $a['id'] ?>">Edit Appointment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Patient Name</label>
                                            <input type="text" name="patient_name" class="form-control" value="<?= htmlspecialchars($a['patient_name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Date</label>
                                            <input type="date" name="date" class="form-control" value="<?= $a['date'] ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Time</label>
                                            <input type="time" name="time" class="form-control" value="<?= $a['time'] ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Doctor</label>
                                            <input type="text" name="doctor" class="form-control" value="<?= htmlspecialchars($a['doctor']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Reason</label>
                                            <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($a['reason']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="update" class="btn btn-secondary">Save changes</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- End Modal -->
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAppointmentModalLabel">Add Appointment</h5>
                <button type="button" class="btn-custom-add " data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Patient Name -->
                <div class="mb-3">
                    <label>Patient Name</label>
                    <input type="text" name="patient_name" class="form-control" required>
                </div>
                <!-- Date -->
                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <!-- Time -->
                <div class="mb-3">
                    <label>Time</label>
                    <input type="time" name="time" class="form-control" required>
                </div>
                <!-- Doctor -->
                <div class="mb-3">
                    <label>Doctor</label>
                    <input type="text" name="doctor" class="form-control" required>
                </div>
                <!-- Reason (Dropdown or Text) -->
                <div class="mb-3">
                    <label>Reason</label>
                    <select name="reason" class="form-control">
                        <option value="Checkup">Checkup</option>
                        <option value="Flu">Flu</option>
                        <option value="Consultation">Consultation</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="create" class="btn btn-secondary">Add Appointment</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS and Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
