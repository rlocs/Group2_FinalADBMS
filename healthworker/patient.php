<?php
require '../dbConnection.php';
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
$database = new Database();
$conn = $database->getConnection();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Patient</title>
        <meta name="description" content="Manage patients, view medical records, and interventions in the patient page.">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/pp.css">
    </head>
    <body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <!-- Left nav links -->
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
                <button type="button" class="btn btn-custom-add"data-bs-toggle="modal" data-bs-target="#addPatientModal">
                <i class="bi bi-plus-circle"></i>
                </button>
                <span class="ms-2">Add Patient</span>
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


        <div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="post" action="patient_crud.php" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPatientModalLabel">Add New Patient</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Age</label>
                            <input type="number" name="age" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Gender</label>
                            <input type="text" name="gender" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Parents</label>
                            <input type="text" name="parents" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>DOB</label>
                            <input type="date" name="dob" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Weight</label>
                            <input type="text" name="weight" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Height</label>
                            <input type="text" name="height" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Blood Type</label>
                            <input type="text" name="blood_type" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Reason</label>
                            <input type="text" name="reason" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_patient" class="btn btn-primary">Add Patient</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>

         <!-- Patient Table -->
<div class="table-responsive mt-4">
    <div class="table-container">
        <h4 class="fw-bold mt-3" style="text-align: left; font-size: 1.8rem; margin-left: 26px;">Patients Records</h4>
        <table class="table table-bordered table-hover bg-white table-intervention">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Parents</th>
                    <th>DOB</th>
                    <th>Weight</th>
                    <th>Height</th>
                    <th>Blood Type</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="patientTableBody">
                <?php
                try {
                    $query = "SELECT *, TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age FROM patients"; 
                    $stmt = $conn->prepare($query);
                    $stmt->execute();

                    while ($p = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= $p['patient_id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['age']) ?></td>
                        <td><?= htmlspecialchars($p['gender']) ?></td>
                        <td><?= htmlspecialchars($p['address']) ?></td>
                        <td><?= htmlspecialchars($p['parents']) ?></td>
                        <td><?= htmlspecialchars($p['dob']) ?></td>
                        <td><?= htmlspecialchars($p['weight']) ?></td>
                        <td><?= htmlspecialchars($p['height']) ?></td>
                        <td><?= htmlspecialchars($p['blood_type']) ?></td>
                        <td><?= htmlspecialchars($p['reason']) ?></td>
                        <td>
                            <!-- Edit Button -->
                            <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['patient_id'] ?>">Edit</button>

                            <!-- Delete Form -->
                            <form method="post" action="delete_patient.php" class="d-inline" onsubmit="return confirm('Delete this patient?');">
                                <input type="hidden" name="delete_id" value="<?= $p['patient_id'] ?>">
                                <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
                            </form>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $p['patient_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $p['patient_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="post" action="patient_crud.php" class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $p['id'] ?>">Edit Patient</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <div class="mb-3">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Age</label>
                                                <input type="number" name="age" class="form-control" value="<?= htmlspecialchars($p['age']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Gender</label>
                                                <input type="text" name="gender" class="form-control" value="<?= htmlspecialchars($p['gender']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Address</label>
                                                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($p['address']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Parents</label>
                                                <input type="text" name="parents" class="form-control" value="<?= htmlspecialchars($p['parents']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>DOB</label>
                                                <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($p['dob']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Weight</label>
                                                <input type="text" name="weight" class="form-control" value="<?= htmlspecialchars($p['weight']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Height</label>
                                                <input type="text" name="height" class="form-control" value="<?= htmlspecialchars($p['height']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Blood Type</label>
                                                <input type="text" name="blood_type" class="form-control" value="<?= htmlspecialchars($p['blood_type']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Reason</label>
                                                <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($p['reason']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php
                } catch (PDOException $e) {
                    echo "Error fetching patients: " . $e->getMessage();
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


        <div class="table-container">
        <div class="table-responsive mt-4">
        <h4 class="fw-bold intervention-heading">Intervention</h4>
        <table class="table table-bordered table-hover bg-white table-intervention">
                <thead class="table-light">
                    <tr>
                        <th>Intervention ID</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Reason</th>
                        <th>Intervention</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="interventionTableBody">
                    <?php
                    // Example dummy data (replace with data from view.php)
                    $interventions = [
                        ["id" => 1, "patient_name" => "John Doe", "doctor" => "Dr. Smith", "reason" => "Routine Checkup", "intervention" => "Vaccination"],
                        ["id" => 2, "patient_name" => "Jane Roe", "doctor" => "Dr. Johnson", "reason" => "Flu Symptoms", "intervention" => "Health Screening"]
                    ];

                    foreach ($interventions as $i): ?>
                    <tr>
                        <td><?= $i['id'] ?></td>
                        <td><?= htmlspecialchars($i['patient_name']) ?></td>
                        <td><?= htmlspecialchars($i['doctor']) ?></td>
                        <td><?= htmlspecialchars($i['reason']) ?></td>
                        <td><?= htmlspecialchars($i['intervention']) ?></td>
                        <td>
                            <!-- View Button -->
                            <button class="btn btn-custom-view btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?= $i['id'] ?>">View</button>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?= $i['id'] ?>" tabindex="-1" aria-labelledby="viewModalLabel<?= $i['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewModalLabel<?= $i['id'] ?>">Intervention Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Patient Name:</strong> <?= htmlspecialchars($i['patient_name']) ?></p>
                                        <p><strong>Doctor:</strong> <?= htmlspecialchars($i['doctor']) ?></p>
                                        <p><strong>Reason:</strong> <?= htmlspecialchars($i['reason']) ?></p>
                                        <p><strong>Intervention:</strong> <?= htmlspecialchars($i['intervention']) ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    
</div>
<script>
function showSection(id) {
    document.querySelectorAll('.content').forEach(div => {
        div.style.display = 'none';
    });
    document.getElementById(id).style.display = 'block';
}
</script>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.11/dist/sweetalert2.all.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
