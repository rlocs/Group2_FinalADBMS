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
    <title>Household Profiles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage HouseHold Profile.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/hhprofile.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
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

<!-- Content -->
<div class="container top-gap-after-navbar">
    <div class="d-flex justify-content-between align-items-center mb-2" style="margin: 0 26px;">
        <div class="d-flex align-items-center">
            <button type="button" name="add" class="btn add-appointment" data-bs-toggle="modal" data-bs-target="#addHouseholdModal">
                <i class="bi bi-plus-circle"></i>
            </button>
            <span class="ms-2">Add Household</span>
        </div>
        <form method="get" class="d-flex" style="max-width: 350px;">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by Head of Household" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-search">Search</button>
            </div>
        </form>
    </div>

   <!-- Add Household Modal -->
<div class="modal fade" id="addHouseholdModal" tabindex="-1" aria-labelledby="addHouseholdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="hh_crud.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addHouseholdModalLabel">Add Household Description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Household Info -->
                <h5><strong>Household Information</strong></h5>
                <div class="mb-3">
                    <label for="head_name" class="form-label">Head of Household</label>
                    <input type="text" name="head_name" id="head_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="purok" class="form-label">Address (Purok)</label>
                    <input type="text" name="purok" id="purok" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nic_number" class="form-label">NIC Number</label>
                    <input type="text" name="nic_number" id="nic_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="num_members" class="form-label">Number of Household Members</label>
                    <input type="number" name="num_members" id="num_members" class="form-control" required>
                </div>

                <hr>

                <h5>Household Member Details</h5>
                <div class="mb-3">
                <label for="member_name">Member Name:</label>
                <input type="text" name="member_name" id="member_name" required><br>

                <label for="relation">Relation to Head:</label>
                <input type="text" name="relation" id="relation" required><br>

                <label for="age">Age:</label>
                <input type="number" name="age" id="age" required><br>

                <label for="sex">Sex:</label>
                <select name="sex" id="sex" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select><br>
                </div>

                <hr>

                <!-- Health Information -->
                <h5><strong>Health Information</strong></h5>
                <div class="mb-3">
                    <label for="medical_condition" class="form-label">Existing Medical Condition</label>
                    <textarea name="medical_condition" id="medical_condition" class="form-control" rows="2" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="allergies" class="form-label">Allergies</label>
                    <textarea name="allergies" id="allergies" class="form-control" rows="2" required></textarea>
                </div>

                <hr>

                <!-- Emergency Contact -->
                <h5><strong>Emergency Contact</strong></h5>
                <div class="mb-3">
                    <label for="emergency_contact_name" class="form-label">Name</label>
                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="emergency_contact_number" class="form-label">Contact Number</label>
                    <input type="text" name="emergency_contact_number" id="emergency_contact_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="emergency_contact_relation" class="form-label">Relationship</label>
                    <input type="text" name="emergency_contact_relation" id="emergency_contact_relation" class="form-control" required>
                </div>

                <hr>

                <!-- Date -->
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" name="add_household" class="btn btn-primary">Add</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>


<!-- Household Profiles Table -->
<div class="table-responsive mt-4">
    <div class="table-container">
        <h4 class="fw-bold mt-3" style="margin-left: 10px;">Household Profiles</h4>
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Head of Household</th>
                    <th>Purok</th>
                    <th>NIC Number</th>
                    <th>Date Registered</th>
                    <th>View Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="hhprofileTableBody">
                <?php
                // Fetch household profiles from the database
                try {
                    // Prepare and execute the query to fetch household profiles
                    $sql = "
                    SELECT 
                        h.*, 
                        mi.medical_condition, 
                        mi.allergies, 
                        ec.emergency_contact_name, 
                        ec.emergency_contact_number, 
                        ec.emergency_contact_relation
                    FROM households h
                    LEFT JOIN medical_information mi ON h.household_id = mi.household_id
                    LEFT JOIN emergency_contacts ec ON h.household_id = ec.household_id
                ";                
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();

                    // Fetch the results
                    $households = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "Error: " . $e->getMessage();
                    exit;
                }
                ?>
                <?php if (count($households) > 0): ?>
                    <?php foreach ($households as $h): ?>
                        <tr>
                            <td><?= $h['household_id'] ?></td>
                            <td><?= htmlspecialchars($h['head_name']) ?></td>
                            <td><?= htmlspecialchars($h['purok']) ?></td>
                            <td><?= htmlspecialchars($h['nic_number']) ?></td>
                            <td><?= htmlspecialchars($h['created_at']) ?></td>
                            <td>
                                <!-- View Description Button -->
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#descModal<?= $h['household_id'] ?>">
                                    View
                                </button>
                            </td>
                            <td class="d-flex gap-2">
                                <!-- Edit Button -->
                                <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editHouseholdModal<?= $h['household_id'] ?>">Edit</button>

                                <!-- Delete Button -->
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this household profile?');">
                                    <input type="hidden" name="delete_id" value="<?= $h['household_id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
                                </form>
                            </td>

                            <!-- Modal for Viewing Household Details -->
                            <div class="modal fade" id="descModal<?= $h['household_id'] ?>" tabindex="-1" aria-labelledby="descModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="descModalLabel">Household Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h5>Head of Household: <?= htmlspecialchars($h['head_name']) ?></h5>
                                            <p><strong>Purok:</strong> <?= htmlspecialchars($h['purok']) ?></p>
                                            <p><strong>NIC Number:</strong> <?= htmlspecialchars($h['nic_number']) ?></p>
                                            <p><strong>Number of Members:</strong> <?= htmlspecialchars($h['num_members']) ?></p>

                                            <!-- Medical and Health Information -->
                                            <h6><strong>Medical Condition:</strong></h6>
                                            <p><?= htmlspecialchars($h['medical_condition']) ?: 'No conditions reported' ?></p>
                                            <h6><strong>Allergies:</strong></h6>
                                            <p><?= htmlspecialchars($h['allergies']) ?: 'No allergies reported' ?></p>

                                            <!-- Emergency Contact Information -->
                                            <h6><strong>Emergency Contact</strong></h6>
                                            <p><strong>Name:</strong> <?= htmlspecialchars($h['emergency_contact_name']) ?></p>
                                            <p><strong>Contact Number:</strong> <?= htmlspecialchars($h['emergency_contact_number']) ?></p>
                                            <p><strong>Relationship:</strong> <?= htmlspecialchars($h['emergency_contact_relation']) ?></p>
                                            <p><strong>Consent Date:</strong> <?= htmlspecialchars($h['created_at']) ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Household Profile Modal -->
                            <div class="modal fade" id="editHouseholdModal<?= $h['household_id'] ?>" tabindex="-1" aria-labelledby="editHouseholdModalLabel<?= $h['household_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog" style="max-width: 800px; width: 100%;"> <!-- Custom width for Edit Modal -->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editHouseholdModalLabel<?= $h['household_id'] ?>">Edit Household Profile</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="post">
                                                <!-- Household Info -->
                                                <div class="mb-3">
                                                    <label>Head of Household</label>
                                                    <input type="text" name="head_name" class="form-control" value="<?= htmlspecialchars($h['head_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Address (Purok)</label>
                                                    <input type="text" name="purok" class="form-control" value="<?= htmlspecialchars($h['purok']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Contact Number</label>
                                                    <input type="text" name="nic_number" class="form-control" value="<?= htmlspecialchars($h['nic_number']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Number of Household Members</label>
                                                    <input type="number" name="num_members" class="form-control" value="<?= $h['num_members'] ?>" required>
                                                </div>

                                                <!-- Health Information -->
                                                <div class="mb-3">
                                                    <label>Medical Condition</label>
                                                    <input type="text" name="medical_condition" class="form-control" value="<?= htmlspecialchars($h['medical_condition']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Allergies</label>
                                                    <input type="text" name="allergies" class="form-control" value="<?= htmlspecialchars($h['allergies']) ?>" required>
                                                </div>

                                                <!-- Emergency Contact -->
                                                <div class="mb-3">
                                                    <label>Emergency Contact Name</label>
                                                    <input type="text" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($h['emergency_contact_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Emergency Contact Number</label>
                                                    <input type="text" name="emergency_contact_number" class="form-control" value="<?= htmlspecialchars($h['emergency_contact_number']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Emergency Contact Relationship</label>
                                                    <input type="text" name="emergency_contact_relation" class="form-control" value="<?= htmlspecialchars($h['emergency_contact_relation']) ?>" required>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" name="update_household" class="btn btn-primary">Save Changes</button>
                                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                    </tr>
                <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No household profiles found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
