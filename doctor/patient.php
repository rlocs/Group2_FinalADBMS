<?php
require '../dbConnection.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 4;
$offset = ($page - 1) * $records_per_page;

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
$database = new Database();
$conn = $database->getConnection();

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $searchTerm = $_GET['search'] ?? '';
    $limit = $records_per_page;
    $offsetAjax = 0; // For live search, we can start from first page or implement pagination later

    if ($searchTerm) {
        $stmt = $conn->prepare("SELECT * FROM patients WHERE name LIKE CONCAT('%', :search_term, '%') ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':search_term', $searchTerm);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offsetAjax, PDO::PARAM_INT);
        $stmt->execute();
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT * FROM patients ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offsetAjax, PDO::PARAM_INT);
        $stmt->execute();
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    header('Content-Type: application/json');
    echo json_encode($patients);
    exit;
}
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
<script src="../healthworker/live.js"></script>
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
            <!-- Removed completely for doctor role -->

            <!-- Search Form -->
            <form method="get" class="d-flex" style="max-width: 350px;">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
<input type="text" id="searchPatient" name="search" class="form-control" placeholder="Search by Name" value="<?= htmlspecialchars($search) ?>">
<button type="button" class="btn btn-search">Search</button>
                </div>
            </form>
        </div>
    </div>


<!-- Removed completely for doctor role -->

         <!-- Patient Table -->
<div class="table-responsive mt-4">
    <div class="table-container">
        <h4 class="fw-bold mt-3" style="text-align: left; font-size: 1.8rem; margin-left: 26px;">Patients Records</h4>
        <table class="table table-bordered table-hover bg-white table-intervention">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
<!-- Removed Age column header as age is calculated from DOB -->
                    <th>Gender</th>
                    <th>Address</th>
                    <th>Parents</th>
                    <th>DOB</th>
                    <th>Weight(kg)</th>
                    <th>Height(cm)</th>
                    <th>Blood Type</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="patientTableBody">
                <?php
                try {
if ($search) {
    // Count total matching records
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE name LIKE CONCAT('%', :search_term, '%')");
    $count_stmt->bindValue(':search_term', $search);
    $count_stmt->execute();
    $total_patients = $count_stmt->fetchColumn();

    // Fetch paginated results
    $stmt = $conn->prepare("SELECT * FROM patients WHERE name LIKE CONCAT('%', :search_term, '%') ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':search_term', $search);
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} else {
    // Count total records
    $count_stmt = $conn->query("SELECT COUNT(*) FROM patients");
    $total_patients = $count_stmt->fetchColumn();

    // Fetch paginated results
    $stmt = $conn->prepare("SELECT * FROM patients ORDER BY patient_id ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$counter = $offset + 1;
function getRandomId(&$used_ids) {
    do {
        $rand_id = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    } while (in_array($rand_id, $used_ids));
    $used_ids[] = $rand_id;
    return $rand_id;
}
foreach ($patients as $p): ?>
                    <tr>
                        <td><?= $p['patient_id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['gender']) ?></td>
                        <td><?= htmlspecialchars($p['address']) ?></td>
                        <td><?= htmlspecialchars($p['parents']) ?></td>
                        <td><?= htmlspecialchars($p['dob']) ?></td>
                        <td><?= htmlspecialchars($p['weight']) ?></td>
                        <td><?= htmlspecialchars($p['height']) ?></td>
                        <td><?= htmlspecialchars($p['blood_type']) ?></td>
                        <td><?= htmlspecialchars($p['reason']) ?></td>
                        <td>
                            <!-- View Button -->
                            <button class="btn btn-custom-view btn-sm" data-bs-toggle="modal" data-bs-target="#viewModalPatient<?= $p['patient_id'] ?>">View</button>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModalPatient<?= $p['patient_id'] ?>" tabindex="-1" aria-labelledby="viewModalLabelPatient<?= $p['patient_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                <h5 class="modal-title" id="viewModalLabelPatient<?= $p['patient_id'] ?>">Patient Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>ID:</strong> <?= htmlspecialchars($p['patient_id']) ?></p>
                                            <p><strong>Name:</strong> <?= htmlspecialchars($p['name']) ?></p>
                                            <p><strong>Gender:</strong> <?= htmlspecialchars($p['gender']) ?></p>
                                            <p><strong>Address:</strong> <?= htmlspecialchars($p['address']) ?></p>
                                            <p><strong>Parents:</strong> <?= htmlspecialchars($p['parents']) ?></p>
                                            <p><strong>DOB:</strong> <?= htmlspecialchars($p['dob']) ?></p>
                                            <p><strong>Weight(kg):</strong> <?= htmlspecialchars($p['weight']) ?></p>
                                            <p><strong>Height(cm):</strong> <?= htmlspecialchars($p['height']) ?></p>
                                            <p><strong>Blood Type:</strong> <?= htmlspecialchars($p['blood_type']) ?></p>
                                            <p><strong>Reason:</strong> <?= htmlspecialchars($p['reason']) ?></p>
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
                <?php
                } catch (PDOException $e) {
                    echo "Error fetching patients: " . $e->getMessage();
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_patients > $records_per_page): ?>
        <nav aria-label="Page navigation example" class="mt-3" style="margin-left: 26px;">
<ul class="pagination justify-content-start">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
              <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
              </a>
            </li>
            <?php
            $total_pages = ceil($total_patients / $records_per_page);
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


        <div class="table-container">
        <div class="table-responsive mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3" style="padding-right: 60px;">
            <h4 class="fw-bold intervention-heading mb-0">Intervention</h4>
            <button type="button" class="btn btn-custom-add" data-bs-toggle="modal" data-bs-target="#addInterventionModal">Add Intervention</button>
        </div>
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
            $intervention_records_per_page = 4;
            $intervention_page = isset($_GET['intervention_page']) && is_numeric($_GET['intervention_page']) ? (int)$_GET['intervention_page'] : 1;
            $intervention_offset = ($intervention_page - 1) * $intervention_records_per_page;

            try {
                // Count total interventions
                $count_stmt = $conn->query("SELECT COUNT(*) FROM interventions");
                $total_interventions = $count_stmt->fetchColumn();

                // Fetch paginated interventions
                $stmt = $conn->prepare("SELECT i.intervention_id, p.name AS patient_name, i.doctor, i.reason, i.intervention FROM interventions i JOIN patients p ON i.patient_id = p.patient_id ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':limit', $intervention_records_per_page, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $intervention_offset, PDO::PARAM_INT);
                $stmt->execute();
                $interventions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error fetching interventions: " . $e->getMessage();
                $interventions = [];
                $total_interventions = 0;
            }

            foreach ($interventions as $i): ?>
            <tr>
                <td><?= $i['intervention_id'] ?></td>
                <td><?= htmlspecialchars($i['patient_name']) ?></td>
                <td><?= htmlspecialchars($i['doctor']) ?></td>
                <td><?= htmlspecialchars($i['reason']) ?></td>
                <td><?= htmlspecialchars($i['intervention']) ?></td>
                <td>
                    <!-- View Button -->
                    <button class="btn btn-custom-view btn-sm" data-bs-toggle="modal" data-bs-target="#viewModalIntervention<?= $i['intervention_id'] ?>">View</button>
                    <!-- Edit Button -->
                    <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $i['intervention_id'] ?>">Edit</button>

                <!-- View Modal -->
                <div class="modal fade" id="viewModalIntervention<?= $i['intervention_id'] ?>" tabindex="-1" aria-labelledby="viewModalLabelIntervention<?= $i['intervention_id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewModalLabelIntervention<?= $i['intervention_id'] ?>">Intervention Details</h5>
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

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $i['intervention_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $i['intervention_id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="post" action="intervention_crud.php" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel<?= $i['intervention_id'] ?>">Edit Intervention</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="intervention_id" value="<?= $i['intervention_id'] ?>">
                                <div class="mb-3">
                                    <label>Reason</label>
                                    <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($i['reason']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label>Intervention</label>
                                    <textarea name="intervention" class="form-control" rows="3" required><?= htmlspecialchars($i['intervention']) ?></textarea>
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
            <?php endforeach; ?>
        </tbody>
            </table>

            <!-- Pagination for interventions -->
            <?php if ($total_interventions > $intervention_records_per_page): ?>
            <nav aria-label="Intervention page navigation" class="mt-3" style="margin-left: 26px;">
                <ul class="pagination justify-content-start">
                    <li class="page-item <?= ($intervention_page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page ?>&intervention_page=<?= $intervention_page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>
                    <?php
                    $total_intervention_pages = ceil($total_interventions / $intervention_records_per_page);
                    for ($i = 1; $i <= $total_intervention_pages; $i++): ?>
                        <li class="page-item <?= ($i == $intervention_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page ?>&intervention_page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($intervention_page >= $total_intervention_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page ?>&intervention_page=<?= $intervention_page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Intervention Modal -->
    <div class="modal fade" id="addInterventionModal" tabindex="-1" aria-labelledby="addInterventionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="intervention_crud.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInterventionModalLabel">Add Intervention</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="patient_id" class="form-label">Patient</label>
                        <select name="patient_id" id="patient_id" class="form-select" required>
                            <option value="" disabled selected>Select a patient</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['patient_id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <input type="text" name="reason" id="reason" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="intervention" class="form-label">Intervention</label>
                        <textarea name="intervention" id="intervention" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="create" class="btn btn-primary">Add Intervention</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
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