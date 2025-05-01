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

// Handle Edit Patient Form Submission directly in this file
if (isset($_POST['update_patient'])) {
    try {
        $patient_id = $_POST['patient_id'];
        $name = $_POST['name'];
        $gender = $_POST['gender'];
        $address = $_POST['address'];
        $parents = $_POST['parents'];
        $dob = $_POST['dob'];
        $weight = $_POST['weight'];
        $height = $_POST['height'];
        $blood_type = $_POST['blood_type'];
        $reason = $_POST['reason'];
        
        // Debug - can uncomment this to see what's being submitted
        /*
        echo "Updating patient:<br>";
        echo "ID: $patient_id<br>";
        echo "Blood Type: $blood_type<br>";
        exit;
        */

        $query = "UPDATE patients SET 
                  name = :name, 
                  gender = :gender, 
                  address = :address, 
                  parents = :parents, 
                  dob = :dob, 
                  weight = :weight, 
                  height = :height, 
                  blood_type = :blood_type, 
                  reason = :reason 
                  WHERE patient_id = :patient_id";
                  
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':parents', $parents);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':weight', $weight);
        $stmt->bindParam(':height', $height);
        $stmt->bindParam(':blood_type', $blood_type, PDO::PARAM_STR); // Explicitly set as string
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':patient_id', $patient_id);

        if ($stmt->execute()) {
            header("Location: patient.php?success=updated");
            exit;
        } else {
            header("Location: patient.php?error=update-failed&msg=" . urlencode($stmt->errorInfo()[2]));
            exit;
        }
    } catch (PDOException $e) {
        header("Location: patient.php?error=update-failed&msg=" . urlencode($e->getMessage()));
        exit;
    }
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
        <!-- Success/Error Alert Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                switch($_GET['success']) {
                    case 'added':
                        echo 'Patient has been added successfully.';
                        break;
                    case 'updated':
                        echo 'Patient information has been updated successfully.';
                        break;
                    case 'deleted':
                        echo 'Patient has been deleted successfully.';
                        break;
                    default:
                        echo 'Operation completed successfully.';
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                switch($_GET['error']) {
                    case 'add-failed':
                        echo 'Failed to add patient. ';
                        break;
                    case 'update-failed':
                        echo 'Failed to update patient information. ';
                        break;
                    case 'delete-failed':
                        echo 'Failed to delete patient. ';
                        break;
                    default:
                        echo 'Operation failed. ';
                }
                
                if (isset($_GET['msg'])) {
                    echo htmlspecialchars(urldecode($_GET['msg']));
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

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
                            <label>Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
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
                            <input type="text" name="weight" class="form-control" placeholder="Ex: 65 kg" required>
                        </div>
                        <div class="mb-3">
                            <label>Height</label>
                            <input type="text" name="height" class="form-control" placeholder="Ex: 175 cm" required>
                        </div>
                        <div class="mb-3">
                            <label>Blood Type</label>
                            <select name="blood_type" class="form-control" required>
                                <option value="">Select Blood Type</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" required></textarea>
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
                    
                    // Add search functionality
                    if (!empty($search)) {
                        $query .= " WHERE name LIKE :search";
                    }
                    
                    $stmt = $conn->prepare($query);
                    
                    // Bind search parameter if needed
                    if (!empty($search)) {
                        $searchTerm = "%$search%";
                        $stmt->bindParam(':search', $searchTerm);
                    }
                    
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
                            <button class="btn btn-custom-edit btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal<?= $p['patient_id'] ?>"
                                    data-patient-id="<?= $p['patient_id'] ?>"
                                    data-name="<?= htmlspecialchars($p['name']) ?>"
                                    data-gender="<?= htmlspecialchars($p['gender']) ?>"
                                    data-address="<?= htmlspecialchars($p['address']) ?>"
                                    data-parents="<?= htmlspecialchars($p['parents']) ?>"
                                    data-dob="<?= htmlspecialchars($p['dob']) ?>"
                                    data-weight="<?= htmlspecialchars($p['weight']) ?>"
                                    data-height="<?= htmlspecialchars($p['height']) ?>"
                                    data-blood-type="<?= htmlspecialchars($p['blood_type']) ?>"
                                    data-reason="<?= htmlspecialchars($p['reason']) ?>">
                                    Edit
                            </button>

                            <!-- Delete Form -->
                            <form method="post" action="delete_patient.php" class="d-inline" onsubmit="return confirm('Delete this patient?');">
                                <input type="hidden" name="delete_id" value="<?= $p['patient_id'] ?>">
                                <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
                            </form>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?= $p['patient_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $p['patient_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="post" action="" class="modal-content"> <!-- Changed action to empty to handle in this file -->
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel<?= $p['patient_id'] ?>">Edit Patient</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="patient_id" value="<?= $p['patient_id'] ?>">
                                            <div class="mb-3">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Gender</label>
                                                <select name="gender" class="form-control" required>
                                                    <option value="">Select Gender</option>
                                                    <option value="Male" <?= $p['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                                    <option value="Female" <?= $p['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                                    <option value="Other" <?= $p['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                                </select>
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
                                                <select name="blood_type" class="form-control" required>
                                                    <option value="">Select Blood Type</option>
                                                    <!-- Debug output to see what's happening -->
                                                    <!-- <?php echo "Current blood type: " . $p['blood_type']; ?> -->
                                                    <option value="A+" <?= trim($p['blood_type']) == 'A+' ? 'selected' : '' ?>>A+</option>
                                                    <option value="A-" <?= trim($p['blood_type']) == 'A-' ? 'selected' : '' ?>>A-</option>
                                                    <option value="B+" <?= trim($p['blood_type']) == 'B+' ? 'selected' : '' ?>>B+</option>
                                                    <option value="B-" <?= trim($p['blood_type']) == 'B-' ? 'selected' : '' ?>>B-</option>
                                                    <option value="AB+" <?= trim($p['blood_type']) == 'AB+' ? 'selected' : '' ?>>AB+</option>
                                                    <option value="AB-" <?= trim($p['blood_type']) == 'AB-' ? 'selected' : '' ?>>AB-</option>
                                                    <option value="O+" <?= trim($p['blood_type']) == 'O+' ? 'selected' : '' ?>>O+</option>
                                                    <option value="O-" <?= trim($p['blood_type']) == 'O-' ? 'selected' : '' ?>>O-</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Reason</label>
                                                <textarea name="reason" class="form-control" required><?= htmlspecialchars($p['reason']) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update_patient" class="btn btn-primary">Save Changes</button>
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

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Dynamic data loading for edit modals
    document.querySelectorAll('.btn-custom-edit').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.querySelector(this.getAttribute('data-bs-target'));
            if (modal) {
                // Get the patient ID and other details
                const patientId = this.getAttribute('data-patient-id');
                const bloodType = this.getAttribute('data-blood-type');
                
                console.log("Opening edit modal for patient ID: " + patientId);
                console.log("Blood type: " + bloodType);
                
                // Ensure blood type is properly selected in dropdown
                if (modal && bloodType) {
                    const bloodTypeSelect = modal.querySelector('select[name="blood_type"]');
                    if (bloodTypeSelect) {
                        // First reset all options
                        Array.from(bloodTypeSelect.options).forEach(option => {
                            option.selected = false;
                        });
                        
                        // Then find and select the matching option
                        Array.from(bloodTypeSelect.options).forEach(option => {
                            if (option.value === bloodType.trim()) {
                                option.selected = true;
                            }
                        });
                    }
                }
            }
        });
    });
});
</script>

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.11/dist/sweetalert2.all.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>