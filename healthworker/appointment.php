<?php
require '../dbConnection.php';
session_start();

// Redirect if not logged in or not Healthworker role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Healthworker') {
    header("Location: ../login.php");
    exit;
}

// Initialize variables
$username = $_SESSION['username'];
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 4;
$offset = ($page - 1) * $records_per_page;

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Database connection
$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $searchTerm = $_GET['search'] ?? '';
    $limit = $records_per_page;
    $offsetAjax = 0; // For live search, start from first page or implement pagination later

    $query = $searchTerm ? "CALL search_appointments_paginated(:search_term, :limit_val, :offset_val)" : "CALL get_all_appointments_paginated(:limit_val, :offset_val)";
    $stmt = $conn->prepare($query);

    if ($searchTerm) {
        $stmt->bindValue(':search_term', $searchTerm, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit_val', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset_val', $offsetAjax, PDO::PARAM_INT);

    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($appointments);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage your appointments and schedule with ease. Add, edit, or delete appointments for patients and doctors in the health center.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/appointment.css">
    <script src="live.js"></script>
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
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'medications.php' ? 'active' : ''; ?>" href="medications.php">Medications</a>
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

<!-- Content Section -->
<div class="container top-gap-after-navbar">
    <div class="d-flex justify-content-between align-items-center mb-2" style="margin: 0 26px;">
        <!-- Add Patient Button -->
        <div class="d-flex align-items-center">
        <button type="button" class="btn btn-custom-add" data-bs-toggle="modal" data-bs-target="#addAppointmentModal" aria-label="Add Appointment">
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
        <h4 class="fw-bold appointment-heading" style="margin-left: 10px;">Appointment List</h4>
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="appointmentTableBody">
                <?php
    $count_stmt = $conn->prepare("CALL search_appointments_count(:search_term)");
    $count_stmt->bindValue(':search_term', $search);
    $count_stmt->execute();
    $total_appointments = $count_stmt->fetchColumn();
    $count_stmt->closeCursor();

    $stmt = $conn->prepare("CALL search_appointments_paginated(:search_term, :limit, :offset)");
    $stmt->bindValue(':search_term', $search);
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $modals = ''; // Start collecting modals

                if (count($appointments) > 0):
$counter = $offset + 1;
$used_ids = [];
function getRandomId(&$used_ids) {
    do {
        $rand_id = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    } while (in_array($rand_id, $used_ids));
    $used_ids[] = $rand_id;
    return $rand_id;
}
foreach ($appointments as $a):
    $id = $a['appointment_id'];
?>
<tr>
    <td><?= $a['appointment_id'] ?></td>
    <td><?= htmlspecialchars($a['patient_name']) ?></td>
    <td><?= htmlspecialchars($a['date']) ?></td>
    <td><?= htmlspecialchars($a['time']) ?></td>
    <td><?= htmlspecialchars($a['doctor']) ?></td>
    <td><?= htmlspecialchars($a['reason']) ?></td>
    <td>
        <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $id ?>" aria-label="Edit Appointment">Edit</button>
        <form method="post" action="appoint_crud.php" class="d-inline" onsubmit="return confirm('Delete this appointment?');">
            <input type="hidden" name="delete_id" value="<?= $id ?>">
            <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
        </form>
    </td>
</tr>
<?php
    // Build the modal HTML for each row
    $modals .= '
    <div class="modal fade" id="editModal' . $id . '" tabindex="-1" aria-labelledby="editModalLabel' . $id . '" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="appoint_crud.php" class="modal-content">
<input type="hidden" name="appointment_id" value="' . $id . '">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel' . $id . '">Edit Appointment</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Patient Name</label>
                        <input type="text" name="patient_name" class="form-control" value="' . htmlspecialchars($a['patient_name']) . '" required>
                    </div>
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="' . htmlspecialchars($a['date']) . '" required>
                    </div>
                    <div class="mb-3">
                        <label>Time</label>
                        <input type="time" name="time" class="form-control" value="' . htmlspecialchars($a['time']) . '" required>
                    </div>
                    <div class="mb-3">
                        <label>Doctor</label>
                        <input type="text" name="doctor" class="form-control" value="' . htmlspecialchars($a['doctor']) . '" required>
                    </div>
                    <div class="mb-3">
                        <label>Reason</label>
                        <input type="text" name="reason" class="form-control" value="' . htmlspecialchars($a['reason']) . '" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update" class="btn btn-secondary">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>';
endforeach;
                else:
                ?>
                <tr><td colspan="7" class="text-center">No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_appointments > $records_per_page): ?>
        <nav aria-label="Page navigation example" class="mt-3">
          <ul class="pagination justify-content-start">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
              </a>
            </li>
            <?php
            $total_pages = ceil($total_appointments / $records_per_page);
            for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Next</span>
              </a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Output all edit modals here -->
<?= $modals ?>
<!-- Add Appointment Modal -->
<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-labelledby="addAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="appoint_crud.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAppointmentModalLabel">Add Appointment</h5>
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