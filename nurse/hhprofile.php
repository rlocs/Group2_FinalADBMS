<?php
require '../dbConnection.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Nurse') {
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
                    <!-- Removed Appointments nav item for Nurse role -->
                    <!--
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointment.php' ? 'active' : ''; ?>" href="appointment.php">Appointments</a>
                    </li>
                    -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'patient.php' ? 'active' : ''; ?>" href="patient.php">Patients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'hhprofile.php' ? 'active' : ''; ?>" href="hhprofile.php">Household Profiles</a>
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
    </div>
</nav>

<!-- Content -->
<div class="container top-gap-after-navbar">
    <div class="d-flex justify-content-between align-items-center mb-2" style="margin: 0 26px;">
        <div class="d-flex" style="max-width: 350px;">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="liveSearchHousehold" name="search" class="form-control" placeholder="Search by Head of Household" value="<?= htmlspecialchars($search) ?>">
                <button type="button" id="searchHouseholdButton" class="btn btn-search">Search</button>
            </div>
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
try {
    if ($search) {
        // Call stored procedure to count total matching records
        $count_stmt = $conn->prepare("CALL search_households_count(:search_term)");
        $count_stmt->bindValue(':search_term', $search);
        $count_stmt->execute();
        $total_households = $count_stmt->fetchColumn();
        $count_stmt->closeCursor();

        // Call stored procedure to fetch paginated results with search filter
        $stmt = $conn->prepare("CALL search_households_paginated(:search_term, :limit_val, :offset_val)");
        $stmt->bindValue(':search_term', $search);
        $stmt->bindValue(':limit_val', $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset_val', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $households = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } else {
        // Call stored procedure to count total records
        $count_stmt = $conn->prepare("CALL get_total_households_count(@total_households)");
        $count_stmt->execute();
        $count_stmt->closeCursor();

        $select_stmt = $conn->query("SELECT @total_households AS total_households");
        $total_households = $select_stmt->fetchColumn();

        // Call stored procedure to fetch paginated results without search filter
        $stmt = $conn->prepare("CALL get_all_households_paginated(:limit_val, :offset_val)");
        $stmt->bindValue(':limit_val', $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset_val', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $households = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    }
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


<!-- Modal for Viewing Household Details -->
<div class="modal fade" id="descModal<?= $h['household_id'] ?>" tabindex="-1" aria-labelledby="descModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                <h5><strong>Medical Condition:</strong></h5>
                <p><?= htmlspecialchars($h['medical_condition']) ?: 'No conditions reported' ?></p>
                <h5><strong>Allergies:</strong></h5>
                <p><?= htmlspecialchars($h['allergies']) ?: 'No allergies reported' ?></p>

                <!-- Emergency Contact Information -->
                <h5><strong>Emergency Contact</strong></h5>
                <p><strong>Name:</strong> <?= htmlspecialchars($h['emergency_contact_name']) ?></p>
                <p><strong>Contact Number:</strong> <?= htmlspecialchars($h['emergency_contact_number']) ?></p>
                <p><strong>Relationship:</strong> <?= htmlspecialchars($h['emergency_contact_relation']) ?></p>
                <p><strong>Consent Date:</strong> <?= htmlspecialchars($h['created_at']) ?></p>

                <hr>
                <h5><strong>Household Members</strong></h5>
                <?php
                $stmt_members = $conn->prepare("SELECT * FROM household_members WHERE household_id = :household_id");
                $stmt_members->bindParam(':household_id', $h['household_id'], PDO::PARAM_INT);
                $stmt_members->execute();
                $members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
                if ($members) {
                    foreach ($members as $member) {
                        ?>
                        <div class="mb-3 border p-3 rounded">
                            <p><strong>Name:</strong> <?= htmlspecialchars($member['member_name']) ?></p>
                            <p><strong>Relation:</strong> <?= htmlspecialchars($member['relation']) ?></p>
                            <p><strong>Age:</strong> <?= htmlspecialchars($member['age']) ?></p>
                            <p><strong>Sex:</strong> <?= htmlspecialchars($member['sex']) ?></p>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <p>No household members found.</p>
                    <?php
                }
                ?>
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
                                        <form method="post" action="hh_crud.php">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editHouseholdModalLabel<?= $h['household_id'] ?>">Edit Household Description</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Household Info -->
                                                <input type="hidden" name="household_id" value="<?= $h['household_id'] ?>">
                                                <div class="mb-3">
                                                    <label>Head of Household</label>
                                                    <input type="text" name="head_name" class="form-control" value="<?= htmlspecialchars($h['head_name']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Address (Purok)</label>
                                                    <input type="text" name="purok" class="form-control" value="<?= htmlspecialchars($h['purok']) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label>NIC Number</label>
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

                                                <hr>

                                                <h5>Household Member Details</h5>
                                                <div id="editHouseholdMembersContainer<?= $h['household_id'] ?>">
                                                    <?php
                                                    // Fetch household members for this household
                                                    $stmt_members = $conn->prepare("SELECT * FROM household_members WHERE household_id = :household_id");
                                                    $stmt_members->bindParam(':household_id', $h['household_id'], PDO::PARAM_INT);
                                                    $stmt_members->execute();
                                                    $members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
                                                    if ($members) {
                                                        foreach ($members as $member) {
                                                            ?>
                                                            <div class="household-member mb-3 border p-3 rounded">
                                                                <label>Member Name:</label>
                                                                <input type="text" name="member_name[]" class="form-control mb-2" value="<?= htmlspecialchars($member['member_name']) ?>" required>

                                                                <label>Relation to Head:</label>
                                                                <input type="text" name="relation[]" class="form-control mb-2" value="<?= htmlspecialchars($member['relation']) ?>" required>

                                                                <label>Age:</label>
                                                                <input type="number" name="age[]" class="form-control mb-2" value="<?= htmlspecialchars($member['age']) ?>" required>

                                                                <label>Sex:</label>
                                                                <select name="sex[]" class="form-select mb-2" required>
                                                                    <option value="Male" <?= $member['sex'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                                                    <option value="Female" <?= $member['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                                                    <option value="Other" <?= $member['sex'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                                                </select>

                                                                <button type="button" class="btn btn-danger btn-sm remove-member-btn">Remove</button>
                                                            </div>
                                                            <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <div class="household-member mb-3 border p-3 rounded">
                                                            <label>Member Name:</label>
                                                            <input type="text" name="member_name[]" class="form-control mb-2" required>

                                                            <label>Relation to Head:</label>
                                                            <input type="text" name="relation[]" class="form-control mb-2" required>

                                                            <label>Age:</label>
                                                            <input type="number" name="age[]" class="form-control mb-2" required>

                                                            <label>Sex:</label>
                                                            <select name="sex[]" class="form-select mb-2" required>
                                                                <option value="Male">Male</option>
                                                                <option value="Female">Female</option>
                                                                <option value="Other">Other</option>
                                                            </select>

                                                            <button type="button" class="btn btn-danger btn-sm remove-member-btn">Remove</button>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <button type="button" id="addEditMemberBtn<?= $h['household_id'] ?>" class="btn btn-success btn-sm mb-3">
                                                    <i class="bi bi-plus-circle"></i> Add Member
                                                </button>

                                                <hr>

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
        <?php
        // Pagination controls
        $total_pages = ceil($total_households / $records_per_page);
        if ($total_pages > 1) {
            echo '<nav aria-label="Page navigation example">';
            echo '<ul class="pagination justify-content-start">';  // Left aligned pagination
            // Previous button
            $prev_page = max(1, $page - 1);
            $prev_disabled = ($page <= 1) ? ' disabled' : '';
            echo '<li class="page-item' . $prev_disabled . '">';
            echo '<a class="page-link" href="?search=' . urlencode($search) . '&page=' . $prev_page . '" tabindex="-1">Previous</a>';
            echo '</li>';

            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? ' active' : '';
                echo '<li class="page-item' . $active . '"><a class="page-link" href="?search=' . urlencode($search) . '&page=' . $i . '">' . $i . '</a></li>';
            }

            // Next button
            $next_page = min($total_pages, $page + 1);
            $next_disabled = ($page >= $total_pages) ? ' disabled' : '';
            echo '<li class="page-item' . $next_disabled . '">';
            echo '<a class="page-link" href="?search=' . urlencode($search) . '&page=' . $next_page . '">Next</a>';
            echo '</li>';

            echo '</ul>';
            echo '</nav>';
        }
        ?>
    </div>
</div>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="live.js"></script>
</body>
</html>
