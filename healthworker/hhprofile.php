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
    <title>Household Profiles</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="appointment.php">Appointments</a></li>
                <li class="nav-item"><a class="nav-link" href="patient.php">Patients</a></li>
                <li class="nav-item"><a class="nav-link active" href="hhprofile.php">Household Profiles</a></li>
            </ul>
            <div class="user-info">
                <div>
                    <a href="profile.php" style="text-decoration: none;font-size: 1.7rem; color: black;"><strong><?= htmlspecialchars($username) ?></strong></a><br>
                </div>
                <form method="POST">
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

    <!-- Add Modal -->
    <div class="modal fade" id="addHouseholdModal" tabindex="-1" aria-labelledby="addHouseholdModalLabel" aria-hidden="true">
        <div class="modal-dialog custom-modal-width">
            <form method="post" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Household</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
   <!-- Appointment Info -->
   <h5><strong>Appointment Information</strong></h5>
                <div class="mb-3">
                    <label for="patient_name" class="form-label">Patient Name</label>
                    <input type="text" name="patient_name" id="patient_name" class="form-control" required placeholder="Enter patient name">
                </div>
                <div class="mb-3">
                    <label for="appointment_date" class="form-label">Appointment Date</label>
                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="appointment_time" class="form-label">Appointment Time</label>
                    <input type="time" name="appointment_time" id="appointment_time" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="doctor" class="form-label">Doctor</label>
                    <select name="doctor" id="doctor" class="form-select" required>
                        <option value="Dr. Smith">Dr. Smith</option>
                        <option value="Dr. Lee">Dr. Lee</option>
                    </select>
                </div>

                <hr>

                <!-- Medical History (Optional) -->
                <h5><strong>Medical History (Optional)</strong></h5>
                <div class="mb-3">
                    <label for="medical_conditions" class="form-label">Existing Medical Conditions</label>
                    <textarea name="medical_conditions" id="medical_conditions" class="form-control" rows="3" placeholder="Enter any medical conditions (if any)"></textarea>
                </div>
                <div class="mb-3">
                    <label for="allergies" class="form-label">Allergies</label>
                    <textarea name="allergies" id="allergies" class="form-control" rows="3" placeholder="Enter any allergies (if any)"></textarea>
                </div>

                <hr>

                <!-- Emergency Contact Info -->
                <h5><strong>Emergency Contact</strong></h5>
                <div class="mb-3">
                    <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" placeholder="Enter emergency contact name" required>
                </div>
                <div class="mb-3">
                    <label for="emergency_contact_number" class="form-label">Emergency Contact Number</label>
                    <input type="text" name="emergency_contact_number" id="emergency_contact_number" class="form-control" placeholder="Enter emergency contact number" required>
                </div>
                <div class="mb-3">
                    <label for="emergency_contact_relationship" class="form-label">Relationship with Emergency Contact</label>
                    <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" class="form-control" placeholder="Enter relationship with emergency contact" required>
                </div>

                <hr>
                <p><strong>Date:</strong> <?= date("Y-m-d") ?></p>
            </div>
                <div class="modal-footer">
                    <button type="submit" name="add_household" class="btn btn-primary">Add</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>

   <!-- Table -->
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
                // Example dummy data (replace with data from view.php)
                $households = [
                    ["id" => 1, "head" => "John Doe","purok" => "3","nic" => "0948003385","date" => "2025-04-15", ],
                    ["id" => 2, "head" => "Jane Roe","purok" => "1","3","nic" => "0948077385","date" => "2025-04-16"]
                ];
            foreach ($households as $h): ?>
            <tr>
                <td><?= $h['id'] ?></td>
                <td><?= htmlspecialchars($h['head']) ?></td>
                <td><?= htmlspecialchars($h['purok']) ?></td>
                <td><?= htmlspecialchars($h['nic']) ?></td>
                <td><?= htmlspecialchars($h['date']) ?></td>
                <td>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#descModal<?= $h['id'] ?>">
                        View
                    </button>

                    <!-- View Description Modal -->
                    <div class="modal fade" id="descModal<?= $h['id'] ?>" tabindex="-1" aria-labelledby="descModalLabel<?= $h['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="descModalLabel<?= $h['id'] ?>">Household Description</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Household Info -->
                                    <h5><strong>Household Information</strong></h5>
                                    <p><strong>Head of Household:</strong> <?= htmlspecialchars($h['head']) ?></p>
                                    <p><strong>Address (Purok):</strong> <?= htmlspecialchars($h['purok']) ?></p>
                                    <p><strong>Contact Number:</strong> <?= htmlspecialchars($h['nic']) ?></p>
                                    <p><strong>Number of Household Members:</strong> <?= $h['id'] == 1 ? '5 members' : '3 members' ?></p>

                                    <hr>

                                    <!-- Household Members Details -->
                                    <h5><strong>Household Members Details</strong></h5>
                                    <ul>
                                        <li><strong>John Doe (Head)</strong>, Relation: Head, Age: 45, Sex: Male</li>
                                        <li><strong>Jane Doe</strong>, Relation: Spouse, Age: 42, Sex: Female</li>
                                        <li><strong>Jim Doe</strong>, Relation: Son, Age: 18, Sex: Male</li>
                                        <li><strong>Jill Doe</strong>, Relation: Daughter, Age: 16, Sex: Female</li>
                                    </ul>

                                    <hr>

                                    <!-- Health Information -->
                                    <h5><strong>Health Information</strong></h5>
                                    <p><strong>Existing Medical Condition:</strong> Hypertension, Asthma</p>
                                    <p><strong>Allergies:</strong> Pollen, Dust</p>

                                    <hr>

                                    <!-- Emergency Contact -->
                                    <h5><strong>Emergency Contact</strong></h5>
                                    <p><strong>Name:</strong> Sarah Doe</p>
                                    <p><strong>Contact Number:</strong> 09123456789</p>
                                    <p><strong>Relationship:</strong> Sister</p>

                                    <hr>
                                    <p><strong>Date:</strong> <?= date("Y-m-d") ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </td>
                <td>
                    <!-- Edit Button -->
                    <button class="btn btn-custom-edit btn-sm" data-bs-toggle="modal" data-bs-target="#editHouseholdModal<?= $h['id'] ?>">Edit</button>

                    <!-- Edit Household Profile Modal -->
                            <div class="modal fade" id="editHouseholdModal<?= $h['id'] ?>" tabindex="-1" aria-labelledby="editHouseholdModalLabel<?= $h['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog" style="max-width: 800px; width: 100%;"> <!-- Custom width for Edit Modal -->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editHouseholdModalLabel<?= $h['id'] ?>">Edit Household Profile</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Household Info -->
                                            <h5><strong>Household Information</strong></h5>
                                            <form method="post">
                                                <div class="mb-3">
                                                    <label>Head of Household</label>
                                                    <input type="text" name="head_name" class="form-control" value="<?= htmlspecialchars($h['head']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Address (Purok)</label>
                                                    <input type="text" name="purok" class="form-control" value="<?= htmlspecialchars($h['purok']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Contact Number</label>
                                                    <input type="text" name="nic_number" class="form-control" value="<?= htmlspecialchars($h['nic']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Number of Household Members</label>
                                                    <input type="number" name="num_members" class="form-control" value="<?= $h['id'] == 1 ? 5 : 3 ?>" required>
                                                </div>

                                                <hr>

                                                <!-- Household Members Details -->
                                                <h5><strong>Household Members Details</strong></h5>
                                                <div class="mb-3">
                                                    <label>Full Name (Head)</label>
                                                    <input type="text" name="member_name_head" class="form-control" value="John Doe" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Relation</label>
                                                    <input type="text" name="relation_head" class="form-control" value="Head" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Age</label>
                                                    <input type="number" name="age_head" class="form-control" value="45" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Sex</label>
                                                    <select name="sex_head" class="form-control" required>
                                                        <option value="Male" <?= $h['id'] == 1 ? 'selected' : '' ?>>Male</option>
                                                        <option value="Female" <?= $h['id'] == 2 ? 'selected' : '' ?>>Female</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label>Full Name (Spouse)</label>
                                                    <input type="text" name="member_name_spouse" class="form-control" value="Jane Doe" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Relation</label>
                                                    <input type="text" name="relation_spouse" class="form-control" value="Spouse" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Age</label>
                                                    <input type="number" name="age_spouse" class="form-control" value="42" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Sex</label>
                                                    <select name="sex_spouse" class="form-control" required>
                                                        <option value="Male" <?= $h['id'] == 1 ? '' : 'selected' ?>>Male</option>
                                                        <option value="Female" <?= $h['id'] == 2 ? 'selected' : '' ?>>Female</option>
                                                    </select>
                                                </div>

                                                <hr>

                                                <!-- Health Information -->
                                                <h5><strong>Health Information</strong></h5>
                                                <div class="mb-3">
                                                    <label>Existing Medical Condition</label>
                                                    <input type="text" name="medical_condition" class="form-control" value="Hypertension, Asthma">
                                                </div>
                                                <div class="mb-3">
                                                    <label>Allergies</label>
                                                    <input type="text" name="allergies" class="form-control" value="Pollen, Dust">
                                                </div>

                                                <hr>

                                                <!-- Emergency Contact -->
                                                <h5><strong>Emergency Contact</strong></h5>
                                                <div class="mb-3">
                                                    <label>Name</label>
                                                    <input type="text" name="emergency_contact_name" class="form-control" value="Sarah Doe">
                                                </div>
                                                <div class="mb-3">
                                                    <label>Contact Number</label>
                                                    <input type="text" name="emergency_contact_number" class="form-control" value="09123456789">
                                                </div>
                                                <div class="mb-3">
                                                    <label>Relationship</label>
                                                    <input type="text" name="emergency_contact_relation" class="form-control" value="Sister">
                                                </div>

                                                <hr>
                                                <div class="mb-3">
                                                    <label>Date</label>
                                                    <input type="date" name="consent_date" class="form-control" value="<?= date("Y-m-d") ?>" required>
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
                    <!-- Delete Button -->
                    <form method="post" class="d-inline" onsubmit="return confirm('Delete this household profile?');">
                        <input type="hidden" name="delete_id" value="<?= $h['id'] ?>">
                        <button type="submit" name="delete" class="btn btn-custom-delete btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
