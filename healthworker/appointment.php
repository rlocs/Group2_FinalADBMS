<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$email = $username . "@gmail.com";
$profilePic = "../profilepic.jpg";

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Dummy search value placeholder
$search = $_GET['search'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointments - Healthworker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/appointment.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
            <!-- Left nav links -->
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="appointment.php">Appointments</a></li>
                <li class="nav-item"><a class="nav-link" href="patient.php">Patients</a></li>
                <li class="nav-item"><a class="nav-link" href="household.php">Household Profiles</a></li>
            </ul>

            <!-- Right profile and logout -->
            <div class="user-info">
                <a href="profile.php"><img src="<?= $profilePic ?>" alt="Profile Picture"></a>
                <div>
                    <div><a href="profile.php" style="text-decoration: none; color: black;"><strong><?= htmlspecialchars($username) ?></strong></a></div>
                    <div><a href="mailto:<?= htmlspecialchars($email) ?>" style="text-decoration: none; color: black;"><?= htmlspecialchars($email) ?></a></div>
                </div>
                <form method="POST" action="">
                    <button type="submit" name="logout" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Content Section -->
<div class="content-section">
    <!-- Add Appointment Button (no fields here) -->
    <button type="button" class="btn btn-custom-add mb-4" data-bs-toggle="modal" data-bs-target="#addAppointmentModal">
        Add Appointment
    </button>

    <!-- Search Form -->
    <form method="get" class="mb-3 d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Search by Name/Time" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-outline-secondary">Search</button>
    </form>

    <!-- Appointment Table -->
    <div class="table-responsive mt-4">
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
                    <td><?= htmlspecialchars($a['reason']) ?></td>
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
                                        <!-- ID is displayed but not editable -->
                                        <div class="mb-3">
                                            <label>ID</label>
                                            <input type="text" class="form-control" value="<?= $a['id'] ?>" readonly>
                                        </div>
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
                                        <button type="submit" name="update" class="btn btn-success">Save changes</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <button type="submit" name="create" class="btn btn-success">Add Appointment</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>

</div>
 <!-- Pagination -->
 <nav>
        <ul class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.11/dist/sweetalert2.all.min.js"></script>

<!-- Bootstrap JS and Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
